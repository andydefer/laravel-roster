<?php

declare(strict_types=1);

namespace Roster\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Roster\Traits\HasRoster;

class TestSchedulable extends Model
{
    protected $table = 'test_schedulables';

    protected $guarded = [];

    use HasRoster;
}
