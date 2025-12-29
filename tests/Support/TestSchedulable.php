<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Test model for schedulable entities in test environments.
 * Provides a concrete implementation for testing scheduling functionality.
 */
class TestSchedulable extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test_schedulables';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
    ];
}
