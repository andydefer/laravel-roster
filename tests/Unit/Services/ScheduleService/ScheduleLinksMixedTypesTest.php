<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Tests\Support\TestCar;
use Tests\Support\TestDoctor;
use Tests\Support\TestRoom;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for schedule links with different types of models.
 */
final class ScheduleLinksMixedTypesTest extends TestCase
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
     * Test basic attachment operations with different model types.
     */
    public function test_basic_attachment_operations(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Medical Center']);
        $availability = availability_for($schedulable)->create([
            'type' => 'appointment',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Medical Appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Create different types of models
        $room = TestRoom::create(['name' => 'Exam Room 101', 'capacity' => 2, 'type' => 'examination']);
        $car = TestCar::create(['model' => 'Ambulance', 'license_plate' => 'AMB-001', 'type' => 'emergency']);
        $doctor = TestDoctor::create(['name' => 'Dr. Johnson', 'specialty' => 'cardiology', 'email' => 'johnson@example.com']);
        $patient = TestSchedulable::create(['name' => 'Patient Smith']);

        $service = schedule_for($availability)->schedule($schedule);

        // 1. Test attaching single models of different types
        $service->attach($room, ['role' => 'location', 'equipment' => 'xray']);
        $service->attach($car, ['purpose' => 'transport', 'driver_required' => true]);
        $service->attach($doctor, ['role' => 'primary_physician', 'specialty' => 'cardiology']);
        $service->attach($patient, ['role' => 'patient', 'priority' => 'normal']);

        // Verify all are attached
        $this->assertTrue($service->hasAttached($room), 'Room should be attached');
        $this->assertTrue($service->hasAttached($car), 'Car should be attached');
        $this->assertTrue($service->hasAttached($doctor), 'Doctor should be attached');
        $this->assertTrue($service->hasAttached($patient), 'Patient should be attached');

        // 2. Test getAttached returns all models
        $allAttached = $service->getAttached();
        $this->assertCount(4, $allAttached, 'Should have 4 attached models');

        // 3. Test getAttachedByType for each model type
        $rooms = $service->getAttachedByType(TestRoom::class);
        $cars = $service->getAttachedByType(TestCar::class);
        $doctors = $service->getAttachedByType(TestDoctor::class);
        $patients = $service->getAttachedByType(TestSchedulable::class);

        $this->assertCount(1, $rooms, 'Should have 1 room');
        $this->assertCount(1, $cars, 'Should have 1 car');
        $this->assertCount(1, $doctors, 'Should have 1 doctor');
        $this->assertCount(1, $patients, 'Should have 1 patient');

        $this->assertInstanceOf(TestRoom::class, $rooms->first());
        $this->assertInstanceOf(TestCar::class, $cars->first());
        $this->assertInstanceOf(TestDoctor::class, $doctors->first());
        $this->assertInstanceOf(TestSchedulable::class, $patients->first());
    }

    /**
     * Test detach operations with mixed model types.
     */
    public function test_detach_operations_with_mixed_models(): void
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
            'title' => 'Surgery',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Create models
        $room = TestRoom::create(['name' => 'Operating Room', 'capacity' => 10, 'type' => 'surgery']);
        $car1 = TestCar::create(['model' => 'Ambulance 1', 'license_plate' => 'AMB-001']);
        $car2 = TestCar::create(['model' => 'Ambulance 2', 'license_plate' => 'AMB-002']);
        $doctor1 = TestDoctor::create(['name' => 'Dr. Surgeon', 'specialty' => 'surgery']);
        $doctor2 = TestDoctor::create(['name' => 'Dr. Assistant', 'specialty' => 'surgery']);
        $nurse = TestSchedulable::create(['name' => 'Nurse Jane']);

        $service = schedule_for($availability)->schedule($schedule);

        // Attach all models
        $service->attachMany([$room, $car1, $car2, $doctor1, $doctor2, $nurse], ['surgery_id' => 'S123']);

        // 1. Test detach single model
        $service->detach($room);
        $this->assertFalse($service->hasAttached($room), 'Room should be detached');
        $this->assertTrue($service->hasAttached($car1), 'Car1 should still be attached');
        $this->assertCount(5, $service->getAttached(), 'Should have 5 models after detaching room');

        // 2. Test detachMany with mixed types
        $service->detachMany([$car1, $doctor1]);
        $this->assertFalse($service->hasAttached($car1), 'Car1 should be detached');
        $this->assertFalse($service->hasAttached($doctor1), 'Doctor1 should be detached');
        $this->assertTrue($service->hasAttached($car2), 'Car2 should still be attached');
        $this->assertTrue($service->hasAttached($doctor2), 'Doctor2 should still be attached');
        $this->assertCount(3, $service->getAttached(), 'Should have 3 models left');

        // 3. Test detachAll
        $service->detachAll();
        $this->assertCount(0, $service->getAttached(), 'Should have 0 models after detachAll');
        $this->assertFalse($service->hasAttached($car2), 'Car2 should be detached');
        $this->assertFalse($service->hasAttached($doctor2), 'Doctor2 should be detached');
        $this->assertFalse($service->hasAttached($nurse), 'Nurse should be detached');
    }

    /**
     * Test complex metadata with different model types.
     */
    public function test_complex_metadata_per_model_type(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Emergency Center']);
        $availability = availability_for($schedulable)->create([
            'type' => 'emergency',
            'daily_start' => '00:00:00',
            'daily_end' => '23:59:59',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Emergency Response',
            'start_datetime' => '2038-01-04 14:30:00',
            'end_datetime' => '2038-01-04 16:30:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        $service = schedule_for($availability)->schedule($schedule);

        // Create models with type-specific metadata
        $ambulance = TestCar::create([
            'model' => 'Mercedes Sprinter',
            'license_plate' => 'EMG-911',
            'type' => 'ambulance'
        ]);

        $operatingRoom = TestRoom::create([
            'name' => 'ER Operating Room',
            'capacity' => 15,
            'type' => 'emergency_surgery'
        ]);

        $traumaSurgeon = TestDoctor::create([
            'name' => 'Dr. Trauma',
            'specialty' => 'trauma_surgery',
            'email' => 'trauma@emergency.example.com'
        ]);

        $paramedic = TestSchedulable::create(['name' => 'Paramedic Jones']);

        // Attach with model-specific metadata
        $service->attach($ambulance, [
            'vehicle_type' => 'ambulance',
            'equipment' => ['defibrillator', 'oxygen_tank', 'stretcher'],
            'crew_size' => 2,
            'response_time' => '5_minutes',
            'status' => 'on_duty'
        ]);

        $service->attach($operatingRoom, [
            'room_type' => 'trauma_center',
            'equipment' => ['surgical_lights', 'anesthesia_machine', 'monitors'],
            'sterilization_level' => 'surgical',
            'available_beds' => 3,
            'emergency_ready' => true
        ]);

        $service->attach($traumaSurgeon, [
            'role' => 'lead_surgeon',
            'specialties' => ['trauma', 'emergency_surgery', 'critical_care'],
            'board_certified' => true,
            'years_experience' => 15,
            'on_call' => true
        ]);

        $service->attach($paramedic, [
            'role' => 'paramedic',
            'certifications' => ['EMT-P', 'ACLS', 'PALS'],
            'shift' => 'day_shift',
            'vehicle_assigned' => 'EMG-911'
        ]);

        // Verify counts by type
        $this->assertCount(1, $service->getAttachedByType(TestCar::class), 'Should have 1 car');
        $this->assertCount(1, $service->getAttachedByType(TestRoom::class), 'Should have 1 room');
        $this->assertCount(1, $service->getAttachedByType(TestDoctor::class), 'Should have 1 doctor');
        $this->assertCount(1, $service->getAttachedByType(TestSchedulable::class), 'Should have 1 schedulable');

        // Verify all are attached
        $this->assertTrue($service->hasAttached($ambulance));
        $this->assertTrue($service->hasAttached($operatingRoom));
        $this->assertTrue($service->hasAttached($traumaSurgeon));
        $this->assertTrue($service->hasAttached($paramedic));

        $this->assertCount(4, $service->getAttached(), 'Should have total of 4 models');
    }

    /**
     * Test chaining operations with mixed model types.
     */
    public function test_chaining_operations(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Medical Facility']);
        $availability = availability_for($schedulable)->create([
            'type' => 'procedure',
            'daily_start' => '08:00:00',
            'daily_end' => '16:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Medical Procedure',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Create models
        $room = TestRoom::create(['name' => 'Procedure Room', 'capacity' => 3]);
        $car = TestCar::create(['model' => 'Patient Transport', 'license_plate' => 'TRANS-001']);
        $doctor = TestDoctor::create(['name' => 'Dr. Procedure', 'specialty' => 'interventional']);
        $assistant = TestSchedulable::create(['name' => 'Medical Assistant']);
        $patient = TestSchedulable::create(['name' => 'Procedure Patient']);

        $service = schedule_for($availability)->schedule($schedule);

        // Chain multiple operations
        $service
            ->attach($room, ['room_prepped' => true])
            ->attach($car, ['transport_ready' => true])
            ->attachMany([$doctor, $assistant], ['staff' => 'medical'])
            ->detach($car) // Change mind about car
            ->attach($patient, ['patient_ready' => true, 'consent' => true])
            ->detach($assistant); // Assistant not needed

        // Verify final state
        $this->assertTrue($service->hasAttached($room), 'Room should be attached');
        $this->assertFalse($service->hasAttached($car), 'Car should be detached');
        $this->assertTrue($service->hasAttached($doctor), 'Doctor should be attached');
        $this->assertFalse($service->hasAttached($assistant), 'Assistant should be detached');
        $this->assertTrue($service->hasAttached($patient), 'Patient should be attached');

        $this->assertCount(3, $service->getAttached(), 'Should have 3 models in final state');

        // Get counts by type
        $rooms = $service->getAttachedByType(TestRoom::class);
        $doctors = $service->getAttachedByType(TestDoctor::class);
        $patients = $service->getAttachedByType(TestSchedulable::class);

        $this->assertCount(1, $rooms, 'Should have 1 room');
        $this->assertCount(1, $doctors, 'Should have 1 doctor');
        $this->assertCount(1, $patients, 'Should have 1 patient (TestSchedulable)');
    }

    /**
     * Test multiple schedules with different attachments.
     */
    public function test_multiple_schedules_different_attachments(): void
    {
        // Arrange
        $schedulable = TestSchedulable::create(['name' => 'Multi-Schedule Center']);
        $availability = availability_for($schedulable)->create([
            'type' => 'multi',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Create multiple schedules
        $schedule1 = schedule_for($availability)->create([
            'title' => 'Morning Session',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 10:30:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        $schedule2 = schedule_for($availability)->create([
            'title' => 'Afternoon Session',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:30:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Shared resources
        $sharedRoom = TestRoom::create(['name' => 'Shared Conference Room', 'capacity' => 20]);
        $sharedCar = TestCar::create(['model' => 'Shared Vehicle', 'license_plate' => 'SHARED-001']);

        // Schedule-specific resources
        $morningDoctor = TestDoctor::create(['name' => 'Dr. Morning', 'specialty' => 'general']);
        $afternoonDoctor = TestDoctor::create(['name' => 'Dr. Afternoon', 'specialty' => 'specialist']);
        $morningPatient = TestSchedulable::create(['name' => 'Morning Patient']);
        $afternoonPatient = TestSchedulable::create(['name' => 'Afternoon Patient']);

        // Create services for each schedule
        $service1 = schedule_for($availability)->schedule($schedule1);
        $service2 = schedule_for($availability)->schedule($schedule2);

        // Attach to schedule 1
        $service1->attachMany([$sharedRoom, $sharedCar, $morningDoctor, $morningPatient], ['session' => 'morning']);

        // Attach to schedule 2
        $service2->attachMany([$sharedRoom, $sharedCar, $afternoonDoctor, $afternoonPatient], ['session' => 'afternoon']);

        // Verify schedule 1 attachments
        $this->assertTrue($service1->hasAttached($sharedRoom), 'Schedule1 should have shared room');
        $this->assertTrue($service1->hasAttached($sharedCar), 'Schedule1 should have shared car');
        $this->assertTrue($service1->hasAttached($morningDoctor), 'Schedule1 should have morning doctor');
        $this->assertTrue($service1->hasAttached($morningPatient), 'Schedule1 should have morning patient');
        $this->assertFalse($service1->hasAttached($afternoonDoctor), 'Schedule1 should NOT have afternoon doctor');
        $this->assertCount(4, $service1->getAttached(), 'Schedule1 should have 4 attachments');

        // Verify schedule 2 attachments
        $this->assertTrue($service2->hasAttached($sharedRoom), 'Schedule2 should have shared room');
        $this->assertTrue($service2->hasAttached($sharedCar), 'Schedule2 should have shared car');
        $this->assertTrue($service2->hasAttached($afternoonDoctor), 'Schedule2 should have afternoon doctor');
        $this->assertTrue($service2->hasAttached($afternoonPatient), 'Schedule2 should have afternoon patient');
        $this->assertFalse($service2->hasAttached($morningDoctor), 'Schedule2 should NOT have morning doctor');
        $this->assertCount(4, $service2->getAttached(), 'Schedule2 should have 4 attachments');

        // Test detach from one schedule doesn't affect the other
        $service1->detach($sharedRoom);
        $this->assertFalse($service1->hasAttached($sharedRoom), 'Schedule1 should not have room after detach');
        $this->assertTrue($service2->hasAttached($sharedRoom), 'Schedule2 should still have room');

        $service2->detach($sharedCar);
        $this->assertTrue($service1->hasAttached($sharedCar), 'Schedule1 should still have car');
        $this->assertFalse($service2->hasAttached($sharedCar), 'Schedule2 should not have car after detach');
    }
}
