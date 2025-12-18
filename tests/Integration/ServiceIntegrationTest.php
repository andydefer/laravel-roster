<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException; // <-- Ajout
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
        // Utiliser des dates futures (2038)
        // 7 juin 2038 = lundi, 8 juin 2038 = mardi

        // 1. Create availability
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame('consultation', $availability->type);

        // 2. Create schedule within availability
        $schedule = $this->scheduleService->create([
            'title' => 'First Consultation',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin 2038
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('First Consultation', $schedule->title);
        $this->assertSame($availability->id, $schedule->availability_id);

        // 3. Create impediment
        $impediment = $this->impedimentService->create([
            'reason' => 'Team Meeting',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Team Meeting', $impediment->reason);
        $this->assertSame($availability->id, $impediment->availability_id);

        // 4. Verify schedule cannot be created in blocked time
        $this->expectException(ScheduleImpedimentOverlapException::class); // <-- Changé ici

        $this->scheduleService->create([
            'title' => 'Conflict Schedule',
            'start_datetime' => '2038-06-07 14:30:00', // Overlaps with impediment
            'end_datetime' => '2038-06-07 15:30:00',
            'status' => 'available',
        ]);

        // Réinitialiser l'exception attendue pour le test suivant
        $this->expectException(OverlappingScheduleException::class);

        $this->scheduleService->create([
            'title' => 'Overlap Schedule',
            'start_datetime' => '2038-06-07 10:30:00', // Overlaps with first schedule
            'end_datetime' => '2038-06-07 11:30:00',
            'status' => 'available',
        ]);

        // 6. Check available time slots
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
        // Create first availability
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        // Create adjacent availability (should merge)
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '12:00:00',
            'end_time' => '15:00:00',
            'days' => ['monday'],
        ]);

        // Verify only one availability exists (they were merged)
        $availabilities = $this->availabilityService->all();
        $this->assertCount(1, $availabilities);

        $mergedAvailability = $availabilities->first();
        $this->assertSame('09:00:00', $mergedAvailability->start_time->format('H:i:s'));
        $this->assertSame('15:00:00', $mergedAvailability->end_time->format('H:i:s'));
    }

    public function test_complex_scheduling_scenario(): void
    {
        // Utiliser des dates futures
        // 7 juin 2038 = lundi, 8 juin 2038 = mardi, 9 juin 2038 = mercredi, 10 juin 2038 = jeudi

        // Create multiple availabilities with different types
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

        // Create schedules for different types
        $consultationSchedule = $this->scheduleService->create([
            'title' => 'Doctor Consultation',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi
            'end_datetime' => '2038-06-07 11:00:00',
            'type' => 'consultation',
            'status' => 'booked',
        ]);

        $trainingSchedule = $this->scheduleService->create([
            'title' => 'Staff Training',
            'start_datetime' => '2038-06-08 15:00:00', // Mardi
            'end_datetime' => '2038-06-08 16:00:00',
            'type' => 'training',
            'status' => 'booked',
        ]);

        // Verify each schedule is linked to correct availability
        $this->assertSame($consultationAvailability->id, $consultationSchedule->availability_id);
        $this->assertSame($trainingAvailability->id, $trainingSchedule->availability_id);

        // Create impediment for consultation time
        $this->impedimentService->create([
            'reason' => 'Emergency',
            'start_datetime' => '2038-06-09 10:00:00', // Mercredi
            'end_datetime' => '2038-06-09 12:00:00',
            'type' => 'consultation',
        ]);

        // Verify impediment blocks consultation but not training
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

        // Test finding next available slots - il faut simuler la date actuelle
        Carbon::setTestNow('2038-06-06 08:00:00'); // Un jour avant

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
        // Create availability with date range en 2038
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        // Schedule within date range should succeed
        $validSchedule = $this->scheduleService->create([
            'title' => 'Valid Schedule',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin 2038, dans la plage
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->assertInstanceOf(Schedule::class, $validSchedule);

        // Schedule outside date range should fail
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        // Utiliser un lundi hors plage (5 juillet 2038 est un lundi)
        $this->scheduleService->create([
            'title' => 'Invalid Schedule',
            'start_datetime' => '2038-07-05 10:00:00', // Lundi 5 juillet 2038, hors plage
            'end_datetime' => '2038-07-05 11:00:00',
        ]);
    }
}
