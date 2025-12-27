<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\DTOs\ScheduleData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing Schedule entities and time slot availability.
 *
 * Handles schedule creation, validation, and finding available time slots
 * considering availability periods and existing conflicts.
 */
class ScheduleService extends AbstractService
{

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
     * Finds the next available time slot from a given starting point.
     *
     * @param int $durationMinutes Required slot duration
     * @param string|null $type Availability type filter
     * @param bool $returnStartOnly Whether to return only start time
     * @param Carbon|null $startFrom Search starting date
     * @param Carbon|null $endBefore Search ending date
     * @return array|Carbon|null Available slot data or start time
     */
    public function findNextSlot(
        int $durationMinutes,
        ?string $type = null,
        bool $returnStartOnly = false,
        ?Carbon $startFrom = null,
        ?Carbon $endBefore = null
    ): array|Carbon|null {
        $startFrom = $startFrom ?? Carbon::now();
        $endBefore = $endBefore ?? $startFrom->copy()->addDays(config('roster.durations.max_search_period_days', 30));

        $searchDate = $startFrom->copy()->startOfDay();

        while ($searchDate->lt($endBefore)) {
            $result = $this->findAvailableSlotInDay(
                day: $searchDate,
                durationMinutes: $durationMinutes,
                type: $type,
                searchStart: $searchDate->isSameDay($startFrom) ? $startFrom : null
            );

            if ($result !== null) {
                return $returnStartOnly ? $result['start'] : $result;
            }

            $searchDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Checks if a time slot is available for scheduling.
     *
     * @param Carbon $start Slot start time
     * @param Carbon $end Slot end time
     * @param string|null $type Availability type filter
     * @return bool True if slot is available
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
     * @param int $durationMinutes Required slot duration
     * @param string|null $type Availability type filter
     * @return Collection<array> Available slots
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
     * @return bool True if period is completely available
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
     * @param int $durationMinutes Required slot duration
     * @param string|null $type Availability type filter
     * @param Carbon|null $searchStart Specific start time within day
     * @return array|null Slot data or null if none found
     */
    private function findAvailableSlotInDay(
        Carbon $day,
        int $durationMinutes,
        ?string $type = null,
        ?Carbon $searchStart = null
    ): ?array {
        /** @var Collection<Availability> $availabilities */
        $availabilities = $this->getAvailabilityRepository()->getForDate(
            schedulable: $this->schedulable,
            date: $day,
            type: $type
        );

        if ($availabilities->isEmpty()) {
            return null;
        }

        foreach ($availabilities as $availability) {
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
     * @param int $durationMinutes Required slot duration
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

        if ($slotStart === null) {
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
     * @return Carbon|null Valid start time or null
     */
    private function determineSearchStart(
        ?Carbon $searchStart,
        Carbon $day,
        Carbon $availabilityStart,
        Carbon $availabilityEnd
    ): ?Carbon {
        if ($searchStart === null || !$searchStart->isSameDay($day)) {
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
     * @param int $durationMinutes Required duration
     * @param int $slotInterval Slot interval
     * @param Availability $availability Availability context
     * @return array|null Slot data or null
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
}
