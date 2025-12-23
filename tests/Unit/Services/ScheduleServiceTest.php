<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\DTOs\ScheduleData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Services\ScheduleService;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

final class ScheduleServiceTest extends TestCase
{
    private ScheduleService $scheduleService;
    private TestSchedulable $testSchedulable;
    private Availability $testAvailability;
    private array $baseScheduleData;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un schedulable de test
        $this->testSchedulable = TestSchedulable::create();

        // Créer une disponibilité de test
        $this->testAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Données de base pour un schedule
        $this->baseScheduleData = [
            'title' => 'Réunion de test',
            'description' => 'Description de test',
            'start_datetime' => '2038-01-04 10:00:00', // Lundi
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED->value,
            'metadata' => ['priority' => 'high'],
        ];

        // Initialiser le service
        $this->scheduleService = app(ScheduleService::class);
        $this->scheduleService->for($this->testSchedulable);

        // Configurer pour les tests
        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);
    }

    protected function tearDown(): void
    {
        // Nettoyer la base de données
        Schedule::query()->delete();
        Impediment::query()->delete();
        Availability::query()->delete();
        TestSchedulable::query()->delete();

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     | Tests CRUD Basiques
     | -----------------------------------------------------------------
     */

    public function test_create_schedule_successfully(): void
    {
        // Arrange
        $data = $this->baseScheduleData;

        // Act
        $schedule = $this->scheduleService->create($this->testAvailability, $data);

        // Assert
        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertNotNull($schedule->id);
        $this->assertEquals($this->testAvailability->id, $schedule->availability_id);
        $this->assertEquals($this->testSchedulable->id, $schedule->schedulable_id);
        $this->assertEquals(TestSchedulable::class, $schedule->schedulable_type);
        $this->assertEquals('Réunion de test', $schedule->title);
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $schedule->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 11:00:00'), $schedule->end_datetime);
        $this->assertEquals(ScheduleStatus::BOOKED, $schedule->status);
        $this->assertEquals(['priority' => 'high'], $schedule->metadata);
    }

    public function test_create_schedule_with_default_status(): void
    {
        // Arrange - Sans spécifier le statut
        $data = [
            'title' => 'Réunion sans statut',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ];

        // Act
        $schedule = $this->scheduleService->create($this->testAvailability, $data);

        // Assert - Doit utiliser le statut par défaut
        $this->assertEquals(ScheduleStatus::AVAILABLE->value, $schedule->status->value);
    }

    public function test_create_schedule_fails_when_end_before_start(): void
    {
        // Arrange
        $data = [
            'title' => 'Schedule invalide',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 10:00:00', // Fin avant début
        ];

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act
        $this->scheduleService->create($this->testAvailability, $data);
    }

    public function test_create_schedule_fails_when_too_short(): void
    {
        // Arrange - Durée trop courte (< 15 minutes)
        $data = [
            'title' => 'Trop court',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:05:00', // 5 minutes seulement
        ];

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        $this->scheduleService->create($this->testAvailability, $data);
    }

    public function test_create_schedule_fails_when_no_availability(): void
    {
        // Arrange - Créer une autre disponibilité pour un autre schedulable
        $otherSchedulable = TestSchedulable::create();
        $otherAvailability = Availability::create([
            'schedulable_id' => $otherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Act & Assert - Doit échouer car l'availability n'appartient pas au bon schedulable
        $this->expectException(ValidationFailedException::class);
        $this->scheduleService->create($otherAvailability, $this->baseScheduleData);
    }

    public function test_update_schedule_successfully(): void
    {
        // Arrange - Créer un schedule
        $schedule = $this->scheduleService->create($this->testAvailability, $this->baseScheduleData);

        $updateData = [
            'title' => 'Titre modifié',
            'description' => 'Description modifiée',
            'metadata' => ['updated' => true],
        ];

        // Act
        $result = $this->scheduleService->update($schedule->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $schedule->refresh();
        $this->assertEquals('Titre modifié', $schedule->title);
        $this->assertEquals('Description modifiée', $schedule->description);
        $this->assertEquals(['updated' => true], $schedule->metadata);
        // Les dates ne devraient pas changer
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $schedule->start_datetime);
    }

    public function test_update_schedule_with_datetime_changes(): void
    {
        // Arrange
        $schedule = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Original',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $updateData = [
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 14:00:00',
        ];

        // Act
        $result = $this->scheduleService->update($schedule->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $schedule->refresh();
        $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $schedule->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 14:00:00'), $schedule->end_datetime);
    }

    public function test_update_schedule_fails_when_overlap(): void
    {

        // Arrange - Créer deux schedules
        $schedule1 = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Schedule 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);


        $schedule2 = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Schedule 2',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);


        // Essayer de déplacer schedule1 pour qu'il chevauche schedule2
        $updateData = [
            'start_datetime' => '2038-01-04 12:30:00', // Chevauche avec schedule2
            'end_datetime' => '2038-01-04 13:30:00',
        ];


        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with an existing schedule/');


        // Act
        $this->scheduleService->update($schedule1->id, $updateData);
    }

    public function test_update_schedule_fails_when_not_found(): void
    {
        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Update validation failed for Schedule.*does not exist/'
        );

        // Act
        $this->scheduleService->update(999999, ['title' => 'test']);
    }

    public function test_delete_schedule_successfully(): void
    {
        // Arrange
        $schedule = $this->scheduleService->create($this->testAvailability, $this->baseScheduleData);

        // Act
        $result = $this->scheduleService->delete($schedule->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(Schedule::find($schedule->id));
    }

    public function test_delete_schedule_fails_when_not_found(): void
    {
        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Update validation failed for Schedule.*does not exist/'
        );

        // Act
        $this->scheduleService->delete(999999);
    }

    public function test_find_schedule_by_id(): void
    {
        // Arrange
        $schedule = $this->scheduleService->create($this->testAvailability, $this->baseScheduleData);

        // Act
        $found = $this->scheduleService->find($schedule->id);

        // Assert
        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertEquals($schedule->id, $found->id);
        $this->assertEquals('Réunion de test', $found->title);
    }

    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange - Créer un schedule pour un autre schedulable
        $otherSchedulable = TestSchedulable::create();
        $otherAvailability = Availability::create([
            'schedulable_id' => $otherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        $otherSchedule = Schedule::create([
            'availability_id' => $otherAvailability->id,
            'schedulable_id' => $otherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'title' => 'Pour autre schedulable',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED->value,
        ]);

        // Act
        $found = $this->scheduleService->find($otherSchedule->id);

        // Assert - Ne devrait pas trouver car pas le bon schedulable
        $this->assertNotInstanceOf(Schedule::class, $found);
    }

    public function test_get_all_schedules(): void
    {
        // Arrange - Créer plusieurs schedules
        $schedules = [
            [
                'title' => 'Réunion 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ],
            [
                'title' => 'Réunion 2',
                'start_datetime' => '2038-01-05 14:00:00',
                'end_datetime' => '2038-01-05 15:00:00',
            ],
        ];

        foreach ($schedules as $schedule) {
            $this->scheduleService->create($this->testAvailability, $schedule);
        }

        // Act
        $result = $this->scheduleService->get();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Réunion 1', $result[0]->title);
        $this->assertEquals('Réunion 2', $result[1]->title);
    }

    public function test_get_with_filters(): void
    {
        // Arrange - Créer des schedules avec différents statuts
        $schedules = [
            ['title' => 'Disponible', 'start_datetime' => '2038-01-04 10:00:00', 'end_datetime' => '2038-01-04 11:00:00', 'status' => ScheduleStatus::AVAILABLE],
            ['title' => 'Réservé', 'start_datetime' => '2038-01-05 10:00:00', 'end_datetime' => '2038-01-05 11:00:00', 'status' => 'booked'],
            ['title' => 'Disponible 2', 'start_datetime' => '2038-01-06 10:00:00', 'end_datetime' => '2038-01-06 11:00:00', 'status' => ScheduleStatus::AVAILABLE],
        ];

        foreach ($schedules as $schedule) {
            $this->scheduleService->create($this->testAvailability, $schedule);
        }

        // Act - Filtrer par statut ScheduleStatus::AVAILABLE
        $this->scheduleService->setFilters(['status' => ScheduleStatus::AVAILABLE]);
        $result = $this->scheduleService->get();

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals('Disponible', $result[0]->title);
        $this->assertEquals('Disponible 2', $result[1]->title);
    }

    public function test_get_with_date_range_filter(): void
    {
        // Arrange - Créer des schedules avec différentes dates
        $schedules = [
            ['title' => 'Janvier', 'start_datetime' => '2038-01-04 10:00:00', 'end_datetime' => '2038-01-04 11:00:00'],
            ['title' => 'Février', 'start_datetime' => '2038-02-04 10:00:00', 'end_datetime' => '2038-02-04 11:00:00'],
            ['title' => 'Janvier tardif', 'start_datetime' => '2038-01-25 10:00:00', 'end_datetime' => '2038-01-25 11:00:00'],
        ];

        foreach ($schedules as $schedule) {
            $this->scheduleService->create($this->testAvailability, $schedule);
        }

        // Act - Filtrer pour janvier seulement
        $this->scheduleService->setFilters([
            'start_date' => '2038-01-01',
            'end_date' => '2038-01-31',
        ]);

        $result = $this->scheduleService->get();

        // Assert
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('Janvier', $titles);
        $this->assertContains('Janvier tardif', $titles);
        $this->assertNotContains('Février', $titles);
    }

    /* -----------------------------------------------------------------
     | Tests de Recherche de Créneaux
     | -----------------------------------------------------------------
     */

    public function test_find_next_slot_without_conflicts(): void
    {
        // Arrange - Créer un schedule pour bloquer 10h-11h
        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Réunion bloquante',
            'start_datetime' => '2038-01-04 10:00:00', // Lundi
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => 'booked',
        ]);

        // Act - Chercher un créneau de 60 minutes
        $slot = $this->scheduleService->findNextSlot(
            60, // 1 heure
            'consultation',
            false,
            Carbon::parse('2038-01-04 10:00:00') // Chercher à partir de 9h
        );

        // Assert - Devrait trouver après 11h
        $this->assertNotNull($slot);
        $this->assertIsArray($slot);
        $this->assertArrayHasKey('start', $slot);
        $this->assertArrayHasKey('end', $slot);
        $this->assertArrayHasKey('availability', $slot);
        $this->assertArrayHasKey('duration_minutes', $slot);

        // Le créneau doit commencer après 11h (fin du schedule existant)

        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 11:00:00')));
        $this->assertEquals(60, $slot['duration_minutes']);
    }

    public function test_find_next_slot_return_start_only(): void
    {
        // Arrange - Pas de conflits
        // Act
        $startOnly = $this->scheduleService->findNextSlot(
            30,
            'consultation',
            true, // returnStartOnly = true
            Carbon::parse('2038-01-04 09:00:00')
        );

        // Assert
        $this->assertNotNull($startOnly);
        $this->assertInstanceOf(Carbon::class, $startOnly);
        $this->assertEquals('2038-01-04 09:00:00', $startOnly->format('Y-m-d H:i:s'));
    }

    public function test_find_next_slot_respects_availability_hours(): void
    {
        // Act - Chercher un créneau qui doit respecter 9h-17h
        $slot = $this->scheduleService->findNextSlot(
            120, // 2 heures
            'consultation',
            false,
            Carbon::parse('2038-01-04 16:00:00') // 16h - trop tard pour 2h
        );

        // Assert - Devrait trouver le lendemain
        $this->assertNotNull($slot);
        $this->assertEquals('2038-01-05', $slot['start']->format('Y-m-d')); // Mardi
        $this->assertEquals('09:00:00', $slot['start']->format('H:i:s')); // Début à 9h
    }

    public function test_find_next_slot_returns_null_when_no_availability(): void
    {
        // Arrange - Créer une disponibilité uniquement pour lundi
        $limitedAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'limited',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00', // Seulement le matin
            'days' => ['monday'], // Seulement lundi
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-10',
        ]);



        // Act - Chercher un créneau le mardi
        $slot = $this->scheduleService->for($this->testSchedulable)->findNextSlot(
            60,
            'limited',
            false,
            Carbon::parse('2038-01-05 09:00:00') // Mardi
        );

        // Assert - Devrait retourner null
        $this->assertNull($slot);
    }

    public function test_is_time_slot_available_returns_true(): void
    {
        // Arrange - Pas de conflits
        // Act
        $isAvailable = $this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-01-04 10:00:00'),
            Carbon::parse('2038-01-04 11:00:00'),
            'consultation'
        );

        // Assert
        $this->assertTrue($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_when_schedule_overlap(): void
    {
        // Arrange - Créer un schedule existant
        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Existing',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
            'status' => 'booked',
        ]);

        // Act - Vérifier créneau chevauchant
        $isAvailable = $this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-01-04 11:00:00'), // Chevauche 11h-11h30
            Carbon::parse('2038-01-04 12:00:00'),
            'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_when_impediment_overlap(): void
    {
        // Arrange - Créer un impediment
        Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Maintenance',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ]);

        // Act - Vérifier créneau chevauchant
        $isAvailable = $this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-01-04 11:00:00'), // Chevauche l'impediment
            Carbon::parse('2038-01-04 12:00:00'),
            'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_when_outside_availability(): void
    {
        // Act - Vérifier créneau en dehors des heures de disponibilité
        $isAvailable = $this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-01-04 18:00:00'), // Après 17h
            Carbon::parse('2038-01-04 19:00:00'),
            'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_with_type_filter(): void
    {
        // Arrange - Créer une disponibilité avec type différent
        $otherAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'training', // Type différent
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Créer un schedule sur l'autre disponibilité
        Schedule::create([
            'availability_id' => $otherAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'title' => 'Training schedule',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => 'booked',
        ]);

        // Act - Vérifier avec type 'consultation'
        $isAvailable = $this->scheduleService->isTimeSlotAvailable(
            Carbon::parse('2038-01-04 10:00:00'),
            Carbon::parse('2038-01-04 11:00:00'),
            'consultation' // Type différent
        );

        // Assert - Devrait être disponible car le schedule est de type 'training'
        $this->assertTrue($isAvailable);
    }

    public function test_find_available_slots_in_range(): void
    {
        // Arrange - Créer quelques schedules pour bloquer des créneaux
        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Bloc matin',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'status' => 'booked',
        ]);

        // Act - Chercher tous les créneaux de 60 minutes sur 2 jours
        $slots = $this->scheduleService->findAvailableSlots(
            Carbon::parse('2038-01-04'),
            Carbon::parse('2038-01-05'),
            60,
            'consultation'
        );

        // Assert
        $this->assertInstanceOf(Collection::class, $slots);

        // Vérifier qu'aucun créneau ne chevauche 10h-12h le 4 janvier
        foreach ($slots as $slot) {
            if ($slot['start']->format('Y-m-d') === '2038-01-04') {
                $slotStart = $slot['start'];
                $slotEnd = $slot['end'];
                $this->assertTrue(
                    $slotEnd->lte(Carbon::parse('2038-01-04 10:00:00')) ||
                        $slotStart->gte(Carbon::parse('2038-01-04 12:00:00')),
                    "Slot {$slotStart->format('H:i')}-{$slotEnd->format('H:i')} should not overlap 10:00-12:00"
                );
            }
        }
    }

    public function test_is_period_available_returns_true(): void
    {
        // Arrange - Pas de conflits
        // Act
        $isAvailable = $this->scheduleService->isPeriodAvailable(
            Carbon::parse('2038-01-04 10:00:00'),
            Carbon::parse('2038-01-04 12:00:00'),
            'consultation'
        );

        // Assert
        $this->assertTrue($isAvailable);
    }

    public function test_is_period_available_returns_false_when_schedule_conflict(): void
    {
        // Arrange - Créer un schedule au milieu de la période
        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Au milieu',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 11:30:00',
            'status' => 'booked',
        ]);

        // Act - Vérifier période plus large
        $isAvailable = $this->scheduleService->isPeriodAvailable(
            Carbon::parse('2038-01-04 10:00:00'), // 10h-13h
            Carbon::parse('2038-01-04 13:00:00'),
            'consultation'
        );

        // Assert - Devrait être false car chevauchement
        $this->assertFalse($isAvailable);
    }

    public function test_is_period_available_returns_false_when_impediment_conflict(): void
    {
        // Arrange - Créer un impediment
        Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Pause',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $isAvailable = $this->scheduleService->isPeriodAvailable(
            Carbon::parse('2038-01-04 10:00:00'),
            Carbon::parse('2038-01-04 13:00:00'),
            'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_period_available_returns_false_when_no_availability(): void
    {
        // Act - Vérifier période en dehors des heures
        $isAvailable = $this->scheduleService->isPeriodAvailable(
            Carbon::parse('2038-01-04 18:00:00'), // Après 17h
            Carbon::parse('2038-01-04 19:00:00'),
            'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_get_available_slots_from_impediments(): void
    {
        // Arrange - Créer quelques impediments
        $impediments = collect([
            Impediment::create([
                'availability_id' => $this->testAvailability->id,
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'reason' => 'Impediment 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]),
            Impediment::create([
                'availability_id' => $this->testAvailability->id,
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'reason' => 'Impediment 2',
                'start_datetime' => '2038-01-04 13:00:00',
                'end_datetime' => '2038-01-04 14:00:00',
            ]),
        ]);

        // Act
        $slots = $this->scheduleService->getAvailableSlotsFromImpediments(
            Carbon::parse('2038-01-04 09:00:00'),
            Carbon::parse('2038-01-04 17:00:00'),
            $impediments
        );

        // Assert
        $this->assertCount(3, $slots);

        // Devrait avoir:
        // 1. 9h-10h (avant premier impediment)
        // 2. 11h-13h (entre les deux impediments)
        // 3. 14h-17h (après dernier impediment)

        $this->assertEquals('09:00:00', $slots[0]['start']->format('H:i:s'));
        $this->assertEquals('10:00:00', $slots[0]['end']->format('H:i:s'));

        $this->assertEquals('11:00:00', $slots[1]['start']->format('H:i:s'));
        $this->assertEquals('13:00:00', $slots[1]['end']->format('H:i:s'));

        $this->assertEquals('14:00:00', $slots[2]['start']->format('H:i:s'));
        $this->assertEquals('17:00:00', $slots[2]['end']->format('H:i:s'));
    }

    public function test_get_available_slots_from_impediments_when_no_impediments(): void
    {
        // Arrange - Pas d'impediments
        $impediments = collect();

        // Act
        $slots = $this->scheduleService->getAvailableSlotsFromImpediments(
            Carbon::parse('2038-01-04 09:00:00'),
            Carbon::parse('2038-01-04 17:00:00'),
            $impediments
        );

        // Assert
        $this->assertCount(1, $slots);
        $this->assertEquals('09:00:00', $slots[0]['start']->format('H:i:s'));
        $this->assertEquals('17:00:00', $slots[0]['end']->format('H:i:s'));
    }

    /* -----------------------------------------------------------------
     | Tests de Cas Limites
     | -----------------------------------------------------------------
     */

    public function test_concurrent_schedule_creation_prevents_double_booking(): void
    {
        // Arrange - Créer un schedule
        $schedule1 = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Premier',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => 'booked',
        ]);

        // Act & Assert - Essayer de créer un schedule chevauchant
        $this->expectException(ValidationFailedException::class);

        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Deuxième',
            'start_datetime' => '2038-01-04 10:30:00', // Chevauche
            'end_datetime' => '2038-01-04 11:30:00',
            'status' => 'booked',
        ]);
    }

    public function test_schedule_on_non_availability_day(): void
    {
        // Arrange - Disponibilité seulement le lundi
        $mondayOnlyAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'monday-only',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'], // Seulement lundi
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act & Assert - Essayer de créer un schedule le mardi
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Day .* is not available/');

        $this->scheduleService->create($mondayOnlyAvailability, [
            'title' => 'Mardi',
            'start_datetime' => '2038-01-05 10:00:00', // Mardi
            'end_datetime' => '2038-01-05 11:00:00',
        ]);
    }

    public function test_schedule_outside_availability_hours(): void
    {
        // Act & Assert - Essayer de créer un schedule avant 9h
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Start time is before availability/');

        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Trop tôt',
            'start_datetime' => '2038-01-04 08:00:00', // Avant 9h
            'end_datetime' => '2038-01-04 09:00:00',
        ]);
    }

    public function test_schedule_exact_boundary_not_overlap(): void
    {
        // Arrange - Créer un schedule
        $schedule1 = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Premier',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act - Créer un schedule qui commence exactement à la fin du premier
        $schedule2 = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Deuxième',
            'start_datetime' => '2038-01-04 11:00:00', // Exactement à la fin
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert - Devrait réussir (pas de chevauchement)
        $this->assertInstanceOf(Schedule::class, $schedule2);
        $this->assertEquals('2038-01-04 11:00:00', $schedule2->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_find_next_slot_with_adjacent_impediments(): void
    {
        // Arrange - Créer des impediments adjacents
        Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-01-04 11:00:00', // Exactement après
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act - Chercher un créneau de 30 minutes
        $slot = $this->scheduleService->findNextSlot(
            30,
            'consultation',
            false,
            Carbon::parse('2038-01-04 10:00:00')
        );

        // Assert - Devrait trouver après 12h
        $this->assertNotNull($slot);
        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 12:00:00')));
    }

    public function test_schedule_metadata_serialization(): void
    {
        // Arrange
        $complexMetadata = [
            'client' => 'John Doe',
            'priority' => 'high',
            'tags' => ['urgent', 'follow-up'],
            'notes' => ['Bring documents'],
        ];

        $data = [
            'title' => 'Réunion complexe',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'metadata' => $complexMetadata,
        ];

        // Act
        $schedule = $this->scheduleService->create($this->testAvailability, $data);

        // Assert
        $this->assertEquals($complexMetadata, $schedule->metadata);
        $this->assertEquals('John Doe', $schedule->metadata['client']);
        $this->assertEquals(['urgent', 'follow-up'], $schedule->metadata['tags']);
    }

    public function test_schedule_duration_calculation(): void
    {
        // Arrange
        $data = [
            'title' => 'Test duration',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:30:00', // 1.5 heures
        ];

        // Act
        $schedule = $this->scheduleService->create($this->testAvailability, $data);

        // Assert
        $this->assertEquals(90, $schedule->duration_minutes); // 1.5 * 60 = 90 minutes
    }

    public function test_schedule_status_validation(): void
    {
        // Arrange - Statut invalide
        $data = [
            'title' => 'Statut invalide',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => 'invalid-status', // Statut invalide
        ];

        // Act
        $schedule = $this->scheduleService->create($this->testAvailability, $data);

        // Assert - Devrait utiliser le statut par défaut
        $this->assertEquals(ScheduleStatus::AVAILABLE->value, $schedule->status->value);
    }

    /* -----------------------------------------------------------------
     | Tests d'Intégration
     | -----------------------------------------------------------------
     */

    public function test_full_booking_scenario(): void
    {
        // Scénario complet: recherche → vérification → création

        // 1. Chercher un créneau
        $slot = $this->scheduleService->findNextSlot(
            60,
            'consultation',
            false,
            Carbon::parse('2038-01-04 09:00:00')
        );

        $this->assertNotNull($slot);

        // 2. Vérifier qu'il est disponible
        $isAvailable = $this->scheduleService->isTimeSlotAvailable(
            $slot['start'],
            $slot['end'],
            'consultation'
        );

        $this->assertTrue($isAvailable);

        // 3. Créer le schedule
        $schedule = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Rendez-vous confirmé',
            'start_datetime' => $slot['start'],
            'end_datetime' => $slot['end'],
            'status' => 'booked',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);

        // 4. Vérifier qu'on ne peut pas recréer le même créneau
        $this->expectException(ValidationFailedException::class);

        $this->scheduleService->create($this->testAvailability, [
            'title' => 'Double booking',
            'start_datetime' => $slot['start'],
            'end_datetime' => $slot['end'],
            'status' => 'booked',
        ]);
    }

    public function test_reschedule_scenario(): void
    {
        // Scénario: création → mise à jour → vérification

        // 1. Créer un schedule
        $schedule = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Original',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => 'booked',
        ]);

        // 2. Créer un autre schedule
        $otherSchedule = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Autre',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
            'status' => 'booked',
        ]);

        // 3. Essayer de déplacer le premier pour chevaucher le deuxième
        $this->expectException(ValidationFailedException::class);

        $this->scheduleService->update($schedule->id, [
            'start_datetime' => '2038-01-04 14:30:00', // Chevauche l'autre
            'end_datetime' => '2038-01-04 15:30:00',
        ]);

        // 4. Vérifier que l'original n'a pas changé
        $schedule->refresh();
        $this->assertEquals('2038-01-04 10:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_complex_availability_scenario(): void
    {
        // Scénario avec multiples disponibilités et types

        // Créer une deuxième disponibilité (uniquement l'après-midi)
        $afternoonAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'afternoon',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // 1. Créer un schedule le matin (première disponibilité)
        $morningSchedule = $this->scheduleService->create($this->testAvailability, [
            'title' => 'Matin',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => 'booked',
        ]);

        // 2. Créer un schedule l'après-midi (deuxième disponibilité)
        $afternoonSchedule = $this->scheduleService->create($afternoonAvailability, [
            'title' => 'Après-midi',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
            'status' => 'booked',
        ]);

        // 3. Vérifier que les deux existent
        $schedules = $this->scheduleService->get();
        $this->assertCount(2, $schedules);

        // 4. Chercher un créneau l'après-midi avec type 'consultation'
        $slot = $this->scheduleService->findNextSlot(
            60,
            'consultation',
            false,
            Carbon::parse('2038-01-04 13:00:00')
        );

        // Devrait trouver un créneau (l'après-midi est de type 'afternoon', pas 'consultation')
        $this->assertNotNull($slot);
    }
}
