<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Throwable;

/**
 * Exception thrown when an impediment overlaps with another impediment.
 */
class OverlappingImpedimentException extends RosterException
{
    public function __construct(
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: 'This time slot overlaps with an existing impediment';
        parent::__construct('OVERLAPPING_IMPEDIMENT', $message, $context, $code, $previous);
    }
}
