<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Roster\Traits\BelongsToSchedulable;

/**
 * Represents an impediment that blocks scheduling within a specific time period.
 *
 * An impediment is a time period where a schedulable resource is unavailable
 * for any scheduling due to various reasons (maintenance, absence, etc.).
 *
 * @property int $id
 * @property int $availability_id
 * @property int $schedulable_id
 * @property string $schedulable_type
 * @property string $reason
 * @property Carbon $start_datetime
 * @property Carbon $end_datetime
 * @property array $metadata
 * @property-read float $duration_minutes
 * @property-read Availability $availability
 */
class Impediment extends Model
{
    use BelongsToSchedulable;

    protected $table = 'roster_impediments';

    protected $fillable = [
        'availability_id',
        'schedulable_id',
        'schedulable_type',
        'reason',
        'start_datetime',
        'end_datetime',
        'metadata',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];


    /**
     * Accessor & mutator for metadata.
     * Accepts either a JSON string or an array from the user.
     */
    protected function metadata(): Attribute
    {
        return Attribute::make(
            get: fn($value) => is_string($value) ? json_decode($value, true) : $value,
            set: fn($value) => is_array($value) ? json_encode($value) : $value
        );
    }

    /**
     * Get the availability this impediment belongs to.
     *
     * @return BelongsTo<Availability, Impediment>
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    /**
     * Get the schedulable entity associated with this impediment.
     *
     * @return MorphTo
     */
    public function schedulable()
    {
        return $this->morphTo();
    }

    /**
     * Determine if this impediment overlaps with a given time period.
     *
     * @param Carbon $start The start of the period to check
     * @param Carbon $end The end of the period to check
     * @return bool True if the impediment overlaps with the period
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Get the duration of the impediment in minutes.
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
     * @return bool True if the impediment is currently active
     */
    public function isActive(): bool
    {
        $now = Carbon::now();

        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Check if the impediment is scheduled to start in the future.
     *
     * @return bool True if the impediment is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Check if the impediment has already ended.
     *
     * @return bool True if the impediment is in the past
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
