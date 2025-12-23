<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilityService;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityService = app(AvailabilityService::class);
        $this->testSchedulable = TestSchedulable::create();
        $this->availabilityService->for($this->testSchedulable);
    }

    public function test_can_create_an_availability(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00', // CORRIGÉ: 8h de durée au lieu de 0h
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert
        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'type' => 'consultation',
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
        ]);

        $this->assertEquals('consultation', $availability->type);
        $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
        $this->assertEquals($this->testSchedulable->id, $availability->schedulable_id);
        $this->assertEquals(TestSchedulable::class, $availability->schedulable_type);
    }

    public function test_days_default_to_all_days_when_not_provided(): void
    {
        // Arrange - Données sans jours mais AVEC dates de validité
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31', // 31 jours > 7, donc tous les jours
            // 'days' non fourni
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Doit utiliser tous les jours par défaut
        $this->assertNotEmpty($availability->days);
        $this->assertEquals(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], $availability->days);
    }

    public function test_merges_adjacent_availabilities_during_creation(): void
    {
        // Arrange - Créer une disponibilité existante
        $existingAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00', // Touche exactement la fin de l'existante
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = $this->availabilityService->create($newData);

        // Assert - La nouvelle disponibilité doit être fusionnée
        $this->assertDatabaseMissing('roster_availabilities', [
            'id' => $existingAvailability->id,
        ]);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => json_encode(['monday', 'wednesday', 'friday']),
        ]);
    }

    public function test_can_update_an_existing_availability(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $updateData = [
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['tuesday', 'thursday'],
        ];

        // Act
        $result = $this->availabilityService->update($availability->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => json_encode(['tuesday', 'thursday']),
        ]);
    }

    public function test_update_throws_exception_when_availability_not_found(): void
    {
        // Arrange
        $availabilityId = 999;
        $updateData = ['daily_start' => '10:00:00'];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Update validation failed for Availability.*does not exist/'
        );

        // Act
        $this->availabilityService->update($availabilityId, $updateData);
    }


    public function test_can_delete_an_availability(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = $this->availabilityService->delete($availability->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('roster_availabilities', [
            'id' => $availability->id,
        ]);
    }

    public function test_delete_throws_exception_when_availability_not_found(): void
    {
        // Arrange
        $availabilityId = 999;

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Delete validation failed for Availability.*does not exist/'
        );

        // Act
        $this->availabilityService->delete($availabilityId);
    }

    public function test_can_find_an_availability_by_id(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = $this->availabilityService->find($availability->id);

        // Assert
        $this->assertInstanceOf(Availability::class, $result);
        $this->assertEquals($availability->id, $result->id);
        $this->assertEquals('consultation', $result->type);
    }

    public function test_find_returns_null_when_availability_not_found(): void
    {
        // Arrange
        $availabilityId = 999;

        // Act
        $result = $this->availabilityService->find($availabilityId);

        // Assert
        $this->assertNotInstanceOf(Availability::class, $result);
    }

    public function test_can_get_all_availabilities_with_filters(): void
    {
        // Arrange - Créer plusieurs disponibilités
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act - Filtrer par type
        $this->availabilityService->setFilters(['type' => 'consultation']);
        $result = $this->availabilityService->get();

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('consultation', $result->first()->type);
    }

    public function test_handles_validation_failure_during_creation(): void
    {
        // Arrange - Données invalides (heure de fin avant heure de début)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '17:00:00',
            'daily_end' => '09:00:00', // Invalide
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_handles_validation_failure_during_update(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mise à jour invalide (date de fin avant date de début)
        $updateData = [
            'validity_end' => '2038-06-30', // Avant la date de début
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->availabilityService->update($availability->id, $updateData);
    }

    public function test_validate_partial_date_update_fails_when_end_before_existing_start(): void
    {
        // Créer une disponibilité existante
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mise à jour invalide : date de fin avant la date de début existante
        $updateData = [
            'validity_end' => '2038-06-30', // Avant validity_start existant (2038-07-01)
        ];

        $this->expectException(ValidationFailedException::class);

        $this->availabilityService->update($availability->id, $updateData);
    }

    public function test_validation_fails_when_required_fields_missing(): void
    {
        // Arrange - Données incomplètes
        $availabilityData = [
            'type' => 'consultation',
            // daily_start manquant
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_sets_and_gets_filters_correctly(): void
    {
        // Arrange
        $filters = [
            'type' => 'consultation',
            'day' => 'monday',
        ];

        // Act
        $this->availabilityService->setFilters($filters);
        $result = $this->availabilityService->getFilters();

        // Assert
        $this->assertSame($filters, $result);
    }

    public function test_does_not_merge_non_adjacent_availabilities(): void
    {
        // Arrange - Créer une disponibilité existante
        $existingAvailability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Nouvelle disponibilité avec un écart
        $newData = [
            'type' => 'consultation',
            'daily_start' => '14:00:00', // 2h après la fin de l'existante
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = $this->availabilityService->create($newData);

        // Assert - Les deux disponibilités doivent exister
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $existingAvailability->id,
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
        ]);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
        ]);
    }

    public function test_validates_minimum_duration(): void
    {
        // Arrange - Durée trop courte (moins de 15 minutes)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '09:04:00', // Seulement 5 minutes
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_validates_date_range_order(): void
    {
        // Arrange - Date de fin avant date de début
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-31', // Date de début après date de fin
            'validity_end' => '2038-07-01',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_cannot_update_schedulable_fields(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Créer un autre schedulable
        $anotherSchedulable = TestSchedulable::create();

        // Essayer de changer le schedulable (interdit)
        $updateData = [
            'schedulable_id' => $anotherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/cannot be changed/");

        // Act
        $this->availabilityService->update($availability->id, $updateData);
    }

    public function test_can_get_availabilities_by_type_filter(): void
    {
        // Arrange - Créer plusieurs disponibilités de différents types
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act - Utiliser whereType
        $result = $this->availabilityService->whereType('consultation')->get();

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('consultation', $result->first()->type);
    }

    public function test_can_reset_filters(): void
    {
        // Arrange
        $this->availabilityService->setFilters(['type' => 'consultation']);
        $this->availabilityService->setFilter('day', 'monday');

        // Act
        $this->availabilityService->resetFilters();

        $filters = $this->availabilityService->getFilters();

        // Assert
        $this->assertEmpty($filters);
    }

    public function test_can_filter_by_availability_id(): void
    {
        // Arrange - Créer une disponibilité avec des schedules
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act - Filtrer par availability_id
        $this->availabilityService->setFilter('availability_id', $availability->id);
        $result = $this->availabilityService->get();

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($availability->id, $result->first()->id);
    }

    public function test_validate_invalid_days_format(): void
    {
        // Arrange - Jours invalides (pas un tableau)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => 'not-an-array', // Invalide - chaîne au lieu de tableau
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception - ATTENTION: Le message a changé
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage("Day 'not-an-array' is not a valid day of week");

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_validate_empty_days_array(): void
    {
        // Arrange - Tableau de jours vide
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [], // Tableau vide
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Days array cannot be empty');

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_partial_update_allowed(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mise à jour partielle - seulement l'heure de fin
        $updateData = [
            'daily_end' => '18:00:00',
            // 'days' non fourni - devrait utiliser la valeur existante
        ];

        // Act - Ne devrait pas lever d'exception
        $result = $this->availabilityService->update($availability->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
            'daily_start' => '09:00:00', // Inchangé
            'days' => json_encode(['monday', 'wednesday']), // Inchangé
        ]);
    }


    public function test_validate_invalid_day_value(): void
    {
        // Arrange - Jour invalide
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'invalid-day'], // Jour invalide
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception - ATTENTION: Le message a changé
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage("Day 'invalid-day' is not a valid day of week");

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_validate_invalid_type(): void
    {
        config()->set('roster-validation.availability_types',  [
            'consultation',
            'training',
            'coaching',
            'meeting',
            'support',
        ],);

        // Arrange - Type invalide
        $availabilityData = [
            'type' => 'invalid-type',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Invalid type 'invalid-type'/");

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_validate_type_allowed_when_config_empty(): void
    {
        config()->set('roster-validation.availability_types', []);

        $this->availabilityService->create([
            'type' => 'anything',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $this->assertTrue(true); // no exception = OK
    }
}
