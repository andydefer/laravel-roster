<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\ScheduleServiceInterface;
use Roster\Enums\EntityType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;

/**
 * Service for managing Schedule entities and time slot availability.
 *
 * Handles schedule creation, validation, and finding available time slots
 * considering availability periods and existing conflicts.
 */
class ScheduleService extends AbstractService implements ScheduleServiceInterface
{
    /**
     * Current schedule for link operations.
     */
    private ?Model $currentSchedule = null;

    /**
     * Set the current schedule for link operations.
     *
     * @param Model $schedule The schedule to use for subsequent link operations
     * @return static Service instance with schedule context
     */
    public function schedule(Model $schedule): static
    {
        $clone = clone $this;
        $clone->currentSchedule = $schedule;
        return $clone;
    }

    /**
     * Returns the entity type for this service.
     *
     * @return EntityType Schedule entity type
     */
    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::SCHEDULE;
    }

    /**
     * Attach a model to the current schedule.
     *
     * @param Model $model The model to attach
     * @param array|null $metadata Optional metadata for the attachment
     * @return static Service instance for method chaining
     * @throws \RuntimeException If no schedule is set
     */
    public function attach(Model $model, ?array $metadata = null): static
    {
        $this->requireCurrentSchedule();

        $this->scheduleRepository->attach(
            scheduleId: $this->currentSchedule->id,
            model: $model,
            metadata: $metadata
        );

        return $this;
    }

    /**
     * Attach multiple models to the current schedule.
     *
     * @param array $models Array of models to attach
     * @param array|null $metadata Optional metadata for all attachments
     * @return static Service instance for method chaining
     * @throws \RuntimeException If no schedule is set
     */
    public function attachMany(array $models, ?array $metadata = null): static
    {
        $this->requireCurrentSchedule();

        $this->scheduleRepository->attachMany(
            scheduleId: $this->currentSchedule->id,
            models: $models,
            metadata: $metadata
        );

        return $this;
    }

    /**
     * Detach a model from the current schedule.
     *
     * @param Model $model The model to detach
     * @return static Service instance for method chaining
     * @throws \RuntimeException If no schedule is set
     */
    public function detach(Model $model): static
    {
        $this->requireCurrentSchedule();

        $this->scheduleRepository->detach(
            scheduleId: $this->currentSchedule->id,
            model: $model
        );

        return $this;
    }

    /**
     * Detach multiple models from the current schedule.
     *
     * @param array $models Array of models to detach
     * @return static Service instance for method chaining
     * @throws \RuntimeException If no schedule is set
     */
    public function detachMany(array $models): static
    {
        $this->requireCurrentSchedule();

        $this->scheduleRepository->detachMany(
            scheduleId: $this->currentSchedule->id,
            models: $models
        );

        return $this;
    }

    /**
     * Detach all models from the current schedule.
     *
     * @return static Service instance for method chaining
     * @throws \RuntimeException If no schedule is set
     */
    public function detachAll(): static
    {
        $this->requireCurrentSchedule();

        $this->scheduleRepository->detachAll(
            scheduleId: $this->currentSchedule->id
        );

        return $this;
    }

    /**
     * Check if a model is attached to the current schedule.
     *
     * @param Model $model The model to check
     * @return bool True if model is attached
     * @throws \RuntimeException If no schedule is set
     */
    public function hasAttached(Model $model): bool
    {
        $this->requireCurrentSchedule();

        return $this->scheduleRepository->hasAttached(
            scheduleId: $this->currentSchedule->id,
            model: $model
        );
    }

    /**
     * Get all models attached to the current schedule.
     *
     * @return Collection All attached models
     * @throws \RuntimeException If no schedule is set
     */
    public function getAttached(): Collection
    {
        $this->requireCurrentSchedule();

        return $this->scheduleRepository->getAttached(
            scheduleId: $this->currentSchedule->id
        );
    }

    /**
     * Get models of a specific type attached to the current schedule.
     *
     * @param string $modelClass The model class to filter by
     * @return Collection Attached models of specified type
     * @throws \RuntimeException If no schedule is set
     */
    public function getAttachedByType(string $modelClass): Collection
    {
        $this->requireCurrentSchedule();

        return $this->scheduleRepository->getAttachedByType(
            scheduleId: $this->currentSchedule->id,
            modelClass: $modelClass
        );
    }

    /**
     * Synchronize attached models for the current schedule.
     *
     * @param array $models Array of models to synchronize
     * @param array|null $metadata Optional metadata for synchronized attachments
     * @return static Service instance for method chaining
     * @throws \RuntimeException If no schedule is set
     */
    public function sync(array $models, ?array $metadata = null): static
    {
        $this->requireCurrentSchedule();

        $this->scheduleRepository->sync(
            scheduleId: $this->currentSchedule->id,
            models: $models,
            metadata: $metadata
        );

        return $this;
    }

    /**
     * Finds the next available time slot from a given starting point.
     *
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Availability type filter
     * @param bool $returnStartOnly Whether to return only start time
     * @param Carbon|null $startFrom Search starting date (defaults to now)
     * @param Carbon|null $endBefore Search ending date (defaults to max period days from start)
     * @return array|Carbon|null Available slot data, start time only, or null if no slot found
     */
    public function findNextSlot(
        int $durationMinutes,
        ?string $type = null,
        bool $returnStartOnly = false,
        ?Carbon $startFrom = null,
        ?Carbon $endBefore = null
    ): array|Carbon|null {
        $searchStart = $startFrom ?? Carbon::now();
        $searchEnd = $endBefore ?? $searchStart->copy()->addDays(
            config('roster.durations.max_search_period_days', 30)
        );

        $currentDate = $searchStart->copy()->startOfDay();

        while ($currentDate->lt($searchEnd)) {
            $slotResult = $this->findAvailableSlotInDay(
                day: $currentDate,
                durationMinutes: $durationMinutes,
                type: $type,
                searchStart: $currentDate->isSameDay($searchStart) ? $searchStart : null
            );

            if ($slotResult !== null) {
                return $returnStartOnly ? $slotResult['start'] : $slotResult;
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Checks if a time slot is available for scheduling.
     *
     * @param Carbon $start Slot start time
     * @param Carbon $end Slot end time
     * @param string|null $type Availability type filter
     * @return bool True if slot is available for scheduling
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->getAvailabilityRepository()->getAvailabilityForTimeSlot(
            model: $this->schedulable,
            start: $start,
            end: $end,
            type: $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availability->id,
            start: $start,
            end: $end
        );

        return !$conflictResult->hasConflicts;
    }

    /**
     * Finds all available time slots in a date range.
     *
     * @param Carbon $startDate Range start date
     * @param Carbon $endDate Range end date
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Availability type filter
     * @return Collection<array> Collection of available slots with start, end, availability and duration
     */
    public function findAvailableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): Collection {
        $availableSlots = collect();
        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate)) {
            $slot = $this->findAvailableSlotInDay(
                day: $currentDate,
                durationMinutes: $durationMinutes,
                type: $type
            );

            if ($slot !== null) {
                $availableSlots->push($slot);
            }

            $currentDate->addDay();
        }

        return $availableSlots;
    }

    /**
     * Checks if a complete time period is available for scheduling.
     *
     * @param Carbon $start Period start time
     * @param Carbon $end Period end time
     * @param string|null $type Availability type filter
     * @return bool True if the entire period is available for scheduling
     */
    public function isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->getAvailabilityRepository()->getAvailabilityForTimeSlot(
            model: $this->schedulable,
            start: $start,
            end: $end,
            type: $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        if (!$availability->isAvailableForSchedule($start, $end)) {
            return false;
        }

        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availability->id,
            start: $start,
            end: $end
        );

        return !$conflictResult->hasConflicts;
    }

    /**
     * Finds an available slot within a specific day.
     *
     * @param Carbon $day Date to search within
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Availability type filter
     * @param Carbon|null $searchStart Specific start time within day
     * @return array|null Slot data with start, end, availability and duration, or null if none found
     */
    private function findAvailableSlotInDay(
        Carbon $day,
        int $durationMinutes,
        ?string $type = null,
        ?Carbon $searchStart = null
    ): ?array {
        /** @var Collection<int, Availability> $dailyAvailabilities */
        $dailyAvailabilities = $this->getAvailabilityRepository()->getForDate(
            model: $this->schedulable,
            date: $day,
            type: $type
        );

        if ($dailyAvailabilities->isEmpty()) {
            return null;
        }

        foreach ($dailyAvailabilities as $availability) {
            $slot = $this->findSlotInAvailability(
                availability: $availability,
                day: $day,
                durationMinutes: $durationMinutes,
                searchStart: $searchStart
            );

            if ($slot !== null) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * Finds a slot within a specific availability.
     *
     * @param Availability $availability Availability to search within
     * @param Carbon $day Date context
     * @param int $durationMinutes Required slot duration in minutes
     * @param Carbon|null $searchStart Specific start time
     * @return array|null Slot data or null if none found
     */
    private function findSlotInAvailability(
        Availability $availability,
        Carbon $day,
        int $durationMinutes,
        ?Carbon $searchStart = null
    ): ?array {
        if (!$availability->daily_start || !$availability->daily_end) {
            return null;
        }

        if (!$availability->isActiveOnDate($day)) {
            return null;
        }

        $availabilityStart = $day->copy()->setTimeFromTimeString($availability->daily_start->format('H:i:s'));
        $availabilityEnd = $day->copy()->setTimeFromTimeString($availability->daily_end->format('H:i:s'));

        $slotStart = $this->determineSearchStart(
            searchStart: $searchStart,
            day: $day,
            availabilityStart: $availabilityStart,
            availabilityEnd: $availabilityEnd
        );

        if (!$slotStart instanceof Carbon) {
            return null;
        }

        $slotInterval = config('roster.durations.default_slot_interval_minutes', 15);
        $slotStart = $this->alignToInterval($slotStart, $slotInterval);

        if ($slotStart->copy()->addMinutes($durationMinutes)->gt($availabilityEnd)) {
            return null;
        }

        return $this->findFirstAvailableSlot(
            slotStart: $slotStart,
            availabilityEnd: $availabilityEnd,
            durationMinutes: $durationMinutes,
            slotInterval: $slotInterval,
            availability: $availability
        );
    }

    /**
     * Determines the search start time considering constraints.
     *
     * @param Carbon|null $searchStart Requested start time
     * @param Carbon $day Current day
     * @param Carbon $availabilityStart Availability start time
     * @param Carbon $availabilityEnd Availability end time
     * @return Carbon|null Valid start time or null if constraints can't be satisfied
     */
    private function determineSearchStart(
        ?Carbon $searchStart,
        Carbon $day,
        Carbon $availabilityStart,
        Carbon $availabilityEnd
    ): ?Carbon {
        if (!$searchStart instanceof Carbon || !$searchStart->isSameDay($day)) {
            return $availabilityStart->copy();
        }

        if ($searchStart->lt($availabilityStart)) {
            return $availabilityStart->copy();
        }

        if ($searchStart->lt($availabilityEnd)) {
            return $searchStart->copy();
        }

        return null;
    }

    /**
     * Aligns a time to the nearest slot interval.
     *
     * @param Carbon $time Time to align
     * @param int $intervalMinutes Interval in minutes
     * @return Carbon Aligned time
     */
    private function alignToInterval(Carbon $time, int $intervalMinutes): Carbon
    {
        if ($time->minute === 0 && $time->second === 0) {
            return $time;
        }

        $minutes = $time->minute;
        $roundedMinutes = ceil($minutes / $intervalMinutes) * $intervalMinutes;

        return $time->copy()->setMinute((int)$roundedMinutes)->setSecond(0);
    }

    /**
     * Finds the first available slot starting from a given time.
     *
     * @param Carbon $slotStart Starting time
     * @param Carbon $availabilityEnd Availability end time
     * @param int $durationMinutes Required duration in minutes
     * @param int $slotInterval Slot interval in minutes
     * @param Availability $availability Availability context
     * @return array|null Slot data with start, end, availability and duration, or null if none found
     */
    private function findFirstAvailableSlot(
        Carbon $slotStart,
        Carbon $availabilityEnd,
        int $durationMinutes,
        int $slotInterval,
        Availability $availability
    ): ?array {
        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($availabilityEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($this->isTimeSlotAvailable($slotStart, $slotEnd, $availability->type)) {
                return [
                    'start' => $slotStart->copy(),
                    'end' => $slotEnd->copy(),
                    'availability' => $availability,
                    'duration_minutes' => $durationMinutes,
                ];
            }

            $slotStart->addMinutes($slotInterval);
        }

        return null;
    }

    /**
     * Gets the availability repository instance.
     *
     * @return AvailabilityRepositoryInterface Availability repository
     */
    protected function getAvailabilityRepository(): AvailabilityRepositoryInterface
    {
        return $this->availabilityRepository;
    }

    /**
     * Requires that a current schedule is set.
     *
     * @throws \RuntimeException If no current schedule is set
     */
    private function requireCurrentSchedule(): void
    {
        if (!$this->currentSchedule instanceof Model) {
            throw new \RuntimeException(
                'No schedule set for link operations. Use schedule() method first.'
            );
        }
    }
}
