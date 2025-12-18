<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Roster\Enums\ScheduleStatus;

class Schedule extends Model
{
    protected $table = 'schedules';

    protected $fillable = [
        'availability_id',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'status',
        'metadata',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'status' => ScheduleStatus::class,
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
     * Relationship to Schedulable (via Availability).
     */
    public function schedulable()
    {
        return $this->availability ? $this->availability->schedulable() : null;
    }

    /**
     * Access type from parent Availability.
     */
    public function getTypeAttribute(): string
    {
        return $this->availability->type;
    }

    /**
     * Check if the Schedule overlaps with a given period.
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Check if the Schedule is currently active.
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Check if the Schedule is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Check if the Schedule is past.
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
