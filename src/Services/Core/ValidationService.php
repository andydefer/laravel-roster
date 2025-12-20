<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Support\Carbon;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\ValidationException;

class ValidationService implements ValidationServiceInterface
{
    /**
     * Validates that a start time occurs before an end time.
     *
     * @param Carbon $start The start datetime
     * @param Carbon $end The end datetime
     * @param string $context The context for field naming (e.g., 'datetime', 'time')
     * @throws TimeRangeValidationException When end time is less than or equal to start time
     */
    public function validateTimeRange(
        Carbon $start,
        Carbon $end,
        string $context = 'datetime'
    ): void {
        if ($end->lte($start)) {
            throw new TimeRangeValidationException([
                'start_' . $context => $start->format('Y-m-d H:i:s'),
                'end_' . $context => $end->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Validates that duration and interval minutes are positive integers.
     *
     * @param int $durationMinutes Total duration in minutes
     * @param int $intervalMinutes Interval duration in minutes
     * @throws ValidationException When either value is zero or negative
     */
    public function validateDurationAndInterval(int $durationMinutes, int $intervalMinutes): void
    {
        if ($durationMinutes <= 0 || $intervalMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                [
                    'minimum_minutes' => 1,
                    'provided_minutes' => min($durationMinutes, $intervalMinutes),
                ]
            );
        }
    }

    /**
     * Validates that a date is in the future.
     *
     * @param Carbon $date The date to validate
     * @throws ValidationException When date is in the past (if validation is enabled)
     */
    public function validateFutureDate(Carbon $date): void
    {
        if (config('roster.validate_future_dates', true) && $date->lt(Carbon::now())) {
            throw ValidationException::withMessage('Cannot schedule in the past');
        }
    }

    /**
     * Validates that a time range meets minimum duration requirements.
     *
     * @param Carbon $start The start datetime
     * @param Carbon $end The end datetime
     * @param int|null $minimumMinutes Custom minimum minutes (uses defaults based on context if null)
     * @throws ValidationException When duration is less than required minimum
     */
    public function validateMinimumDuration(
        Carbon $start,
        Carbon $end,
        ?int $minimumMinutes = null
    ): void {
        $minimumMinutes = $minimumMinutes ?? $this->determineDefaultMinimumMinutes();

        if ($start->diffInMinutes($end) < $minimumMinutes) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                [
                    'minimum_minutes' => $minimumMinutes,
                    'provided_minutes' => $start->diffInMinutes($end),
                ]
            );
        }
    }

    /**
     * Determines the default minimum minutes based on calling context.
     *
     * @return int The default minimum minutes
     */
    private function determineDefaultMinimumMinutes(): int
    {
        $callerFunction = debug_backtrace()[1]['function'] ?? '';

        if (str_contains($callerFunction, 'Impediment')) {
            return config('roster.durations.minimum_impediment_minutes', 5);
        }

        if (str_contains($callerFunction, 'Schedule')) {
            return config('roster.durations.minimum_schedule_minutes', 15);
        }

        return 1;
    }

    /**
     * Validates that all required fields exist in the data array.
     *
     * @param array<string, mixed> $data The data to validate
     * @param array<string> $requiredFields List of required field names
     * @throws ValidationException When any required field is missing
     */
    public function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $requiredField) {
            if (! isset($data[$requiredField])) {
                throw new ValidationException(
                    ValidationType::INVALID_TIME_RANGE,
                    ['missing_field' => $requiredField]
                );
            }
        }
    }

    /**
     * Parses and validates datetime range from array data.
     *
     * @param array<string, mixed> $data The input data array
     * @param string $startField The key for start datetime
     * @param string $endField The key for end datetime
     * @return array{start: Carbon, end: Carbon} Validated start and end datetime objects
     * @throws ValidationException When required fields are missing or time range is invalid
     */
    public function parseAndValidateDateTimeRange(
        array $data,
        string $startField = 'start_datetime',
        string $endField = 'end_datetime'
    ): array {
        $this->validateRequiredFields($data, [$startField, $endField]);

        $timezone = config('roster.timezone', 'UTC');
        $start = Carbon::parse($data[$startField])->setTimezone($timezone);
        $end = Carbon::parse($data[$endField])->setTimezone($timezone);

        $this->validateTimeRange($start, $end);

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Parses and validates time range from array data.
     *
     * @param array<string, mixed> $data The input data array
     * @param string $startField The key for start time
     * @param string $endField The key for end time
     * @return array{start: Carbon, end: Carbon} Validated start and end time objects
     * @throws ValidationException When required fields are missing or time range is invalid
     */
    public function parseAndValidateTimeRange(
        array $data,
        string $startField = 'start_time',
        string $endField = 'end_time'
    ): array {
        $this->validateRequiredFields($data, [$startField, $endField]);

        $start = Carbon::parse($data[$startField]);
        $end = Carbon::parse($data[$endField]);

        $this->validateTimeRange($start, $end, 'time');

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Validates that a timezone string is recognized by PHP.
     *
     * @param string $timezone The timezone identifier to validate
     * @return bool True if the timezone is valid, false otherwise
     */
    public function validateTimezone(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }
}
