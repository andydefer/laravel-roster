<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

/**
 * Enum representing missing resource error types.
 *
 * This enum defines errors that occur when required resources
 * (like availability or schedulable entities) are not provided
 * or cannot be found during operations.
 */
enum MissingResourceType: string
{
    case MISSING_AVAILABILITY = 'MISSING_AVAILABILITY';
    case MISSING_SCHEDULABLE = 'MISSING_SCHEDULABLE';

    /**
     * Get the default human-readable message for this missing resource error.
     *
     * @return string The descriptive message explaining what resource is missing
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::MISSING_AVAILABILITY => 'Must belong to an Availability',
            self::MISSING_SCHEDULABLE => 'No schedulable resource specified. Call for() with a schedulable entity before executing the query.',
        };
    }
}
