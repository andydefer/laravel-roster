<?php

declare(strict_types=1);

// ==== src/Models/Schedule.php ====

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
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

    protected static function booted(): void
    {
        self::creating(function (Schedule $schedule): void {
            $schedule->validateAgainstAvailability();
            $schedule->validateNoOverlappingSchedules();
            $schedule->validateNoOverlappingImpediments();
        });

        self::updating(function (Schedule $schedule): void {
            if ($schedule->isDirty(['start_datetime', 'end_datetime', 'availability_id'])) {
                $schedule->validateAgainstAvailability();
                $schedule->validateNoOverlappingSchedules($schedule->id);
                $schedule->validateNoOverlappingImpediments($schedule->id);
            }
        });
    }

    /**
     * Relation vers l'Availability parente
     */
    public function availability(): BelongsTo
    {
        return $this->belongsTo(Availability::class);
    }

    /**
     * Relation vers le Schedulable (via Availability)
     * Cette relation est dynamique grâce au polymorphisme
     */
    public function schedulable()
    {
        return $this->availability ? $this->availability->schedulable() : null;
    }

    /**
     * Accès au type depuis l'Availability parente
     */
    public function getTypeAttribute(): string
    {
        return $this->availability->type;
    }

    /**
     * Vérifier si le Schedule chevauche une période donnée
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Valider que le Schedule est contenu dans l'Availability parente
     */
    protected function validateAgainstAvailability(): void
    {
        if (! $this->availability) {
            throw new InvalidArgumentException('Schedule must belong to an Availability');
        }

        $availability = $this->availability;

        // Vérifier que les jours correspondent
        $dayOfWeek = strtolower($this->start_datetime->englishDayOfWeek);
        if (! in_array($dayOfWeek, $availability->days)) {
            throw new InvalidArgumentException(
                sprintf('Schedule day (%s) is not in Availability days', $dayOfWeek)
            );
        }

        // Vérifier que l'horaire est dans les plages de l'Availability
        $startTime = $this->start_datetime->format('H:i:s');
        $endTime = $this->end_datetime->format('H:i:s');

        if (
            $startTime < $availability->start_time->format('H:i:s') ||
            $endTime > $availability->end_time->format('H:i:s')
        ) {
            throw new InvalidArgumentException(
                'Schedule time range is outside Availability hours'
            );
        }

        // Vérifier les dates de période
        if ($availability->start_date && $this->start_datetime->lt($availability->start_date)) {
            throw new InvalidArgumentException(
                'Schedule starts before Availability start date'
            );
        }

        if ($availability->end_date && $this->end_datetime->gt($availability->end_date)) {
            throw new InvalidArgumentException(
                'Schedule ends after Availability end date'
            );
        }
    }

    /**
     * Valider qu'il n'y a pas de chevauchement avec d'autres Schedules
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
            throw new InvalidArgumentException('Schedule overlaps with another schedule');
        }
    }

    /**
     * Valider qu'il n'y a pas de chevauchement avec des Impediments
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
            throw new InvalidArgumentException('Schedule overlaps with an impediment');
        }
    }

    /**
     * Récupérer la durée en minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Vérifier si le Schedule est actif (en cours)
     */
    public function isActive(): bool
    {
        $now = Carbon::now();

        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Vérifier si le Schedule est à venir
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Vérifier si le Schedule est passé
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
