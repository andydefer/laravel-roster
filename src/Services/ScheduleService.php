<?php

declare(strict_types=1);

namespace Roster\Services;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\Core\AbstractSchedulableService;
use Roster\Traits\FilterableTrait;

/**
 * Service for managing schedules within the roster system.
 *
 * Handles creation, updates, deletion, and validation of schedules,
 * ensuring they don't overlap with existing schedules or impediments.
 */
class ScheduleService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected ValidationServiceInterface $validationService;
    protected AvailabilityRepositoryInterface $availabilityRepository;
    protected ImpedimentRepositoryInterface $impedimentRepository;
    protected ScheduleRepositoryInterface $scheduleRepository;
    protected SlotFinderInterface $slotFinder;
    protected ?Schedule $currentSchedule = null;

    /**
     * Initialize the schedule service with required dependencies.
     */
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
     * Validate that schedule duration meets minimum requirements.
     *
     * @param string $operation The operation being performed
     * @param int $minImpedimentMinutes Minimum impediment minutes (unused)
     * @param int $minScheduleMinutes Minimum schedule minutes required
     * @param int $defaultDurationMinutes Default duration (unused)
     *
     * @throws ValidationException When duration is below minimum
     */
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

    /**
     * Validate maximum days constraint (not applicable for schedules).
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        // Not applicable for schedules
    }

    /**
     * Get the validation service instance.
     */
    protected function getValidationService(): ValidationServiceInterface
    {
        return $this->validationService;
    }

    /**
     * Validate schedule data before creation.
     *
     * @throws ValidationException When validation fails
     * @throws OverlappingScheduleException When schedule overlaps existing schedule
     * @throws ScheduleImpedimentOverlapException When schedule overlaps with impediment
     */
    protected function validateBeforeCreate(): void
    {
        $this->validateScheduleData();

        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        if ($this->scheduleRepository->hasOverlappingSchedule($this->data['availability_id'], $start, $end)) {
            throw new OverlappingScheduleException(
                TimeSlotOverlapType::SCHEDULE_OVERLAP,
                [
                    'availability_id' => $this->data['availability_id'],
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        if ($this->impedimentRepository->hasOverlappingImpediments($this->data['availability_id'], $start, $end)) {
            throw new ScheduleImpedimentOverlapException(
                TimeSlotOverlapType::SCHEDULE_IMPEDIMENT_CONFLICT,
                [
                    'availability_id' => $this->data['availability_id'],
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * Process schedule data before creation.
     */
    protected function processBeforeCreate(): void
    {
        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }
    }

    /**
     * Execute the schedule creation.
     */
    protected function executeCreate(): Schedule
    {
        return $this->scheduleRepository->create($this->data);
    }

    /**
     * Validate schedule data before update.
     *
     * @param int $id Schedule ID to update
     *
     * @throws ValidationException When validation fails or schedule not found
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentSchedule = $this->find($id);

        if (!$this->currentSchedule instanceof Schedule) {
            $this->throwNotFoundException();
        }

        if ($this->data === []) {
            return;
        }

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

    /**
     * Process schedule data before update.
     */
    protected function processBeforeUpdate(int $id): void
    {
        // Additional processing if needed
    }

    /**
     * Execute the schedule update.
     *
     * @param int $id Schedule ID to update
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->scheduleRepository->update($id, $this->data);
    }

    /**
     * Create a new schedule with explicit availability.
     *
     * @param Availability|array<string, mixed> $availabilityOrData Availability instance or data array
     * @param array<string, mixed>|null $data Data array if first param is Availability
     *
     * @return Schedule Created schedule
     *
     * @throws BadMethodCallException When using deprecated signature
     * @throws InvalidArgumentException When arguments are invalid
     * @throws ValidationException When validation fails
     */
    public function create($availabilityOrData, ?array $data = null): Schedule
    {
        if ($availabilityOrData instanceof Availability && $data !== null) {
            return $this->createWithAvailability($availabilityOrData, $data);
        }

        if (is_array($availabilityOrData) && $data === null) {
            throw new BadMethodCallException(
                'Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.'
            );
        }

        throw new InvalidArgumentException('Invalid arguments for create method');
    }

    /**
     * Create a new schedule with explicit availability.
     *
     * @param Availability $availability The availability to link to
     * @param array<string, mixed> $data Schedule data
     *
     * @return Schedule Created schedule
     *
     * @throws ValidationException When validation fails
     */
    private function createWithAvailability(Availability $availability, array $data): Schedule
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->validateAvailabilityOwnership($availability);
        $this->data['availability_id'] = $availability->id;
        $this->data = $this->applyConfigurationRules($this->data, 'create');
        $this->validateConfigurationRules('create');
        $this->validateBeforeCreate();
        $this->processBeforeCreate();

        $schedule = $this->executeCreate();
        $this->afterCreate($schedule);

        return $schedule;
    }

    /**
     * Validate that the availability belongs to the current schedulable.
     *
     * @param Availability $availability The availability to validate
     *
     * @throws ValidationException When availability doesn't belong to schedulable
     */
    private function validateAvailabilityOwnership(Availability $availability): void
    {
        if (
            $availability->schedulable_id !== $this->schedulable->id ||
            $availability->schedulable_type !== get_class($this->schedulable)
        ) {
            throw new ValidationException(ValidationType::INVALID_AVAILABILITY);
        }
    }

    /**
     * Find a schedule by ID.
     *
     * @param int $id Schedule ID
     */
    public function find(int $id): ?Schedule
    {
        $this->validateSchedulable();
        return $this->scheduleRepository->find($id);
    }

    /**
     * Delete a schedule by ID.
     *
     * @param int $id Schedule ID
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $schedule = $this->find($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        return $this->scheduleRepository->delete($id);
    }

    /**
     * Get schedules between two dates.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     *
     * @return Collection<int, Schedule> Schedules in the date range
     */
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

    /**
     * Check if a specific time slot is available for scheduling.
     *
     * @param Carbon $start Start time
     * @param Carbon $end End time
     * @param string|null $type Optional availability type filter
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlotWithConflictInfo(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        return $availability instanceof Availability
            && !$availability->has_overlapping_schedules
            && !$availability->has_overlapping_impediments;
    }

    /**
     * Check if an entire period is available for scheduling.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @param string|null $type Optional availability type filter
     */
    public function isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        return $this->slotFinder->isPeriodAvailable($this->schedulable, $start, $end, $type);
    }

    /**
     * Find the first available period within a date range.
     *
     * @param Carbon $startDate Start of search range
     * @param Carbon $endDate End of search range
     * @param int $durationMinutes Required duration in minutes
     * @param string|null $type Optional availability type filter
     *
     * @return array<string, Carbon>|null Array with 'start' and 'end' keys or null if no period found
     *
     * @throws ValidationException When duration is invalid
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
     * Find the next available time slot from now.
     *
     * @param int $durationMinutes Required duration in minutes
     * @param string|null $type Optional availability type filter
     *
     * @return array<string, Carbon>|null Array with 'start' and 'end' keys or null if no slot found
     *
     * @throws ValidationException When duration is invalid
     */
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

    /**
     * Validate basic schedule data requirements.
     *
     * @throws ValidationException When schedule data is invalid
     */
    protected function validateScheduleData(): void
    {
        ['start' => $start] = $this->validationService->parseAndValidateDateTimeRange($this->data);
        $this->validationService->validateFutureDate($start);
    }

    /**
     * Find availability matching the current data.
     */
    protected function findMatchingAvailability(): ?Availability
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        $type = $this->data['type'] ?? null;

        return $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);
    }

    /**
     * Build a query with applied filters.
     */
    protected function buildQueryWithFilters(): Builder
    {
        return $this->scheduleRepository->buildQueryWithFilters(
            $this->schedulable->id,
            get_class($this->schedulable),
            $this->filters
        );
    }
}
