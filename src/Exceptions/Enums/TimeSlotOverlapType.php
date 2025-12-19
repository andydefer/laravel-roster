<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

/**
 * Enum representing types of time slot overlaps.
 *
 * This enum defines various overlap scenarios between schedules and impediments,
 * helping to identify the specific nature of scheduling conflicts.
 */
enum TimeSlotOverlapType: string
{
    case SCHEDULE_OVERLAP = 'SCHEDULE_OVERLAP';
    case IMPEDIMENT_OVERLAP = 'IMPEDIMENT_OVERLAP';
    case SCHEDULE_IMPEDIMENT_CONFLICT = 'SCHEDULE_IMPEDIMENT_CONFLICT';

    /**
     * Get the default human-readable message for this overlap type.
     *
     * @return string The descriptive message explaining the overlap conflict
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::SCHEDULE_OVERLAP => 'Schedule overlaps with another schedule',
            self::IMPEDIMENT_OVERLAP => 'Time slot overlaps with an existing impediment',
            self::SCHEDULE_IMPEDIMENT_CONFLICT => 'Cannot schedule when impediment exists, or create impediment that overlaps with schedule',
        };
    }
}
