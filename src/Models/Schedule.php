<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roster\Casts\TimezoneAwareDateTimeCast;
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
 * @property array|null $metadata
 * @property-read float $duration_minutes
 * @property-read string $type
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read Availability $availability
 */
class Schedule extends Model
{
    use BelongsToSchedulable;
    use SoftDeletes;

    /**
     * The table associated with the model.
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
     * @var array<string, string|class-string>
     */
    protected $casts = [
        'status' => ScheduleStatus::class,
        'start_datetime' => TimezoneAwareDateTimeCast::class,
        'end_datetime' => TimezoneAwareDateTimeCast::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Handle metadata attribute serialization and deserialization.
     *
     * @return Attribute<array|null, array|string|null>
     */
    protected function metadata(): Attribute
    {
        return Attribute::make(
            get: function ($value): ?array {
                if ($value === null) {
                    return null;
                }

                if (is_string($value)) {
                    return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                }

                return $value;
            },
            set: function ($value): ?string {
                if ($value === null) {
                    return null;
                }

                if (is_array($value)) {
                    return json_encode($value, JSON_THROW_ON_ERROR);
                }

                return $value;
            }
        );
    }

    /**
     * Get the availability this schedule belongs to.
     *
     * @return BelongsTo<Availability, Schedule>
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(
            related: Availability::class,
            foreignKey: 'availability_id'
        );
    }

    /**
     * Get the schedulable entity through the parent availability.
     *
     * @return Relation|null The schedulable relationship
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

        $startsBeforeEnd = $this->start_datetime->lt($end);
        $endsAfterStart = $this->end_datetime->gt($start);

        return $startsBeforeEnd && $endsAfterStart;
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

        $hasStarted = $this->start_datetime->lte($now);
        $hasNotEnded = $this->end_datetime->gte($now);

        return $hasStarted && $hasNotEnded;
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
