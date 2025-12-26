<?php

declare(strict_types=1);

namespace Roster\Domain\Helpers;

use Illuminate\Support\Carbon;
use Carbon\CarbonInterval;

class TimeSlotHelper
{
    /**
     * Check if two time slots overlap.
     * Overlap occurs when: startA < endB && endA > startB
     */
    public static function overlaps(
        Carbon $startA,
        Carbon $endA,
        Carbon $startB,
        Carbon $endB
    ): bool {
        return $startA->lt($endB) && $endA->gt($startB);
    }

    /**
     * Check if a time is within a time slot (inclusive start, exclusive end).
     */
    public static function isWithin(Carbon $time, Carbon $start, Carbon $end): bool
    {
        return $time->gte($start) && $time->lt($end);
    }

    /**
     * Calculate overlap duration between two time slots.
     * Returns null if no overlap.
     */
    public static function getOverlapDuration(
        Carbon $startA,
        Carbon $endA,
        Carbon $startB,
        Carbon $endB
    ): ?CarbonInterval {
        if (!self::overlaps($startA, $endA, $startB, $endB)) {
            return null;
        }

        $overlapStart = $startA->greaterThan($startB) ? $startA : $startB;
        $overlapEnd = $endA->lessThan($endB) ? $endA : $endB;

        return $overlapStart->diffAsCarbonInterval($overlapEnd);
    }

    /**
     * Calculate available slots by removing blocked periods.
     * @param mixed[][] $blockedPeriods
     */
    public static function calculateAvailableSlots(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        array $blockedPeriods
    ): array {
        $availableSlots = [];
        $currentTime = $rangeStart->copy();

        // Sort blocked periods by start time
        usort($blockedPeriods, function (array $a, array $b): int {
            return $a['start'] <=> $b['start'];
        });

        foreach ($blockedPeriods as $blockedPeriod) {
            $blockStart = $blockedPeriod['start'] instanceof Carbon ? $blockedPeriod['start'] : Carbon::parse($blockedPeriod['start']);
            $blockEnd = $blockedPeriod['end'] instanceof Carbon ? $blockedPeriod['end'] : Carbon::parse($blockedPeriod['end']);

            // If there's a gap before the block
            if ($blockStart->gt($currentTime)) {
                $availableSlots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $blockStart->copy(),
                ];
            }

            // Move current time to the end of the block
            $currentTime = $currentTime->gt($blockEnd) ? $currentTime : $blockEnd;
        }

        // Add remaining time after last block
        if ($currentTime->lt($rangeEnd)) {
            $availableSlots[] = [
                'start' => $currentTime->copy(),
                'end' => $rangeEnd->copy(),
            ];
        }

        return $availableSlots;
    }

    /**
     * Check if a time slot is completely contained within another.
     */
    public static function isContainedWithin(
        Carbon $innerStart,
        Carbon $innerEnd,
        Carbon $outerStart,
        Carbon $outerEnd
    ): bool {
        return $innerStart->gte($outerStart) && $innerEnd->lte($outerEnd);
    }
}
