<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Throwable;

/**
 * Exception thrown when no matching availability is found.
 */
class NoMatchingAvailabilityException extends RosterException
{
    public function __construct(
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: 'No matching availability found';
        parent::__construct('NO_MATCHING_AVAILABILITY', $message, $context, $code, $previous);
    }
}
