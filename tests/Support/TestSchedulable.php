<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\HasRoster;

class TestSchedulable extends Model
{
    use HasRoster;

    protected $table = 'test_schedulables';

    public $timestamps = false;

    protected $guarded = [];
}
