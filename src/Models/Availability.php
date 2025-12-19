<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Availability extends Model
{
    protected $table = 'roster_availabilities';

    protected $fillable = [
        'schedulable_id',
        'schedulable_type',
        'type',
        'start_time',
        'end_time',
        'days',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Polymorphic relationship to the owner.
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relationship to Schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'availability_id');
    }

    /**
     * Relationship to Impediments.
     */
    public function impediments(): HasMany
    {
        return $this->hasMany(Impediment::class, 'availability_id');
    }

    /**
     * Check if a given time period is available for a Schedule.
     */
    public function isAvailableForSchedule(Carbon $start, Carbon $end): bool
    {
        // Check day
        $dayOfWeek = strtolower($start->englishDayOfWeek);
        if (!in_array($dayOfWeek, $this->days)) {
            return false;
        }

        // Check time
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');

        if (
            $startTime < $this->start_time->format('H:i:s') ||
            $endTime > $this->end_time->format('H:i:s')
        ) {
            return false;
        }

        // Check period dates
        if ($this->start_date && $start->lt($this->start_date)) {
            return false;
        }

        return !($this->end_date && $end->gt($this->end_date));
    }
}
