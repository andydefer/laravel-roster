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
        'daily_start',
        'daily_end',
        'days',
        'validity_start',
        'validity_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'daily_start' => 'datetime:h:i:s',
        'daily_end' => 'datetime:h:i:s',
        'validity_start' => 'datetime',  // Changé de 'date' à 'datetime'
        'validity_end' => 'datetime',     // Changé de 'date' à 'datetime'
        'days' => 'array'
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
        if (!$this->isAvailableOnDay($start)) {
            return false;
        }

        if (!$this->isWithinDailyWindow($start, $end)) {
            return false;
        }

        return $this->isWithinValidityPeriod($start, $end);
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
     * Check if the time period falls within the availability's daily time window.
     *
     * @param Carbon $start Start time to check
     * @param Carbon $end End time to check
     * @return bool True if within daily time window, false otherwise
     */
    private function isWithinDailyWindow(Carbon $start, Carbon $end): bool
    {
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');
        $dailyStart = $this->daily_start->format('H:i:s');
        $dailyEnd = $this->daily_end->format('H:i:s');

        return $startTime >= $dailyStart && $endTime <= $dailyEnd;
    }

    /**
     * Check if the time period falls within the availability's validity period.
     *
     * @param Carbon $start Start date to check
     * @param Carbon $end End date to check
     * @return bool True if within validity period, false otherwise
     */
    private function isWithinValidityPeriod(Carbon $start, Carbon $end): bool
    {
        if ($this->validity_start && $start->lt($this->validity_start)) {
            return false;
        }

        return !($this->validity_end && $end->gt($this->validity_end));
    }

    /**
     * Check if the availability is active on a specific date.
     *
     * @param Carbon $date Date to check
     * @return bool True if active on the given date, false otherwise
     */
    public function isActiveOnDate(Carbon $date): bool
    {
        if (!$this->isAvailableOnDay($date)) {
            return false;
        }

        if ($this->validity_start && $date->lt($this->validity_start)) {
            return false;
        }

        return !($this->validity_end && $date->gt($this->validity_end));
    }

    /**
     * Get the daily slot duration in minutes.
     *
     * @return int Duration in minutes
     */
    public function getDailyDurationMinutes(): int
    {
        return $this->daily_start->diffInMinutes($this->daily_end);
    }

    /**
     * Get the validity period duration in days.
     *
     * @return int|null Duration in days, or null if unlimited
     */
    public function getValidityDurationDays(): ?int
    {
        if (!$this->validity_start || !$this->validity_end) {
            return null;
        }

        return $this->validity_start->diffInDays($this->validity_end);
    }

    /**
     * Check if the validity period is unlimited (no end date).
     *
     * @return bool True if unlimited, false otherwise
     */
    public function hasUnlimitedValidity(): bool
    {
        return $this->validity_end === null;
    }

    /**
     * Check if the validity period has started.
     *
     * @param Carbon|null $date Optional date to check (defaults to now)
     * @return bool True if validity period has started
     */
    public function hasValidityStarted(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::now();

        if ($this->validity_start === null) {
            return true; // No start date means it's always started
        }

        return $date->gte($this->validity_start);
    }

    /**
     * Check if the validity period has ended.
     *
     * @param Carbon|null $date Optional date to check (defaults to now)
     * @return bool True if validity period has ended
     */
    public function hasValidityEnded(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::now();

        if ($this->validity_end === null) {
            return false; // No end date means it never ends
        }

        return $date->gt($this->validity_end);
    }

    /**
     * Check if the validity period is currently active.
     *
     * @param Carbon|null $date Optional date to check (defaults to now)
     * @return bool True if currently within validity period
     */
    public function isValidityActive(?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::now();

        return $this->hasValidityStarted($date) && !$this->hasValidityEnded($date);
    }
}
