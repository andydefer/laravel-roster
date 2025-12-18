<?php

declare(strict_types=1);

namespace Roster\Enums;

use Roster\Traits\EnumValues;

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
