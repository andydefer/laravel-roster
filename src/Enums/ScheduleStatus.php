<?php

declare(strict_types=1);

namespace Roster\Enums;

use Roster\Traits\EnumValues;

enum ScheduleStatus: string
{
    use EnumValues;

    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case CANCELLED = 'cancelled';
    case BLOCKED = 'blocked';
}
