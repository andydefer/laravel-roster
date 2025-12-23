<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Roster\Contracts\Validation\ValidatorInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

final class ImpedimentServiceTest extends TestCase
{
    private ImpedimentService $impedimentService;

    private TestSchedulable $testSchedulable;

    private ScheduleService $scheduleService;

    private Availability $testAvailability;

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

        // Initialiser le service
        $this->impedimentService = App::make(ImpedimentService::class);
        $this->scheduleService = App::make(ScheduleService::class);
        $this->impedimentService->for($this->testSchedulable);
    }

    protected function tearDown(): void
    {
        // Nettoyer la base de données
        Impediment::query()->delete();
        Schedule::query()->delete();
        Availability::query()->delete();
        TestSchedulable::query()->delete();

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     | Tests CRUD
     | -----------------------------------------------------------------
     */

    public function test_create_impediment_successfully(): void
    {
        // Arrange
        $data = [
            'reason' => 'Maintenance système',
            'start_datetime' => '2038-01-04 10:00:00', // Lundi
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => ['priority' => 'high'],
        ];

        // Act
        $impediment = $this->impedimentService->create($this->testAvailability, $data);

        // Assert
        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertNotNull($impediment->id);
        $this->assertEquals($this->testAvailability->id, $impediment->availability_id);
        $this->assertEquals($this->testSchedulable->id, $impediment->schedulable_id);
        $this->assertEquals(TestSchedulable::class, $impediment->schedulable_type);
        $this->assertEquals('Maintenance système', $impediment->reason);
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $impediment->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 12:00:00'), $impediment->end_datetime);
        $this->assertEquals(['priority' => 'high'], $impediment->metadata);
    }

    public function test_create_impediment_without_metadata(): void
    {
        // Arrange
        $data = [
            'reason' => 'Formation',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 16:00:00',
        ];

        // Act
        $impediment = $this->impedimentService->create($this->testAvailability, $data);

        // Assert
        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertEquals('Formation', $impediment->reason);
        $this->assertEmpty($impediment->metadata);
    }

    public function test_create_impediment_fails_when_end_before_start(): void
    {
        // Arrange
        $data = [
            'reason' => 'Test invalide',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 10:00:00', // Fin avant début
        ];

        // Expect
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->impedimentService->create($this->testAvailability, $data);
    }

    public function test_update_impediment_successfully(): void
    {
        // Arrange - Créer un impediment
        $impediment = Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Ancienne raison',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => ['old' => 'data'],
        ]);

        $updateData = [
            'reason' => 'Nouvelle raison',
            'metadata' => ['updated' => true],
        ];

        // Act
        $result = $this->impedimentService->update($impediment->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertEquals('Nouvelle raison', $impediment->reason);
        $this->assertEquals(['updated' => true], $impediment->metadata);
        // Les dates ne devraient pas changer
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $impediment->start_datetime);
    }

    public function test_update_impediment_with_datetime_changes(): void
    {
        // Arrange
        $impediment = Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Original',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        $updateData = [
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ];

        // Act
        $result = $this->impedimentService->for($this->testSchedulable)->update($impediment->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $impediment->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 15:00:00'), $impediment->end_datetime);
    }
    public function test_update_impediment_throws_exception_when_not_found(): void
    {
        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Update validation failed for Impediment.*does not exist/'
        );

        // Act
        $this->impedimentService->update(999999, ['reason' => 'test']);
    }

    public function test_delete_impediment_successfully(): void
    {

        // Arrange
        $impediment = $this->impedimentService->for($this->testSchedulable)->create($this->testAvailability, [
            'reason' => 'À supprimer',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);


        // Act
        $result = $this->impedimentService->for($this->testSchedulable)->delete($impediment->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(Impediment::find($impediment->id));
    }

    public function test_delete_impediment_throws_exception_when_not_found(): void
    {
        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Delete validation failed for Impediment.*does not exist/'
        );

        // Act
        $this->impedimentService->delete(999999);
    }
    /* -----------------------------------------------------------------
     | Tests de Recherche
     | -----------------------------------------------------------------
     */

    public function test_find_impediment_by_id(): void
    {
        // Arrange
        $impediment = Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Test find',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $found = $this->impedimentService->find($impediment->id);

        // Assert
        $this->assertInstanceOf(Impediment::class, $found);
        $this->assertEquals($impediment->id, $found->id);
    }

    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange - Créer un autre schedulable
        $otherSchedulable = TestSchedulable::create();
        $otherAvailability = Availability::create([
            'schedulable_id' => $otherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $impediment = Impediment::create([
            'availability_id' => $otherAvailability->id,
            'schedulable_id' => $otherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Pour autre schedulable',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $found = $this->impedimentService->find($impediment->id);

        // Assert
        $this->assertNotInstanceOf(Impediment::class, $found);
    }

    public function test_get_all_impediments(): void
    {
        // Arrange - Créer plusieurs impediments
        $impediments = [
            [
                'reason' => 'Impediment 1',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ],
            [
                'reason' => 'Impediment 2',
                'start_datetime' => '2038-01-05 14:00:00',
                'end_datetime' => '2038-01-05 16:00:00',
            ],
        ];

        foreach ($impediments as $impediment) {
            $this->impedimentService->create($this->testAvailability, $impediment);
        }

        // Act
        $result = $this->impedimentService->get();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Impediment 1', $result[0]->reason);
        $this->assertEquals('Impediment 2', $result[1]->reason);
    }

    public function test_get_with_filters(): void
    {
        // Arrange - Créer des impediments avec différentes dates
        $impediments = [
            [
                'reason' => 'Janvier',
                'start_datetime' => '2038-01-04 10:00:00',
                'end_datetime' => '2038-01-04 12:00:00',
            ],
            [
                'reason' => 'Février',
                'start_datetime' => '2038-02-04 10:00:00',
                'end_datetime' => '2038-02-04 12:00:00',
            ],
            [
                'reason' => 'Janvier tardif',
                'start_datetime' => '2038-01-25 10:00:00',
                'end_datetime' => '2038-01-25 12:00:00',
            ],
        ];

        foreach ($impediments as $impediment) {
            $this->impedimentService->create($this->testAvailability, $impediment);
        }

        // Act - Filtrer pour janvier seulement
        $this->impedimentService->setFilters([
            'start_date' => '2038-01-01',
            'end_date' => '2038-01-31',
        ]);

        $result = $this->impedimentService->get();

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
        $impediments = [
            ['reason' => 'Maintenance système', 'start_datetime' => '2038-01-04 10:00:00', 'end_datetime' => '2038-01-04 12:00:00'],
            ['reason' => 'Formation sécurité', 'start_datetime' => '2038-01-05 10:00:00', 'end_datetime' => '2038-01-05 12:00:00'],
            ['reason' => 'Maintenance réseau', 'start_datetime' => '2038-01-06 10:00:00', 'end_datetime' => '2038-01-06 12:00:00'],
        ];

        foreach ($impediments as $impediment) {
            $this->impedimentService->create($this->testAvailability, $impediment);
        }

        // Act - Filtrer par raison contenant "Maintenance"
        $this->impedimentService->setFilters([
            'reason' => 'Maintenance',
        ]);

        $result = $this->impedimentService->get();

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
        // Arrange - Créer un schedule existant
        Schedule::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'title' => 'Rendez-vous existant',
            'start_datetime' => '2038-01-04 11:00:00', // 11h-13h
            'end_datetime' => '2038-01-04 13:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Act - Vérifier chevauchement
        $wouldOverlap = $this->impedimentService->wouldOverlapWithSchedule(
            $this->testAvailability->id,
            Carbon::parse('2038-01-04 10:00:00'), // 10h-12h (chevauche 11h-12h)
            Carbon::parse('2038-01-04 12:00:00')
        );

        // Assert
        $this->assertTrue($wouldOverlap);
    }

    public function test_would_overlap_with_schedule_returns_false(): void
    {
        // Arrange - Créer un schedule
        Schedule::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'title' => 'Rendez-vous',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        // Act - Vérifier créneau non chevauchant
        $wouldOverlap = $this->impedimentService->wouldOverlapWithSchedule(
            $this->testAvailability->id,
            Carbon::parse('2038-01-04 14:00:00'), // Après le schedule
            Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert
        $this->assertFalse($wouldOverlap);
    }

    public function test_would_overlap_with_schedule_excluding_current_impediment(): void
    {
        // Arrange - Créer un impediment existant
        $impediment = Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Existant',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act - Vérifier chevauchement en excluant l'impediment courant
        $wouldOverlap = $this->impedimentService->wouldOverlapWithSchedule(
            $this->testAvailability->id,
            Carbon::parse('2038-01-04 12:00:00'), // Chevauche l'impediment existant
            Carbon::parse('2038-01-04 14:00:00'),
            $impediment->id // Exclure cet impediment
        );

        // Assert - Devrait retourner false car on exclut l'impediment existant
        $this->assertFalse($wouldOverlap);
    }

    public function test_would_overlap_with_other_impediment_returns_true(): void
    {
        // Arrange - Créer un impediment existant
        Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Impediment existant',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act - Vérifier chevauchement avec autre impediment
        $wouldOverlap = $this->impedimentService->wouldOverlapWithOtherImpediment(
            $this->testAvailability->id,
            Carbon::parse('2038-01-04 10:00:00'), // 10h-12h (chevauche 11h-12h)
            Carbon::parse('2038-01-04 12:00:00')
        );

        // Assert
        $this->assertTrue($wouldOverlap);
    }

    public function test_would_overlap_with_other_impediment_returns_false(): void
    {
        // Arrange - Créer un impediment
        Impediment::create([
            'availability_id' => $this->testAvailability->id,
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'reason' => 'Impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act - Vérifier créneau non chevauchant
        $wouldOverlap = $this->impedimentService->wouldOverlapWithOtherImpediment(
            $this->testAvailability->id,
            Carbon::parse('2038-01-04 14:00:00'), // Après l'impediment
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
        $this->impedimentService->create($this->testAvailability, [
            'reason' => 'Test block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act - Vérifier créneau chevauchant
        $isBlocked = $this->impedimentService->isTimeSlotBlocked(
            Carbon::parse('2038-01-04 11:00:00'), // 11h-13h (chevauche 11h-12h)
            Carbon::parse('2038-01-04 13:00:00')
        );

        // Assert
        $this->assertTrue($isBlocked);
    }

    public function test_is_time_slot_blocked_returns_false(): void
    {
        // Arrange - Créer un impediment
        $this->impedimentService->create($this->testAvailability, [
            'reason' => 'Test block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act - Vérifier créneau non chevauchant
        $isBlocked = $this->impedimentService->isTimeSlotBlocked(
            Carbon::parse('2038-01-04 14:00:00'), // Après l'impediment
            Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert
        $this->assertFalse($isBlocked);
    }

    public function test_is_time_slot_blocked_with_type_filter(): void
    {
        // Arrange - Créer une disponibilité avec un type différent
        $otherAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'emergency', // Type différent
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Créer un impediment sur l'autre disponibilité
        $this->impedimentService->create($otherAvailability, [
            'reason' => 'Emergency block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act - Vérifier avec filtre type 'consultation'
        $isBlocked = $this->impedimentService->isTimeSlotBlocked(
            Carbon::parse('2038-01-04 11:00:00'),
            Carbon::parse('2038-01-04 13:00:00'),
            'consultation' // Type différent
        );

        // Assert - Ne devrait pas trouver l'impediment car type différent
        $this->assertFalse($isBlocked);
    }

    public function test_get_available_time_slots(): void
    {
        // Arrange - Créer un impediment
        $this->impedimentService->create($this->testAvailability, [
            'reason' => 'Meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Mock du SlotFinder avec attentes
        $mockSlotFinder = $this->createMock(SlotFinderInterface::class);
        $mockSlotFinder->expects($this->once())
            ->method('getAvailableSlotsFromImpediments')
            ->with(
                Carbon::parse('2038-01-04 09:00:00'),
                Carbon::parse('2038-01-04 17:00:00'),
                $this->isInstanceOf(Collection::class)
            )
            ->willReturn(collect([
                ['start' => '2038-01-04 12:00:00', 'end' => '2038-01-04 13:00:00'],
                ['start' => '2038-01-04 13:00:00', 'end' => '2038-01-04 14:00:00'],
            ]));

        // Injecter le mock
        $this->impedimentService = new ImpedimentService(
            App::make(ValidatorInterface::class),
            App::make(AvailabilityRepositoryInterface::class),
            App::make(ImpedimentRepositoryInterface::class),
            App::make(ScheduleRepositoryInterface::class),
            $mockSlotFinder
        );
        $this->impedimentService->for($this->testSchedulable);

        // Act
        $slots = $this->impedimentService->getAvailableTimeSlots(
            Carbon::parse('2038-01-04 09:00:00'),
            Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert
        $this->assertCount(2, $slots);
        $this->assertEquals('2038-01-04 12:00:00', $slots[0]['start']);
        $this->assertEquals('2038-01-04 13:00:00', $slots[1]['start']);
    }

    public function test_get_available_time_slots_when_no_availability(): void
    {
        // Arrange - Pas de disponibilité pour ce créneau
        $mockAvailabilityRepo = $this->createMock(AvailabilityRepositoryInterface::class);
        $mockAvailabilityRepo->expects($this->once())
            ->method('findForTimeSlot')
            ->with(
                $this->testSchedulable,
                Carbon::parse('2038-01-04 09:00:00'),
                Carbon::parse('2038-01-04 17:00:00'),
                $this->isNull()
            )
            ->willReturn(null);

        // Injecter le mock
        $this->impedimentService = new ImpedimentService(
            App::make(ValidatorInterface::class),
            $mockAvailabilityRepo,
            App::make(ImpedimentRepositoryInterface::class),
            App::make(ScheduleRepositoryInterface::class),
            App::make(SlotFinderInterface::class)
        );
        $this->impedimentService->for($this->testSchedulable);

        // Act
        $slots = $this->impedimentService->getAvailableTimeSlots(
            Carbon::parse('2038-01-04 09:00:00'),
            Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert
        $this->assertInstanceOf(Collection::class, $slots);
        $this->assertCount(0, $slots);
    }

    /* -----------------------------------------------------------------
     | Tests d'Intégration
     | -----------------------------------------------------------------
     */

    public function test_impediment_prevents_schedule_creation(): void
    {
        // Arrange - Créer un impediment
        $this->impedimentService->create($this->testAvailability, [
            'reason' => 'Rendez-vous médical',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act & Assert - Essayer de créer un schedule chevauchant
        $this->expectException(ValidationFailedException::class);

        $scheduleData = [
            'title' => 'Nouveau rendez-vous',
            'start_datetime' => '2038-01-04 11:00:00', // Chevauche l'impediment
            'end_datetime' => '2038-01-04 13:00:00',
            'status' => ScheduleStatus::BOOKED,
        ];


        $this->scheduleService->for($this->testSchedulable)->create($this->testAvailability, $scheduleData);

        // Note: Dans la vraie application, ce serait bloqué par une règle de validation
        // Ce test montre l'intention, pas l'implémentation exacte

    }

    public function test_multiple_impediments_on_same_day(): void
    {
        // Arrange - Créer plusieurs impediments le même jour
        $impediments = [
            ['reason' => 'Réunion matin', 'start_datetime' => '2038-01-04 09:00:00', 'end_datetime' => '2038-01-04 10:30:00'],
            ['reason' => 'Pause déjeuner', 'start_datetime' => '2038-01-04 12:00:00', 'end_datetime' => '2038-01-04 13:30:00'],
            ['reason' => 'Formation', 'start_datetime' => '2038-01-04 15:00:00', 'end_datetime' => '2038-01-04 17:00:00'],
        ];

        foreach ($impediments as $impediment) {
            $this->impedimentService->create($this->testAvailability, $impediment);
        }

        // Act - Récupérer tous les impediments
        $allImpediments = $this->impedimentService->get();

        // Assert
        $this->assertCount(3, $allImpediments);
        $this->assertEquals('Réunion matin', $allImpediments[0]->reason);
        $this->assertEquals('Pause déjeuner', $allImpediments[1]->reason);
        $this->assertEquals('Formation', $allImpediments[2]->reason);
    }

    public function test_impediment_metadata_serialization(): void
    {
        // Arrange
        $complexMetadata = [
            'category' => 'maintenance',
            'priority' => 'high',
            'teams' => ['IT', 'Support'],
            'notes' => ['System upgrade required'],
        ];

        $data = [
            'reason' => 'Maintenance complexe',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => $complexMetadata,
        ];

        // Act
        $impediment = $this->impedimentService->create($this->testAvailability, $data);

        // Assert
        $this->assertEquals($complexMetadata, $impediment->metadata);
        $this->assertEquals('maintenance', $impediment->metadata['category']);
        $this->assertEquals(['IT', 'Support'], $impediment->metadata['teams']);
    }

    public function test_impediment_duration_calculation(): void
    {
        // Arrange
        $data = [
            'reason' => 'Test duration',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:30:00', // 2.5 heures
        ];

        // Act
        // dd($this->testAvailability->toArray(), $data);
        $impediment = $this->impedimentService->for($this->testSchedulable)->create($this->testAvailability, $data);

        // Assert
        $this->assertEquals(150, $impediment->duration_minutes); // 2.5 * 60 = 150 minutes
    }

    /* -----------------------------------------------------------------
     | Tests de Cas Limites
     | -----------------------------------------------------------------
     */

    public function test_impediment_exact_time_boundary(): void
    {
        // Arrange - Créer un impediment
        $impediment = $this->impedimentService->create($this->testAvailability, [
            'reason' => 'Exact boundary',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act & Assert - Vérifier les limites exactes
        $this->assertTrue($impediment->overlapsWith(
            Carbon::parse('2038-01-04 10:00:00'),
            Carbon::parse('2038-01-04 11:00:00')
        ));

        // Début exactement à la fin ne devrait pas chevaucher
        $this->assertFalse($impediment->overlapsWith(
            Carbon::parse('2038-01-04 12:00:00'), // Exactement à la fin
            Carbon::parse('2038-01-04 13:00:00')
        ));
    }
}
