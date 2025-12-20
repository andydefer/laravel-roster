<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

/**
 * Enum representing general validation error types.
 *
 * This enum defines common validation failures that can occur during
 * schedule and impediment operations, including time range issues,
 * duration constraints, and availability matching problems.
 */
enum ValidationType: string
{
    case INVALID_TIME_RANGE = 'INVALID_TIME_RANGE';
    case MINIMUM_DURATION_NOT_MET = 'MINIMUM_DURATION_NOT_MET';
    case NO_MATCHING_AVAILABILITY = 'NO_MATCHING_AVAILABILITY';
    case INVALID_AVAILABILITY = 'INVALID_AVAILABILITY'; // AJOUTÉ
    case CUSTOM = 'CUSTOM';

    /**
     * Get the default human-readable message for this validation type.
     *
     * @return string The descriptive message explaining the validation error
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::INVALID_TIME_RANGE => 'End datetime must be after start datetime',
            self::MINIMUM_DURATION_NOT_MET => 'Duration must be at least the minimum required minutes',
            self::NO_MATCHING_AVAILABILITY => 'No matching availability found',
            self::INVALID_AVAILABILITY => 'The provided availability does not belong to this schedulable', // AJOUTÉ
            self::CUSTOM => 'Custom validation error',
        };
    }
}
