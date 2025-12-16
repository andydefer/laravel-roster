<?php

namespace Roster\Enums;

enum ScheduleStatus: string
{
    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case CANCELLED = 'cancelled';
    case BLOCKED = 'blocked';

    /**
     * Retourne toutes les valeurs possibles de l'enum
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Vérifie si le statut courant est égal à un autre
     */
    public function is(ScheduleStatus $status): bool
    {
        return $this->value === $status->value;
    }
}
