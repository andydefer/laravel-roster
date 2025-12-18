<?php

declare(strict_types=1);

namespace Roster\Exceptions;

/**
 * Enum containing exception types for the Roster package.
 */
enum ExceptionType: string
{
    // Availability violations
    case DAY_NOT_IN_AVAILABILITY = 'DAY_NOT_IN_AVAILABILITY';
    case TIME_OUTSIDE_AVAILABILITY_HOURS = 'TIME_OUTSIDE_AVAILABILITY_HOURS';
    case STARTS_BEFORE_AVAILABILITY = 'STARTS_BEFORE_AVAILABILITY';
    case ENDS_AFTER_AVAILABILITY = 'ENDS_AFTER_AVAILABILITY';

        // Time slot overlaps
    case SCHEDULE_OVERLAP = 'SCHEDULE_OVERLAP';
    case IMPEDIMENT_OVERLAP = 'IMPEDIMENT_OVERLAP';
    case SCHEDULE_IMPEDIMENT_OVERLAP = 'SCHEDULE_IMPEDIMENT_OVERLAP';
    case IMPEDIMENT_SCHEDULE_OVERLAP = 'IMPEDIMENT_SCHEDULE_OVERLAP';

        // Missing parent/required resources
    case MISSING_AVAILABILITY = 'MISSING_AVAILABILITY';
    case MISSING_SCHEDULABLE = 'MISSING_SCHEDULABLE';

        // Validation errors
    case INVALID_TIME_RANGE = 'INVALID_TIME_RANGE';
    case MINIMUM_DURATION_NOT_MET = 'MINIMUM_DURATION_NOT_MET';
    case NO_MATCHING_AVAILABILITY = 'NO_MATCHING_AVAILABILITY';

    /**
     * Get the default message for the exception type.
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            // Availability violations
            self::DAY_NOT_IN_AVAILABILITY => 'Day is not in allowed availability days',
            self::TIME_OUTSIDE_AVAILABILITY_HOURS => 'Time range is outside availability hours',
            self::STARTS_BEFORE_AVAILABILITY => 'Starts before availability start date',
            self::ENDS_AFTER_AVAILABILITY => 'Ends after availability end date',

            // Time slot overlaps
            self::SCHEDULE_OVERLAP => 'Schedule overlaps with another schedule',
            self::IMPEDIMENT_OVERLAP => 'Time slot overlaps with an existing impediment',
            self::SCHEDULE_IMPEDIMENT_OVERLAP => 'Schedule overlaps with an impediment',
            self::IMPEDIMENT_SCHEDULE_OVERLAP => 'Cannot create impediment that overlaps with existing schedules',

            // Missing parent/required resources
            self::MISSING_AVAILABILITY => 'Must belong to an Availability',
            self::MISSING_SCHEDULABLE => 'No schedulable specified. Use the for() method before executing the query.',

            // Validation errors
            self::INVALID_TIME_RANGE => 'End datetime must be after start datetime',
            self::MINIMUM_DURATION_NOT_MET => 'Duration must be at least the minimum required minutes',
            self::NO_MATCHING_AVAILABILITY => 'No matching availability found',
        };
    }
}
