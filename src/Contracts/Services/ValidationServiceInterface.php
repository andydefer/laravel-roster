<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;

interface ValidationServiceInterface
{
    /**
     * Parse and validate a time range from array data.
     *
     * @param  array<string, mixed>  $data  Data containing 'start_time' and 'end_time'
     * @return array{start: Carbon, end: Carbon} Validated time range
     *
     * @throws ValidationException When validation fails
     */
    public function parseAndValidateTimeRange(array $data): array;

    /**
     * Parse and validate a datetime range from array data.
     *
     * @param  array<string, mixed>  $data  Data containing 'start_datetime' and 'end_datetime'
     * @return array{start: Carbon, end: Carbon} Validated datetime range
     *
     * @throws ValidationException When validation fails
     */
    public function parseAndValidateDateTimeRange(array $data): array;

    /**
     * Validate duration and interval parameters.
     *
     * Ensures duration is positive and interval is not greater than duration.
     *
     * @param  int  $durationMinutes  Slot duration in minutes
     * @param  int  $intervalMinutes  Interval between slots in minutes
     *
     * @throws ValidationException When validation fails
     */
    public function validateDurationAndInterval(int $durationMinutes, int $intervalMinutes): void;

    /**
     * Validate that a time range is logical and valid.
     *
     * @param  Carbon  $start  Range start time
     * @param  Carbon  $end  Range end time
     * @param  string  $context  Validation context for error messages
     *
     * @throws ValidationException When range is invalid
     */
    public function validateTimeRange(
        Carbon $start,
        Carbon $end,
        string $context = 'datetime'
    ): void;

    /**
     * Validate that a time range meets minimum duration requirements.
     *
     * @param  Carbon  $start  Range start time
     * @param  Carbon  $end  Range end time
     * @param  int  $minimumMinutes  Minimum required duration in minutes
     *
     * @throws ValidationException When duration is insufficient
     */
    public function validateMinimumDuration(
        Carbon $start,
        Carbon $end,
        int $minimumMinutes
    ): void;

    /**
     * Validate that a date occurs in the future.
     *
     * @param  Carbon  $date  Date to validate
     *
     * @throws ValidationException When date is not in the future
     */
    public function validateFutureDate(Carbon $date): void;

    /**
     * Validate that all required fields are present in the data array.
     *
     * @param  array<string, mixed>  $data  Data to validate
     * @param  array<string>  $requiredFields  List of required field names
     *
     * @throws ValidationException When required fields are missing
     */
    public function validateRequiredFields(array $data, array $requiredFields): void;

    /**
     * Validate that a timezone string is valid.
     *
     * @param  string  $timezone  Timezone identifier to validate
     * @return bool True if timezone is valid
     */
    public function validateTimezone(string $timezone): bool;
}
