<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Enums\ScheduleStatus;
use Tests\Support\TestCar;
use Tests\Support\TestDoctor;
use Tests\Support\TestRoom;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Test suite for attachable model functionality.
 *
 * Validates that models can be attached to schedules and manage
 * their schedule relationships through the attachable trait.
 */
class AttachableToSchedulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that models can be attached to schedules.
     */
    public function test_model_can_be_attached_to_schedule(): void
    {
        // Arrange: Create schedulable, availability, schedule and attachable models
        $schedulable = TestSchedulable::create(['name' => 'Clinic']);
        $availability = availability_for($schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Medical Consultation',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        $room = TestRoom::create([
            'name' => 'Exam Room 1',
            'capacity' => 2,
            'type' => 'examination',
        ]);

        $car = TestCar::create([
            'model' => 'Ambulance',
            'license_plate' => 'AMB-001',
            'type' => 'emergency',
        ]);

        $doctor = TestDoctor::create([
            'name' => 'Dr. Johnson',
            'specialty' => 'surgery',
            'email' => 'johnson@example.com',
        ]);

        // Act: Test attaching models to schedule
        $room->attachToSchedule($schedule, ['role' => 'examination_room']);
        $car->attachToSchedule($schedule, ['purpose' => 'patient_transport']);
        $doctor->attachToSchedule($schedule, ['role' => 'primary_physician']);

        // Assert: Verify all models are attached correctly
        $this->assertTrue($room->isAttachedToSchedule($schedule));
        $this->assertTrue($car->isAttachedToSchedule($schedule));
        $this->assertTrue($doctor->isAttachedToSchedule($schedule));

        // Act: Get attached schedules for room
        $roomSchedules = $room->attachedSchedules()->get();

        // Assert: Room should have exactly one attached schedule
        $this->assertCount(1, $roomSchedules);
        $this->assertEquals($schedule->id, $roomSchedules->first()->id);

        // Act: Get schedules with metadata
        $schedulesWithMetadata = $room->attachedSchedulesWithLinkMetadata();

        // Assert: Metadata should be accessible through pivot
        $this->assertCount(1, $schedulesWithMetadata);
        $this->assertNotNull($schedulesWithMetadata->first()->pivot->metadata);

        // Act: Get schedules with specific metadata
        $schedulesWithRole = $room->attachedSchedulesWithMetadata('role', 'examination_room');

        // Assert: Should find schedule with matching metadata
        $this->assertCount(1, $schedulesWithRole);

        // Act: Detach room from schedule
        $room->detachFromSchedule($schedule);

        // Assert: Room should no longer be attached
        $this->assertFalse($room->isAttachedToSchedule($schedule));

        // Act: Sync doctor's schedules
        $doctor->syncSchedules([$schedule], ['role' => 'consultant']);

        // Assert: Doctor should remain attached with updated metadata
        $this->assertTrue($doctor->isAttachedToSchedule($schedule));

        // Arrange: Create separate availability for doctor
        $doctorAvailability = availability_for($doctor)->create([
            'type' => 'surgery',
            'daily_start' => '08:00:00',
            'daily_end' => '16:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Create schedule for doctor
        $doctorSchedule = schedule_for($doctorAvailability)->create([
            'title' => 'Surgery Schedule',
            'start_datetime' => '2038-01-04 08:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Assert: Doctor should have its own schedules
        $this->assertCount(1, $doctor->schedules);
        $this->assertEquals($doctorSchedule->id, $doctor->schedules->first()->id);
    }
}
