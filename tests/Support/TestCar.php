<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\AttachableToSchedules;

/**
 * Test model for cars that can be attached to schedules.
 */
class TestCar extends Model
{
    use AttachableToSchedules;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_cars';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model',
        'license_plate',
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
