<?php

declare(strict_types=1);

namespace Roster\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Roster\Casts\TimezoneAwareDateTimeCast;
use Roster\Domain\Helpers\TimeWindowHelper;
use Roster\Traits\BelongsToSchedulable;

/**
 * Represents a time period where a schedulable resource is unavailable.
 *
 * An impediment blocks scheduling for various reasons such as maintenance,
 * absence, or other constraints that prevent resource availability.
 *
 * @property int $id
 * @property int $availability_id
 * @property int $schedulable_id
 * @property string $schedulable_type
 * @property string $reason
 * @property Carbon $start_datetime
 * @property Carbon $end_datetime
 * @property array|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read float $duration_minutes
 * @property-read Availability $availability
 * @property-read Model $schedulable
 */
class Impediment extends Model
{
    use BelongsToSchedulable;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roster_impediments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'availability_id',
        'schedulable_id',
        'schedulable_type',
        'reason',
        'start_datetime',
        'end_datetime',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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

                return is_string($value) ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : $value;
            },
            set: function ($value): ?string {
                if ($value === null) {
                    return null;
                }

                return is_array($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;
            }
        );
    }

    /**
     * Get the availability associated with this impediment.
     *
     * @return BelongsTo<Availability, Impediment>
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    /**
     * Get the schedulable resource associated with this impediment.
     *
     * @return MorphTo<Model, Impediment>
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if this impediment overlaps with a given time period.
     *
     * @param Carbon $start Start time of the period to check
     * @param Carbon $end End time of the period to check
     * @return bool True if there is any overlap
     *
     * @throws InvalidArgumentException When the time window is not valid
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        TimeWindowHelper::assertDailyWindow($start, $end);
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Get the impediment duration in minutes.
     *
     * @return float Duration in minutes
     */
    public function getDurationMinutesAttribute(): float
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Check if the impediment is currently active.
     *
     * @return bool True if current time is within the impediment period
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Check if the impediment is scheduled to start in the future.
     *
     * @return bool True if the impediment has not started yet
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Check if the impediment has already ended.
     *
     * @return bool True if the impediment is completely in the past
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
