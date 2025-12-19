<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Services\Core\AbstractSchedulableService;
use Roster\Traits\FilterableTrait;

class ScheduleService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected ValidationServiceInterface $validationService;
    protected AvailabilityRepositoryInterface $availabilityRepository;
    protected ImpedimentRepositoryInterface $impedimentRepository;
    protected ScheduleRepositoryInterface $scheduleRepository;
    protected SlotFinderInterface $slotFinder;
    protected ?Schedule $currentSchedule = null;

    public function __construct(
        ValidationServiceInterface $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository,
        ScheduleRepositoryInterface $scheduleRepository,
        SlotFinderInterface $slotFinder
    ) {
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->slotFinder = $slotFinder;
    }

    // ========== HOOK METHODS IMPLEMENTATION ==========

    protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void {
        if (isset($this->data['start_datetime'], $this->data['end_datetime'])) {
            $start = Carbon::parse($this->data['start_datetime']);
            $end = Carbon::parse($this->data['end_datetime']);

            if ($start->diffInMinutes($end) < $minScheduleMinutes) {
                $this->throwMinimumDurationException($minScheduleMinutes);
            }
        }
    }

    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        // Not applicable for schedules
    }

    protected function getValidationService(): ValidationServiceInterface
    {
        return $this->validationService;
    }

    protected function validateBeforeCreate(): void
    {
        $this->validateScheduleData();
        $availability = $this->findMatchingAvailability();

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        if ($this->scheduleRepository->hasOverlappingSchedule($availability->id, $start, $end)) {
            throw new OverlappingScheduleException(
                TimeSlotOverlapType::SCHEDULE_OVERLAP,
                [
                    'availability_id' => $availability->id,
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        if ($this->impedimentRepository->hasOverlappingImpediments($availability->id, $start, $end)) {
            throw new ScheduleImpedimentOverlapException(
                TimeSlotOverlapType::SCHEDULE_IMPEDIMENT_CONFLICT,
                [
                    'availability_id' => $availability->id,
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        $this->data['availability_id'] = $availability->id;
    }

    protected function processBeforeCreate(): void
    {
        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }
    }

    protected function executeCreate(): Schedule
    {
        return $this->scheduleRepository->create($this->data);
    }

    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentSchedule = $this->find($id);

        if (!$this->currentSchedule instanceof Schedule) {
            $this->throwNotFoundException();
        }

        if ($this->data === []) {
            return;
        }

        // Validate time range if datetime fields are being updated
        if (isset($this->data['start_datetime']) || isset($this->data['end_datetime'])) {
            $validationData = array_merge(
                [
                    'start_datetime' => $this->currentSchedule->start_datetime,
                    'end_datetime' => $this->currentSchedule->end_datetime,
                ],
                $this->data
            );
            $this->validationService->parseAndValidateDateTimeRange($validationData);
        }

        // If dates change, check new availability
        if (isset($this->data['start_datetime'])) {
            $newAvailability = $this->findMatchingAvailability();
            if (!$newAvailability instanceof Availability) {
                throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
            }

            ['start' => $start, 'end' => $end] = $this->validationService
                ->parseAndValidateDateTimeRange(array_merge([
                    'start_datetime' => $this->currentSchedule->start_datetime,
                    'end_datetime' => $this->currentSchedule->end_datetime,
                ], $this->data));

            if ($this->scheduleRepository->hasOverlappingSchedule($newAvailability->id, $start, $end, $id)) {
                $this->throwOverlapException();
            }

            if ($this->impedimentRepository->hasOverlappingImpediments($newAvailability->id, $start, $end)) {
                throw ValidationException::withMessage('Schedule overlaps with an impediment');
            }

            if ($newAvailability->id !== $this->currentSchedule->availability_id) {
                $this->data['availability_id'] = $newAvailability->id;
            }
        }
    }

    protected function processBeforeUpdate(int $id): void
    {
        // Additional processing if needed
    }

    protected function executeUpdate(int $id): bool
    {
        return $this->scheduleRepository->update($id, $this->data);
    }

    // ========== ORIGINAL METHODS ==========

    public function find(int $id): ?Schedule
    {
        $this->validateSchedulable();
        return $this->scheduleRepository->findById($id);
    }

    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $schedule = $this->find($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        return $this->scheduleRepository->delete($id);
    }

    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        return $this->scheduleRepository->getForDateRange(
            $this->schedulable->id,
            get_class($this->schedulable),
            $start,
            $end,
            $this->filters
        );
    }

    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlotWithPartialOverlaps(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        return $availability instanceof Availability
            && !$availability->has_overlapping_schedules
            && !$availability->has_overlapping_impediments;
    }

    public function isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        return $this->slotFinder->isPeriodAvailable($this->schedulable, $start, $end, $type);
    }

    public function findFirstAvailablePeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        return $this->slotFinder->findFirstAvailablePeriod(
            $this->schedulable,
            $startDate,
            $endDate,
            $durationMinutes,
            $type
        );
    }

    public function findNextAvailableSlot(int $durationMinutes, ?string $type = null): ?array
    {
        $this->validateSchedulable();

        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        return $this->slotFinder->findNextSlot($this->schedulable, $durationMinutes, $type);
    }

    protected function validateScheduleData(): void
    {
        ['start' => $start] = $this->validationService->parseAndValidateDateTimeRange($this->data);
        $this->validationService->validateFutureDate($start);
    }

    protected function findMatchingAvailability(): ?Availability
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        $type = $this->data['type'] ?? null;
        return $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);
    }

    protected function applyFilters(): Builder
    {
        return $this->scheduleRepository->applyFilters(
            $this->schedulable->id,
            get_class($this->schedulable),
            $this->filters
        );
    }
}
