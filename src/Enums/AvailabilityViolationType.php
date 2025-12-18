<?php

declare(strict_types=1);

namespace Roster\Exceptions;

enum AvailabilityViolationType: string
{
    case DAY_NOT_IN_AVAILABILITY = 'DAY_NOT_IN_AVAILABILITY';
    case TIME_OUTSIDE_AVAILABILITY_HOURS = 'TIME_OUTSIDE_AVAILABILITY_HOURS';
    case STARTS_BEFORE_AVAILABILITY = 'STARTS_BEFORE_AVAILABILITY';
    case ENDS_AFTER_AVAILABILITY = 'ENDS_AFTER_AVAILABILITY';

    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::DAY_NOT_IN_AVAILABILITY => 'Day is not in allowed availability days',
            self::TIME_OUTSIDE_AVAILABILITY_HOURS => 'Time range is outside availability hours',
            self::STARTS_BEFORE_AVAILABILITY => 'Starts before availability start date',
            self::ENDS_AFTER_AVAILABILITY => 'Ends after availability end date',
        };
    }
}
