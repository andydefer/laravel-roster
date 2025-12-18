<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Support\Carbon;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\ValidationType;

class ValidationService
{
    /**
     * Validate time range with proper context.
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
     * Validate that a date is in the future.
     */
    public function validateFutureDate(Carbon $date): void
    {
        if (config('roster.availability.validate_future_dates', true) && $date->lt(Carbon::now())) {
            throw ValidationException::withMessage('Cannot schedule in the past');
        }
    }

    /**
     * Validate minimum duration.
     */
    public function validateMinimumDuration(
        Carbon $start,
        Carbon $end,
        ?int $minimumMinutes = null
    ): void {
        $defaultMinutes = match (true) {
            str_contains(debug_backtrace()[1]['function'] ?? '', 'Impediment') =>
            config('roster.durations.minimum_impediment_minutes', 5),
            str_contains(debug_backtrace()[1]['function'] ?? '', 'Schedule') =>
            config('roster.durations.minimum_schedule_minutes', 15),
            default => 1
        };

        $minimumMinutes = $minimumMinutes ?? $defaultMinutes;

        if ($start->diffInMinutes($end) < $minimumMinutes) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                [
                    'minimum_minutes' => $minimumMinutes,
                    'provided_minutes' => $start->diffInMinutes($end)
                ]
            );
        }
    }

    /**
     * Validate that required fields exist.
     *
     * @param array<string, mixed> $data
     * @param array<string> $requiredFields
     */
    public function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $requiredField) {
            if (!isset($data[$requiredField])) {
                throw new ValidationException(
                    ValidationType::INVALID_TIME_RANGE,
                    ['missing_field' => $requiredField]
                );
            }
        }
    }

    /**
     * Parse and validate datetime from array.
     *
     * @param array<string, mixed> $data
     * @return array{start: Carbon, end: Carbon}
     */
    public function parseAndValidateDateTimeRange(
        array $data,
        string $startField = 'start_datetime',
        string $endField = 'end_datetime'
    ): array {
        $this->validateRequiredFields($data, [$startField, $endField]);

        $start = Carbon::parse($data[$startField])->setTimezone(config('roster.timezone', 'UTC'));
        $end = Carbon::parse($data[$endField])->setTimezone(config('roster.timezone', 'UTC'));

        $this->validateTimeRange($start, $end);

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Parse and validate time from array.
     *
     * @param array<string, mixed> $data
     * @return array{start: Carbon, end: Carbon}
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
     * Validate timezone.
     */
    public function validateTimezone(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }
}
