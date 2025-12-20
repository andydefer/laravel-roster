<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\ValidationType;
use Throwable;

/**
 * Exception thrown when no matching availability is found for a schedule request.
 */
class NoMatchingAvailabilityException extends RosterException
{
    /**
     * Create a new NoMatchingAvailabilityException instance.
     *
     * @param  array  $context  Additional context about the failed availability lookup
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
        $message = $message ?: ValidationType::NO_MATCHING_AVAILABILITY->getDefaultMessage();

        parent::__construct(
            type: ValidationType::NO_MATCHING_AVAILABILITY->value,
            message: $message,
            context: $context,
            code: $code,
            previous: $previous
        );
    }
}
