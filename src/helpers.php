<?php

declare(strict_types=1);

/**
 * Roster Helpers
 *
 * Collection of helper functions for the Roster package.
 * Provides date and day-related utilities and service instantiation helpers.
 */

use Carbon\Month;
use Carbon\WeekDay;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Roster\Support\RosterServiceContext;

if (!function_exists('roster_day_of_week')) {
    /**
     * Returns the day of the week for a given date.
     *
     * @param string|DateTimeInterface $date Date string or DateTimeInterface instance
     * @return string|null Lowercase day name (e.g., 'monday') or null for invalid dates
     */
    function roster_day_of_week(string|DateTimeInterface $date): ?string
    {
        try {
            $dateTime = $date instanceof DateTimeInterface ? $date : new DateTime($date);
            return strtolower($dateTime->format('l'));
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('roster_days_in_period')) {
    /**
     * Returns all days occurring within a date period, in standard week order.
     *
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $startDate
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $endDate
     * @return array<string> Unique lowercase day names within the period, sorted Monday → Sunday
     */
    function roster_days_in_period(
        DateTimeInterface|WeekDay|Month|string|int|float|null $startDate,
        DateTimeInterface|WeekDay|Month|string|int|float|null $endDate
    ): array {
        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $days = [];

            for ($currentDate = $start->copy(); $currentDate <= $end; $currentDate->addDay()) {
                $days[] = strtolower($currentDate->format('l'));
            }

            $days = array_unique($days);

            $weekOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            usort($days, function ($a, $b) use ($weekOrder) {
                return array_search($a, $weekOrder) <=> array_search($b, $weekOrder);
            });

            return $days;
        } catch (Exception) {
            return [];
        }
    }
}

if (!function_exists('roster_format_period_days_for_display')) {
    /**
     * Formats period days for human-readable display.
     * Detects continuous sequences and formats them as "X to Y".
     *
     * @param array<string> $days List of lowercase day names (should be sorted)
     * @return string Formatted display string (e.g., "Thursday to Sunday" or "Monday, Wednesday and Friday")
     */
    function roster_format_period_days_for_display(array $days): string
    {
        if ($days === []) {
            return '';
        }

        $days = sort_days_by_week_order($days);

        if (count($days) === 1) {
            return ucfirst($days[0]);
        }

        if (is_continuous_sequence($days)) {
            return format_continuous_sequence($days);
        }

        return roster_format_days_for_display($days);
    }
}

if (!function_exists('roster_format_days_for_display')) {
    /**
     * Formats a list of days for human-readable display.
     *
     * @param array<string> $days List of lowercase day names
     * @return string Formatted display string (e.g., "Monday, Tuesday and Thursday")
     */
    function roster_format_days_for_display(array $days): string
    {
        if ($days === []) {
            return '';
        }

        $capitalizedDays = array_map('ucfirst', $days);

        if (count($capitalizedDays) === 1) {
            return $capitalizedDays[0];
        }

        if (count($capitalizedDays) === 2) {
            return $capitalizedDays[0] . ' and ' . $capitalizedDays[1];
        }

        $lastDay = array_pop($capitalizedDays);
        return implode(', ', $capitalizedDays) . ' and ' . $lastDay;
    }
}

if (!function_exists('roster_period_duration_in_days')) {
    /**
     * Calculates the duration of a period in days.
     *
     * @param string|DateTimeInterface $startDate Start date
     * @param string|DateTimeInterface $endDate End date
     * @return int|null Number of days (inclusive) or null for invalid dates
     */
    function roster_period_duration_in_days(
        string|DateTimeInterface $startDate,
        string|DateTimeInterface $endDate
    ): ?int {
        try {
            if (!$startDate instanceof DateTimeInterface) {
                $startDate = new DateTime($startDate);
            }

            if (!$endDate instanceof DateTimeInterface) {
                $endDate = new DateTime($endDate);
            }

            return (int) $startDate->diff($endDate)->days + 1;
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('roster_is_day_in_period')) {
    /**
     * Checks if a specific day occurs within a date period.
     *
     * @param string $day Day to check (e.g., 'monday')
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $startDate Start date
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $endDate End date
     * @return bool True if the day occurs within the period
     */
    function roster_is_day_in_period(
        string $day,
        DateTimeInterface|WeekDay|Month|string|int|float|null $startDate,
        DateTimeInterface|WeekDay|Month|string|int|float|null $endDate
    ): bool {
        $daysInPeriod = roster_days_in_period($startDate, $endDate);
        return in_array($day, $daysInPeriod, true);
    }
}

if (!function_exists('roster_get_valid_days_in_period')) {
    /**
     * Filters a list of days to keep only those occurring within a date period.
     *
     * @param array<string> $days List of days to filter
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $startDate Start date
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $endDate End date
     * @return array<string> Filtered days sorted in week order
     */
    function roster_get_valid_days_in_period(
        array $days,
        DateTimeInterface|WeekDay|Month|string|int|float|null $startDate,
        DateTimeInterface|WeekDay|Month|string|int|float|null $endDate
    ): array {
        $daysInPeriod = roster_days_in_period($startDate, $endDate);
        $validDays = array_unique(array_intersect($days, $daysInPeriod));

        return sort_days_by_week_order($validDays);
    }
}

if (!function_exists('roster_should_auto_adjust_days')) {
    /**
     * Determines whether days should be automatically adjusted based on period duration.
     *
     * @param string|DateTimeInterface|null $startDate Start date
     * @param string|DateTimeInterface|null $endDate End date
     * @return bool True if auto-adjustment should occur (period duration < 7 days)
     */
    function roster_should_auto_adjust_days(
        string|DateTimeInterface|null $startDate,
        string|DateTimeInterface|null $endDate
    ): bool {
        if ($startDate === null || $endDate === null) {
            return false;
        }

        try {
            $duration = roster_period_duration_in_days($startDate, $endDate);
            return $duration !== null && $duration < 7;
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('availability_for')) {
    /**
     * Creates an Availability service instance for a given schedulable model.
     *
     * @param Model $model The schedulable model instance
     * @throws BindingResolutionException If the service cannot be resolved from the container
     */
    function availability_for(Model $model): AvailabilityService
    {
        return RosterServiceContext::allow(function () use ($model) {
            /** @var AvailabilityService $service */
            $service = app('roster.availability');
            return $service->for(model: $model);
        });
    }
}

if (!function_exists('impediment_for')) {
    /**
     * Creates an Impediment service instance for a given availability.
     * Automatically extracts the schedulable from the availability's polymorphic relationship.
     *
     * @param Availability $availability The availability model instance
     * @throws InvalidArgumentException If the availability has no schedulable relationship
     * @throws BindingResolutionException If the service cannot be resolved from the container
     */
    function impediment_for(Availability $availability): ImpedimentService
    {
        return RosterServiceContext::allow(function () use ($availability) {
            $schedulable = $availability->schedulable;

            if (!$schedulable) {
                throw new InvalidArgumentException(
                    'The provided availability does not have a schedulable relationship.'
                );
            }

            /** @var ImpedimentService $service */
            $service = app('roster.impediment');
            return $service->for(model: $schedulable)->owner(model: $availability);
        });
    }
}

if (!function_exists('schedule_for')) {
    /**
     * Creates a Schedule service instance for a given availability.
     * Automatically extracts the schedulable from the availability's polymorphic relationship.
     *
     * @param Availability $availability The availability model instance
     * @throws InvalidArgumentException If the availability has no schedulable relationship
     * @throws BindingResolutionException If the service cannot be resolved from the container
     */
    function schedule_for(Availability $availability): ScheduleService
    {
        return RosterServiceContext::allow(function () use ($availability) {
            $schedulable = $availability->schedulable;

            if (!$schedulable) {
                throw new InvalidArgumentException(
                    'The provided availability does not have a schedulable relationship.'
                );
            }

            /** @var ScheduleService $service */
            $service = app('roster.schedule');
            return $service->for(model: $schedulable)->owner(model: $availability);
        });
    }
}

if (!function_exists('roster_timezone')) {
    /**
     * Get the current effective timezone.
     *
     * @return string Current timezone identifier
     */
    function roster_timezone(): string
    {
        return TimezoneHelper::getEffectiveTimezone();
    }
}

if (!function_exists('roster_to_utc')) {
    /**
     * Convert datetime to UTC for storage.
     *
     * @param mixed $datetime Datetime to convert
     * @return Carbon|null Datetime converted to UTC, or null on failure
     */
    function roster_to_utc(mixed $datetime): ?Carbon
    {
        return TimezoneHelper::toSystem($datetime);
    }
}

if (!function_exists('roster_to_user_timezone')) {
    /**
     * Convert datetime to user timezone for display.
     *
     * @param mixed $datetime Datetime to convert
     * @return Carbon|null Datetime converted to user timezone, or null on failure
     */
    function roster_to_user_timezone(mixed $datetime): ?Carbon
    {
        return TimezoneHelper::toUser($datetime);
    }
}

if (!function_exists('roster_format_local')) {
    /**
     * Format datetime in user's local timezone.
     *
     * @param mixed $datetime Datetime to format
     * @param string $format PHP date format string
     * @return string|null Formatted datetime string, or null on failure
     */
    function roster_format_local(mixed $datetime, string $format = 'Y-m-d H:i:s'): ?string
    {
        return TimezoneHelper::formatForDisplay($datetime, $format);
    }
}

if (!function_exists('sort_days_by_week_order')) {
    /**
     * Sorts days according to week order (Monday to Sunday).
     *
     * @param array<string> $days Days to sort
     * @return array<string> Sorted days
     */
    function sort_days_by_week_order(array $days): array
    {
        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        usort($days, function (string $firstDay, string $secondDay) use ($dayOrder): int {
            $firstIndex = array_search($firstDay, $dayOrder, true);
            $secondIndex = array_search($secondDay, $dayOrder, true);
            return $firstIndex <=> $secondIndex;
        });

        return $days;
    }
}

if (!function_exists('is_continuous_sequence')) {
    /**
     * Checks if days form a continuous sequence (wrapping weekends allowed).
     *
     * @param array<string> $days Days to check (must be sorted by week order)
     * @return bool True if days form a continuous sequence
     */
    function is_continuous_sequence(array $days): bool
    {
        $dayIndices = array_map(
            function (string $day): int {
                $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                return array_search($day, $dayOrder, true);
            },
            $days
        );

        for ($index = 0; $index < count($dayIndices) - 1; ++$index) {
            $currentIndex = $dayIndices[$index];
            $nextIndex = $dayIndices[$index + 1];

            $isConsecutive = $nextIndex === $currentIndex + 1;
            $wrapsWeekend = $currentIndex === 6 && $nextIndex === 0;

            if (!$isConsecutive && !$wrapsWeekend) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('format_continuous_sequence')) {
    /**
     * Formats a continuous sequence of days for display.
     *
     * @param array<string> $days Days in sequence (must be sorted and continuous)
     * @return string Formatted display string
     */
    function format_continuous_sequence(array $days): string
    {
        $firstDay = ucfirst($days[0]);
        $lastDay = ucfirst(end($days));

        if ($days[0] === 'sunday' && end($days) === 'saturday') {
            return 'Monday to Sunday';
        }

        return sprintf('%s to %s', $firstDay, $lastDay);
    }
}
