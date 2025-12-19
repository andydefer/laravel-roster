<?php

declare(strict_types=1);

namespace Roster\Enums;

use Roster\Traits\EnumValues;

/**
 * Represents the current status of a schedule item.
 *
 * This enum defines the possible states for schedule items, indicating
 * availability, booking status, or administrative blocks.
 */
enum ScheduleStatus: string
{
    use EnumValues;

    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case CANCELLED = 'cancelled';
    case BLOCKED = 'blocked';
}
