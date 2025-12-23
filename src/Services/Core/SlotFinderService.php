<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Models\Availability;
use Roster\Models\Impediment;

/**
 * Service for finding available time slots while considering conflicts from schedules and impediments.
 */
class SlotFinderService implements SlotFinderInterface
{
    public function __construct(
        private readonly AvailabilityRepositoryInterface $availabilityRepository,
    ) {}


    /**
     * Check if an entire time period is available without interruptions.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $start Period start datetime
     * @param Carbon $end Period end datetime
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
            model: $model,
            start: $start,
            end: $end,
            type: $type
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

                    return $this->availabilityRepository->isAvailableOnDate($availability, $current) &&
                        $availability->start_time->format('H:i') <= $current->format('H:i') &&
                        $availability->end_time->format('H:i') >= $slotEnd->format('H:i');
                }
            );

            if ($availability === null || ! $this->isTimeSlotConflictFree($availability, $current, $slotEnd)) {
                return false;
            }

            $current->addMinutes($checkIntervalMinutes);
        }

        return true;
    }



    /**
     * Calculate available time slots by removing impediments from a time range.
     *
     * @param Carbon $start Start of the time range
     * @param Carbon $end End of the time range
     * @param Collection $impediments Collection of impediments
     * @return Collection<int, array<string, mixed>> Available time slots
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

        return ! $hasOverlappingSchedule && ! $hasOverlappingImpediments;
    }
}
