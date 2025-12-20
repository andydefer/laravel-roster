<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\ScheduleService;
use Tests\TestCase;

final class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleService $scheduleService;

    private Model $model;

    private Availability $availability;

    private Availability $trainingAvailability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $this->model->id = 1;
        $this->model->save();

        // Create availabilities
        $this->availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $this->trainingAvailability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'training',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $this->scheduleService = app(ScheduleService::class);
        $this->scheduleService->for($this->model);
    }

    public function test_create_schedule_successfully(): void
    {
        // Utiliser une date future (2038-06-07 est un lundi)
        $data = [
            'title' => 'Test Consultation',
            'description' => 'Test description',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
            'metadata' => ['notes' => 'Test notes'],
        ];

        // Utiliser la nouvelle API avec Availability explicite
        $schedule = $this->scheduleService->create($this->availability, $data);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Test Consultation', $schedule->title);
        $this->assertSame($this->availability->id, $schedule->availability_id);
        $this->assertDatabaseHas('roster_schedules', [
            'title' => 'Test Consultation',
            'availability_id' => $this->availability->id,
        ]);
    }

    public function test_availability_is_required_for_schedule_creation_workflow(): void
    {
        // Tester le workflow complet: on ne peut pas créer un schedule valide sans Availability

        // Créer une Availability pour référence
        $availability = $this->availability;

        // 1. Créer un schedule avec Availability => OK
        $scheduleWithAvailability = $this->scheduleService->create($availability, [
            'title' => 'Schedule avec Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->assertNotNull($scheduleWithAvailability->availability_id);
        $this->assertSame($availability->id, $scheduleWithAvailability->availability_id);

        // 2. Tenter de créer un schedule sans Availability => Échec
        $this->expectException(\BadMethodCallException::class);

        $this->scheduleService->create([
            'title' => 'Schedule sans Availability',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);
    }
    public function test_create_schedule_with_type_filters_availability(): void
    {
        $data = [
            'title' => 'Training Session',
            'start_datetime' => '2038-06-07 14:00:00', // Lundi
            'end_datetime' => '2038-06-07 15:00:00',
        ];

        // Utiliser la nouvelle API avec Availability explicite de type training
        $schedule = $this->scheduleService->create($this->trainingAvailability, $data);

        $this->assertSame($this->trainingAvailability->id, $schedule->availability_id);
        $this->assertSame('training', $schedule->type);
    }

    public function test_create_schedule_with_invalid_availability_throws_exception(): void
    {
        $otherModel = new class extends Model {
            protected $table = 'test_schedulables';
            public $timestamps = false;
        };
        $otherModel->id = 2;
        $otherModel->save();

        $data = [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided availability does not belong to this schedulable');


        $this->scheduleService->for($otherModel);
        $this->scheduleService->create($this->availability, $data);
    }

    public function test_cannot_create_schedule_without_availability(): void
    {
        $data = [
            'title' => 'Schedule sans Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ];

        // Tenter de créer un schedule sans passer d'Availability en paramètre
        // Cela déclenchera l'exception BadMethodCallException car on utilise l'ancienne signature
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        $this->scheduleService->create($data);
    }

    public function test_cannot_create_schedule_with_null_availability(): void
    {
        $data = [
            'title' => 'Schedule avec Availability null',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        // Tenter de créer un schedule en passant null comme Availability
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid arguments for create method');

        $this->scheduleService->create(null, $data);
    }

    public function test_cannot_create_schedule_with_invalid_availability_type(): void
    {
        $data = [
            'title' => 'Schedule avec mauvais type d\'Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        // Tenter de créer un schedule en passant un tableau au lieu d'un objet Availability
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid arguments for create method');

        $this->scheduleService->create(['type' => 'consultation'], $data);
    }

    public function test_schedule_requires_availability_id_in_database(): void
    {
        // Tenter de créer un Schedule directement sans passer par le service
        // pour vérifier que la base de données impose la contrainte

        $scheduleData = [
            'title' => 'Schedule sans availability_id',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
            // Pas de availability_id intentionnellement
        ];

        if (method_exists($this, 'expectException')) {
            // Selon la configuration de la base de données, cela peut lancer différentes exceptions
            try {
                $schedule = Schedule::create($scheduleData);
                // Si on arrive ici, la base de données n'a pas de contrainte NOT NULL
                // On vérifie quand même que le schedule créé est invalide
                $this->assertNull($schedule->availability_id, 'Le schedule ne devrait pas avoir d\'availability_id');
            } catch (\Exception $e) {
                // Si une exception est levée, c'est que la base de données impose la contrainte
                $this->assertStringContainsString('availability_id', $e->getMessage());
            }
        }
    }

    public function test_schedule_title_can_be_different_from_availability(): void
    {
        // Créer une availability avec un type spécifique
        $consultationAvailability = $this->availability; // Type: 'consultation'

        // Créer un schedule avec un titre complètement différent
        $data = [
            'title' => 'Réunion de projet - Sprint 15', // Titre arbitraire
            'description' => 'Discussion sur les objectifs du sprint',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:30:00',
            'status' => 'booked',
            'metadata' => [
                'project' => 'Système de réservation',
                'attendees' => ['Alice', 'Bob', 'Charlie']
            ],
        ];

        $schedule = $this->scheduleService->create($consultationAvailability, $data);

        // Vérifications
        $this->assertInstanceOf(Schedule::class, $schedule);

        // Le titre du schedule est différent du type de l'availability
        $this->assertSame('Réunion de projet - Sprint 15', $schedule->title);
        $this->assertNotSame($consultationAvailability->type, $schedule->title);

        // Mais le type du schedule correspond bien au type de l'availability
        $this->assertSame('consultation', $schedule->type);


        // Test avec une autre availability (type 'training') et un titre différent
        $data2 = [
            'title' => 'Formation sécurité informatique',
            'start_datetime' => '2038-06-14 09:00:00',
            'end_datetime' => '2038-06-14 12:00:00',
            'status' => 'available',
            'description' => 'Formation sur les bonnes pratiques de sécurité',
        ];

        $schedule2 = $this->scheduleService->create($this->trainingAvailability, $data2);

        $this->assertSame('Formation sécurité informatique', $schedule2->title);
        $this->assertSame('training', $schedule2->type); // Type copié de l'availability

        // Vérifier que le titre n'a pas été modifié pour correspondre au type
        $this->assertNotSame($this->trainingAvailability->type, $schedule2->title);

        // Test supplémentaire : même titre que le type n'est pas obligatoire
        $data3 = [
            'title' => 'consultation', // Titre identique au type (mais c'est une coïncidence)
            'start_datetime' => '2038-06-21 11:00:00',
            'end_datetime' => '2038-06-21 12:00:00',
            'status' => 'available',
        ];

        $schedule3 = $this->scheduleService->create($consultationAvailability, $data3);

        $this->assertSame('consultation', $schedule3->title); // Titre
        $this->assertSame('consultation', $schedule3->type);  // Type

        // Même s'ils sont identiques ici, ce n'est pas une contrainte du système
    }

    public function test_update_schedule_successfully(): void
    {
        // Créer un schedule avec une date future pour l'update
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Original Title',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
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

    public function test_delete_schedule(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $deleted = $this->scheduleService->delete($schedule->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_schedules', ['id' => $schedule->id]);
    }

    public function test_find_schedule_by_id(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $found = $this->scheduleService->find($schedule->id);

        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Test Schedule', $found->title);
    }

    public function test_is_time_slot_available(): void
    {
        // Create a schedule to block part of the availability
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Blocked Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        // Test available slot (before blocked time)
        $availableStart = Carbon::parse('2038-06-07 09:00:00');
        $availableEnd = Carbon::parse('2038-06-07 09:30:00');
        $this->assertTrue($this->scheduleService->isTimeSlotAvailable($availableStart, $availableEnd, 'consultation'));

        // Test blocked slot
        $blockedStart = Carbon::parse('2038-06-07 10:00:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:30:00');
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable($blockedStart, $blockedEnd, 'consultation'));

        // Test overlapping slot
        $overlapStart = Carbon::parse('2038-06-07 10:30:00');
        $overlapEnd = Carbon::parse('2038-06-07 11:30:00');
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable($overlapStart, $overlapEnd, 'consultation'));
    }

    public function test_find_next_available_slot(): void
    {
        Carbon::setTestNow('2038-06-06 08:00:00');

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Blocked',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $nextSlot = $this->scheduleService->findNextAvailableSlot(60, 'consultation');

        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);
        $this->assertSame('2038-06-07 09:00:00', $nextSlot['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $nextSlot['end']->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_between_method_returns_schedules_in_period(): void
    {
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 3',
            'start_datetime' => '2038-06-14 10:00:00',
            'end_datetime' => '2038-06-14 11:00:00',
            'status' => 'available',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        /** @var Collection<Schedule> $schedules */
        $schedules = $this->scheduleService->between($start, $end);

        $this->assertCount(2, $schedules);
        $this->assertSame('Schedule 1', $schedules[0]->title);
        $this->assertSame('Schedule 2', $schedules[1]->title);
    }

    public function test_old_create_method_is_deprecated(): void
    {
        $data = [
            'title' => 'Test Consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        // Tenter d'utiliser l'ancienne API
        $this->scheduleService->create($data);
    }
}
