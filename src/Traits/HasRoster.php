<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Roster\Models\Availability;
use Roster\Models\Impediment;
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
     * @return MorphMany
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    /**
     * Defines the availabilities relationship (availability rules).
     *
     * @return MorphMany
     */
    public function availabilities()
    {
        return $this->morphMany(Availability::class, 'schedulable');
    }

    /**
     * Defines the availabilities relationship (availability rules).
     *
     * @return MorphMany
     */
    public function impediments()
    {
        return $this->morphMany(Impediment::class, 'schedulable');
    }
}
