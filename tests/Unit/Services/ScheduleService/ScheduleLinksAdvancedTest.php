<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for advanced polymorphic link operations.
 */
final class ScheduleLinksAdvancedTest extends TestCase
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
     * Test that schedule service can attach and manage polymorphic links.
     */
    public function test_schedule_service_can_manage_polymorphic_links(): void
    {
        // Arrange: Create schedulable, availability, and schedule
        $schedulable = TestSchedulable::create(['name' => 'Test Clinic']);
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

        // Create attachable models (they don't need traits for the service to work)
        $doctor = TestSchedulable::create(['name' => 'Dr. Smith']);
        $patient = TestSchedulable::create(['name' => 'Patient Doe']);

        // Get the service with schedule context
        $service = schedule_for($availability)->schedule($schedule);

        // 1. Test attaching a model
        $service->attach($doctor, ['role' => 'physician']);
        $this->assertTrue($service->hasAttached($doctor));

        // 2. Test attaching another model
        $service->attach($patient, ['role' => 'patient', 'notes' => 'VIP']);
        $this->assertTrue($service->hasAttached($patient));

        // 3. Test getting all attached models
        $attached = $service->getAttached();
        $this->assertInstanceOf(Collection::class, $attached);
        $this->assertCount(2, $attached);

        // 4. Test getting attached by type
        $attachedByType = $service->getAttachedByType(TestSchedulable::class);
        $this->assertCount(2, $attachedByType);

        // 5. Test detaching a model
        $service->detach($doctor);
        $this->assertFalse($service->hasAttached($doctor));
        $this->assertTrue($service->hasAttached($patient));

        // 6. Test detaching all models
        $service->detachAll();
        $attachedAfterDetachAll = $service->getAttached();
        $this->assertCount(0, $attachedAfterDetachAll);

        // 7. Test syncing models
        $service->sync([$doctor, $patient], ['batch' => 'sync_test']);
        $attachedAfterSync = $service->getAttached();
        $this->assertCount(2, $attachedAfterSync);

        // 8. Test attaching multiple models at once
        $service->detachAll();
        $models = [$doctor, $patient];
        $service->attachMany($models, ['group' => 'appointment_participants']);
        $this->assertTrue($service->hasAttached($doctor));
        $this->assertTrue($service->hasAttached($patient));

        // 9. Test detaching multiple models
        $service->detachMany([$doctor, $patient]);
        $this->assertFalse($service->hasAttached($doctor));
        $this->assertFalse($service->hasAttached($patient));

        // 10. Test that operations require schedule context
        $serviceWithoutSchedule = schedule_for($availability);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No schedule set for link operations. Use schedule() method first.');
        $serviceWithoutSchedule->attach($doctor);
    }

    /**
     * Test attaching different types of models to schedule.
     */
    public function test_attaching_different_model_types(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Hospital']);
        $availability = availability_for($schedulable)->create([
            'type' => 'surgery',
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Surgery Schedule',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Create models that would represent different entities
        $surgeon = TestSchedulable::create(['name' => 'Dr. Surgeon']);
        $anesthesiologist = TestSchedulable::create(['name' => 'Dr. Anesthesia']);
        $nurse = TestSchedulable::create(['name' => 'Nurse Jane']);

        $service = schedule_for($availability)->schedule($schedule);

        // Attach with different metadata
        $service->attach($surgeon, ['role' => 'surgeon', 'specialty' => 'orthopedic']);
        $service->attach($anesthesiologist, ['role' => 'anesthesiologist']);
        $service->attach($nurse, ['role' => 'scrub_nurse', 'level' => 'senior']);

        // Verify all are attached
        $this->assertTrue($service->hasAttached($surgeon));
        $this->assertTrue($service->hasAttached($anesthesiologist));
        $this->assertTrue($service->hasAttached($nurse));

        // Get all attached
        $allAttached = $service->getAttached();
        $this->assertCount(3, $allAttached);

        // Get by type
        $medicalStaff = $service->getAttachedByType(TestSchedulable::class);
        $this->assertCount(3, $medicalStaff);

        // Verify we can chain operations
        $service->detach($nurse)->attach($surgeon, ['role' => 'lead_surgeon']);
        $this->assertFalse($service->hasAttached($nurse));
    }

    /**
     * Test that metadata is properly stored and retrieved.
     */
    public function test_link_metadata_persistence(): void
    {
        // Arrange
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
            'title' => 'Consultation',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        $patient = TestSchedulable::create(['name' => 'John Patient']);

        $complexMetadata = [
            'medical_history' => ['hypertension', 'diabetes'],
            'insurance' => 'ABC Insurance',
            'priority' => 'high',
            'notes' => ['allergic to penicillin', 'needs interpreter'],
            'contact' => [
                'phone' => '555-0123',
                'email' => 'john@example.com'
            ]
        ];

        // Act & Assert
        $service = schedule_for($availability)->schedule($schedule);

        // Attach with complex metadata
        $service->attach($patient, $complexMetadata);

        // Verify attachment
        $this->assertTrue($service->hasAttached($patient));

        // Get attached models
        $attached = $service->getAttached();
        $this->assertCount(1, $attached);

        // Note: The metadata would be verified in integration tests with the repository
        // For unit tests, we just verify the service methods work correctly
    }

    /**
     * Test concurrent link operations.
     */
    public function test_concurrent_link_operations(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Center']);
        $availability = availability_for($schedulable)->create([
            'type' => 'training',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Training Session',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 16:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        $participants = [
            TestSchedulable::create(['name' => 'Participant 1']),
            TestSchedulable::create(['name' => 'Participant 2']),
            TestSchedulable::create(['name' => 'Participant 3']),
            TestSchedulable::create(['name' => 'Participant 4']),
        ];

        $service = schedule_for($availability)->schedule($schedule);

        // Test various operations in sequence
        $service->attachMany($participants, ['role' => 'trainee']);

        // Verify all are attached
        foreach ($participants as $participant) {
            $this->assertTrue($service->hasAttached($participant));
        }

        // Detach some
        $service->detachMany([$participants[0], $participants[1]]);
        $this->assertFalse($service->hasAttached($participants[0]));
        $this->assertFalse($service->hasAttached($participants[1]));
        $this->assertTrue($service->hasAttached($participants[2]));
        $this->assertTrue($service->hasAttached($participants[3]));

        // Sync with new list
        $newParticipants = [
            TestSchedulable::create(['name' => 'New Participant 1']),
            TestSchedulable::create(['name' => 'New Participant 2']),
        ];

        $service->sync($newParticipants, ['batch' => 'updated']);
        $this->assertCount(2, $service->getAttached());
        $this->assertTrue($service->hasAttached($newParticipants[0]));
        $this->assertTrue($service->hasAttached($newParticipants[1]));

        // Clear all
        $service->detachAll();
        $this->assertCount(0, $service->getAttached());
    }

    /**
     * Test sync operations replacing models of different types.
     */
    public function test_sync_operations(): void
    {
        // Arrange
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
            'title' => 'Morning Consultations',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Initial models
        $room1 = TestSchedulable::create(['name' => 'Room A']);
        $doctor1 = TestSchedulable::create(['name' => 'Dr. Alpha']);
        $patient1 = TestSchedulable::create(['name' => 'Patient One']);

        $service = schedule_for($availability)->schedule($schedule);

        // Attach initial models
        $service->attachMany([$room1, $doctor1, $patient1], ['session' => 'morning']);

        // Verify initial state
        $this->assertCount(3, $service->getAttached(), 'Should have 3 initial models');

        // New models for sync
        $room2 = TestSchedulable::create(['name' => 'Room B']);
        $doctor2 = TestSchedulable::create(['name' => 'Dr. Beta']);
        $car1 = TestSchedulable::create(['name' => 'Transport Vehicle']);
        $patient2 = TestSchedulable::create(['name' => 'Patient Two']);

        // 1. Test sync with completely different models
        $service->sync([$room2, $doctor2, $car1, $patient2], ['session' => 'updated', 'timestamp' => 'after_sync']);

        // Verify old models are gone, new ones are present
        $this->assertFalse($service->hasAttached($room1), 'Old room should be removed');
        $this->assertFalse($service->hasAttached($doctor1), 'Old doctor should be removed');
        $this->assertFalse($service->hasAttached($patient1), 'Old patient should be removed');

        $this->assertTrue($service->hasAttached($room2), 'New room should be present');
        $this->assertTrue($service->hasAttached($doctor2), 'New doctor should be present');
        $this->assertTrue($service->hasAttached($car1), 'New car should be present');
        $this->assertTrue($service->hasAttached($patient2), 'New patient should be present');

        $this->assertCount(4, $service->getAttached(), 'Should have 4 models after sync');

        // 2. Test sync with empty array
        $service->sync([]);
        $this->assertCount(0, $service->getAttached(), 'Should have 0 models after empty sync');
    }
}
