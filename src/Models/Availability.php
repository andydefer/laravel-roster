<?php

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $table = 'availabilities';

    protected $fillable = [
        'schedulable_id',
        'schedulable_type',
        'type',         // ex: consultation, eglise
        'start_time',   // horaire de début
        'end_time',     // horaire de fin
        'days',         // JSON : ['monday','tuesday']
        'start_date',   // période de début, nullable
        'end_date',     // période de fin, nullable
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
    public function schedulable()
    {
        return $this->morphTo();
    }
}
