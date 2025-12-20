<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Throwable;

/**
 * Exception thrown when a time slot overlaps with an existing impediment.
 *
 * Used to indicate scheduling conflicts with impediments (blocks, reservations,
 * or other constraints) that prevent scheduling in a particular time slot.
 */
class OverlappingImpedimentException extends RosterException
{
    /**
     * Create a new OverlappingImpedimentException instance.
     *
     * @param TimeSlotOverlapType $type Type of impediment overlap
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
