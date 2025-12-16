<?php

namespace Roster\Traits;

use Roster\Models\Availability;
use Roster\Models\Schedule;

trait Schedulable
{
    /**
     * Un modèle peut avoir plusieurs créneaux (schedules)
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    /**
     * Un modèle peut avoir plusieurs disponibilités (availabilities)
     */
    public function availabilities()
    {
        return $this->morphMany(Availability::class, 'schedulable');
    }
}
