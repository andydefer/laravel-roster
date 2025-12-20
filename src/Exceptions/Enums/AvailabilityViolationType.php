<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

/**
 * Represents types of availability constraint violations.
 *
 * Used to classify specific cases where a schedule or impediment violates
 * the defined availability constraints of a resource.
 */
enum AvailabilityViolationType: string
{
    case DAY_NOT_IN_AVAILABILITY = 'DAY_NOT_IN_AVAILABILITY';
    case TIME_OUTSIDE_AVAILABILITY_HOURS = 'TIME_OUTSIDE_AVAILABILITY_HOURS';
    case STARTS_BEFORE_AVAILABILITY = 'STARTS_BEFORE_AVAILABILITY';
    case ENDS_AFTER_AVAILABILITY = 'ENDS_AFTER_AVAILABILITY';

    /**
     * Get the default human-readable message for this violation type.
     *
     * @return string Descriptive message explaining the violation
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::DAY_NOT_IN_AVAILABILITY => 'Schedule day is not available',
            self::TIME_OUTSIDE_AVAILABILITY_HOURS => 'Schedule time range is outside Availability hours',
            self::STARTS_BEFORE_AVAILABILITY => 'Schedule starts before availability period',
            self::ENDS_AFTER_AVAILABILITY => 'Schedule ends after availability period',
        };
    }
}
