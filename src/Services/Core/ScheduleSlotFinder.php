<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Models\Availability;

class ScheduleSlotFinder
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository,
        private ScheduleRepositoryInterface $scheduleRepository,
        private ImpedimentRepositoryInterface $impedimentRepository
    ) {}

    /**
     * Find the next available time slot.
     */
    public function findNextAvailableSlot(
        Model $model,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $now = Carbon::now();

        // Search in the next 30 days
        for ($i = 0; $i < 30; ++$i) {
            $currentDate = $now->copy()->addDays($i)->startOfDay();
            $availabilities = $this->availabilityRepository->getForDate($model, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slot = $this->findSlotInAvailability(
                    $availability,
                    $currentDate,
                    $durationMinutes,
                    $i === 0,
                    $type
                );

                if ($slot) {
                    return $slot;
                }
            }
        }

        return null;
    }

    /**
     * Find all available slots in a period.
     */
    public function findAvailableSlots(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array {
        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->availabilityRepository->getForDate($model, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $availabilitySlots = $this->findAllSlotsInAvailability(
                    $availability,
                    $currentDate,
                    $durationMinutes,
                    $currentDate->isSameDay($startDate) ? $startDate : null,
                    $type
                );

                $slots = array_merge($slots, $availabilitySlots);
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Get the first available period of a specific duration.
     */
    public function findFirstAvailablePeriod(
        $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $currentDate = $startDate->copy();
        $interval = 15; // minutes

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->availabilityRepository->getForDate($schedulable, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // For the first day, start at the later of slot start or start date time
                if ($currentDate->isSameDay($startDate) && $slotStart->lt($startDate)) {
                    $slotStart = $startDate->copy();
                }

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

                    if ($proposedEnd->lte($currentDate) || $proposedEnd->gt($endDate)) {
                        $slotStart->addMinutes($interval);
                        continue;
                    }

                    if ($this->isTimeSlotAvailable($schedulable, $slotStart, $proposedEnd, $type)) {
                        return [
                            'start' => $slotStart->copy(),
                            'end' => $proposedEnd,
                            'availability_id' => $availability->id,
                            'type' => $availability->type,
                        ];
                    }

                    $slotStart->addMinutes($interval);
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Find a slot in an availability.
     */
    private function findSlotInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        bool $isToday = false,
        ?string $type = null
    ): ?array {
        $startTime = $availability->start_time;
        $endTime = $availability->end_time;

        $currentSlot = $date->copy()
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endOfSlot = $date->copy()
            ->setTime($endTime->hour, $endTime->minute, $endTime->second);

        // If it's today and current time is after start time, start at current time
        if ($isToday) {
            $now = Carbon::now();
            if ($now->gt($currentSlot) && $now->lt($endOfSlot)) {
                $currentSlot = $now->copy()->addMinutes(1);
            }
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            // Validate the proposed slot's time range
            if ($proposedEnd->lte($currentSlot)) {
                continue;
            }

            // Check availability
            if ($this->isTimeSlotAvailable($availability->schedulable, $currentSlot, $proposedEnd, $type)) {
                return [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            $currentSlot->addMinutes(15);
        }

        return null;
    }

    /**
     * Find all slots in an availability for a given date.
     */
    private function findAllSlotsInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        ?Carbon $minStartTime = null,
        ?string $type = null
    ): array {
        $slots = [];
        $startTime = $availability->start_time;
        $endTime = $availability->end_time;

        $currentSlot = $date->copy()
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endOfSlot = $date->copy()
            ->setTime($endTime->hour, $endTime->minute, $endTime->second);

        if ($minStartTime instanceof Carbon && $minStartTime->gt($currentSlot)) {
            $currentSlot = $minStartTime->copy();
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            if ($proposedEnd->lte($currentSlot)) {
                $currentSlot->addMinutes(15);
                continue;
            }

            if ($this->isTimeSlotAvailable($availability->schedulable, $currentSlot, $proposedEnd, $type)) {
                $slots[] = [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            $currentSlot->addMinutes(15);
        }

        return $slots;
    }

    /**
     * Check if a time slot is available.
     */
    private function isTimeSlotAvailable(Model $model, Carbon $start, Carbon $end, ?string $type = null): bool
    {
        // Find a matching availability
        $availability = $this->availabilityRepository->findForTimeSlot($model, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return false;
        }

        // Check for overlapping schedules using repository
        $hasOverlappingSchedule = $this->scheduleRepository->hasOverlappingSchedule($availability->id, $start, $end);

        // Check for overlapping impediments using repository
        $hasOverlappingImpediment = $this->impedimentRepository->hasOverlappingImpediment($availability->id, $start, $end);

        return !$hasOverlappingSchedule && !$hasOverlappingImpediment;
    }

    /**
     * Check if a time period is completely available.
     */
    public function isPeriodAvailable(
        $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        // Split into 30-minute intervals to check each slot
        $current = $start->copy();
        $interval = 30; // minutes

        while ($current->lt($end)) {
            $slotEnd = $current->copy()->addMinutes($interval);
            if ($slotEnd->gt($end)) {
                $slotEnd = $end->copy();
            }

            if (!$this->isTimeSlotAvailable($schedulable, $current, $slotEnd, $type)) {
                return false;
            }

            $current->addMinutes($interval);
        }

        return true;
    }
}
