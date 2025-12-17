<?php

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;

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
