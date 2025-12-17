<?php

namespace Roster\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;

class TestSchedulable extends Model
{
    protected $table = 'test_schedulables';

    protected $guarded = [];

    // Utiliser le trait Schedulable si nécessaire
    use \Roster\Traits\HasRoster;
}
