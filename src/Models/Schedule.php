<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roster\Domain\Helpers\TimeWindowHelper;
use Roster\Enums\ScheduleStatus;
use Roster\Traits\BelongsToSchedulable;

/**
 * Represents a scheduled event within an availability period.
 *
 * A schedule is a booked time slot within an availability, representing
 * an actual commitment or appointment that has been scheduled.
 *
 * @property int $id
 * @property int $availability_id
 * @property int $schedulable_id
 * @property string $schedulable_type
 * @property string $title
 * @property string|null $description
 * @property Carbon $start_datetime
 * @property Carbon $end_datetime
 * @property ScheduleStatus $status
 * @property array $metadata
 * @property-read float $duration_minutes
 * @property-read string $type
 * @property-read Availability $availability
 */
class Schedule extends Model
{
    use BelongsToSchedulable;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roster_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'availability_id',
        'schedulable_id',
        'schedulable_type',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'status' => ScheduleStatus::class,
        'metadata' => 'array',
    ];

    /**
     * Get the availability this schedule belongs to.
     *
     * @return BelongsTo<Availability, Schedule>
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    /**
     * Get the schedulable entity through the parent availability.
     */
    public function schedulable(): ?Relation
    {
        return $this->availability?->schedulable();
    }

    /**
     * Get the type from the parent availability.
     *
     * @return string The availability type
     */
    public function getTypeAttribute(): string
    {
        return $this->availability->type;
    }

    /**
     * Determine if this schedule overlaps with a given time period.
     *
     * @param Carbon $start Start time of the period to check
     * @param Carbon $end End time of the period to check
     * @return bool True if the schedule overlaps with the period
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        TimeWindowHelper::assertDailyWindow($start, $end);
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Get the duration of the schedule in minutes.
     *
     * @return float Duration in minutes
     */
    public function getDurationMinutesAttribute(): float
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Check if the schedule is currently active.
     *
     * @return bool True if the schedule is currently active
     */
    public function isActive(): bool
    {
        $now = Carbon::now();

        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Check if the schedule is scheduled to start in the future.
     *
     * @return bool True if the schedule is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Check if the schedule has already ended.
     *
     * @return bool True if the schedule is in the past
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
