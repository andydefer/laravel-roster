<?php

declare(strict_types=1);

namespace Roster\Exceptions;

/**
 * Exception thrown when time range validation fails.
 */
class TimeRangeValidationException extends RosterException
{
    public function __construct(
        array $context = [],
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = $message ?: 'End datetime must be after start datetime';
        parent::__construct(TimeRangeValidationType::END_BEFORE_START->value, $message, $context, $code, $previous);
    }
}
