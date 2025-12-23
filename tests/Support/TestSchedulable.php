<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;

class TestSchedulable extends Model
{
    protected $table = 'test_schedulables';

    public $timestamps = false;

    protected $guarded = [];
}
