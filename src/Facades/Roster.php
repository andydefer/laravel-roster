<?php

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Roster\Services\ScheduleService schedules()
 * @method static \Roster\Services\AvailabilityService availabilities()
 */
class Roster extends Facade
{
    /**
     * Retourne l’alias du container pour le manager
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster';
    }
}
