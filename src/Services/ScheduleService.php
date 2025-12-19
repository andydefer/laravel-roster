<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Validate future dates hook
     */
    protected function validateFutureDatesHook(string $operation): void
    {
        // For schedules, validate start_datetime is not in the past
        if (isset($this->data['start_datetime'])) {
            $startDatetime = Carbon::parse($this->data['start_datetime']);

            if ($startDatetime->isPast() && $operation === 'create') {
                throw ValidationException::withMessage(
                    'Schedule start datetime cannot be in the past'
                );
            }
        }
    }

    /**
     * Validate duration hook
     */
    protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void {
        // For schedules, validate minimum duration
        if (isset($this->data['start_datetime'], $this->data['end_datetime'])) {
            $start = Carbon::parse($this->data['start_datetime']);
            $end = Carbon::parse($this->data['end_datetime']);

            if ($start->diffInMinutes($end) < $minScheduleMinutes) {
                throw ValidationException::withMessage(
                    sprintf('Schedule must be at least %d minutes', $minScheduleMinutes)
                );
            }
        }
    }

    /**
     * Validate max days hook
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        // For schedules, this might not apply directly, but could be used for recurring schedules
        // You can implement if needed
    }

    /**
     * Validate timezone hook
     */
    protected function validateTimezoneHook(string $timezone): void
    {
        // Validate timezone for datetime fields
        if ((isset($this->data['start_datetime']) || isset($this->data['end_datetime'])) && !$this->validationService->validateTimezone($timezone)) {
            throw ValidationException::withMessage(
                'Invalid timezone: ' . $timezone
            );
        }
    }

    /**
     * Validate before create hook
     */
    protected function validateBeforeCreate(): void
    {
        // Original validation logic
        $this->validateScheduleData($this->data);

        // Find matching availability
        $availability = $this->findMatchingAvailability($this->data);

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        // Check for overlapping schedules and impediments
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        if ($this->scheduleRepository->hasOverlappingSchedule($availability->id, $start, $end)) {
            throw new OverlappingScheduleException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
        }

        if ($this->impedimentRepository->hasOverlappingImpediments($availability->id, $start, $end)) {
            throw new ScheduleImpedimentOverlapException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
        }

        // Store availability ID in data for creation
        $this->data['availability_id'] = $availability->id;
    }

    /**
     * Process before create hook
     */
    protected function processBeforeCreate(): void
    {
        // Additional processing if needed
        // Ensure metadata is properly formatted
        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }

        // Set default status if not provided
        if (!isset($this->data['status'])) {
            $this->data['status'] = 'available';
        }
    }

    /**
     * Execute create
     */
    protected function executeCreate(): Schedule
    {
        return $this->scheduleRepository->create($this->data);
    }

    /**
     * After create hook
     */
    protected function afterCreate($schedule): void
    {
        // Clear cache if enabled
        if (config('roster.cache.enabled', true)) {
            $this->clearScheduleCache($schedule->id);
        }
    }

    /**
     * Validate before update hook
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentSchedule = $this->find($id);

        if (!$this->currentSchedule instanceof Schedule) {
            throw ValidationException::withMessage('Schedule not found');
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
        if ($this->data !== [] && isset($this->data['start_datetime'])) {
            $newAvailability = $this->findMatchingAvailability($this->data);
            if (!$newAvailability instanceof Availability) {
                throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
            }

            // Check for overlaps with new availability
            ['start' => $start, 'end' => $end] = $this->validationService
                ->parseAndValidateDateTimeRange(array_merge([
                    'start_datetime' => $this->currentSchedule->start_datetime,
                    'end_datetime' => $this->currentSchedule->end_datetime,
                ], $this->data));

            if ($this->scheduleRepository->hasOverlappingSchedule($newAvailability->id, $start, $end, $id)) {
                throw ValidationException::withMessage('Schedule overlaps with another schedule');
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
     * Process before update hook
     */
    protected function processBeforeUpdate(int $id): void
    {
        // Additional processing if needed
    }

    /**
     * Execute update
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->scheduleRepository->update($id, $this->data);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        if ($result && config('roster.cache.enabled', true)) {
            $this->clearScheduleCache($id);
        }
    }

    // ========== ORIGINAL METHODS (adapted) ==========

    /**
     * Find a schedule by its ID.
     */
    public function find(int $id): ?Schedule
    {
        $this->validateSchedulable();
        return $this->scheduleRepository->findById($id);
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

        return $this->scheduleRepository->delete($id);
    }

    /**
     * Get schedules for a given period.
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
     * Check availability for a time slot.
     */
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

    /**
     * Check if a time period is completely available.
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

        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        return $this->slotFinder->findNextSlot($this->schedulable, $durationMinutes, $type);
    }


    /**
     * Validate schedule data including time range.
     * @param array<string, mixed> $data
     */
    protected function validateScheduleData(array $data): void
    {
        ['start' => $start] = $this->validationService->parseAndValidateDateTimeRange($data);
        $this->validationService->validateFutureDate($start);
    }

    /**
     * Find matching availability for a schedule.
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
     */
    protected function applyFilters(): Builder
    {
        return $this->scheduleRepository->applyFilters(
            $this->schedulable->id,
            get_class($this->schedulable),
            $this->filters
        );
    }

    /**
     * Clear schedule cache
     */
    private function clearScheduleCache(int $scheduleId): void
    {
        $prefix = config('roster.cache.prefix', 'roster_');
        $cacheKey = $prefix . 'schedule_' . $scheduleId;

        Cache::forget($cacheKey);
        Cache::tags(['schedule_' . $scheduleId])->flush();
    }
}
