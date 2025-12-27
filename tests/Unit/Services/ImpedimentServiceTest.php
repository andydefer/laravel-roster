<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Roster\Facades\Availability;
use Roster\Facades\Impediment;
use Roster\Facades\Schedule;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

final class ImpedimentServiceTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $testSchedulable;

    private AvailabilityModel $availabilityModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();

        // Configurer pour les tests
        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);

        // Créer la disponibilité UNIQUEMENT via la facade
        $this->availabilityModel = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     | Tests CRUD
     | -----------------------------------------------------------------
     */

    public function test_create_impediment_successfully(): void
    {
        // Act
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Maintenance système',
                'start_datetime' => '2038-01-04 10:00:00', // Lundi
                'end_datetime' => '2038-01-04 12:00:00',
                'metadata' => ['priority' => 'high'],
            ]);

        // Assert
        $this->assertInstanceOf(ImpedimentModel::class, $impediment);
        $this->assertEquals('Maintenance système', $impediment->reason);
        $this->assertEquals($this->availabilityModel->id, $impediment->availability_id);
        $this->assertEquals($this->testSchedulable->id, $impediment->schedulable_id);
        $this->assertEquals(['priority' => 'high'], $impediment->metadata);
    }

    public function test_create_impediment_without_metadata(): void
    {
        // Act
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Formation',
                'start_datetime' => '2038-01-05 14:00:00', // Mardi
                'end_datetime' => '2038-01-05 16:00:00',
            ]);

        // Assert
        $this->assertEquals('Formation', $impediment->reason);
        $this->assertEmpty($impediment->metadata);
    }

    public function test_create_impediment_fails_when_end_before_start(): void
    {
        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test invalide',
                'start_datetime' => '2038-01-04 12:00:00',
                'end_datetime' => '2038-01-04 10:00:00', // Avant le début
            ]);
    }

    public function test_create_impediment_fails_when_too_short(): void
    {
        // Configurer durée minimale pour le test
        Config::set('roster.durations.minimum_impediment_minutes', 15);

        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act - 5 minutes seulement
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test trop court',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 10:05:00', // 5 minutes
            ]);
    }

    public function test_update_impediment_successfully(): void
    {
        // Arrange
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Raison originale',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
                'metadata' => ['original' => true],
            ]);

        // Act
        $result = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->update($impediment->id, [
                'reason' => 'Nouvelle raison',
                'metadata' => ['updated' => true],
            ]);

        // Assert
        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertEquals('Nouvelle raison', $impediment->reason);
        $this->assertEquals(['updated' => true], $impediment->metadata);
    }

    public function test_update_impediment_with_datetime_changes(): void
    {
        // Arrange
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Original',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act
        $result = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->update($impediment->id, [
                'start_datetime' => '2038-01-04 13:00:00',
                'end_datetime' => '2038-01-04 15:00:00',
            ]);

        // Assert
        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $impediment->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 15:00:00'), $impediment->end_datetime);
    }

    public function test_update_impediment_throws_exception_when_not_found(): void
    {
        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Impediment with given ID does not exist/');

        // Act
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->update(999999, ['reason' => 'test']);
    }

    public function test_delete_impediment_successfully(): void
    {
        // Arrange
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'À supprimer',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act
        $result = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->delete($impediment->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(ImpedimentModel::find($impediment->id));
    }

    public function test_delete_impediment_throws_exception_when_not_found(): void
    {
        // Expect
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Impediment with given ID does not exist/');

        // Act
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->delete(999999);
    }

    /* -----------------------------------------------------------------
     | Tests de Recherche
     | -----------------------------------------------------------------
     */

    public function test_find_impediment_by_id(): void
    {
        // Arrange
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test find',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act
        $found = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->find($impediment->id);

        // Assert
        $this->assertInstanceOf(ImpedimentModel::class, $found);
        $this->assertEquals($impediment->id, $found->id);
    }

    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange
        $otherSchedulable = TestSchedulable::create();

        $otherAvailability = Availability::for($otherSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $impediment = Impediment::for($otherSchedulable)
            ->owner($otherAvailability)
            ->create([
                'reason' => 'Pour autre schedulable',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Essayer de trouver avec le mauvais schedulable
        $found = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->find($impediment->id);

        // Assert - Ne devrait pas trouver
        $this->assertNotInstanceOf(\Roster\Models\Impediment::class, $found);
    }

    public function test_get_all_impediments(): void
    {
        // Arrange
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Impediment 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Impediment 2',
                'start_datetime' => '2038-01-05 14:00:00',
                'end_datetime' => '2038-01-05 16:00:00',
            ]);

        // Act
        $result = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->all();

        // Assert
        $this->assertCount(2, $result);
        $reasons = $result->pluck('reason')->toArray();
        $this->assertContains('Impediment 1', $reasons);
        $this->assertContains('Impediment 2', $reasons);
    }

    public function test_get_with_filters(): void
    {
        // Arrange
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Janvier',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Février',
                'start_datetime' => '2038-02-04 10:00:00',
                'end_datetime' => '2038-02-04 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Janvier tardif',
                'start_datetime' => '2038-01-25 10:00:00',
                'end_datetime' => '2038-01-25 12:00:00',
            ]);

        // Act - Filtrer pour janvier seulement
        $result = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->setFilter('start_datetime', '2038-01-01')
            ->setFilter('end_datetime', '2038-01-31')
            ->all();
        // Assert
        $this->assertCount(2, $result);
        $reasons = $result->pluck('reason')->toArray();
        $this->assertContains('Janvier', $reasons);
        $this->assertContains('Janvier tardif', $reasons);
        $this->assertNotContains('Février', $reasons);
    }

    public function test_get_with_reason_filter(): void
    {
        // Arrange
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Maintenance système',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Formation sécurité',
                'start_datetime' => '2038-01-05 10:00:00',
                'end_datetime' => '2038-01-05 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Maintenance réseau',
                'start_datetime' => '2038-01-06 10:00:00',
                'end_datetime' => '2038-01-06 12:00:00',
            ]);

        // Act - Filtrer par raison contenant "Maintenance"
        $result = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->setFilter('reason', 'Maintenance')
            ->all();

        // Assert
        $this->assertCount(2, $result);
        $reasons = $result->pluck('reason')->toArray();
        $this->assertContains('Maintenance système', $reasons);
        $this->assertContains('Maintenance réseau', $reasons);
        $this->assertNotContains('Formation sécurité', $reasons);
    }

    /* -----------------------------------------------------------------
     | Tests de Vérification de Chevauchement
     | -----------------------------------------------------------------
     */

    public function test_would_overlap_with_schedule_returns_true(): void
    {
        // Arrange - Créer un schedule
        Schedule::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'title' => 'Réunion existante',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        // Act - Vérifier chevauchement
        $wouldOverlap = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->wouldOverlapWithSchedule(
                $this->availabilityModel->id,
                Carbon::parse('2038-01-04 10:00:00'),
                Carbon::parse('2038-01-04 12:00:00')
            );

        // Assert
        $this->assertTrue($wouldOverlap);
    }

    public function test_would_overlap_with_schedule_returns_false(): void
    {
        // Arrange - Créer un schedule
        Schedule::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'title' => 'Réunion existante',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        // Act - Vérifier pas de chevauchement
        $wouldOverlap = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->wouldOverlapWithSchedule(
                $this->availabilityModel->id,
                Carbon::parse('2038-01-04 14:00:00'),
                Carbon::parse('2038-01-04 15:00:00')
            );

        // Assert
        $this->assertFalse($wouldOverlap);
    }

    public function test_would_overlap_with_schedule_excluding_current_impediment(): void
    {
        // Arrange - Créer un impediment existant
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Existant',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        // Act - Vérifier avec exclusion
        $wouldOverlap = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->wouldOverlapWithSchedule(
                $this->availabilityModel->id,
                Carbon::parse('2038-01-04 12:00:00'), // Chevauche l'impediment existant
                Carbon::parse('2038-01-04 14:00:00'),
                $impediment->id // Exclure cet impediment
            );

        // Assert - Pas de chevauchement avec schedule
        $this->assertFalse($wouldOverlap);
    }

    public function test_would_overlap_with_other_impediment_returns_true(): void
    {
        // Arrange - Créer un impediment existant
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Impediment existant',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        // Act - Vérifier chevauchement
        $wouldOverlap = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->wouldOverlapWithOtherImpediment(
                $this->availabilityModel->id,
                Carbon::parse('2038-01-04 10:00:00'),
                Carbon::parse('2038-01-04 12:00:00')
            );

        // Assert
        $this->assertTrue($wouldOverlap);
    }

    public function test_would_overlap_with_other_impediment_returns_false(): void
    {
        // Arrange - Créer un impediment existant
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Impediment existant',
                'start_datetime' => '2038-01-04 11:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        // Act - Vérifier pas de chevauchement
        $wouldOverlap = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->wouldOverlapWithOtherImpediment(
                $this->availabilityModel->id,
                Carbon::parse('2038-01-04 14:00:00'),
                Carbon::parse('2038-01-04 15:00:00')
            );

        // Assert
        $this->assertFalse($wouldOverlap);
    }

    /* -----------------------------------------------------------------
     | Tests de Vérification de Disponibilité
     | -----------------------------------------------------------------
     */

    public function test_is_time_slot_blocked_returns_true(): void
    {
        // Arrange - Créer un impediment
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test block',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Vérifier créneau chevauchant
        $isBlocked = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->isTimeSlotBlocked(
                Carbon::parse('2038-01-04 11:00:00'),
                Carbon::parse('2038-01-04 13:00:00')
            );

        // Assert
        $this->assertTrue($isBlocked);
    }

    public function test_is_time_slot_blocked_returns_false(): void
    {
        // Arrange - Créer un impediment
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test block',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Vérifier créneau non chevauchant
        $isBlocked = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->isTimeSlotBlocked(
                Carbon::parse('2038-01-04 14:00:00'),
                Carbon::parse('2038-01-04 15:00:00')
            );

        // Assert
        $this->assertFalse($isBlocked);
    }

    public function test_is_time_slot_blocked_with_type_filter(): void
    {
        $otherUser = TestSchedulable::create();

        // Arrange - Créer une deuxième disponibilité avec type différent
        $otherAvailability = Availability::for($this->testSchedulable)->create([
            'type' => 'emergency',
            'daily_start' => '18:00:00',
            'daily_end' => '21:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);


        // Créer un impediment sur l'autre disponibilité
        Impediment::for($this->testSchedulable)
            ->owner($otherAvailability)
            ->create([
                'reason' => 'Emergency block',
                'start_datetime' => '2038-01-04 19:00:00',
                'end_datetime' => '2038-01-04 21:00:00',
            ]);

        // Act - Vérifier avec type 'consultation' (différent de 'emergency')
        $isBlocked = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->isTimeSlotBlocked(
                Carbon::parse('2038-01-04 20:00:00'),
                Carbon::parse('2038-01-04 20:30:00'),
                'consultation' // Type différent
            );

        // Assert - Ne devrait pas être bloqué car impediment est sur un type différent
        $this->assertFalse($isBlocked);
    }

    public function test_get_available_time_slots(): void
    {
        // Arrange - Créer un impediment
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Meeting',
                'start_datetime' => '2038-01-04 10:00:00', // Lundi
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act
        $slots = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->getAvailableTimeSlots(
                Carbon::parse('2038-01-04 09:00:00'),
                Carbon::parse('2038-01-04 17:00:00')
            );

        // Assert
        $this->assertInstanceOf(Collection::class, $slots);

        // Devrait avoir 2 créneaux: avant 10h et après 12h
        $this->assertCount(2, $slots);

        // Premier créneau: 9h-10h
        $this->assertEquals('2038-01-04 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-01-04 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));

        // Deuxième créneau: 12h-17h
        $this->assertEquals('2038-01-04 12:00:00', $slots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-01-04 17:00:00', $slots[1]['end']->format('Y-m-d H:i:s'));
    }

    public function test_get_available_time_slots_when_no_availability(): void
    {
        // Arrange - Bloquer toute la journée
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Full day meeting',
                'start_datetime' => '2038-01-04 09:00:00',
                'end_datetime' => '2038-01-04 17:00:00',
            ]);

        // Act
        $slots = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->getAvailableTimeSlots(
                Carbon::parse('2038-01-04 09:00:00'),
                Carbon::parse('2038-01-04 17:00:00')
            );

        // Assert
        $this->assertInstanceOf(Collection::class, $slots);
        $this->assertCount(0, $slots); // Aucun créneau disponible
    }

    /* -----------------------------------------------------------------
     | Tests d'Intégration
     | -----------------------------------------------------------------
     */

    public function test_impediment_prevents_schedule_creation(): void
    {
        // Arrange - Créer un impediment
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Rendez-vous médical',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Expect - La création de schedule devrait échouer
        $this->expectException(ValidationFailedException::class);

        $this->expectExceptionMessageMatches(
            '/Schedule overlaps with existing impediment/'
        );

        // Act - Essayer de créer un schedule qui chevauche l'impediment
        Schedule::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'title' => 'Nouveau rendez-vous',
                'start_datetime' => '2038-01-04 11:00:00', // Chevauche l'impediment
                'end_datetime' => '2038-01-04 13:00:00',
                'status' => ScheduleStatus::BOOKED->value,
            ]);
    }

    public function test_multiple_impediments_on_same_day(): void
    {
        // Arrange - Créer plusieurs impediments
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Réunion matin',
                'start_datetime' => '2038-01-04 09:00:00',
                'end_datetime' => '2038-01-04 10:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Pause déjeuner',
                'start_datetime' => '2038-01-04 12:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Formation',
                'start_datetime' => '2038-01-04 15:00:00',
                'end_datetime' => '2038-01-04 16:00:00',
            ]);

        // Act
        $allImpediments = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->all();

        // Assert
        $this->assertCount(3, $allImpediments);
        $reasons = $allImpediments->pluck('reason')->toArray();
        $this->assertContains('Réunion matin', $reasons);
        $this->assertContains('Pause déjeuner', $reasons);
        $this->assertContains('Formation', $reasons);
    }

    public function test_impediment_metadata_serialization(): void
    {
        // Arrange
        $complexMetadata = [
            'category' => 'maintenance',
            'priority' => 'high',
            'teams' => ['IT', 'Support'],
            'notes' => 'Système critique',
        ];

        // Act
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Maintenance complexe',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
                'metadata' => $complexMetadata,
            ]);

        // Assert
        $this->assertEquals('maintenance', $impediment->metadata['category']);
        $this->assertEquals('high', $impediment->metadata['priority']);
        $this->assertEquals(['IT', 'Support'], $impediment->metadata['teams']);
        $this->assertEquals('Système critique', $impediment->metadata['notes']);
    }

    public function test_impediment_duration_calculation(): void
    {
        // Arrange & Act
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test duration',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:30:00', // 2.5 heures = 150 minutes
            ]);

        // Assert
        $this->assertEquals(150, $impediment->duration_minutes);
    }

    /* -----------------------------------------------------------------
     | Tests de Cas Limites
     | -----------------------------------------------------------------
     */

    public function test_impediment_exact_time_boundary(): void
    {
        // Arrange
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Exact boundary',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act & Assert
        // Chevauchement à l'intérieur
        $this->assertTrue($impediment->overlapsWith(
            Carbon::parse('2038-01-04 10:00:00'),
            Carbon::parse('2038-01-04 11:00:00')
        ));

        // Chevauchement à la limite (début exact)
        $this->assertTrue($impediment->overlapsWith(
            Carbon::parse('2038-01-04 09:00:00'),
            Carbon::parse('2038-01-04 10:30:00')
        ));

        // Pas de chevauchement (commence exactement à la fin)
        $this->assertFalse($impediment->overlapsWith(
            Carbon::parse('2038-01-04 12:00:00'), // Exactement à la fin
            Carbon::parse('2038-01-04 13:00:00')
        ));
    }

    public function test_impediment_active_status_live(): void
    {
        $now = now();

        // Skip si on est trop proche de minuit (après 23h40)
        if ($now->format('H') == 23 && (int)$now->format('i') >= 40) {
            $this->markTestSkipped('Impossible de tester un créneau live qui traverse minuit.');
        }

        // Arrange - Availability live
        $dailyStart = $now->copy();
        $dailyEnd   = $now->copy()->addMinutes(20);
        $validityEnd = $now->copy()->addHour();

        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'instant-test',
            'daily_start' => $dailyStart->format('H:i:s'),
            'daily_end' => $dailyEnd->format('H:i:s'),
            'days' => [strtolower($now->englishDayOfWeek)],
            'validity_start' => $now->copy(),
            'validity_end' => $validityEnd,
        ]);

        // Impediment dans la fenêtre valide
        $start = $now->copy()->addSecond();
        $end   = $now->copy()->addMinutes(10);

        $impediment = Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Live active test',
                'start_datetime' => $start,
                'end_datetime' => $end,
            ]);

        sleep(2);

        // Assert
        $this->assertTrue($impediment->isActive());
        $this->assertFalse($impediment->isPast());
        $this->assertFalse($impediment->isUpcoming());
    }


    public function test_concurrent_impediment_creation_prevents_overlap(): void
    {
        // Arrange - Créer un premier impediment
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Premier impediment',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Expect - La création du deuxième devrait échouer
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/overlaps with existing impediment/'
        );


        // Act - Essayer de créer un deuxième qui chevauche
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Deuxième impediment',
                'start_datetime' => '2038-01-04 11:00:00', // Chevauche
                'end_datetime' => '2038-01-04 13:00:00',
            ]);
    }

    public function test_impediment_on_non_availability_day(): void
    {
        // Arrange - Disponibilité seulement le lundi
        $mondayOnlyAvailability = Availability::for($this->testSchedulable)->create([
            'type' => 'monday-only',
            'daily_start' => '20:00:00',
            'daily_end' => '22:00:00',
            'days' => ['monday'], // Seulement lundi
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Expect - Essayer de créer un impediment le mardi devrait échouer
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/failed for Impediment.*not allowed.*only permits/i'
        );



        // Act
        Impediment::for($this->testSchedulable)
            ->owner($mondayOnlyAvailability)
            ->create([
                'reason' => 'Impediment mardi',
                'start_datetime' => '2038-01-05 21:00:00', // Mardi
                'end_datetime' => '2038-01-07 21:30:00',
            ]);
    }


    public function test_impediment_outside_availability_hours(): void
    {
        // Arrange - Disponibilité 9h-17h
        $limitedAvailability = Availability::for($this->testSchedulable)->create([
            'type' => 'limited',
            'daily_start' => '20:00:00',
            'daily_end' => '23:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Expect - Essayer de créer un impediment avant 9h devrait échouer
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/selected start time .* is before the availability start time/'
        );


        // Act
        Impediment::for($this->testSchedulable)
            ->owner($limitedAvailability)
            ->create([
                'reason' => 'Impediment trop tôt',
                'start_datetime' => '2038-01-04 16:00:00', // Avant 9h
                'end_datetime' => '2038-01-04 19:00:00',
            ]);
    }

    public function test_impediment_exact_boundary_not_overlap(): void
    {
        // Arrange
        $impediment1 = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Premier impediment',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        // Act - Créer un deuxième qui commence exactement à la fin du premier
        $impediment2 = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Deuxième impediment',
                'start_datetime' => '2038-01-04 11:00:00', // Exactement à la fin du premier
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Assert - Devrait réussir (pas de chevauchement)
        $this->assertInstanceOf(ImpedimentModel::class, $impediment2);
        $this->assertEquals('2038-01-04 11:00:00', $impediment2->start_datetime->format('Y-m-d H:i:s'));

        // Vérifier qu'ils ne se chevauchent pas
        $this->assertFalse($impediment1->overlapsWith(
            $impediment2->start_datetime,
            $impediment2->end_datetime
        ));
    }

    public function test_find_available_slots_with_adjacent_impediments(): void
    {
        // Arrange - Créer des impediments adjacents
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Impediment 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 11:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Impediment 2',
                'start_datetime' => '2038-01-04 11:00:00', // Exactement à la fin du premier
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act
        $slots = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->getAvailableTimeSlots(
                Carbon::parse('2038-01-04 09:00:00'),
                Carbon::parse('2038-01-04 17:00:00')
            );

        // Assert - Devrait avoir 2 créneaux: avant 10h et après 12h
        $this->assertCount(2, $slots);

        // Premier créneau: 9h-10h
        $this->assertEquals('2038-01-04 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-01-04 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));

        // Deuxième créneau: 12h-17h
        $this->assertEquals('2038-01-04 12:00:00', $slots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-01-04 17:00:00', $slots[1]['end']->format('Y-m-d H:i:s'));
    }

    public function test_complete_blocking_scenario(): void
    {
        // Arrange - Créer plusieurs impediments qui couvrent toute la journée
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Réunion matin',
                'start_datetime' => '2038-01-04 09:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Pause déjeuner',
                'start_datetime' => '2038-01-04 12:00:00',
                'end_datetime' => '2038-01-04 13:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Réunion après-midi',
                'start_datetime' => '2038-01-04 13:00:00',
                'end_datetime' => '2038-01-04 17:00:00',
            ]);

        // Act - Vérifier qu'aucun créneau n'est disponible
        $isBlocked = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->isTimeSlotBlocked(
                Carbon::parse('2038-01-04 10:00:00'),
                Carbon::parse('2038-01-04 11:00:00')
            );

        // Assert
        $this->assertTrue($isBlocked);

        // Act - Chercher créneaux disponibles
        $slots = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->getAvailableTimeSlots(
                Carbon::parse('2038-01-04 09:00:00'),
                Carbon::parse('2038-01-04 17:00:00')
            );

        // Assert - Aucun créneau disponible
        $this->assertCount(0, $slots);
    }

    public function test_impediment_with_json_string_metadata(): void
    {
        // Arrange - JSON string comme metadata
        $jsonMetadata = json_encode([
            'client' => 'ABC Corp',
            'priority' => 'urgent',
            'notify' => true
        ]);

        // Act
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Maintenance urgente',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
                'metadata' => $jsonMetadata, // JSON string
            ]);

        // Assert - Doit être automatiquement décodé en tableau
        $this->assertIsArray($impediment->metadata);
        $this->assertEquals('ABC Corp', $impediment->metadata['client']);
        $this->assertEquals('urgent', $impediment->metadata['priority']);
        $this->assertTrue($impediment->metadata['notify']);
    }

    public function test_paginate_impediments(): void
    {
        // Arrange - Créer 25 impediments sur des jours permis (lundi à vendredi)
        $startDate = Carbon::parse('2038-01-04'); // lundi
        for ($i = 0; $i < 25; ++$i) {
            // Calcul du jour suivant valide
            $date = $startDate->copy()->addWeeks(intdiv($i, 5))->addDays($i % 5);

            Impediment::for($this->testSchedulable)
                ->owner($this->availabilityModel)
                ->create([
                    'reason' => "Impediment " . ($i + 1),
                    'start_datetime' => $date->setTime(10, 0, 0)->toDateTimeString(),
                    'end_datetime' => $date->setTime(12, 0, 0)->toDateTimeString(),
                ]);
        }

        // Act - Paginer
        $lengthAwarePaginator = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->paginate(10);


        // Assert
        $this->assertEquals(25, $lengthAwarePaginator->total());
        $this->assertEquals(10, $lengthAwarePaginator->perPage());
        $this->assertEquals(3, $lengthAwarePaginator->lastPage());
        $this->assertCount(10, $lengthAwarePaginator->items());
    }



    /* -----------------------------------------------------------------
     | Tests de Réinitialisation
     | -----------------------------------------------------------------
     */

    public function test_reset_filters(): void
    {
        // Arrange
        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test 2',
                'start_datetime' => '2038-01-05 10:00:00',
                'end_datetime' => '2038-01-05 12:00:00',
            ]);

        // Act - Appliquer un filtre, puis réinitialiser
        $filtered = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->setFilter('start_date', '2038-01-05')
            ->resetFilters()
            ->all();




        // Assert
        $this->assertCount(2, $filtered); // Nous avons toujours 2 items car les filtres ont été réinitialisés

    }

    public function test_clear_all_data(): void
    {
        // Arrange
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel)
            ->create([
                'reason' => 'Test clear',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ]);

        // Act - Utiliser clear() sur le service
        $impedimentService = Impediment::for($this->testSchedulable)
            ->owner($this->availabilityModel);

        $impedimentService->clear();

        // Assert - Le service devrait être vide mais l'impediment existe toujours
        $this->assertEmpty($impedimentService->getFilters());
        $this->assertEmpty($impedimentService->getData());

        // L'impediment devrait toujours exister en base
        $this->assertNotNull(ImpedimentModel::find($impediment->id));
    }
}
