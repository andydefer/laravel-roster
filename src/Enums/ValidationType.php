<?php

declare(strict_types=1);

namespace Roster\Exceptions;

enum ValidationType: string
{
    case INVALID_TIME_RANGE = 'INVALID_TIME_RANGE';
    case MINIMUM_DURATION_NOT_MET = 'MINIMUM_DURATION_NOT_MET';
    case NO_MATCHING_AVAILABILITY = 'NO_MATCHING_AVAILABILITY';

    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::INVALID_TIME_RANGE => 'End datetime must be after start datetime',
            self::MINIMUM_DURATION_NOT_MET => 'Duration must be at least the minimum required minutes',
            self::NO_MATCHING_AVAILABILITY => 'No matching availability found',
        };
    }
}
