<?php

declare(strict_types=1);

namespace Roster\Enums;

use Roster\Traits\EnumValues;

/**
 * Represents days of the week in a standardized format.
 *
 * This enum provides consistent day naming for scheduling and availability
 * calculations throughout the roster system.
 */
enum DaysOfWeek: string
{
    use EnumValues;

    case MONDAY    = 'monday';
    case TUESDAY   = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY  = 'thursday';
    case FRIDAY    = 'friday';
    case SATURDAY  = 'saturday';
    case SUNDAY    = 'sunday';
}
