<?php

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;
use Roster\Services\AvailabilityService;
use Illuminate\Database\Eloquent\Model;

class Availability extends Facade
{
    /**
     * Retourne l'alias du container pour le service
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.availability';
    }
}
