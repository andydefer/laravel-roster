<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Roster\Facades\Availability;
use Roster\Facades\Impediment;
use Roster\Facades\Schedule;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

final class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();

        // Configurer pour les tests
        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);
    }

    /* -----------------------------------------------------------------
     | Tests CRUD Basiques avec respect des principes
     | -----------------------------------------------------------------
     */

    public function test_create_schedule_successfully(): void
    {
        // Arrange - Créer une disponibilité UNIQUE pour ce test
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Réunion de test',
            'description' => 'Description de test',
            'start_datetime' => '2038-01-04 10:00:00', // Lundi
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED->value,
            'metadata' => ['priority' => 'high'],
        ];

        // Act
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create($scheduleData);

        // Assert
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertNotNull($schedule->id);
        $this->assertEquals($availability->id, $schedule->availability_id);
        $this->assertEquals('Réunion de test', $schedule->title);
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $schedule->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 11:00:00'), $schedule->end_datetime);
        $this->assertEquals(ScheduleStatus::BOOKED, $schedule->status);
        $this->assertEquals(['priority' => 'high'], $schedule->metadata);
    }

    public function test_create_schedule_with_default_status(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Réunion sans statut',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ];

        // Act
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create($scheduleData);

        // Assert - Doit utiliser le statut par défaut
        $this->assertEquals(ScheduleStatus::AVAILABLE->value, $schedule->status->value);
    }

    public function test_create_schedule_fails_when_end_before_start(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Réunion invalide',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 11:00:00', // Avant le début
        ];

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create($scheduleData);
    }

    public function test_create_schedule_fails_when_too_short(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Réunion trop courte',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:05:00', // 5 minutes seulement
        ];

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create($scheduleData);
    }

    public function test_create_schedule_fails_when_no_availability(): void
    {
        // Arrange - Créer deux schedulables différents
        $schedulable1 = TestSchedulable::create();
        $schedulable2 = TestSchedulable::create();

        $availabilityForSchedulable1 = Availability::for($schedulable1)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        $scheduleData = [
            'title' => 'Réunion avec mauvaise disponibilité',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ];

        // Expect - Doit échouer car l'availability n'appartient pas au bon schedulable
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Create validation failed for Schedule: availability_id → Invalid availability ID/'
        );


        // Act - Utiliser l'availability de schedulable1 avec schedulable2
        Schedule::for($schedulable2)
            ->owner($availabilityForSchedulable1)
            ->create($scheduleData);
    }

    public function test_update_schedule_successfully(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion originale',
                'description' => 'Description originale',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
                'metadata' => ['original' => true],
            ]);

        $updateData = [
            'title' => 'Titre modifié',
            'description' => 'Description modifiée',
            'metadata' => ['updated' => true],
        ];

        // Act
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->update($schedule->id, $updateData);

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
        // Arrange - Créer une disponibilité assez large
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion originale',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act - Déplacer dans la même journée, même disponibilité
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->update($schedule->id, [
                'start_datetime' => '2038-01-04 13:00:00',
                'end_datetime' => '2038-01-04 14:00:00',
            ]);

        // Assert
        $this->assertTrue($result);
        $schedule->refresh();
        $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $schedule->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 14:00:00'), $schedule->end_datetime);
    }

    public function test_update_schedule_fails_when_overlap(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer deux schedules différents
        $schedule1 = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion 2',
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
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing schedule/');

        // Act
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->update($schedule1->id, $updateData);
    }

    public function test_create_schedule_fails_when_overlap(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Schedule existant
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Schedule existant',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Données du schedule qui va chevaucher
        $overlappingData = [
            'title' => 'Schedule chevauchant',
            'start_datetime' => '2038-01-04 10:30:00', // overlap
            'end_datetime' => '2038-01-04 11:30:00',
        ];

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/overlaps with existing schedule/'
        );

        // Act - Tentative de création
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create($overlappingData);
    }


    public function test_update_schedule_fails_when_not_found(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule with given ID does not exist/');

        // Act
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->update(999999, ['title' => 'test']);
    }

    public function test_delete_schedule_successfully(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion à supprimer',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->delete($schedule->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(ScheduleModel::find($schedule->id));
    }

    public function test_delete_schedule_fails_when_not_found(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule with given ID does not exist/');

        // Act
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->delete(999999);
    }

    public function test_find_schedule_by_id(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion à trouver',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act
        $found = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->find($schedule->id);

        // Assert
        $this->assertInstanceOf(ScheduleModel::class, $found);
        $this->assertEquals($schedule->id, $found->id);
        $this->assertEquals('Réunion à trouver', $found->title);
    }

    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange - Créer deux schedulables
        $schedulable1 = TestSchedulable::create();
        $schedulable2 = TestSchedulable::create();

        $availability1 = Availability::for($schedulable1)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $availability2 = Availability::for($schedulable2)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule pour schedulable2
        $scheduleForSchedulable2 = Schedule::for($schedulable2)
            ->owner($availability2)
            ->create([
                'title' => 'Pour autre schedulable',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act - Essayer de récupérer ce schedule avec le service pour schedulable1
        $found = Schedule::for($schedulable1)
            ->owner($availability1)
            ->find($scheduleForSchedulable2->id);

        // Assert - Ne devrait pas trouver car ce schedule n'appartient pas au bon schedulable
        $this->assertNotInstanceOf(\Roster\Models\Schedule::class, $found);
    }

    public function test_all_schedules(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion 2',
                'start_datetime' => '2038-01-05 14:00:00',
                'end_datetime' => '2038-01-05 15:00:00',
            ]);

        // Act
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->all();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('Réunion 1', $titles);
        $this->assertContains('Réunion 2', $titles);
    }

    public function test_get_schedules_with_filters(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'], // seuls jours autorisés
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Crée uniquement des schedules sur monday et tuesday
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Disponible',
                'start_datetime' => '2038-01-04 10:00:00', // lundi
                'end_datetime' => '2038-01-04 11:00:00',
                'status' => ScheduleStatus::AVAILABLE->value,
            ]);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réservé',
                'start_datetime' => '2038-01-05 10:00:00', // mardi
                'end_datetime' => '2038-01-05 11:00:00',
                'status' => ScheduleStatus::BOOKED->value,
            ]);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Disponible 2',
                'start_datetime' => '2038-01-11 10:00:00', // lundi suivant
                'end_datetime' => '2038-01-11 11:00:00',
                'status' => ScheduleStatus::AVAILABLE->value,
            ]);

        // Act - Filtrer par statut ScheduleStatus::AVAILABLE
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->setFilter('status', ScheduleStatus::AVAILABLE->value)
            ->all();

        // Assert
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('Disponible', $titles);
        $this->assertContains('Disponible 2', $titles);
        $this->assertNotContains('Réservé', $titles);
    }


    public function test_get_schedules_with_datetime_range_filter(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Crée uniquement des schedules dans la période valide et sur des jours permis
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Janvier',
                'start_datetime' => '2038-01-04 10:00:00', // lundi
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Janvier tardif',
                'start_datetime' => '2038-01-25 10:00:00', // lundi
                'end_datetime' => '2038-01-25 11:00:00',
            ]);

        // Act - Filtrer pour janvier seulement avec datetime complet
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-01-31 23:59:59')
            ->all();

        // Assert
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('Janvier', $titles);
        $this->assertContains('Janvier tardif', $titles);
    }

    /* -----------------------------------------------------------------
     | Tests de Recherche de Créneaux
     | -----------------------------------------------------------------
     */

    public function test_find_next_slot_without_conflicts(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule pour bloquer 10h-11h
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion bloquante',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act - Chercher un créneau de 60 minutes
        $slot = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findNextSlot(
                120, // 1 heure
                'consultation',
                false,
                Carbon::parse('2038-01-04 09:00:00') // Chercher à partir de 9h
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
        $this->assertEquals(120, $slot['duration_minutes']);
        $this->assertEquals($availability->id, $slot['availability']->id);
    }

    public function test_find_next_slot_return_start_only(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $startOnly = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findNextSlot(
                30,
                'consultation',
                true, // returnStartOnly = true
                Carbon::parse('2038-01-04 09:00:00')
            );

        // Assert
        $this->assertNotNull($startOnly);
        $this->assertInstanceOf(Carbon::class, $startOnly);
        $this->assertSame('2038-01-04 09:00:00', $startOnly->format('Y-m-d H:i:s'));
    }

    public function test_find_next_slot_respects_availability_hours(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act - Chercher un créneau qui doit respecter 9h-17h
        $slot = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findNextSlot(
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
        // Arrange - Créer une disponibilité uniquement pour lundi matin
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'limited',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00', // Seulement le matin
            'days' => ['monday'], // Seulement lundi
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-10',
        ]);

        // Act - Chercher un créneau le mardi (jour non disponible)
        $slot = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findNextSlot(
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
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isTimeSlotAvailable(
                Carbon::parse('2038-01-04 10:00:00'),
                Carbon::parse('2038-01-04 11:00:00'),
                'consultation'
            );

        // Assert
        $this->assertTrue($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_when_schedule_overlap(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule existant
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion existante',
                'start_datetime' => '2038-01-04 10:30:00',
                'end_datetime' => '2038-01-04 11:30:00',
            ]);

        // Act - Vérifier créneau chevauchant
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isTimeSlotAvailable(
                Carbon::parse('2038-01-04 11:00:00'), // Chevauche 11h-11h30
                Carbon::parse('2038-01-04 12:00:00'),
                'consultation'
            );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_when_impediment_overlap(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un impediment
        Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Maintenance',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Vérifier créneau chevauchant
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isTimeSlotAvailable(
                Carbon::parse('2038-01-04 11:00:00'), // Chevauche l'impediment
                Carbon::parse('2038-01-04 12:00:00'),
                'consultation'
            );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_returns_false_when_outside_availability(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act - Vérifier créneau en dehors des heures de disponibilité
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isTimeSlotAvailable(
                Carbon::parse('2038-01-04 18:00:00'), // Après 17h
                Carbon::parse('2038-01-04 19:00:00'),
                'consultation'
            );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_time_slot_available_with_type_filter(): void
    {
        // Arrange - Créer deux disponibilités de types différents
        $availabilityConsultation = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00', // Matin seulement
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $availabilityTraining = Availability::for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00', // Après-midi seulement
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule sur la disponibilité training
        Schedule::for($this->testSchedulable)
            ->owner($availabilityTraining)
            ->create([
                'title' => 'Training',
                'start_datetime' => '2038-01-04 14:00:00',
                'end_datetime' => '2038-01-04 15:00:00',
            ]);

        // Act - Vérifier avec type 'consultation' (matin, différent de training)
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availabilityConsultation)
            ->isTimeSlotAvailable(
                Carbon::parse('2038-01-04 10:00:00'), // Matin
                Carbon::parse('2038-01-04 11:00:00'),
                'consultation' // Type consultation
            );

        // Assert - Devrait être disponible car c'est un créneau du matin pour consultation
        // Le schedule training de l'après-midi ne devrait pas affecter
        $this->assertTrue($isAvailable);
    }

    public function test_find_available_slots_in_range(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule pour bloquer 10h-12h lundi
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion 10h-12h',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Chercher tous les créneaux de 60 minutes sur 2 jours
        $slots = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findAvailableSlots(
                Carbon::parse('2038-01-04'),
                Carbon::parse('2038-01-05'),
                60,
                'consultation'
            );

        // Assert
        $this->assertInstanceOf(Collection::class, $slots);

        // Vérifier qu'aucun créneau lundi ne chevauche 10h-12h
        foreach ($slots as $slot) {
            if ($slot['start']->format('Y-m-d') === '2038-01-04') {
                $slotStart = $slot['start'];
                $slotEnd = $slot['end'];
                $this->assertTrue(
                    $slotEnd->lte(Carbon::parse('2038-01-04 10:00:00')) ||
                        $slotStart->gte(Carbon::parse('2038-01-04 12:00:00')),
                    sprintf('Slot %s-%s should not overlap 10:00-12:00', $slotStart->format('H:i'), $slotEnd->format('H:i'))
                );
            }
        }
    }

    public function test_is_period_available_returns_true(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isPeriodAvailable(
                Carbon::parse('2038-01-04 10:00:00'),
                Carbon::parse('2038-01-04 12:00:00'),
                'consultation'
            );

        // Assert
        $this->assertTrue($isAvailable);
    }

    public function test_is_period_available_returns_false_when_schedule_conflict(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule au milieu de la période
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion au milieu',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Vérifier période plus large
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isPeriodAvailable(
                Carbon::parse('2038-01-04 10:00:00'), // 10h-13h
                Carbon::parse('2038-01-04 13:00:00'),
                'consultation'
            );

        // Assert - Devrait être false car chevauchement
        $this->assertFalse($isAvailable);
    }

    public function test_is_period_available_returns_false_when_impediment_conflict(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un impediment
        Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Maintenance',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isPeriodAvailable(
                Carbon::parse('2038-01-04 10:00:00'),
                Carbon::parse('2038-01-04 13:00:00'),
                'consultation'
            );

        // Assert
        $this->assertFalse($isAvailable);
    }

    public function test_is_period_available_returns_false_when_no_availability(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act - Vérifier période en dehors des heures
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isPeriodAvailable(
                Carbon::parse('2038-01-04 18:00:00'), // Après 17h
                Carbon::parse('2038-01-04 19:00:00'),
                'consultation'
            );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /* -----------------------------------------------------------------
     | Tests de Cas Limites
     | -----------------------------------------------------------------
     */

    public function test_concurrent_schedule_creation_prevents_double_booking(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Première réunion',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act & Assert - Essayer de créer un schedule chevauchant
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing schedule/');

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Deuxième réunion',
                'start_datetime' => '2038-01-04 10:30:00', // Chevauche
                'end_datetime' => '2038-01-04 11:30:00',
            ]);
    }

    public function test_schedule_on_non_availability_day(): void
    {
        // Arrange - Disponibilité seulement le lundi
        $mondayOnlyAvailability = Availability::for($this->testSchedulable)->create([
            'type' => 'monday-only',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'], // Seulement lundi
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act & Assert - Essayer de créer un schedule le mardi
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/The selected date 2038-01-05 \(tuesday\) is not allowed because this availability only permits the following days: monday/'
        );

        Schedule::for($this->testSchedulable)
            ->owner($mondayOnlyAvailability)
            ->create([
                'title' => 'Réunion mardi',
                'start_datetime' => '2038-01-05 10:00:00', // Mardi
                'end_datetime' => '2038-01-05 11:00:00',
            ]);
    }


    public function test_schedule_outside_availability_hours(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act & Assert - Essayer de créer un schedule avant 9h
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/The selected start time .* is before the availability start time .*/'
        );

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion trop tôt',
                'start_datetime' => '2038-01-04 08:00:00', // Avant 9h
                'end_datetime' => '2038-01-04 09:00:00',
            ]);
    }


    public function test_schedule_exact_boundary_not_overlap(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer un schedule
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Première réunion',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act - Créer un schedule qui commence exactement à la fin du premier
        $schedule2 = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Deuxième réunion',
                'start_datetime' => '2038-01-04 11:00:00', // Exactement à la fin du premier
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Assert - Devrait réussir (pas de chevauchement)
        $this->assertInstanceOf(ScheduleModel::class, $schedule2);
        $this->assertEquals('2038-01-04 11:00:00', $schedule2->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_find_next_slot_with_adjacent_impediments(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Créer des impediments adjacents
        Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Impediment 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Impediment 2',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Chercher un créneau de 30 minutes
        $slot = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findNextSlot(
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
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $complexMetadata = [
            'client' => 'John Doe',
            'priority' => 'high',
            'tags' => ['urgent', 'follow-up'],
            'notes' => ['Bring documents'],
        ];

        // Act
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion avec métadata',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
                'metadata' => $complexMetadata,
            ]);

        // Assert
        $this->assertEquals($complexMetadata, $schedule->metadata);
        $this->assertEquals('John Doe', $schedule->metadata['client']);
        $this->assertEquals(['urgent', 'follow-up'], $schedule->metadata['tags']);
    }

    public function test_schedule_duration_calculation(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion longue',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:30:00',
            ]);

        // Assert
        $this->assertEquals(90, $schedule->duration_minutes); // 1.5 * 60 = 90 minutes
    }

    /* -----------------------------------------------------------------
     | Tests d'Intégration
     | -----------------------------------------------------------------
     */

    public function test_full_booking_scenario(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // 1. Chercher un créneau
        $slot = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->findNextSlot(
                60,
                'consultation',
                false,
                Carbon::parse('2038-01-04 09:00:00')
            );

        $this->assertNotNull($slot);

        // 2. Vérifier qu'il est disponible
        $isAvailable = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->isTimeSlotAvailable(
                $slot['start'],
                $slot['end'],
                'consultation'
            );

        $this->assertTrue($isAvailable);

        // 3. Créer le schedule
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion créée',
                'start_datetime' => $slot['start']->format('Y-m-d H:i:s'),
                'end_datetime' => $slot['end']->format('Y-m-d H:i:s'),
            ]);

        $this->assertInstanceOf(ScheduleModel::class, $schedule);

        // 4. Vérifier qu'on ne peut pas recréer le même créneau
        $this->expectException(ValidationFailedException::class);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Deuxième réunion',
                'start_datetime' => $slot['start']->format('Y-m-d H:i:s'),
                'end_datetime' => $slot['end']->format('Y-m-d H:i:s'),
            ]);
    }

    public function test_reschedule_scenario(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // 1. Créer un schedule
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Réunion originale',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // 2. Créer un autre schedule
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Autre réunion',
                'start_datetime' => '2038-01-04 14:00:00',
                'end_datetime' => '2038-01-04 15:00:00',
            ]);

        // 3. Essayer de déplacer le premier pour chevaucher le deuxième
        $this->expectException(ValidationFailedException::class);

        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->update($schedule->id, [
                'start_datetime' => '2038-01-04 14:30:00', // Chevauche l'autre
                'end_datetime' => '2038-01-04 15:30:00',
            ]);

        // 4. Vérifier que l'original n'a pas changé
        $schedule->refresh();
        $this->assertEquals('2038-01-04 10:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_complex_availability_scenario(): void
    {
        // Arrange - Créer deux disponibilités qui NE SE CHEVAUCHENT PAS
        $morningAvailability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00', // Matin seulement
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $afternoonAvailability = Availability::for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00', // Après-midi seulement
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Note: Ces deux disponibilités ne se chevauchent pas (matin vs après-midi)

        // 1. Créer un schedule le matin
        Schedule::for($this->testSchedulable)
            ->owner($morningAvailability)
            ->create([
                'title' => 'Réunion matin',
                'start_datetime' => '2038-01-04 09:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // 2. Créer un schedule l'après-midi
        Schedule::for($this->testSchedulable)
            ->owner($afternoonAvailability)
            ->create([
                'title' => 'Réunion après-midi',
                'start_datetime' => '2038-01-04 14:00:00',
                'end_datetime' => '2038-01-04 15:00:00',
            ]);

        // 3. Vérifier que les deux existent
        $morningSchedules = Schedule::for($this->testSchedulable)
            ->owner($morningAvailability)
            ->all();

        $afternoonSchedules = Schedule::for($this->testSchedulable)
            ->owner($afternoonAvailability)
            ->all();

        $this->assertCount(1, $morningSchedules);
        $this->assertCount(1, $afternoonSchedules);

        // 4. Chercher un créneau le matin avec type 'consultation'
        $slot = Schedule::for($this->testSchedulable)
            ->owner($morningAvailability)
            ->findNextSlot(
                60,
                'consultation',
                false,
                Carbon::parse('2038-01-04 09:00:00')
            );

        // Devrait trouver un créneau (après 11h, le créneau 10h-11h est pris)
        $this->assertNotNull($slot);
        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 11:00:00')));
    }
}
