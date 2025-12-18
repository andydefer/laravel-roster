<?php

declare(strict_types=1);

namespace Roster\Enums;

enum ScheduleStatus: string
{
    use EnumValues;
    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case CANCELLED = 'cancelled';
    case BLOCKED = 'blocked';

    /**
     * Vérifie si le statut courant est égal à un autre
     */
    public function is(ScheduleStatus $scheduleStatus): bool
    {
        return $this->value === $scheduleStatus->value;
    }
}
