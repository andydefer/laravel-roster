<?php

declare(strict_types=1);

namespace Roster\Domain\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Roster\Enums\DaysOfWeek;

/**
 * Utility class for time slot operations and calculations.
 *
 * Provides static methods for checking overlaps, calculating durations,
 * and managing time slots within date ranges.
 */
class TimeSlotHelper
{
    /**
     * Check if two time slots overlap.
     *
     * Overlap occurs when: startA < endB && endA > startB
     *
     * @param Carbon $firstSlotStart Start of the first time slot
     * @param Carbon $firstSlotEnd End of the first time slot
     * @param Carbon $secondSlotStart Start of the second time slot
     * @param Carbon $secondSlotEnd End of the second time slot
     * @return bool True if the time slots overlap
     */
    public static function overlaps(
        Carbon $firstSlotStart,
        Carbon $firstSlotEnd,
        Carbon $secondSlotStart,
        Carbon $secondSlotEnd
    ): bool {
        return $firstSlotStart->lt($secondSlotEnd) && $firstSlotEnd->gt($secondSlotStart);
    }

    /**
     * Check if a time point is within a time slot (inclusive start, exclusive end).
     *
     * @param Carbon $timePoint Time point to check
     * @param Carbon $slotStart Start of the time slot
     * @param Carbon $slotEnd End of the time slot
     * @return bool True if the time point is within the slot
     */
    public static function isWithin(Carbon $timePoint, Carbon $slotStart, Carbon $slotEnd): bool
    {
        return $timePoint->gte($slotStart) && $timePoint->lt($slotEnd);
    }

    /**
     * Calculate overlap duration between two time slots.
     *
     * @param Carbon $firstSlotStart Start of the first time slot
     * @param Carbon $firstSlotEnd End of the first time slot
     * @param Carbon $secondSlotStart Start of the second time slot
     * @param Carbon $secondSlotEnd End of the second time slot
     * @return CarbonInterval|null Overlap duration or null if no overlap
     */
    public static function getOverlapDuration(
        Carbon $firstSlotStart,
        Carbon $firstSlotEnd,
        Carbon $secondSlotStart,
        Carbon $secondSlotEnd
    ): ?CarbonInterval {
        if (!self::overlaps($firstSlotStart, $firstSlotEnd, $secondSlotStart, $secondSlotEnd)) {
            return null;
        }

        $overlapStart = $firstSlotStart->greaterThan($secondSlotStart) ? $firstSlotStart : $secondSlotStart;
        $overlapEnd = $firstSlotEnd->lessThan($secondSlotEnd) ? $firstSlotEnd : $secondSlotEnd;

        return $overlapStart->diffAsCarbonInterval($overlapEnd);
    }

    /**
     * Calculate available slots by removing blocked periods from a time range.
     *
     * @param Carbon $rangeStart Start of the time range to analyze
     * @param Carbon $rangeEnd End of the time range to analyze
     * @param array<array{start: Carbon|string, end: Carbon|string}> $blockedPeriods Array of blocked periods
     * @return array<array{start: Carbon, end: Carbon}> Available time slots
     */
    public static function calculateAvailableSlots(
        Carbon $rangeStart,
        Carbon $rangeEnd,
        array $blockedPeriods
    ): array {
        $availableSlots = [];
        $currentTime = $rangeStart->copy();

        self::sortBlockedPeriods($blockedPeriods);

        foreach ($blockedPeriods as $blockedPeriod) {
            $blockStart = self::normalizeCarbon($blockedPeriod['start']);
            $blockEnd = self::normalizeCarbon($blockedPeriod['end']);

            if ($blockStart->gt($currentTime)) {
                $availableSlots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $blockStart->copy(),
                ];
            }

            $currentTime = self::getLaterTime($currentTime, $blockEnd);
        }

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
     *
     * @param Carbon $innerSlotStart Start of the inner time slot
     * @param Carbon $innerSlotEnd End of the inner time slot
     * @param Carbon $outerSlotStart Start of the outer time slot
     * @param Carbon $outerSlotEnd End of the outer time slot
     * @return bool True if the inner slot is contained within the outer slot
     */
    public static function isContainedWithin(
        Carbon $innerSlotStart,
        Carbon $innerSlotEnd,
        Carbon $outerSlotStart,
        Carbon $outerSlotEnd
    ): bool {
        return $innerSlotStart->gte($outerSlotStart) && $innerSlotEnd->lte($outerSlotEnd);
    }

