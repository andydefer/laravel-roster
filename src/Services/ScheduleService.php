<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Traits\FilterableTrait;

class ScheduleService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected ?Model $schedulable = null;

    protected ValidationServiceInterface $validationService;

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    protected ScheduleRepositoryInterface $scheduleRepository;

    protected SlotFinderInterface $slotFinder;

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

    /**
     * Create a new schedule.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Schedule
    {
        $this->validateSchedulable();
        $this->validateScheduleData($data);

        // Find matching availability
        $availability = $this->findMatchingAvailability($data);

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        // Check for overlapping schedules and impediments
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        if ($this->scheduleRepository->hasOverlappingSchedule($availability->id, $start, $end)) {
            throw new OverlappingScheduleException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
        }

        if ($this->impedimentRepository->hasOverlappingImpediment($availability->id, $start, $end)) {
            throw new ScheduleImpedimentOverlapException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
        }

        // Prepare data for creation
        $scheduleData = array_merge($data, [
            'availability_id' => $availability->id,
        ]);

        // Delegate to repository
        return $this->scheduleRepository->create($scheduleData);
    }

    /**
     * Update an existing schedule.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $schedule = $this->find($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        // Validate time range if datetime fields are being updated
        if (isset($data['start_datetime']) || isset($data['end_datetime'])) {
            $validationData = array_merge(
                [
                    'start_datetime' => $schedule->start_datetime,
                    'end_datetime' => $schedule->end_datetime,
                ],
                $data
            );
            $this->validationService->parseAndValidateDateTimeRange($validationData);
        }

        // If dates change, check new availability
        if ($data !== [] && isset($data['start_datetime'])) {
            $newAvailability = $this->findMatchingAvailability($data);
            if (!$newAvailability instanceof Availability) {
                throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
            }

            // Check for overlaps with new availability
            ['start' => $start, 'end' => $end] = $this->validationService
                ->parseAndValidateDateTimeRange(array_merge([
                    'start_datetime' => $schedule->start_datetime,
                    'end_datetime' => $schedule->end_datetime,
                ], $data));

            if ($this->scheduleRepository->hasOverlappingSchedule($newAvailability->id, $start, $end, $id)) {
                throw ValidationException::withMessage('Schedule overlaps with another schedule');
            }

            if ($this->impedimentRepository->hasOverlappingImpediment($newAvailability->id, $start, $end)) {
                throw ValidationException::withMessage('Schedule overlaps with an impediment');
            }

            if ($newAvailability->id !== $schedule->availability_id) {
                $data['availability_id'] = $newAvailability->id;
            }
        }

        // Delegate to repository
        return $this->scheduleRepository->update($id, $data);
    }

    /**
     * Delete a schedule.
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $schedule = $this->find($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        // Delegate to repository
        return $this->scheduleRepository->delete($id);
    }

    /**
     * Find a schedule by its ID.
     */
    public function find(int $id): ?Schedule
    {
        $this->validateSchedulable();

        // Delegate to repository
        return $this->scheduleRepository->findById($id);
    }

    /**
     * Get schedules for a given period.
     *
     * @return Collection<int, Schedule>
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        // Delegate to repository
        return $this->scheduleRepository->getBetweenDates(
            $this->schedulable->id,
            get_class($this->schedulable),
            $start,
            $end,
            $this->filters
        );
    }

    /**
     * Check availability for a time slot.
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        // Requête unique avec jointures
        $availability = $this->availabilityRepository->findForTimeSlotWithOverlaps(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        return $availability instanceof Availability && !$availability->has_overlapping_schedules && !$availability->has_overlapping_impediments;
    }



    /**
     * Check if a time period is completely available (no schedules or impediments).
     */
    public function isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        return $this->slotFinder->isPeriodAvailable($this->schedulable, $start, $end, $type);
    }

    /**
     * Get the first available period of a specific duration.
     */
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

    /**
     * Find the next available time slot.
     */
    public function findNextAvailableSlot(int $durationMinutes, ?string $type = null): ?array
    {
        $this->validateSchedulable();

        // Validate duration is positive
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        return $this->slotFinder->findNextAvailableSlot($this->schedulable, $durationMinutes, $type);
    }

    /**
     * Find all available slots in a period.
     */
    public function findAvailableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        // Validate duration is positive
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        // Delegate to ScheduleSlotFinder
        return $this->slotFinder->findAvailableSlots(
            $this->schedulable,
            $startDate,
            $endDate,
            $durationMinutes,
            $type
        );
    }

    /**
     * Validate schedule data including time range.
     *
     * @param array<string, mixed> $data
     */
    protected function validateScheduleData(array $data): void
    {
        ['start' => $start] = $this->validationService->parseAndValidateDateTimeRange($data);
        $this->validationService->validateFutureDate($start);
    }

    /**
     * Find matching availability for a schedule.
     *
     * @param array<string, mixed> $data
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        $type = $data['type'] ?? null;
        return $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);
    }

    /**
     * Apply filters to the query.
     * Note: Cette méthode n'est plus utilisée directement car on délègue au repository
     * Elle est gardée pour compatibilité avec le trait FilterableTrait
     *
     * @return Builder
     */
    protected function applyFilters()
    {
        // Délégation au repository
        return $this->scheduleRepository->applyFilters(
            $this->schedulable->id,
            get_class($this->schedulable),
            $this->filters
        );
    }
}
