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
 * Service for managing schedule entities and time slot availability.
 *
 * Provides methods for schedule creation, validation, and finding available time slots
 * while considering availability periods and existing scheduling conflicts.
 */
class ScheduleService extends AbstractService implements ScheduleServiceInterface
{
    /**
     * The currently active schedule for link operations.
     *
     * @var Model|null
     */
    private ?Model $currentSchedule = null;

    /**
     * Set the current schedule for subsequent link operations.
     *
     * @param Model $schedule The schedule model to use as context
     * @return static Service instance with schedule context for method chaining
     */
    public function schedule(Model $schedule): static
    {
        $clone = clone $this;
        $clone->currentSchedule = $schedule;
        return $clone;
    }

    /**
     * Returns the entity type enum for this service.
     *
     * @return EntityType The schedule entity type
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
     * @throws \RuntimeException When no schedule is currently set
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
     * @throws \RuntimeException When no schedule is currently set
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
     * @throws \RuntimeException When no schedule is currently set
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
     * @throws \RuntimeException When no schedule is currently set
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
     * @throws \RuntimeException When no schedule is currently set
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
     * @return bool True if the model is attached, false otherwise
     * @throws \RuntimeException When no schedule is currently set
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
     * @throws \RuntimeException When no schedule is currently set
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
     * @return Collection Attached models of the specified type
     * @throws \RuntimeException When no schedule is currently set
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
     * @throws \RuntimeException When no schedule is currently set
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
     * Find the next available time slot from a given starting point.
     *
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Availability type filter
     * @param bool $returnStartOnly Whether to return only the start time
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
            $slot = $this->findFirstAvailableSlotInDay(
                day: $currentDate,
                durationMinutes: $durationMinutes,
                type: $type,
                searchStart: $currentDate->isSameDay($searchStart) ? $searchStart : null
            );

            if ($slot !== null) {
                return $returnStartOnly ? $slot['start'] : $slot;
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Check if a specific time slot is available for scheduling.
     *
     * @param Carbon $start Slot start time
     * @param Carbon $end Slot end time
     * @param string|null $type Availability type filter
     * @return bool True if the slot is available for scheduling
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->getAvailabilityRepository()->getAvailabilityForTimeSlot(
            model: $this->schedulable,
            slotStart: $start,
            slotEnd: $end,
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
     * Find all available time slots within a date range.
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
            $dailySlots = $this->findAllAvailableSlotsInDay(
                day: $currentDate,
                durationMinutes: $durationMinutes,
                type: $type
            );

            $availableSlots = $availableSlots->merge($dailySlots);
            $currentDate->addDay();
        }

        return $availableSlots->sortBy('start')->values();
    }

    /**
     * Check if a complete time period is available for scheduling.
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
            slotStart: $start,
            slotEnd: $end,
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
     * Get the availability repository instance.
     *
     * @return AvailabilityRepositoryInterface Availability repository
     */
    protected function getAvailabilityRepository(): AvailabilityRepositoryInterface
    {
        return $this->availabilityRepository;
    }

    /**
     * Require that a current schedule is set for link operations.
     *
     * @throws \RuntimeException When no current schedule is set
     */
    private function requireCurrentSchedule(): void
    {
        if (!$this->currentSchedule instanceof Model) {
            throw new \RuntimeException(
                'No schedule set for link operations. Use schedule() method first.'
            );
        }
    }

    /**
     * Find all available slots within a specific day.
     *
     * @param Carbon $day Date to search within
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Availability type filter
     * @return Collection<array> Collection of all available slots for the day
     */
    private function findAllAvailableSlotsInDay(
        Carbon $day,
        int $durationMinutes,
        ?string $type = null
    ): Collection {
        $dailySlots = collect();

        /** @var Collection<int, Availability> $dailyAvailabilities */
        $dailyAvailabilities = $this->getAvailabilityRepository()->getForDate(
            model: $this->schedulable,
            date: $day,
            type: $type
        );

        if ($dailyAvailabilities->isEmpty()) {
            return $dailySlots;
        }

        foreach ($dailyAvailabilities as $availability) {
            $slots = $this->findAllSlotsInAvailability(
                availability: $availability,
                day: $day,
                durationMinutes: $durationMinutes
            );

            $dailySlots = $dailySlots->merge($slots);
        }

        return $dailySlots->sortBy('start')->values();
    }

    /**
     * Find all slots within a specific availability for a day.
     *
     * Algorithm:
     * 1. Start at daily_start aligned to interval
     * 2. For each interval, create a slot of required duration
     * 3. Check if slot is available (no conflicts with impediments/schedules)
     * 4. If available, add it and skip the full duration (to avoid overlaps)
     * 5. Otherwise, move to next interval
     *
     * @param Availability $availability Availability to search within
     * @param Carbon $day Date context
     * @param int $durationMinutes Required slot duration in minutes
     * @return Collection<array> Collection of all available slots in this availability
     */
    private function findAllSlotsInAvailability(
        Availability $availability,
        Carbon $day,
        int $durationMinutes
    ): Collection {
        $slots = collect();

        if (!$availability->daily_start || !$availability->daily_end) {
            return $slots;
        }

        if (!$availability->isActiveOnDate($day)) {
            return $slots;
        }

        $availabilityStart = $this->createDayTime($day, $availability->daily_start);
        $availabilityEnd = $this->createDayTime($day, $availability->daily_end);

        $slotInterval = (int) config('roster.durations.default_slot_interval_minutes', 15);
        $slotStart = $this->alignToInterval($availabilityStart, $slotInterval);

        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($availabilityEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($this->isTimeSlotAvailable($slotStart, $slotEnd, $availability->type)) {
                $slots->push($this->createSlotData($slotStart, $slotEnd, $availability, $durationMinutes));

                $slotStart = $slotEnd->copy();
            } else {
                $slotStart->addMinutes($slotInterval);
            }

            $slotStart = $this->alignToInterval($slotStart, $slotInterval);
        }

        return $slots;
    }

