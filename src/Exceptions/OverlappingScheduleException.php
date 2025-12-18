<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Throwable;

/**
 * Exception thrown when a schedule overlaps with another schedule.
 */
class OverlappingScheduleException extends RosterException
{
    public function __construct(
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: 'Schedule overlaps with another schedule';
        parent::__construct('OVERLAPPING_SCHEDULE', $message, $context, $code, $previous);
    }
}
