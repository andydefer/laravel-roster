<?php

declare(strict_types=1);

namespace Roster\Exceptions;

/**
 * Exception thrown when a resource violates availability constraints.
 */
class AvailabilityViolationException extends RosterException
{
    public function __construct(
        AvailabilityViolationType $type,
        array $context = [],
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = $message ?: $type->getDefaultMessage();
        parent::__construct($type->value, $message, $context, $code, $previous);
    }
}
