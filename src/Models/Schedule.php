<?php

declare(strict_types=1);

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Roster\Enums\ScheduleStatus;
use Roster\Exceptions\AvailabilityViolationException;
use Roster\Exceptions\AvailabilityViolationType;
use Roster\Exceptions\MissingResourceException;
use Roster\Exceptions\MissingResourceType;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\TimeRangeValidationType;
use Roster\Exceptions\TimeSlotOverlapException;
use Roster\Exceptions\TimeSlotOverlapType;

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

    protected static function booted(): void
    {
        self::creating(function (Schedule $schedule): void {
            $schedule->validateTimeRange();
            $schedule->validateAgainstAvailability();
            $schedule->validateNoOverlappingSchedules();
            $schedule->validateNoOverlappingImpediments();
        });

        self::updating(function (Schedule $schedule): void {
            if ($schedule->isDirty(['start_datetime', 'end_datetime', 'availability_id'])) {
                $schedule->validateTimeRange();
                $schedule->validateAgainstAvailability();
                $schedule->validateNoOverlappingSchedules($schedule->id);
                $schedule->validateNoOverlappingImpediments($schedule->id);
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
     * Validate that start datetime is before end datetime.
     */
    protected function validateTimeRange(): void
    {
        if ($this->end_datetime->lte($this->start_datetime)) {
            throw new TimeRangeValidationException(
                [
                    'start_datetime' => $this->start_datetime->format('Y-m-d H:i:s'),
                    'end_datetime' => $this->end_datetime->format('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * Validate that the Schedule is contained within the parent Availability.
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
                    'schedule_start' => $this->start_datetime->toDateString(),
                    'availability_start' => $availability->start_date->toDateString(),
                ]
            );
        }

        if ($availability->end_date && $this->end_datetime->gt($availability->end_date)) {
            throw new AvailabilityViolationException(
                AvailabilityViolationType::ENDS_AFTER_AVAILABILITY,
                [
                    'schedule_end' => $this->end_datetime->toDateString(),
                    'availability_end' => $availability->end_date->toDateString(),
                ]
            );
        }
    }

    /**
     * Validate that there is no overlap with other Schedules.
     */
    protected function validateNoOverlappingSchedules(?int $excludeId = null): void
    {
        $query = self::where('availability_id', $this->availability_id)
            ->where(function ($q): void {
                $q->where('start_datetime', '<', $this->end_datetime)
                    ->where('end_datetime', '>', $this->start_datetime);
            });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new TimeSlotOverlapException(
                TimeSlotOverlapType::SCHEDULE_OVERLAP
            );
        }
    }

    /**
     * Validate that there is no overlap with Impediments.
     */
    protected function validateNoOverlappingImpediments(?int $excludeId = null): void
    {
        $overlappingImpediment = Impediment::where('availability_id', $this->availability_id)
            ->where(function ($q): void {
                $q->where('start_datetime', '<', $this->end_datetime)
                    ->where('end_datetime', '>', $this->start_datetime);
            })
            ->exists();

        if ($overlappingImpediment) {
            throw new TimeSlotOverlapException(
                TimeSlotOverlapType::SCHEDULE_IMPEDIMENT_OVERLAP
            );
        }
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
