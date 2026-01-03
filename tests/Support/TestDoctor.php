<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\AttachableToSchedules;
use Roster\Traits\HasRoster;

/**
 * Test model for doctors that can both have schedules and be attached to schedules.
 */
class TestDoctor extends Model
{
    use HasRoster;
    use AttachableToSchedules;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_doctors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'specialty',
        'email',
    ];
}
