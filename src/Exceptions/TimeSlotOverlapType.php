<?php

declare(strict_types=1);

namespace Roster\Exceptions;

enum TimeSlotOverlapType: string
{
    case SCHEDULE_OVERLAP = 'SCHEDULE_OVERLAP';
    case IMPEDIMENT_OVERLAP = 'IMPEDIMENT_OVERLAP';
    case SCHEDULE_IMPEDIMENT_OVERLAP = 'SCHEDULE_IMPEDIMENT_OVERLAP';
    case IMPEDIMENT_SCHEDULE_OVERLAP = 'IMPEDIMENT_SCHEDULE_OVERLAP';

    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::SCHEDULE_OVERLAP => 'Schedule overlaps with another schedule',
            self::IMPEDIMENT_OVERLAP => 'Time slot overlaps with an existing impediment',
            self::SCHEDULE_IMPEDIMENT_OVERLAP => 'Schedule overlaps with an impediment',
            self::IMPEDIMENT_SCHEDULE_OVERLAP => 'Cannot create impediment that overlaps with existing schedules',
        };
    }
}