    /**
     * Find the first available slot within a specific day.
     *
     * @param Carbon $day Date to search within
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Availability type filter
     * @param Carbon|null $searchStart Specific start time within day
     * @return array|null Slot data with start, end, availability and duration, or null if none found
     */
    private function findFirstAvailableSlotInDay(
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
            $slot = $this->findFirstSlotInAvailability(
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
     * Find the first available slot within a specific availability.
     *
     * @param Availability $availability Availability to search within
     * @param Carbon $day Date context
     * @param int $durationMinutes Required slot duration in minutes
     * @param Carbon|null $searchStart Specific start time
     * @return array|null Slot data or null if none found
     */
    private function findFirstSlotInAvailability(
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

        $availabilityStart = $this->createDayTime($day, $availability->daily_start);
        $availabilityEnd = $this->createDayTime($day, $availability->daily_end);

        $slotStart = $this->determineSearchStart(
            searchStart: $searchStart,
            day: $day,
            availabilityStart: $availabilityStart,
            availabilityEnd: $availabilityEnd
        );

        if (!$slotStart instanceof Carbon) {
            return null;
        }

        $slotInterval = (int) config('roster.durations.default_slot_interval_minutes', 15);
        $slotStart = $this->alignToInterval($slotStart, $slotInterval);

        if ($slotStart->copy()->addMinutes($durationMinutes)->gt($availabilityEnd)) {
            return null;
        }

        return $this->findNextAvailableSlotFromTime(
            slotStart: $slotStart,
            availabilityEnd: $availabilityEnd,
            durationMinutes: $durationMinutes,
            slotInterval: $slotInterval,
            availability: $availability
        );
    }

    /**
     * Create a Carbon instance for a specific time on a given day.
     *
     * @param Carbon $day The day
     * @param Carbon $time The time to set
     * @return Carbon Combined date and time
     */
    private function createDayTime(Carbon $day, Carbon $time): Carbon
    {
        return $day->copy()->setTimeFromTimeString($time->format('H:i:s'));
    }

    /**
     * Create standardized slot data array.
     *
     * @param Carbon $start Slot start time
     * @param Carbon $end Slot end time
     * @param Availability $availability The availability
     * @param int $durationMinutes Slot duration in minutes
     * @return array Slot data structure
     */
    private function createSlotData(Carbon $start, Carbon $end, Availability $availability, int $durationMinutes): array
    {
        return [
            'start' => $start->copy(),
            'end' => $end->copy(),
            'availability' => $availability,
            'duration_minutes' => $durationMinutes,
        ];
    }

    /**
     * Determine the search start time considering constraints.
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
     * Align a time to the nearest slot interval.
     *
     * @param Carbon $time Time to align
     * @param int $intervalMinutes Interval in minutes
     * @return Carbon Aligned time
     */
    private function alignToInterval(Carbon $time, int $intervalMinutes): Carbon
    {
        if ($intervalMinutes <= 0) {
            return $time->copy()->setSecond(0);
        }

        if ($time->minute === 0 && $time->second === 0) {
            return $time;
        }

        $minutes = $time->minute;
        $roundedMinutes = ceil($minutes / $intervalMinutes) * $intervalMinutes;

        if ($roundedMinutes >= 60) {
            return $time->copy()
                ->addHour()
                ->setMinute(0)
                ->setSecond(0);
        }

        return $time->copy()->setMinute((int)$roundedMinutes)->setSecond(0);
    }

    /**
     * Find the next available slot starting from a given time.
     *
     * @param Carbon $slotStart Starting time
     * @param Carbon $availabilityEnd Availability end time
     * @param int $durationMinutes Required duration in minutes
     * @param int $slotInterval Slot interval in minutes
     * @param Availability $availability Availability context
     * @return array|null Slot data or null if none found
     */
    private function findNextAvailableSlotFromTime(
        Carbon $slotStart,
        Carbon $availabilityEnd,
        int $durationMinutes,
        int $slotInterval,
        Availability $availability
    ): ?array {
        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($availabilityEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($this->isTimeSlotAvailable($slotStart, $slotEnd, $availability->type)) {
                return $this->createSlotData($slotStart, $slotEnd, $availability, $durationMinutes);
            }

            $slotStart->addMinutes($slotInterval);
            $slotStart = $this->alignToInterval($slotStart, $slotInterval);
        }

        return null;
    }
}
