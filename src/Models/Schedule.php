<?php

namespace Roster\Models;

use Illuminate\Database\Eloquent\Model;
use Roster\Enums\ScheduleStatus;

class Schedule extends Model
{
    protected $table = 'schedules';

    protected $fillable = [
        'schedulable_id',
        'schedulable_type',
        'title',
        'description',
        'start_date',
        'end_date',
        'type',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => ScheduleStatus::class, // cast automatique vers l'enum
    ];

    /**
     * Relation polymorphique vers le propriétaire
     */
    public function schedulable()
    {
        return $this->morphTo();
    }
}
