<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Impediment extends Model
{
    protected $table = 'impediments';

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
        'metadata' => 'array',
    ];

    /**
     * Relationship to parent Availability.
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }

    /**
     * Polymorphic relationship to schedulable entity.
     */
    public function schedulable()
    {
        return $this->morphTo();
    }

    /**
     * Check if the Impediment overlaps with a given period.
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): float
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Check if the Impediment is currently active.
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Check if the Impediment is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Check if the Impediment is past.
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
