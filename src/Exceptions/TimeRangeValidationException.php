<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Throwable;

/**
 * Exception thrown when time range validation fails.
 *
 * This exception is typically thrown when attempting to create or modify
 * a time slot with an invalid date/time range (e.g., end time before start time).
 */
class TimeRangeValidationException extends RosterException
{
    /**
     * Create a new TimeRangeValidationException instance.
     *
     * @param  array  $context  Additional context about the validation failure
     * @param  string  $message  Custom error message
     * @param  int  $code  Error code
     * @param  Throwable|null  $previous  Previous exception
     */
    public function __construct(
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: 'End datetime must be after start datetime';
        parent::__construct(
            type: 'END_BEFORE_START',
            message: $message,
            context: $context,
            code: $code,
            previous: $previous
        );
    }
}
