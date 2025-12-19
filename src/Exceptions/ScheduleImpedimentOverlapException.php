<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Throwable;

/**
 * Exception thrown when a schedule overlaps with an impediment.
 *
 * This exception is specifically for conflicts between scheduled events
 * and impediments (blocked/unavailable time periods).
 */
class ScheduleImpedimentOverlapException extends RosterException
{
    /**
     * Create a new ScheduleImpedimentOverlapException instance.
     *
     * @param TimeSlotOverlapType $type Type of overlap
     * @param array $context Additional context about the overlap conflict
     * @param string $message Custom error message
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception
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
