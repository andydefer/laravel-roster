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
     * Expected keys:
     * - start_time
     * - end_time
     *
     * @param array<string, mixed> $data
     *
     * @return array{start: Carbon, end: Carbon}
     *
     * @throws ValidationException
     */
    public function parseAndValidateTimeRange(array $data): array;

    /**
     * Parse and validate a datetime range from array data.
     *
     * Expected keys:
     * - start_datetime
     * - end_datetime
     *
     * @param array<string, mixed> $data
     *
     * @return array{start: Carbon, end: Carbon}
     *
     * @throws ValidationException
     */
    public function parseAndValidateDateTimeRange(array $data): array;

    /**
     * Validate a time range.
     *
     * @param Carbon $start Start of the range
     * @param Carbon $end End of the range
     * @param string $context Optional context for validation (default: 'datetime')
     *
     * @throws ValidationException If the range is invalid
     */
    public function validateTimeRange(
        Carbon $start,
        Carbon $end,
        string $context = 'datetime'
    ): void;

    /**
     * Validate a minimum duration between two dates.
     *
     * @param Carbon $start Start of the range
     * @param Carbon $end End of the range
     * @param int $minimumMinutes Minimum duration in minutes
     *
     * @throws ValidationException If the duration is less than the minimum
     */
    public function validateMinimumDuration(
        Carbon $start,
        Carbon $end,
        int $minimumMinutes
    ): void;

    /**
     * Validate that a given date is in the future.
     *
     * This method checks if the provided date occurs after the current date and time.
     *
     * @param Carbon $date The date to validate
     *
     * @throws ValidationException If the date is not in the future
     */
    public function validateFutureDate(Carbon $date): void;
}
