<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Throwable;

/**
 * Exception thrown when a time slot overlaps with another resource.
 *
 * This exception covers various overlap scenarios such as schedule overlaps,
 * availability conflicts, or other time-based resource conflicts.
 */
class TimeSlotOverlapException extends RosterException
{
    /**
     * Create a new TimeSlotOverlapException instance.
     *
     * @param  TimeSlotOverlapType  $type  Type of overlap that occurred
     * @param  array  $context  Additional context about the overlap
     * @param  string  $message  Custom error message
     * @param  int  $code  Error code
     * @param  Throwable|null  $previous  Previous exception
     */
    public function __construct(
        TimeSlotOverlapType $type,
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: $type->getDefaultMessage();
        parent::__construct(
            type: $type->value,
            message: $message,
            context: $context,
            code: $code,
            previous: $previous
        );
    }
}
