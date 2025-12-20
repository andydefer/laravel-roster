<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Tests\TestCase;

/**
 * Integration tests for the Roster service layer.
 *
 * Verifies the complete workflow and interactions between
 * Availability, Schedule, and Impediment services.
 */
final class ServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilityService;

    private ScheduleService $scheduleService;

    private ImpedimentService $impedimentService;

    /**
     * Set up test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $schedulable = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        $schedulable->id = 1;
        $schedulable->save();

        $this->availabilityService = app(AvailabilityService::class);
        $this->scheduleService = app(ScheduleService::class);
        $this->impedimentService = app(ImpedimentService::class);

        $this->availabilityService->for($schedulable);
        $this->scheduleService->for($schedulable);
        $this->impedimentService->for($schedulable);
    }

    /**
     * Tests the complete workflow: availability creation, scheduling,
     * impediment creation, and conflict detection.
     */
    public function testCompleteWorkflowAvailabilityScheduleImpediment(): void
    {
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame('consultation', $availability->type);

        $schedule = $this->scheduleService->create($availability, [
            'title' => 'First Consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('First Consultation', $schedule->title);
        $this->assertSame($availability->id, $schedule->availability_id);

        $impediment = $this->impedimentService->create($availability, [
            'reason' => 'Team Meeting',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Team Meeting', $impediment->reason);
        $this->assertSame($availability->id, $impediment->availability_id);

        $this->expectException(ScheduleImpedimentOverlapException::class);
        $this->scheduleService->create($availability, [
            'title' => 'Conflict Schedule',
            'start_datetime' => '2038-06-07 14:30:00',
            'end_datetime' => '2038-06-07 15:30:00',
            'status' => 'available',
        ]);
    }

    /**
     * Tests validation for overlapping schedules.
     */
    public function testScheduleOverlappingValidation(): void
    {
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $this->scheduleService->create($availability, [
            'title' => 'First Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $this->expectException(OverlappingScheduleException::class);
        $this->scheduleService->create($availability, [
            'title' => 'Overlap Schedule',
            'start_datetime' => '2038-06-07 10:30:00',
            'end_datetime' => '2038-06-07 11:30:00',
            'status' => 'available',
        ]);
    }

    /**
     * Tests time slot availability checking with schedules and impediments.
     */
    public function testTimeSlotAvailability(): void
    {
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $this->scheduleService->create($availability, [
            'title' => 'Booked Slot',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $this->impedimentService->create($availability, [
            'reason' => 'Meeting',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 09:00:00');

        $this->assertTrue($this->scheduleService->isTimeSlotAvailable(
            $start,
            Carbon::parse('2038-06-07 09:30:00'),
            'consultation'
        ));

        $this->assertFalse($this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-06-07 10:30:00'),
            Carbon::parse('2038-06-07 11:00:00'),
            'consultation'
        ));

        $this->assertFalse($this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-06-07 14:30:00'),
            Carbon::parse('2038-06-07 15:00:00'),
            'consultation'
        ));
    }

    /**
     * Tests automatic merging of adjacent availability slots.
     */
    public function testAvailabilityMergingAndAdjacentSlots(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '12:00:00',
            'end_time' => '15:00:00',
            'days' => ['monday'],
        ]);

        $availabilities = $this->availabilityService->all();
        $this->assertCount(1, $availabilities);

        $mergedAvailability = $availabilities->first();
        $this->assertSame('09:00:00', $mergedAvailability->start_time->format('H:i:s'));
        $this->assertSame('15:00:00', $mergedAvailability->end_time->format('H:i:s'));
    }

    /**
     * Tests complex scheduling scenarios with multiple availability types.
     */
    public function testComplexSchedulingScenario(): void
    {
        $consultationAvailability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday', 'wednesday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $trainingAvailability = $this->availabilityService->create([
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $consultationSchedule = $this->scheduleService->create($consultationAvailability, [
            'title' => 'Doctor Consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $trainingSchedule = $this->scheduleService->create($trainingAvailability, [
            'title' => 'Staff Training',
            'start_datetime' => '2038-06-08 15:00:00',
            'end_datetime' => '2038-06-08 16:00:00',
            'status' => 'booked',
        ]);

        $this->assertSame($consultationAvailability->id, $consultationSchedule->availability_id);
        $this->assertSame($trainingAvailability->id, $trainingSchedule->availability_id);

        $this->impedimentService->create($consultationAvailability, [
            'reason' => 'Emergency',
            'start_datetime' => '2038-06-09 10:00:00',
            'end_datetime' => '2038-06-09 12:00:00',
        ]);

        $this->assertTrue($this->impedimentService->isTimeSlotBlocked(
            Carbon::parse('2038-06-09 11:00:00'),
            Carbon::parse('2038-06-09 11:30:00'),
            'consultation'
        ));

        $this->assertFalse($this->impedimentService->isTimeSlotBlocked(
            Carbon::parse('2038-06-09 11:00:00'),
            Carbon::parse('2038-06-09 11:30:00'),
            'training'
        ));

        Carbon::setTestNow('2038-06-06 08:00:00');

        $nextConsultationSlot = $this->scheduleService->findNextAvailableSlot(60, 'consultation');
        $nextTrainingSlot = $this->scheduleService->findNextAvailableSlot(60, 'training');

        $this->assertIsArray($nextConsultationSlot);
        $this->assertIsArray($nextTrainingSlot);
        $this->assertArrayHasKey('type', $nextConsultationSlot);
        $this->assertArrayHasKey('type', $nextTrainingSlot);
        $this->assertSame('consultation', $nextConsultationSlot['type']);
        $this->assertSame('training', $nextTrainingSlot['type']);

        Carbon::setTestNow();
    }

    /**
     * Tests validation when using availability from wrong schedulable.
     */
    public function testWrongAvailabilityValidation(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $otherSchedulable = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        $otherSchedulable->id = 2;
        $otherSchedulable->save();

        $availabilityService2 = app(AvailabilityService::class);
        $availabilityService2->for($otherSchedulable);

        $otherAvailability = $availabilityService2->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided availability does not belong to this schedulable');

        $this->scheduleService->create($otherAvailability, [
            'title' => 'Wrong Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);
    }

    /**
     * Tests complete impediment lifecycle (create, find, delete).
     */
    public function testImpedimentWorkflow(): void
    {
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $impediment = $this->impedimentService->create($availability, [
            'reason' => 'Maintenance',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 12:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);

        $found = $this->impedimentService->find($impediment->id);
        $this->assertSame($impediment->id, $found->id);

        $deleted = $this->impedimentService->delete($impediment->id);
        $this->assertTrue($deleted);

        $foundAfterDelete = $this->impedimentService->find($impediment->id);
        $this->assertNotInstanceOf(Impediment::class, $foundAfterDelete);
    }

    /**
     * Tests schedule update functionality.
     */
    public function testScheduleUpdateWorkflow(): void
    {
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $schedule = $this->scheduleService->create($availability, [
            'title' => 'Original Title',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
            'description' => 'Original description',
        ]);

        $updated = $this->scheduleService->update($schedule->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
    }
}
