<?php
// ==== src/Facades/Roster.php ====

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Roster\Services\ScheduleService schedules()
 * @method static \Roster\Services\AvailabilityService availabilities()
 * @method static \Roster\Services\ImpedimentService impediments()
 */
class Roster extends Facade
{
    /**
     * Retourne l'alias du container pour le manager
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster';
    }
}
