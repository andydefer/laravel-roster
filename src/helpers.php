<?php

/**
 * Roster Helpers
 *
 * Collection of helper functions for the Roster package.
 * Provides date and day-related utilities and service instantiation helpers.
 */

use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\WeekDay;
use Carbon\Month;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Container\BindingResolutionException;
use Roster\Models\Availability as ModelsAvailability;
use Roster\Support\RosterServiceContext;

if (!function_exists('roster_day_of_week')) {
    /**
     * Returns the day of the week for a given date.
     *
     * @param string|DateTimeInterface $date Date string or DateTimeInterface instance
     * @return string|null Lowercase day name (e.g., 'monday') or null for invalid dates
     */
    function roster_day_of_week($date): ?string
    {
        try {
            $dateTime = $date instanceof DateTimeInterface ? $date : new DateTime($date);
            return strtolower($dateTime->format('l'));
        } catch (Exception $exception) {
            return null;
        }
    }
}

if (!function_exists('roster_days_in_period')) {
    /**
     * Returns all days occurring within a date period.
     *
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $startDate Start date
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $endDate End date
     * @return array<string> Unique lowercase day names within the period
     */
    function roster_days_in_period(DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): array
    {
        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $days = [];
            $currentDate = $start->copy();

            while ($currentDate <= $end) {
                $days[] = strtolower($currentDate->format('l'));
                $currentDate->addDay();
            }

            return array_unique($days);
        } catch (Exception $exception) {
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

        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        usort(
            $days,
            fn($a, $b): int =>
            array_search($a, $dayOrder, true) <=> array_search($b, $dayOrder, true)
        );

        if (count($days) === 1) {
            return ucfirst($days[0]);
        }

        $dayIndices = array_map(fn($day): false|int => array_search($day, $dayOrder, true), $days);

        $isContinuousSequence = true;
        for ($i = 0; $i < count($dayIndices) - 1; ++$i) {
            $currentIndex = $dayIndices[$i];
            $nextIndex = $dayIndices[$i + 1];

            $isConsecutive = $nextIndex === $currentIndex + 1;
            $wrapsWeekend = $currentIndex === 6 && $nextIndex === 0;

            if (!$isConsecutive && !$wrapsWeekend) {
                $isContinuousSequence = false;
                break;
            }
        }

        if ($isContinuousSequence) {
            $firstDay = ucfirst($days[0]);
            $lastDay = ucfirst(end($days));

            if ($days[0] === 'sunday' && end($days) === 'saturday') {
                return 'Monday to Sunday';
            }

            return sprintf('%s to %s', $firstDay, $lastDay);
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
    function roster_period_duration_in_days($startDate, $endDate): ?int
    {
        try {
            if (!$startDate instanceof DateTimeInterface) {
                $startDate = new DateTime($startDate);
            }

            if (!$endDate instanceof DateTimeInterface) {
                $endDate = new DateTime($endDate);
            }

            return (int) $startDate->diff($endDate)->days + 1;
        } catch (Exception $exception) {
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
    function roster_is_day_in_period(string $day, DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): bool
    {
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
    function roster_get_valid_days_in_period(array $days, DateTimeInterface|WeekDay|Month|string|int|float|null $startDate, DateTimeInterface|WeekDay|Month|string|int|float|null $endDate): array
    {
        $daysInPeriod = roster_days_in_period($startDate, $endDate);
        $validDays = array_unique(array_intersect($days, $daysInPeriod));

        $dayOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        usort(
            $validDays,
            fn($a, $b): int =>
            array_search($a, $dayOrder, true) <=> array_search($b, $dayOrder, true)
        );

        return array_values($validDays);
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
    function roster_should_auto_adjust_days($startDate, $endDate): bool
    {
        if ($startDate === null || $endDate === null) {
            return false;
        }

        try {
            $duration = roster_period_duration_in_days($startDate, $endDate);
            return $duration !== null && $duration < 7;
        } catch (Exception $exception) {

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
        return RosterServiceContext::allowViaHelper(function () use ($model) {
            /** @var AvailabilityService $service */
            $service = app('roster.availability');
            return $service->for($model);
        });
    }
}

if (!function_exists('impediment_for')) {
    /**
     * Creates an Impediment service instance for a given availability.
     * Automatically extracts the schedulable from the availability's polymorphic relationship.
     *
     * @param ModelsAvailability $modelsAvailability The availability model instance
     * @throws InvalidArgumentException If the availability has no schedulable relationship
     * @throws BindingResolutionException If the service cannot be resolved from the container
     */
    function impediment_for(ModelsAvailability $modelsAvailability): ImpedimentService
    {
        return RosterServiceContext::allowViaHelper(function () use ($modelsAvailability) {
            $schedulable = $modelsAvailability->schedulable;

            if (!$schedulable) {
                throw new InvalidArgumentException(
                    'The provided availability does not have a schedulable relationship.'
                );
            }

            /** @var ImpedimentService $service */
            $service = app('roster.impediment');
            return $service->for($schedulable)->owner($modelsAvailability);
        });
    }
}

if (!function_exists('schedule_for')) {
    /**
     * Creates a Schedule service instance for a given availability.
     * Automatically extracts the schedulable from the availability's polymorphic relationship.
     *
     * @param ModelsAvailability $modelsAvailability The availability model instance
     * @throws InvalidArgumentException If the availability has no schedulable relationship
     * @throws BindingResolutionException If the service cannot be resolved from the container
     */
    function schedule_for(ModelsAvailability $modelsAvailability): ScheduleService
    {
        return RosterServiceContext::allowViaHelper(function () use ($modelsAvailability) {
            $schedulable = $modelsAvailability->schedulable;

            if (!$schedulable) {
                throw new InvalidArgumentException(
                    'The provided availability does not have a schedulable relationship.'
                );
            }

            /** @var ScheduleService $service */
            $service = app('roster.schedule');
            return $service->for($schedulable)->owner($modelsAvailability);
        });
    }
}
