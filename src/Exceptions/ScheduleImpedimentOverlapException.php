<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Throwable;

/**
 * Exception thrown when a schedule overlaps with an impediment.
 */
class ScheduleImpedimentOverlapException extends RosterException
{
    public function __construct(
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: 'Schedule overlaps with an impediment';
        parent::__construct('SCHEDULE_IMPEDIMENT_OVERLAP', $message, $context, $code, $previous);
    }
}
