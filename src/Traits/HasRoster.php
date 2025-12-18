<?php

declare(strict_types=1);

namespace Roster\Traits;

use Roster\Models\Availability;
use Roster\Models\Schedule;

trait HasRoster
{
    /**
     * Get all schedules (concrete planned time slots) for the model.
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    /**
     * Get all availabilities (availability rules) for the model.
     */
    public function availabilities()
    {
        return $this->morphMany(Availability::class, 'schedulable');
    }
}
