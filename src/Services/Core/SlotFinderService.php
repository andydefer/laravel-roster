<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;

/**
 * Service for finding available time slots while considering conflicts from schedules and impediments.
 */
class SlotFinderService implements SlotFinderInterface
{
    private const MAX_DAYS_TO_CHECK = 365;

    private const DEFAULT_INTERVAL_MINUTES = 15;

    public function __construct(
        private readonly AvailabilityRepositoryInterface $availabilityRepository,
        private readonly ValidationServiceInterface $validationService
    ) {}

    /**
     * Find the next available time slot with strict conflict checking.
     *
     * @param Model $model The model to check availability for
     * @param int $durationMinutes Required duration in minutes
     * @param string|null $type Optional availability type filter
     * @return array|null Slot details or null if no slot found
     */
    public function findNextAvailableSlot(
        Model $model,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $now = Carbon::now();
        $startDate = $now->copy()->startOfDay();
        $endDate = $now->copy()->addDays(30)->endOfDay();

        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $model,
            $startDate,
            $endDate,
            $type
        );

        for ($dayOffset = 0; $dayOffset < 30; ++$dayOffset) {
            $currentDate = $now->copy()->addDays($dayOffset)->startOfDay();

            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $availabilities->filter(
                fn(Availability $availability): bool => $this->availabilityRepository->isAvailabilityValidForDate($availability, $currentDate)
            );

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slot = $this->findSlotInAvailability(
                    $dailyAvailability,
                    $currentDate,
                    $durationMinutes,
                    $dayOffset === 0
                );

                if ($slot !== null) {
                    return $slot;
                }
            }
        }

        return null;
    }

    /**
     * Find all available slots in a period with strict conflict checking.
     *
     * @param Model $model The model to check availability for
     * @param Carbon $startDate Start of the search period
     * @param Carbon $endDate End of the search period
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Optional availability type filter
     * @return array List of available slots
     */
    public function findAvailableSlots(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array {
        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $model,
            $startDate,
            $endDate,
            $type
        );

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $availabilities->filter(
                fn(Availability $availability): bool => $this->availabilityRepository->isAvailabilityValidForDate($availability, $currentDate)
            );

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $availabilitySlots = $this->findAllSlotsInAvailability(
                    $dailyAvailability,
                    $currentDate,
                    $durationMinutes,
                    $currentDate->isSameDay($startDate) ? $startDate : null
                );
                $slots = array_merge($slots, $availabilitySlots);
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Get the first available period of a specific duration with strict conflict checking.
     *
     * @param Model $model The model to check availability for
     * @param Carbon $startDate Start of the search period
     * @param Carbon $endDate End of the search period
     * @param int $durationMinutes Required duration in minutes
     * @param string|null $type Optional availability type filter
     * @return array|null First available period or null if none found
     */
    public function findFirstAvailablePeriod(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $model,
            $startDate,
            $endDate,
            $type
        );

        $currentDate = $startDate->copy();
        $intervalMinutes = self::DEFAULT_INTERVAL_MINUTES;

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $availabilities->filter(
                fn(Availability $availability): bool => $this->availabilityRepository->isAvailabilityValidForDate($availability, $currentDate)
            );

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slot = $this->findFirstSlotInDailyAvailability(
                    $dailyAvailability,
                    $currentDate,
                    $startDate,
                    $durationMinutes,
                    $intervalMinutes,
                    $endDate
                );

                if ($slot !== null) {
                    return $slot;
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Check if a time period is completely available with strict conflict checking.
     *
     * @param Model $model The model to check availability for
     * @param Carbon $start Start of the period
     * @param Carbon $end End of the period
     * @param string|null $type Optional availability type filter
     * @return bool True if the entire period is available
     */
    public function isPeriodAvailable(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $model,
            $start,
            $end,
            $type
        );

        $current = $start->copy();
        $checkIntervalMinutes = 30;

        while ($current->lt($end)) {
            $slotEnd = $current->copy()->addMinutes($checkIntervalMinutes);
            if ($slotEnd->gt($end)) {
                $slotEnd = $end->copy();
            }

            $availability = $availabilities->first(
                function ($availability) use ($current, $slotEnd, $type): bool {
                    if ($type !== null && $availability->type !== $type) {
                        return false;
                    }

                    return $this->availabilityRepository->isAvailabilityValidForDate($availability, $current) &&
                        $availability->start_time->format('H:i') <= $current->format('H:i') &&
                        $availability->end_time->format('H:i') >= $slotEnd->format('H:i');
                }
            );

            if ($availability === null || !$this->isTimeSlotConflictFree($availability, $current, $slotEnd)) {
                return false;
            }

            $current->addMinutes($checkIntervalMinutes);
        }

        return true;
    }

    /**
     * Find all available slots between two dates with configurable parameters.
     *
     * @param object $schedulable The schedulable entity
     * @param Carbon $startDate Start of the search period
     * @param Carbon $endDate End of the search period
     * @param int $durationMinutes Required slot duration in minutes
     * @param int $intervalMinutes Interval between slot checks in minutes
     * @param string|null $type Optional availability type filter
     * @return array List of available slots
     * @throws ValidationException If validation fails
     */
    public function findAvailableSlotsBetween(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array {
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');
        $this->validationService->validateDurationAndInterval($durationMinutes, $intervalMinutes);

        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $schedulable,
            $startDate,
            $endDate,
            $type
        );

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->availabilityRepository->filterAvailabilitiesForDate($availabilities, $currentDate);

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    if ($this->isTimeSlotConflictFree($dailyAvailability, $slotStart, $slotStart->copy()->addMinutes($durationMinutes))) {
                        $slots[] = [
                            'start' => $slotStart->copy(),
                            'end' => $slotStart->copy()->addMinutes($durationMinutes),
                            'type' => $dailyAvailability->type,
                            'availability_id' => $dailyAvailability->id,
                        ];
                    }

                    $slotStart->addMinutes($intervalMinutes);
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Find the next available slot starting from a specific date.
     *
     * @param object $schedulable The schedulable entity
     * @param Carbon $fromDate Starting date for the search
     * @param int $durationMinutes Required slot duration in minutes
     * @return Carbon|null Next available slot start time or null
     * @throws ValidationException If duration is invalid
     */
    public function nextAvailableSlot(
        object $schedulable,
        Carbon $fromDate,
        int $durationMinutes = 60
    ): ?Carbon {
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        $startDate = $fromDate->copy()->startOfDay();
        $endDate = $startDate->copy()->addDays(self::MAX_DAYS_TO_CHECK)->endOfDay();

        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $schedulable,
            $startDate,
            $endDate
        );

        for ($dayOffset = 0; $dayOffset < self::MAX_DAYS_TO_CHECK; ++$dayOffset) {
            $checkDate = $fromDate->copy()->addDays($dayOffset)->startOfDay();

            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->availabilityRepository->filterAvailabilitiesForDate($availabilities, $checkDate);

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $checkDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $checkDate->copy()->setTimeFrom($dailyAvailability->end_time);

                if ($dayOffset === 0 && $slotStart->lt($fromDate)) {
                    $slotStart = $fromDate->copy();
                }

                $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if (
                    $proposedEnd->lte($slotEnd) &&
                    $this->isTimeSlotConflictFree($dailyAvailability, $slotStart, $proposedEnd)
                ) {
                    return $slotStart;
                }
            }
        }

        return null;
    }

    /**
     * Get all available slots in a period with conflict checking.
     *
     * @param object $schedulable The schedulable entity
     * @param Carbon $startDate Start of the search period
     * @param Carbon $endDate End of the search period
     * @param int $durationMinutes Required slot duration in minutes
     * @param int $intervalMinutes Interval between slot checks in minutes
     * @return array List of available slots
     */
    public function availableSlots(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30
    ): array {
        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $schedulable,
            $startDate,
            $endDate
        );

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->availabilityRepository->filterAvailabilitiesForDate($availabilities, $currentDate);

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    if ($this->isTimeSlotConflictFree($dailyAvailability, $slotStart, $slotStart->copy()->addMinutes($durationMinutes))) {
                        $slots[] = [
                            'start' => $slotStart->copy(),
                            'end' => $slotStart->copy()->addMinutes($durationMinutes),
                            'type' => $dailyAvailability->type,
                            'availability_id' => $dailyAvailability->id,
                        ];
                    }

                    $slotStart->addMinutes($intervalMinutes);
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Check if there is any availability between two dates.
     *
     * @param object $schedulable The schedulable entity
     * @param Carbon $start Start of the period
     * @param Carbon $end End of the period
     * @param string|null $type Optional availability type filter
     * @return bool True if any availability exists
     * @throws ValidationException If time range validation fails
     */
    public function hasAvailabilityBetween(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        $this->validationService->validateTimeRange($start, $end);

        $currentDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->endOfDay();

        $availabilities = $this->availabilityRepository->getAvailabilitiesWithConflictInfo(
            $schedulable,
            $start,
            $end,
            $type
        );

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->availabilityRepository->filterAvailabilitiesForDate($availabilities, $currentDate);

            if ($dailyAvailabilities->isNotEmpty()) {
                foreach ($dailyAvailabilities as $dailyAvailability) {
                    if ($this->hasAvailableTimeInDailyAvailability($dailyAvailability, $currentDate, $start, $end)) {
                        return true;
                    }
                }
            }

            $currentDate->addDay();
        }

        return false;
    }

    /**
     * Calculate available time slots by removing impediments from a time range.
     *
     * @param Carbon $start Start of the time range
     * @param Carbon $end End of the time range
     * @param Collection $impediments Collection of impediments
     * @return Collection Available time slots
     */
    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection {
        if ($impediments->isEmpty()) {
            return collect([['start' => $start, 'end' => $end]]);
        }

        $availableSlots = collect();
        $currentTime = $start->copy();

        /** @var Impediment $impediment */
        foreach ($impediments as $impediment) {
            $impStart = $impediment->start_datetime;
            $impEnd = $impediment->end_datetime;

            if ($impStart->gt($currentTime)) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            $currentTime = $currentTime->gt($impEnd) ? $currentTime : $impEnd;
        }

        if ($currentTime->lt($end)) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }



    /**
     * Find a slot within a specific availability for a given date.
     *
     * @param Availability $availability The availability to search within
     * @param Carbon $date The date to search on
     * @param int $durationMinutes Required slot duration in minutes
     * @param bool $isToday Whether this is the current day
     * @return array|null Slot details or null if no slot found
     */
    private function findSlotInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        bool $isToday = false
    ): ?array {
        $slotStart = $date->copy()->setTimeFrom($availability->start_time);
        $slotEnd = $date->copy()->setTimeFrom($availability->end_time);

        if ($isToday) {
            $now = Carbon::now();
            if ($now->gt($slotStart) && $now->lt($slotEnd)) {
                $slotStart = $now->copy()->addMinutes(1);
            }
        }

        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
            $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($proposedEnd->lte($slotStart)) {
                $slotStart->addMinutes(self::DEFAULT_INTERVAL_MINUTES);
                continue;
            }

            if ($this->isTimeSlotConflictFree($availability, $slotStart, $proposedEnd)) {
                return [
                    'start' => $slotStart->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            $slotStart->addMinutes(self::DEFAULT_INTERVAL_MINUTES);
        }

        return null;
    }

    /**
     * Find all slots within an availability for a given date.
     *
     * @param Availability $availability The availability to search within
     * @param Carbon $date The date to search on
     * @param int $durationMinutes Required slot duration in minutes
     * @param Carbon|null $minStartTime Minimum start time (for partial days)
     * @return array List of available slots
     */
    private function findAllSlotsInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        ?Carbon $minStartTime = null
    ): array {
        $slots = [];
        $slotStart = $date->copy()->setTimeFrom($availability->start_time);
        $slotEnd = $date->copy()->setTimeFrom($availability->end_time);

        if ($minStartTime instanceof Carbon && $minStartTime->gt($slotStart)) {
            $slotStart = $minStartTime->copy();
        }

        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
            $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($proposedEnd->lte($slotStart)) {
                $slotStart->addMinutes(self::DEFAULT_INTERVAL_MINUTES);
                continue;
            }

            if ($this->isTimeSlotConflictFree($availability, $slotStart, $proposedEnd)) {
                $slots[] = [
                    'start' => $slotStart->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            $slotStart->addMinutes(self::DEFAULT_INTERVAL_MINUTES);
        }

        return $slots;
    }

    /**
     * Check if a time slot has no conflicts with schedules or impediments.
     *
     * @param Availability $availability The availability containing conflict data
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @return bool True if the slot has no conflicts
     */
    private function isTimeSlotConflictFree(
        Availability $availability,
        Carbon $start,
        Carbon $end
    ): bool {
        $hasOverlappingSchedule = $availability->schedules->contains(
            fn($schedule): bool => $schedule->overlapsWith($start, $end)
        );

        $hasOverlappingImpediments = $availability->impediments->contains(
            fn($impediment): bool => $impediment->overlapsWith($start, $end)
        );

        return !$hasOverlappingSchedule && !$hasOverlappingImpediments;
    }

    /**
     * Find the first available slot in a daily availability.
     *
     * @param Availability $availability The daily availability
     * @param Carbon $currentDate Current date being checked
     * @param Carbon $searchStartDate Start date of the search
     * @param int $durationMinutes Required duration in minutes
     * @param int $intervalMinutes Check interval in minutes
     * @param Carbon $searchEndDate End date of the search
     * @return array|null First available slot or null
     */
    private function findFirstSlotInDailyAvailability(
        Availability $availability,
        Carbon $currentDate,
        Carbon $searchStartDate,
        int $durationMinutes,
        int $intervalMinutes,
        Carbon $searchEndDate
    ): ?array {
        $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
        $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

        if ($currentDate->isSameDay($searchStartDate) && $slotStart->lt($searchStartDate)) {
            $slotStart = $searchStartDate->copy();
        }

        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
            $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($proposedEnd->lte($currentDate) || $proposedEnd->gt($searchEndDate)) {
                $slotStart->addMinutes($intervalMinutes);
                continue;
            }

            if ($this->isTimeSlotConflictFree($availability, $slotStart, $proposedEnd)) {
                return [
                    'start' => $slotStart->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                ];
            }

            $slotStart->addMinutes($intervalMinutes);
        }

        return null;
    }

    /**
     * Check if there is available time within a daily availability.
     *
     * @param Availability $availability The daily availability
     * @param Carbon $currentDate Current date being checked
     * @param Carbon $searchStart Start of search period
     * @param Carbon $searchEnd End of search period
     * @return bool True if there is available time
     */
    private function hasAvailableTimeInDailyAvailability(
        Availability $availability,
        Carbon $currentDate,
        Carbon $searchStart,
        Carbon $searchEnd
    ): bool {
        $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
        $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

        if ($currentDate->isSameDay($searchStart) && $slotStart->lt($searchStart)) {
            $slotStart = $searchStart->copy();
        }

        if ($currentDate->isSameDay($searchEnd) && $slotEnd->gt($searchEnd)) {
            $slotEnd = $searchEnd->copy();
        }

        return $slotStart->lt($slotEnd);
    }
}
