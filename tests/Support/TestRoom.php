<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\AttachableToSchedules;

/**
 * Test model for rooms that can be attached to schedules.
 */
class TestRoom extends Model
{
    use AttachableToSchedules;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'capacity',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity' => 'integer',
    ];
}
