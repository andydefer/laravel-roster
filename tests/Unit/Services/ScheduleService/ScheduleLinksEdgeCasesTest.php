<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Tests\Support\TestCar;
use Tests\Support\TestDoctor;
use Tests\Support\TestRoom;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for edge cases in polymorphic link operations.
 */
final class ScheduleLinksEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    /** @var Model The schedulable model used for testing */
    private Model $schedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = TestSchedulable::create();

        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);
    }

    /**
     * Test edge cases with model attachments.
     */
    public function test_edge_cases(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Test Center']);
        $availability = availability_for($schedulable)->create([
            'type' => 'test',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Appointment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        $service = schedule_for($availability)->schedule($schedule);

        // 1. Test attaching same model multiple times (should not duplicate)
        $room = TestRoom::create(['name' => 'Test Room', 'capacity' => 5]);

        $service->attach($room, ['first' => 'attachment']);
        $this->assertCount(1, $service->getAttached(), 'First attachment');

        $service->attach($room, ['second' => 'attachment']); // Same model, different metadata
        $this->assertCount(1, $service->getAttached(), 'Should still have 1 after re-attaching same model');

        // 2. Test detaching non-attached model (should not error)
        $nonAttachedCar = TestCar::create(['model' => 'Not Attached', 'license_plate' => 'NA-001']);
        $service->detach($nonAttachedCar); // Should not throw error
        $this->assertFalse($service->hasAttached($nonAttachedCar), 'Non-attached car should not be attached');

        // 3. Test attachMany with empty array
        $service->attachMany([]);
        $this->assertCount(1, $service->getAttached(), 'Should still have 1 model');

        // 4. Test detachMany with empty array
        $initialCount = $service->getAttached()->count();
        $service->detachMany([]);
        $this->assertCount($initialCount, $service->getAttached(), 'Count should not change after empty detachMany');

        // 5. Test detachAll when already empty (after detaching room)
        $service->detach($room);
        $service->detachAll(); // Should not error
        $this->assertCount(0, $service->getAttached(), 'Should be empty after detachAll');

        // 6. Test attaching null metadata
        $doctor = TestDoctor::create(['name' => 'Dr. Test', 'specialty' => 'testing']);
        $service->attach($doctor, null);
        $this->assertTrue($service->hasAttached($doctor), 'Should attach with null metadata');
    }
}
