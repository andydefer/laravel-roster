<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

/**
 * Enumeration of validation error types for scheduling operations.
 *
 * Defines standardized validation failure categories including time range issues,
 * duration constraints, availability matching problems, and conflict detection.
 */
enum ValidationType: string
{
    /**
     * Start datetime is not before end datetime.
     */
    case INVALID_TIME_RANGE = 'INVALID_TIME_RANGE';

    /**
     * Duration does not meet minimum requirements.
     */
    case MINIMUM_DURATION_NOT_MET = 'MINIMUM_DURATION_NOT_MET';

    /**
     * No availability matches the requested time period.
     */
    case NO_MATCHING_AVAILABILITY = 'NO_MATCHING_AVAILABILITY';

    /**
     * Availability does not belong to the specified schedulable.
     */
    case INVALID_AVAILABILITY = 'INVALID_AVAILABILITY';

    /**
     * Time slot overlaps with existing schedule.
     */
    case OVERLAPPING_TIME_SLOT = 'OVERLAPPING_TIME_SLOT';

    /**
     * Custom or unspecified validation error.
     */
    case CUSTOM = 'CUSTOM';

    /**
     * Returns the default human-readable message for the validation type.
     *
     * @return string Descriptive error message
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::INVALID_TIME_RANGE => 'End datetime must be after start datetime',
            self::MINIMUM_DURATION_NOT_MET => 'Duration must be at least the minimum required minutes',
            self::NO_MATCHING_AVAILABILITY => 'No matching availability found',
            self::INVALID_AVAILABILITY => 'The provided availability does not belong to this schedulable',
            self::OVERLAPPING_TIME_SLOT => 'Time slot overlaps with an existing schedule',
            self::CUSTOM => 'Custom validation error',
        };
    }
}
