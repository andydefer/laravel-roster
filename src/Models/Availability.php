<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roster\Casts\TimezoneAwareDateTimeCast;
use Roster\Domain\Helpers\TimeWindowHelper;
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
    use SoftDeletes;

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
        'validity_start' => TimezoneAwareDateTimeCast::class,
        'validity_end' => TimezoneAwareDateTimeCast::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'days' => 'array',
    ];

    /**
     * Get the schedulable resource that owns this availability.
     *
     * @return MorphTo The polymorphic relationship to schedulable resources
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the schedules associated with this availability.
     *
     * @return HasMany The schedules relationship
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'availability_id');
    }

    /**
     * Get the impediments associated with this availability.
     *
     * @return HasMany The impediments relationship
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
        TimeWindowHelper::assertDailyWindow($start, $end);

        return $this->isAvailableOnDay($start)
            && $this->isWithinDailyWindow($start, $end)
            && $this->isWithinValidityPeriod($start, $end);
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
        TimeWindowHelper::assertDailyWindow($start, $end);

        $startTime = $this->formatTimeToHoursMinutesSeconds($start);
        $endTime = $this->formatTimeToHoursMinutesSeconds($end);
        $dailyStart = $this->formatTimeToHoursMinutesSeconds($this->daily_start);
        $dailyEnd = $this->formatTimeToHoursMinutesSeconds($this->daily_end);

        return $startTime >= $dailyStart && $endTime <= $dailyEnd;
    }

    /**
     * Format a datetime to H:i:s format.
     *
     * @param Carbon $dateTime The datetime to format
     * @return string Formatted time in H:i:s format
     */
    private function formatTimeToHoursMinutesSeconds(Carbon $dateTime): string
    {
        return $dateTime->format('H:i:s');
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
        TimeWindowHelper::assertDailyWindow($start, $end);

        $hasStarted = $this->validity_start === null || $start->gte($this->validity_start);
        $hasNotEnded = $this->validity_end === null || $end->lte($this->validity_end);

        return $hasStarted && $hasNotEnded;
    }

    /**
     * Check if the availability is active on a specific date.
     *
     * @param Carbon $date Date to check
     * @return bool True if active on the given date, false otherwise
     */
    public function isActiveOnDate(Carbon $date): bool
    {
        return $this->isAvailableOnDay($date)
            && $this->isDateWithinValidity($date);
    }

    /**
     * Check if a date is within the validity period.
     *
     * @param Carbon $date Date to check
     * @return bool True if date is within validity period
     */
    private function isDateWithinValidity(Carbon $date): bool
    {
        $isAfterOrEqualStart = $this->validity_start === null || $date->gte($this->validity_start);
        $isBeforeOrEqualEnd = $this->validity_end === null || $date->lte($this->validity_end);

        return $isAfterOrEqualStart && $isBeforeOrEqualEnd;
    }

    /**
     * Get the daily slot duration in minutes.
     *
     * @return int Duration in minutes
     */
    public function getDailyDurationMinutes(): int
    {
        return (int) $this->daily_start->diffInMinutes($this->daily_end);
    }

    /**
     * Get the validity period duration in days.
     *
     * @return int|null Duration in days, or null if start or end is missing
     */
    public function getValidityDurationDays(): ?int
    {
        if ($this->validity_start === null || $this->validity_end === null) {
            return null;
        }

        return (int) $this->validity_start->diffInDays($this->validity_end);
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
        $checkDate = $date ?? Carbon::now();

        if ($this->validity_start === null) {
            return true;
        }

        return $checkDate->gte($this->validity_start);
    }

    /**
     * Check if the validity period has ended.
     *
     * @param Carbon|null $date Optional date to check (defaults to now)
     * @return bool True if validity period has ended
     */
    public function hasValidityEnded(?Carbon $date = null): bool
    {
        $checkDate = $date ?? Carbon::now();

        if ($this->validity_end === null) {
            return false;
        }

        return $checkDate->gt($this->validity_end);
    }

    /**
     * Check if the validity period is currently active.
     *
     * @param Carbon|null $date Optional date to check (defaults to now)
     * @return bool True if currently within validity period
     */
    public function isValidityActive(?Carbon $date = null): bool
    {
        $checkDate = $date ?? Carbon::now();

        return $this->hasValidityStarted($checkDate) && !$this->hasValidityEnded($checkDate);
    }
}
