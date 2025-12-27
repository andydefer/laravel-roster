<?php

declare(strict_types=1);

namespace Roster\Domain\Helpers;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Utility class for validating and working with daily time windows.
 *
 * Provides validation methods to ensure time windows are properly formed
 * with start times preceding end times for daily scheduling operations.
 */
final class TimeWindowHelper
{
    /**
     * Validates that a daily time window has a start time that precedes or equals the end time.
     *
     * @param Carbon $start Start time of the window
     * @param Carbon $end End time of the window
     * @param string $fieldName Field identifier for error messages
     *
     * @throws InvalidArgumentException When end time is before start time
     */
    public static function assertDailyWindow(Carbon $start, Carbon $end, string $fieldName = 'daily_time_range'): void
    {
        if ($end->lt($start)) {
            throw new InvalidArgumentException(sprintf(
                '%s: End time (%s) must be after start time (%s)',
                $fieldName,
                $end->format('H:i:s'),
                $start->format('H:i:s')
            ));
        }
    }

    /**
     * Asserts that a daily time window is valid.
     *
     * Alias for assertDailyWindow() for API consistency.
     *
     * @param Carbon $start Start time of the window
     * @param Carbon $end End time of the window
     * @param string $fieldName Field identifier for error messages
     *
     * @throws InvalidArgumentException When end time is before start time
     */
    public static function assertValidWindow(Carbon $start, Carbon $end, string $fieldName = 'daily_time_range'): void
    {
        self::assertDailyWindow($start, $end, $fieldName);
    }

    /**
     * Checks if a daily time window is valid without throwing an exception.
     *
     * @param Carbon $start Start time of the window
     * @param Carbon $end End time of the window
     *
     * @return bool True if the window is valid (start <= end), false otherwise
     */
    public static function isValidWindow(Carbon $start, Carbon $end): bool
    {
        return !$end->lt($start);
    }
}
