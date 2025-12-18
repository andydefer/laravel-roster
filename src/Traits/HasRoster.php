<?php

declare(strict_types=1);

namespace Roster\Traits;

use Roster\Models\Availability;
use Roster\Models\Schedule;

trait HasRoster
{
    /**
     * Le modèle possède des schedules (planifications concrètes)
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    /**
     * Le modèle possède des availabilities (règles de disponibilité)
     */
    public function availabilities()
    {
        return $this->morphMany(Availability::class, 'schedulable');
    }
}
