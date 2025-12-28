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
     * Unified method to determine adjusted days for both create and update operations.
     *
     * @param array<int, string>|null $requestedDays Days from the request (DTO)
     * @param Carbon|null $validityStart Validity start date
     * @param Carbon|null $validityEnd Validity end date
     * @param array<int, string>|null $existingDays Existing days from entity (for update operations)
     * @param Carbon|null $existingValidityStart Existing validity start (for update operations)
     * @param Carbon|null $existingValidityEnd Existing validity end (for update operations)
     * @param bool $isUpdate Whether this is an update operation
     * @return array<int, string> Adjusted days
     */
    public static function getAdjustedDays(
        ?array $requestedDays,
        ?Carbon $validityStart,
        ?Carbon $validityEnd,
        ?array $existingDays = null,
        ?Carbon $existingValidityStart = null,
        ?Carbon $existingValidityEnd = null,
        bool $isUpdate = false
    ): array {
        // For CREATE operation
        if (!$isUpdate) {
            return self::getAutoAdjustedDays($requestedDays, $validityStart, $validityEnd);
        }

        // For UPDATE operation
        // 1️⃣ If DTO already has days, keep them
        if ($requestedDays !== null) {
            return $requestedDays;
        }

        // 2️⃣ Determine effective validity range
        $start = $validityStart ?? $existingValidityStart;
        $end = $validityEnd ?? $existingValidityEnd;

        // 3️⃣ Detect if dates changed
        $datesChanged = $validityStart instanceof Carbon || $validityEnd instanceof Carbon;

        // 4️⃣ If date range invalid or unchanged, return existing days
        if (!$start instanceof Carbon || !$end instanceof Carbon || $start->gt($end) || !$datesChanged) {
            return $existingDays ?? [];
        }

        // 5️⃣ Filter days based on new period
        return roster_get_valid_days_in_period($existingDays ?? [], $start, $end);
    }

    /**
     * Determine the auto-adjusted days based on provided days and validity period.
     *
     * Rules:
     * - If days are explicitly provided, they are returned as-is
     * - If validity dates are invalid or auto-adjustment is disabled, all days are returned
     * - Otherwise, days are calculated from the validity period
     *
     * @param array<int, string>|null $days Explicitly provided days
     * @param Carbon|null $validityStart Validity start date
     * @param Carbon|null $validityEnd Validity end date
     * @return array<int, string> Adjusted days
     */
    private static function getAutoAdjustedDays(
        ?array $days,
        ?Carbon $validityStart,
        ?Carbon $validityEnd
    ): array {
        // 1️⃣ Explicit days always win
        if ($days !== null) {
            return $days;
        }

        // 2️⃣ Invalid range or auto-adjust disabled → all days
        if (
            !$validityStart instanceof Carbon ||
            !$validityEnd instanceof Carbon ||
            $validityStart->gt($validityEnd) ||
            !roster_should_auto_adjust_days($validityStart, $validityEnd)
        ) {
            return DaysOfWeek::values();
        }

        // 3️⃣ Auto-adjust days from period
        return roster_days_in_period($validityStart, $validityEnd);
    }
}
