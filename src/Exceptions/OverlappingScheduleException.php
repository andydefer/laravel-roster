<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Throwable;

/**
 * Exception thrown when a schedule overlaps with an existing schedule.
 *
 * This exception provides specific information about the type of schedule overlap
 * and includes contextual data for debugging and error handling.
 */
class OverlappingScheduleException extends RosterException
{
    /**
     * Create a new OverlappingScheduleException instance.
     *
     * @param TimeSlotOverlapType $type Type of schedule overlap
     * @param array $context Additional context about the overlap
     * @param string $message Custom error message (uses default from TimeSlotOverlapType if empty)
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception in the chain
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
