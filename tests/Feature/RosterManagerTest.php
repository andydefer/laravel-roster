<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\RosterManager;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Tests\TestCase;

final class RosterManagerTest extends TestCase
{
    use RefreshDatabase;

    private RosterManager $rosterManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rosterManager = app(RosterManager::class);
    }

    public function test_manager_provides_schedule_service(): void
    {
        $scheduleService = $this->rosterManager->schedules();

        $this->assertInstanceOf(ScheduleService::class, $scheduleService);
    }

    public function test_manager_provides_availability_service(): void
    {
        $availabilityService = $this->rosterManager->availabilities();

        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
    }

    public function test_manager_provides_impediment_service(): void
    {
        $impedimentService = $this->rosterManager->impediments();

        $this->assertInstanceOf(ImpedimentService::class, $impedimentService);
    }

    public function test_services_can_be_accessed_through_manager(): void
    {
        $this->assertInstanceOf(ScheduleService::class, $this->rosterManager->schedules());
        $this->assertInstanceOf(AvailabilityService::class, $this->rosterManager->availabilities());
        $this->assertInstanceOf(ImpedimentService::class, $this->rosterManager->impediments());
    }
}
