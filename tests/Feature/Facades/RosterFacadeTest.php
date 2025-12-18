<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Facades\Roster as RosterFacade;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Tests\TestCase;

final class RosterFacadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_facade_provides_schedule_service(): void
    {
        $scheduleService = RosterFacade::schedules();

        $this->assertInstanceOf(ScheduleService::class, $scheduleService);
    }

    public function test_facade_provides_availability_service(): void
    {
        $availabilityService = RosterFacade::availabilities();

        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
    }

    public function test_facade_provides_impediment_service(): void
    {
        $impedimentService = RosterFacade::impediments();

        $this->assertInstanceOf(ImpedimentService::class, $impedimentService);
    }

    public function test_all_services_can_be_accessed_through_facade(): void
    {
        $this->assertInstanceOf(ScheduleService::class, RosterFacade::schedules());
        $this->assertInstanceOf(AvailabilityService::class, RosterFacade::availabilities());
        $this->assertInstanceOf(ImpedimentService::class, RosterFacade::impediments());
    }

    public function test_services_can_be_scoped_to_schedulable(): void
    {
        $schedulable = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $schedulable->id = 1;
        $schedulable->save();

        $scheduleService = RosterFacade::schedules()->for($schedulable);
        $availabilityService = RosterFacade::availabilities()->for($schedulable);
        $impedimentService = RosterFacade::impediments()->for($schedulable);

        $this->assertSame($schedulable, $scheduleService->getSchedulable());
        $this->assertSame($schedulable, $availabilityService->getSchedulable());
        $this->assertSame($schedulable, $impedimentService->getSchedulable());
    }
}
