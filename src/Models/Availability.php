<?php

declare(strict_types=1);

// ==== src/Models/Availability.php ====

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class Availability extends Model
{
    protected $table = 'availabilities';

    protected $fillable = [
        'schedulable_id',
        'schedulable_type',
        'type',
        'start_time',
        'end_time',
        'days',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relation polymorphique vers le propriétaire
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation vers les Schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Relation vers les Impediments
     */
    public function impediments(): HasMany
    {
        return $this->hasMany(Impediment::class);
    }

    /**
     * Vérifier si une période donnée est disponible pour un Schedule
     */
    public function isAvailableForSchedule(Carbon $start, Carbon $end): bool
    {
        // Vérifier le jour
        $dayOfWeek = strtolower($start->englishDayOfWeek);
        if (! in_array($dayOfWeek, $this->days)) {
            return false;
        }

        // Vérifier l'horaire
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');

        if (
            $startTime < $this->start_time->format('H:i:s') ||
            $endTime > $this->end_time->format('H:i:s')
        ) {
            return false;
        }

        // Vérifier les dates de période
        if ($this->start_date && $start->lt($this->start_date)) {
            return false;
        }

        return ! ($this->end_date && $end->gt($this->end_date));
    }
}
