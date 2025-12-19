<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\AvailabilityViolationType;
use Throwable;

/**
 * Exception thrown when a schedule violates availability constraints.
 */
class AvailabilityViolationException extends RosterException
{
    /**
     * Create a new AvailabilityViolationException instance.
     *
     * @param AvailabilityViolationType $type Type of availability violation
     * @param array $context Additional context about the violation
     * @param string $message Custom error message
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        AvailabilityViolationType $type,
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: $type->getDefaultMessage();
        parent::__construct($type->value, $message, $context, $code, $previous);
    }
}
