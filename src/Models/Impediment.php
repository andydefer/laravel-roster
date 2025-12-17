<?php
// ==== src/Models/Impediment.php ====

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class Impediment extends Model
{
    protected $table = 'impediments';

    protected $fillable = [
        'availability_id',
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
        static::creating(function (Impediment $impediment) {
            $impediment->validateAgainstAvailability();
            $impediment->validateNotOverlappingWithSchedules();
        });

        static::updating(function (Impediment $impediment) {
            if ($impediment->isDirty(['start_datetime', 'end_datetime', 'availability_id'])) {
                $impediment->validateAgainstAvailability();
                // NE PAS valider les chevauchements ici - les schedules seront supprimés dans le hook updated
            }
        });

        static::created(function (Impediment $impediment) {
            $impediment->deleteOverlappingSchedules();
        });

        static::updated(function (Impediment $impediment) {
            if ($impediment->isDirty(['start_datetime', 'end_datetime'])) {
                $impediment->deleteOverlappingSchedules();
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
     * Vérifier si l'Impediment chevauche une période donnée
     */
    public function overlapsWith(Carbon $start, Carbon $end): bool
    {
        return $this->start_datetime->lt($end) && $this->end_datetime->gt($start);
    }

    /**
     * Valider que l'Impediment est contenu dans l'Availability parente
     */
    protected function validateAgainstAvailability(): void
    {
        if (!$this->availability) {
            throw new InvalidArgumentException('Impediment must belong to an Availability');
        }

        $availability = $this->availability;

        // Vérifier que les jours correspondent
        $dayOfWeek = strtolower($this->start_datetime->englishDayOfWeek);
        if (!in_array($dayOfWeek, $availability->days)) {
            throw new InvalidArgumentException(
                "Impediment day ({$dayOfWeek}) is not in Availability days"
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
                'Impediment time range is outside Availability hours'
            );
        }

        // Vérifier les dates de période
        if ($availability->start_date && $this->start_datetime->lt($availability->start_date)) {
            throw new InvalidArgumentException(
                'Impediment starts before Availability start date'
            );
        }

        if ($availability->end_date && $this->end_datetime->gt($availability->end_date)) {
            throw new InvalidArgumentException(
                'Impediment ends after Availability end date'
            );
        }
    }

    /**
     * Valider qu'il n'y a pas de chevauchement avec des Schedules
     * (pour empêcher la création si des Schedules existent)
     */
    protected function validateNotOverlappingWithSchedules(?int $excludeId = null): void
    {
        $overlappingSchedules = Schedule::where('availability_id', $this->availability_id)
            ->where(function ($q) {
                $q->where('start_datetime', '<', $this->end_datetime)
                    ->where('end_datetime', '>', $this->start_datetime);
            })
            ->exists();

        if ($overlappingSchedules) {
            throw new InvalidArgumentException(
                'Cannot create impediment that overlaps with existing schedules'
            );
        }
    }

    /**
     * Supprimer les Schedules qui chevauchent
     */
    protected function deleteOverlappingSchedules(): void
    {
        Schedule::where('availability_id', $this->availability_id)
            ->where(function ($q) {
                $q->where('start_datetime', '<', $this->end_datetime)
                    ->where('end_datetime', '>', $this->start_datetime);
            })
            ->delete();
    }

    /**
     * Récupérer la durée en minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Vérifier si l'Impediment est actif (en cours)
     */
    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->start_datetime->lte($now) && $this->end_datetime->gte($now);
    }

    /**
     * Vérifier si l'Impediment est à venir
     */
    public function isUpcoming(): bool
    {
        return $this->start_datetime->gt(Carbon::now());
    }

    /**
     * Vérifier si l'Impediment est passé
     */
    public function isPast(): bool
    {
        return $this->end_datetime->lt(Carbon::now());
    }
}
