<?php

declare(strict_types=1);

namespace Roster\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\TimeSlotOverlapException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Repositories\AvailabilityRepository;
use Roster\Services\Core\SlotFinderService;
use Roster\Services\Core\ValidationService;
use Roster\Services\ScheduleService;
use Roster\Tests\TestCase;

final class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleService $scheduleService;

    private TestSchedulable $testSchedulable;

    /**
     * Dates fixes pour juin 2027
     */
    private Carbon $mondayJune7;
        // Lundi 7 juin 2027
    private Carbon $tuesdayJune8;
       // Mardi 8 juin 2027
    private Carbon $nextMondayJune14; // Lundi suivant 14 juin 2027

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_schedulables', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Créer les dépendances nécessaires
        $validationService = new ValidationService();
        $availabilityRepository = new AvailabilityRepository($validationService);
        $slotFinderService = new SlotFinderService($validationService);

        // Maintenant créer le ScheduleService final
        $this->scheduleService = new ScheduleService(
            $validationService,
            $availabilityRepository,
            $slotFinderService
        );

        $this->testSchedulable = TestSchedulable::create(['name' => 'Test Schedulable']);

        // Dates fixes de juin 2027
        $this->mondayJune7 = Carbon::create(2027, 6, 7); // Lundi
        $this->tuesdayJune8 = Carbon::create(2027, 6, 8); // Mardi
        $this->nextMondayJune14 = Carbon::create(2027, 6, 14); // Lundi suivant
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_schedule_with_valid_data(): void
    {
        // Créer une disponibilité pour les lundis
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $startDatetime = $this->mondayJune7->copy()->setTime(9, 0);
        $endDatetime = $this->mondayJune7->copy()->setTime(10, 0);

        // Créer un schedule valide
        $schedule = $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation Patient A',
            'description' => 'Consultation de routine',
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'status' => 'available',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertEquals('Consultation Patient A', $schedule->title);
        $this->assertEquals('2027-06-07 09:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals('consultation', $schedule->type);
        $this->assertEquals($availability->id, $schedule->availability_id);
    }

    public function test_create_schedule_fails_without_matching_availability(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
        ]);
    }

    public function test_create_schedule_fails_on_wrong_day(): void
    {
        // Créer une disponibilité seulement le lundi
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // NE PAS créer d'availability pour le mardi
        // Le test doit échouer car il n'y a pas d'availability pour le mardi

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        // Essayer de créer un schedule le mardi
        $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation',
            'start_datetime' => $this->tuesdayJune8->copy()->setTime(9, 0),
            'end_datetime' => $this->tuesdayJune8->copy()->setTime(10, 0),
            'status' => 'available',
        ]);
    }

    public function test_create_schedule_fails_when_specific_type_not_available_on_day(): void
    {
        // Créer une disponibilité 'consultation' seulement le lundi
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer une disponibilité 'meeting' pour le mardi
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['tuesday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        // Essayer de créer un schedule le mardi avec type spécifique 'consultation'
        // Il ne trouvera pas d'availability 'consultation' pour le mardi
        $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation',
            'start_datetime' => $this->tuesdayJune8->copy()->setTime(9, 0),
            'end_datetime' => $this->tuesdayJune8->copy()->setTime(10, 0),
            'status' => 'available',
            'type' => 'consultation', // Type spécifique
        ]);
    }

    public function test_create_schedule_fails_outside_time_range(): void
    {
        // Créer une disponibilité avec horaires limités
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00', // Commence à 9h
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Ici, le schedule est à 8h00-8h30, ce qui est EN DEHORS des heures de disponibilité (9h-17h)
        // Le système devrait d'abord essayer de trouver une disponibilité correspondante
        // Mais comme 8h00-8h30 n'est PAS dans 9h-17h, il ne trouvera PAS de disponibilité
        // Donc l'exception est ValidationException::NO_MATCHING_AVAILABILITY
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(8, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(8, 30),
            'status' => 'available',
        ]);
    }

    public function test_create_schedule_fails_with_overlapping_schedule(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un premier schedule
        Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation 1',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        $this->expectException(TimeSlotOverlapException::class);
        $this->expectExceptionMessage('Schedule overlaps with another schedule');

        // Essayer de créer un schedule qui chevauche
        $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation 2',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 30),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 30),
        ]);
    }

    public function test_create_schedule_fails_with_overlapping_impediment(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un impediment
        Impediment::create([
            'availability_id' => $availability->id,
            'reason' => 'Maladie',
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
        ]);

        $this->expectException(TimeSlotOverlapException::class);
        $this->expectExceptionMessage('Schedule overlaps with an impediment');

        $this->scheduleService->for($this->testSchedulable)->create([
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(11, 0),
        ]);
    }

    public function test_is_time_slot_available_returns_true_for_free_slot(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $start = $this->mondayJune7->copy()->setTime(9, 0);
        $end = $this->mondayJune7->copy()->setTime(10, 0);

        $isAvailable = $this->scheduleService->for($this->testSchedulable)
            ->isTimeSlotAvailable($start, $end, 'consultation');

        $this->assertTrue($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_for_overlapping_schedule(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un schedule
        Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        $start = $this->mondayJune7->copy()->setTime(9, 30);
        $end = $this->mondayJune7->copy()->setTime(10, 30);

        $isAvailable = $this->scheduleService->for($this->testSchedulable)
            ->isTimeSlotAvailable($start, $end, 'consultation');

        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_for_overlapping_impediment(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un impediment
        Impediment::create([
            'availability_id' => $availability->id,
            'reason' => 'Pause',
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 0),
        ]);

        $start = $this->mondayJune7->copy()->setTime(12, 30);
        $end = $this->mondayJune7->copy()->setTime(13, 30);

        $isAvailable = $this->scheduleService->for($this->testSchedulable)
            ->isTimeSlotAvailable($start, $end, 'consultation');

        $this->assertFalse($isAvailable);
    }

    public function test_find_next_available_slot_returns_correct_slot(): void
    {
        // Supprimer l'utilisation de Mockery pour ce test
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'tuesday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Simuler que nous sommes le dimanche 6 juin 2027 (avant le lundi 7)
        Carbon::setTestNow('2027-06-06 00:00:00');

        // Appeler la méthode sans mock
        $nextSlot = $this->scheduleService->for($this->testSchedulable)
            ->findNextAvailableSlot(60, 'consultation');

        // Vérifier que nous obtenons un créneau
        $this->assertNotNull($nextSlot, 'Should find an available slot');

        // Vérifier que le créneau est dans la disponibilité
        $this->assertEquals($availability->id, $nextSlot['availability_id']);
        $this->assertEquals('consultation', $nextSlot['type']);

        // Vérifier que la durée est correcte
        $duration = $nextSlot['start']->diffInMinutes($nextSlot['end']);
        $this->assertEquals(60, $duration, 'Slot should be 60 minutes long');

        // Nettoyer le test de temps
        Carbon::setTestNow();
    }

    public function test_update_schedule_with_valid_changes(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation Original',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        $newStart = $this->mondayJune7->copy()->setTime(11, 0);
        $newEnd = $this->mondayJune7->copy()->setTime(12, 0);

        $updated = $this->scheduleService->for($this->testSchedulable)->update($schedule->id, [
            'title' => 'Consultation Modifiée',
            'start_datetime' => $newStart,
            'end_datetime' => $newEnd,
        ]);

        $this->assertTrue($updated);
        $schedule->refresh();
        $this->assertEquals('Consultation Modifiée', $schedule->title);
        $this->assertEquals('2027-06-07 11:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_update_schedule_fails_with_overlap(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation 1',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        $schedule2 = Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation 2',
            'start_datetime' => $this->mondayJune7->copy()->setTime(11, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'status' => 'available',
        ]);

        $this->expectException(TimeSlotOverlapException::class);

        // Essayer de déplacer schedule2 pour qu'il chevauche schedule1
        $this->scheduleService->for($this->testSchedulable)->update($schedule2->id, [
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 30),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 30),
        ]);
    }

    public function test_delete_schedule(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        $result = $this->scheduleService->for($this->testSchedulable)->delete($schedule->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_get_schedules_between_dates(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation 1',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation 2',
            'start_datetime' => $this->nextMondayJune14->copy()->setTime(9, 0),
            'end_datetime' => $this->nextMondayJune14->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        $startDate = Carbon::create(2027, 6, 1);
        $endDate = Carbon::create(2027, 6, 10);

        $schedules = $this->scheduleService->for($this->testSchedulable)
            ->between($startDate, $endDate);

        $this->assertCount(1, $schedules);
        $this->assertEquals('Consultation 1', $schedules->first()->title);
    }

    public function test_filter_schedules_by_type(): void
    {
        // Créer deux availabilities avec types différents
        $availability1 = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $availability2 = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'training',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        Schedule::create([
            'availability_id' => $availability1->id,
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $availability2->id,
            'title' => 'Training',
            'start_datetime' => $this->mondayJune7->copy()->setTime(18, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(19, 0),
            'status' => 'available',
        ]);

        $consultations = $this->scheduleService->for($this->testSchedulable)
            ->whereType('consultation')
            ->get();

        $this->assertCount(1, $consultations);
        $this->assertEquals('Consultation', $consultations->first()->title);
    }
}
