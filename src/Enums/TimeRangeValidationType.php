<?php

declare(strict_types=1);

namespace Roster\Exceptions;

/**
 * Enum for time range validation errors.
 */
enum TimeRangeValidationType: string
{
    case END_BEFORE_START = 'END_BEFORE_START';
    case START_AFTER_END = 'START_AFTER_END';

    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::END_BEFORE_START => 'End datetime must be after start datetime',
            self::START_AFTER_END => 'Start datetime must be before end datetime',
        };
    }
}
