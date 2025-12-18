<?php

declare(strict_types=1);

// ==== src/Facades/Roster.php ====

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;

/**
 * @method static ScheduleService schedules()
 * @method static AvailabilityService availabilities()
 * @method static ImpedimentService impediments()
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
