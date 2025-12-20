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
    private Model $schedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = new class extends Model {
            protected $table = 'test_schedulables';
            public $timestamps = false;
        };
        $this->schedulable->id = 1;
        $this->schedulable->save();

        $this->availabilityService = app(AvailabilityService::class);
        $this->scheduleService = app(ScheduleService::class);
        $this->impedimentService = app(ImpedimentService::class);

        $this->availabilityService->for($this->schedulable);
        $this->scheduleService->for($this->schedulable);
        $this->impedimentService->for($this->schedulable);
    }

    public function test_complete_workflow_availability_schedule_impediment(): void
    {
        // 1. Créer une availability
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

        // 2. Créer un schedule avec la nouvelle API
        $schedule = $this->scheduleService->create($availability, [
            'title' => 'First Consultation',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin 2038
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('First Consultation', $schedule->title);
        $this->assertSame($availability->id, $schedule->availability_id);

        // 3. Créer un impediment avec la nouvelle API
        $impediment = $this->impedimentService->create($availability, [
            'reason' => 'Team Meeting',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Team Meeting', $impediment->reason);
        $this->assertSame($availability->id, $impediment->availability_id);

        // 4. Tenter de créer un schedule qui chevauche l'impediment
        $this->expectException(ScheduleImpedimentOverlapException::class);
        $this->scheduleService->create($availability, [
            'title' => 'Conflict Schedule',
            'start_datetime' => '2038-06-07 14:30:00',
            'end_datetime' => '2038-06-07 15:30:00',
            'status' => 'available',
        ]);
    }


    public function test_schedule_overlapping_validation(): void
    {
        // Créer une availability
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        // Créer un premier schedule
        $this->scheduleService->create($availability, [
            'title' => 'First Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        // Tenter de créer un schedule qui chevauche
        $this->expectException(OverlappingScheduleException::class);
        $this->scheduleService->create($availability, [
            'title' => 'Overlap Schedule',
            'start_datetime' => '2038-06-07 10:30:00',
            'end_datetime' => '2038-06-07 11:30:00',
            'status' => 'available',
        ]);
    }

    public function test_time_slot_availability(): void
    {
        // Créer une availability
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        // Créer un schedule
        $this->scheduleService->create($availability, [
            'title' => 'Booked Slot',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        // Créer un impediment
        $this->impedimentService->create($availability, [
            'reason' => 'Meeting',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        // Tester les créneaux disponibles
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

    public function test_availability_merging_and_adjacent_slots(): void
    {
        // Créer deux availabilities adjacentes
        $availability1 = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '12:00:00',
            'end_time' => '15:00:00',
            'days' => ['monday'],
        ]);

        // Vérifier qu'elles sont fusionnées
        $availabilities = $this->availabilityService->all();
        $this->assertCount(1, $availabilities);

        $mergedAvailability = $availabilities->first();
        $this->assertSame('09:00:00', $mergedAvailability->start_time->format('H:i:s'));
        $this->assertSame('15:00:00', $mergedAvailability->end_time->format('H:i:s'));
    }

    public function test_complex_scheduling_scenario(): void
    {
        // Créer deux availabilities de types différents
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

        // Créer des schedules pour chaque type
        $consultationSchedule = $this->scheduleService->create($consultationAvailability, [
            'title' => 'Doctor Consultation',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin 2038
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $trainingSchedule = $this->scheduleService->create($trainingAvailability, [
            'title' => 'Staff Training',
            'start_datetime' => '2038-06-08 15:00:00', // Mardi 8 juin 2038
            'end_datetime' => '2038-06-08 16:00:00',
            'status' => 'booked',
        ]);

        $this->assertSame($consultationAvailability->id, $consultationSchedule->availability_id);
        $this->assertSame($trainingAvailability->id, $trainingSchedule->availability_id);

        // Créer un impediment
        $this->impedimentService->create($consultationAvailability, [
            'reason' => 'Emergency',
            'start_datetime' => '2038-06-09 10:00:00', // Mercredi 9 juin 2038
            'end_datetime' => '2038-06-09 12:00:00',
        ]);

        // Tester le blocage par type
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

        // Tester la recherche de créneaux disponibles
        Carbon::setTestNow('2038-06-06 08:00:00');

        $nextConsultationSlot = $this->scheduleService->findNextAvailableSlot(60, 'consultation');
        $nextTrainingSlot = $this->scheduleService->findNextAvailableSlot(60, 'training');

        $this->assertIsArray($nextConsultationSlot);
        $this->assertIsArray($nextTrainingSlot);
        $this->assertArrayHasKey('type', $nextConsultationSlot);
        $this->assertArrayHasKey('type', $nextTrainingSlot);
        $this->assertSame('consultation', $nextConsultationSlot['type']);
        $this->assertSame('training', $nextTrainingSlot['type']);

        Carbon::setTestNow(); // Nettoyer
    }


    public function test_wrong_availability_validation(): void
    {
        // Créer une availability pour ce schedulable
        $availability1 = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        // Créer un autre schedulable avec sa propre availability
        $otherSchedulable = new class extends Model {
            protected $table = 'test_schedulables';
            public $timestamps = false;
        };
        $otherSchedulable->id = 2;
        $otherSchedulable->save();

        $availabilityService2 = app(AvailabilityService::class);
        $availabilityService2->for($otherSchedulable);

        $availability2 = $availabilityService2->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        // Tenter d'utiliser l'availability du mauvais schedulable
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided availability does not belong to this schedulable');

        $this->scheduleService->create($availability2, [
            'title' => 'Wrong Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);
    }

    public function test_impediment_workflow(): void
    {
        // Créer une availability
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        // Créer un impediment
        $impediment = $this->impedimentService->create($availability, [
            'reason' => 'Maintenance',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 12:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);

        // Vérifier qu'on peut trouver l'impediment
        $found = $this->impedimentService->find($impediment->id);
        $this->assertSame($impediment->id, $found->id);

        // Vérifier la suppression
        $deleted = $this->impedimentService->delete($impediment->id);
        $this->assertTrue($deleted);

        // Vérifier qu'il n'existe plus
        $foundAfterDelete = $this->impedimentService->find($impediment->id);
        $this->assertNull($foundAfterDelete);
    }

    public function test_schedule_update_workflow(): void
    {
        // Créer une availability
        $availability = $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        // Créer un schedule
        $schedule = $this->scheduleService->create($availability, [
            'title' => 'Original Title',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
            'description' => 'Original description',
        ]);

        // Mettre à jour le schedule
        $updated = $this->scheduleService->update($schedule->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        // Vérifier les changements
        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
    }
}
