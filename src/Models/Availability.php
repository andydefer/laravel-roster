<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Roster\Traits\BelongsToSchedulable;

/**
 * Represents an availability period for a schedulable resource.
 *
 * An availability defines when a resource (user, team, equipment, etc.)
 * is available for scheduling, including time windows, days of week,
 * and date ranges.
 */
class Availability extends Model
{
    use BelongsToSchedulable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roster_availabilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the schedulable resource that owns this availability.
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the schedules associated with this availability.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'availability_id');
    }

    /**
     * Get the impediments associated with this availability.
     */
    public function impediments(): HasMany
    {
        return $this->hasMany(Impediment::class, 'availability_id');
    }

    /**
     * Determine if a specific time period is available for scheduling.
     *
     * Checks if the given time period falls within this availability's
     * defined days, time window, and date range.
     *
     * @param Carbon $start Start time of the period to check
     * @param Carbon $end End time of the period to check
     * @return bool True if the period is available, false otherwise
     */
    public function isAvailableForSchedule(Carbon $start, Carbon $end): bool
    {
        if (! $this->isAvailableOnDay($start)) {
            return false;
        }

        if (! $this->isWithinTimeWindow($start, $end)) {
            return false;
        }

        return $this->isWithinDateRange($start, $end);
    }

    /**
     * Check if the availability includes the given day of week.
     *
     * @param Carbon $date Date to check
     * @return bool True if the day is available, false otherwise
     */
    private function isAvailableOnDay(Carbon $date): bool
    {
        $dayOfWeek = strtolower($date->englishDayOfWeek);

        return in_array($dayOfWeek, $this->days, true);
    }

    /**
     * Check if the time period falls within the availability's time window.
     *
     * @param Carbon $start Start time to check
     * @param Carbon $end End time to check
     * @return bool True if within time window, false otherwise
     */
    private function isWithinTimeWindow(Carbon $start, Carbon $end): bool
    {
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');
        $availabilityStart = $this->start_time->format('H:i:s');
        $availabilityEnd = $this->end_time->format('H:i:s');

        return $startTime >= $availabilityStart && $endTime <= $availabilityEnd;
    }

    /**
     * Check if the time period falls within the availability's date range.
     *
     * @param Carbon $start Start date to check
     * @param Carbon $end End date to check
     * @return bool True if within date range, false otherwise
     */
    private function isWithinDateRange(Carbon $start, Carbon $end): bool
    {
        if ($this->start_date && $start->lt($this->start_date)) {
            return false;
        }

        return !($this->end_date && $end->gt($this->end_date));
    }
}
