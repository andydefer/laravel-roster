<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Roster\Exceptions\AvailabilityViolationException;
use Roster\Exceptions\AvailabilityViolationType;
use Roster\Exceptions\MissingResourceException;
use Roster\Exceptions\MissingResourceType;
use Roster\Exceptions\TimeSlotOverlapException;
use Roster\Exceptions\TimeSlotOverlapType;

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

    protected static function booted(): void
    {
        self::creating(function (Impediment $impediment): void {
            $impediment->validateAgainstAvailability();
            $impediment->validateNotOverlappingWithSchedules();
        });

        self::updating(function (Impediment $impediment): void {
            if ($impediment->isDirty(['start_datetime', 'end_datetime', 'availability_id'])) {
                $impediment->validateAgainstAvailability();
                $impediment->validateNotOverlappingWithSchedules($impediment->id);
            }
        });
    }

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
     * Validate that the Impediment is contained within the parent Availability.
     */
    protected function validateAgainstAvailability(): void
    {
        if (!$this->availability) {
            throw new MissingResourceException(
                MissingResourceType::MISSING_AVAILABILITY
            );
        }

        $availability = $this->availability;

        // Check that days match
        $dayOfWeek = strtolower($this->start_datetime->englishDayOfWeek);
        if (!in_array($dayOfWeek, $availability->days)) {
            throw new AvailabilityViolationException(
                AvailabilityViolationType::DAY_NOT_IN_AVAILABILITY,
                ['day' => $dayOfWeek, 'allowed_days' => $availability->days]
            );
        }

        // Check that time is within Availability ranges
        $startTime = $this->start_datetime->format('H:i:s');
        $endTime = $this->end_datetime->format('H:i:s');
        $availStart = $availability->start_time->format('H:i:s');
        $availEnd = $availability->end_time->format('H:i:s');

        if ($startTime < $availStart || $endTime > $availEnd) {
            throw new AvailabilityViolationException(
                AvailabilityViolationType::TIME_OUTSIDE_AVAILABILITY_HOURS,
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'availability_start' => $availStart,
                    'availability_end' => $availEnd,
                ]
            );
        }

        // Check period dates
        if ($availability->start_date && $this->start_datetime->lt($availability->start_date)) {
            throw new AvailabilityViolationException(
                AvailabilityViolationType::STARTS_BEFORE_AVAILABILITY,
                [
                    'impediment_start' => $this->start_datetime->toDateString(),
                    'availability_start' => $availability->start_date->toDateString(),
                ]
            );
        }

        if ($availability->end_date && $this->end_datetime->gt($availability->end_date)) {
            throw new AvailabilityViolationException(
                AvailabilityViolationType::ENDS_AFTER_AVAILABILITY,
                [
                    'impediment_end' => $this->end_datetime->toDateString(),
                    'availability_end' => $availability->end_date->toDateString(),
                ]
            );
        }
    }

    /**
     * Validate that there is no overlap with Schedules.
     */
    protected function validateNotOverlappingWithSchedules(?int $excludeId = null): void
    {
        $overlappingSchedules = Schedule::where('availability_id', $this->availability_id)
            ->where(function ($q): void {
                $q->where('start_datetime', '<', $this->end_datetime)
                    ->where('end_datetime', '>', $this->start_datetime);
            })
            ->exists();

        if ($overlappingSchedules) {
            throw new TimeSlotOverlapException(
                TimeSlotOverlapType::IMPEDIMENT_SCHEDULE_OVERLAP
            );
        }
    }

    /**
     * Delete overlapping Schedules.
     */
    protected function deleteOverlappingSchedules(): void
    {
        Schedule::where('availability_id', $this->availability_id)
            ->where(function ($q): void {
                $q->where('start_datetime', '<', $this->end_datetime)
                    ->where('end_datetime', '>', $this->start_datetime);
            })
            ->delete();
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
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
