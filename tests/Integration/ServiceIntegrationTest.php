<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Tests\TestCase;

final class ServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilityService;

    private ScheduleService $scheduleService;

    private ImpedimentService $impedimentService;

    protected function setUp(): void
    {
        parent::setUp();

        $schedulable = new class extends Model {
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

    public function test_complete_workflow_availability_schedule_impediment(): void
    {
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame('consultation', $availability->type);

        $schedule = $this->scheduleService->create([
            'title' => 'First Consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('First Consultation', $schedule->title);
        $this->assertSame($availability->id, $schedule->availability_id);

        $impediment = $this->impedimentService->create([
            'reason' => 'Team Meeting',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Team Meeting', $impediment->reason);
        $this->assertSame($availability->id, $impediment->availability_id);

        $this->expectException(ScheduleImpedimentOverlapException::class);
        $this->scheduleService->create([
            'title' => 'Conflict Schedule',
            'start_datetime' => '2038-06-07 14:30:00',
            'end_datetime' => '2038-06-07 15:30:00',
            'status' => 'available',
        ]);

        $this->expectException(OverlappingScheduleException::class);

        $this->scheduleService->create([
            'title' => 'Overlap Schedule',
            'start_datetime' => '2038-06-07 10:30:00',
            'end_datetime' => '2038-06-07 11:30:00',
            'status' => 'available',
        ]);

        $start = Carbon::parse('2038-06-07 09:00:00');
        Carbon::parse('2038-06-07 17:00:00');

        $this->assertTrue($this->scheduleService->isTimeSlotAvailable($start, Carbon::parse('2038-06-07 09:30:00')));
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-06-07 10:30:00'),
            Carbon::parse('2038-06-07 11:00:00')
        ));
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-06-07 14:30:00'),
            Carbon::parse('2038-06-07 15:00:00')
        ));
    }

    public function test_availability_merging_and_adjacent_slots(): void
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

    public function test_complex_scheduling_scenario(): void
    {

        $consultationAvailability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday', 'wednesday'],
        ]);

        $trainingAvailability = $this->availabilityService->create([
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
        ]);

        $consultationSchedule = $this->scheduleService->create([
            'title' => 'Doctor Consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'type' => 'consultation',
            'status' => 'booked',
        ]);

        $trainingSchedule = $this->scheduleService->create([
            'title' => 'Staff Training',
            'start_datetime' => '2038-06-08 15:00:00',
            'end_datetime' => '2038-06-08 16:00:00',
            'type' => 'training',
            'status' => 'booked',
        ]);

        $this->assertSame($consultationAvailability->id, $consultationSchedule->availability_id);
        $this->assertSame($trainingAvailability->id, $trainingSchedule->availability_id);

        $this->impedimentService->create([
            'reason' => 'Emergency',
            'start_datetime' => '2038-06-09 10:00:00',
            'end_datetime' => '2038-06-09 12:00:00',
            'type' => 'consultation',
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
        $this->assertSame('consultation', $nextConsultationSlot['type'] ?? null);
        $this->assertSame('training', $nextTrainingSlot['type'] ?? null);

        Carbon::setTestNow(); // Nettoyer
    }

    public function test_date_range_constraints(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $validSchedule = $this->scheduleService->create([
            'title' => 'Valid Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->assertInstanceOf(Schedule::class, $validSchedule);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->scheduleService->create([
            'title' => 'Invalid Schedule',
            'start_datetime' => '2038-07-05 10:00:00',
            'end_datetime' => '2038-07-05 11:00:00',
        ]);
    }
}
