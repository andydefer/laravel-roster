<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

/**
 * Enum for time range validation errors.
 */
enum TimeRangeValidationType: string
{
    case END_BEFORE_START = 'END_BEFORE_START';

    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::END_BEFORE_START => 'End datetime must be after start datetime',
        };
    }
}