    /**
     * Sort blocked periods by their start time.
     *
     * @param array<array{start: Carbon|string, end: Carbon|string}> &$blockedPeriods Array to sort
     */
    private static function sortBlockedPeriods(array &$blockedPeriods): void
    {
        usort($blockedPeriods, function (array $firstPeriod, array $secondPeriod): int {
            $firstStart = self::normalizeCarbon($firstPeriod['start']);
            $secondStart = self::normalizeCarbon($secondPeriod['start']);

            return $firstStart <=> $secondStart;
        });
    }

    /**
     * Normalize input to Carbon instance.
     *
     * @param Carbon|string $dateTime Date to normalize
     * @return Carbon Normalized Carbon instance
     */
    private static function normalizeCarbon(Carbon|string $dateTime): Carbon
    {
        return $dateTime instanceof Carbon ? $dateTime : Carbon::parse($dateTime);
    }

    /**
     * Get the later of two times.
     *
     * @param Carbon $firstTime First time to compare
     * @param Carbon $secondTime Second time to compare
     * @return Carbon The later time
     */
    private static function getLaterTime(Carbon $firstTime, Carbon $secondTime): Carbon
    {
        return $firstTime->gt($secondTime) ? $firstTime : $secondTime;
    }


    /**
     * Determine the auto-adjusted days based on existing days and validity period.
     *
     * @param array<string>|null $days Explicitly provided days, if any
     * @param Carbon|null $validityStart Start of the validity period
     * @param Carbon|null $validityEnd End of the validity period
     * @return array<int, string> Adjusted days
     */
    public static function getAutoAdjustedDays(?array $days, ?Carbon $validityStart, ?Carbon $validityEnd): array
    {


        // Return all days if dates are invalid or auto-adjustment is disabled
        if (!self::shouldAutoAdjustDays($validityStart, $validityEnd)) {
            return DaysOfWeek::values();
        }

        // Otherwise, calculate days in period
        return roster_days_in_period($validityStart, $validityEnd);
    }

    /**
     * Determine whether automatic adjustment of days should be performed.
     *
     * @param Carbon|null $start Validity start date
     * @param Carbon|null $end Validity end date
     * @return bool True if auto-adjustment should occur
     */
    private static function shouldAutoAdjustDays(?Carbon $start, ?Carbon $end): bool
    {

        return $start instanceof Carbon
            && $end instanceof Carbon
            && roster_should_auto_adjust_days($start, $end);
    }

    /**
     * Calculate the valid days after updating validity period.
     *
     * @param array<int, string> $existingDays Current days
     * @param ?Carbon $existingValidityStart Current entity validity start
     * @param ?Carbon $existingValidityEnd Current entity validity end
     * @param ?array<int, string> $currentDays Current DTO days, if set
     * @param ?Carbon $dtoValidityStart DTO validity start
     * @param ?Carbon $dtoValidityEnd DTO validity end
     * @return array<int, string> Adjusted days
     */
    public static function getFilteredDaysForUpdate(
        array $existingDays,
        ?Carbon $existingValidityStart,
        ?Carbon $existingValidityEnd,
        ?array $currentDays = null,
        ?Carbon $dtoValidityStart = null,
        ?Carbon $dtoValidityEnd = null
    ): array {
        // 1️⃣ If DTO already has days, keep them
        if ($currentDays !== null) {
            return $currentDays;
        }

        // 2️⃣ Determine effective validity range
        $start = $dtoValidityStart ?? $existingValidityStart;
        $end   = $dtoValidityEnd ?? $existingValidityEnd;

        // 3️⃣ Detect if dates changed
        $datesChanged = $dtoValidityStart instanceof Carbon || $dtoValidityEnd instanceof Carbon;

        // 4️⃣ If date range invalid or unchanged, return existing days
        if (!$start instanceof Carbon || !$end instanceof Carbon || $start->gt($end) || !$datesChanged) {
            return $existingDays;
        }

        // 5️⃣ Filter days based on new period
        return roster_get_valid_days_in_period($existingDays, $start, $end);
    }
}
