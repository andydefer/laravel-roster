<?php

declare(strict_types=1);

namespace Roster\Traits;

use Roster\Models\Availability;
use Roster\Models\Schedule;

/**
 * Enables a model to have roster-related relationships.
 *
 * Provides morph relationships for schedules and availabilities.
 */
trait HasRoster
{
    /**
     * Defines the schedules relationship (concrete planned time slots).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    /**
     * Defines the availabilities relationship (availability rules).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function availabilities()
    {
        return $this->morphMany(Availability::class, 'schedulable');
    }
}
