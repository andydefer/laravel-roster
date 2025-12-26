<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Facades\Availability as AvailabilityFacade;
use Roster\Models\Availability;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
    }

    public function test_can_create_an_availability(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act - DOIT utiliser la Facade avec ->for()
        $availability = AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);

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
        // Arrange - Données sans jours
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);

        // Assert - Doit utiliser tous les jours par défaut
        $this->assertNotEmpty($availability->days);
        $this->assertEquals(
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            $availability->days
        );
    }

    public function test_can_update_an_existing_availability(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
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
        $result = AvailabilityFacade::for($this->testSchedulable)->update($availability->id, $updateData);

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
        AvailabilityFacade::for($this->testSchedulable)->update($availabilityId, $updateData);
    }

    public function test_can_delete_an_availability(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = AvailabilityFacade::for($this->testSchedulable)->delete($availability->id);

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
        AvailabilityFacade::for($this->testSchedulable)->delete($availabilityId);
    }

    public function test_can_find_an_availability_by_id(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = AvailabilityFacade::for($this->testSchedulable)->find($availability->id);

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
        $result = AvailabilityFacade::for($this->testSchedulable)->find($availabilityId);

        // Assert
        $this->assertNotInstanceOf(\Roster\Models\Availability::class, $result);
    }

    public function test_can_get_all_availabilities_with_filters(): void
    {
        // Arrange - Créer plusieurs disponibilités
        AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act - Filtrer par type
        $result = AvailabilityFacade::for($this->testSchedulable)
            ->whereType('consultation')
            ->all();

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
            'daily_end' => '09:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_handles_validation_failure_during_update(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mise à jour invalide (date de fin avant date de début)
        $updateData = [
            'validity_end' => '2038-06-30',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        AvailabilityFacade::for($this->testSchedulable)->update($availability->id, $updateData);
    }

    public function test_validate_partial_date_update_fails_when_end_before_existing_start(): void
    {
        // Créer une disponibilité existante
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mise à jour invalide
        $updateData = [
            'validity_end' => '2038-06-30',
        ];

        $this->expectException(ValidationFailedException::class);

        AvailabilityFacade::for($this->testSchedulable)->update($availability->id, $updateData);
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
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_sets_and_gets_filters_correctly(): void
    {
        // Arrange
        $availabilityService = AvailabilityFacade::for($this->testSchedulable);
        $filters = [
            'type' => 'consultation',
            'day' => 'monday',
        ];

        // Act
        $availabilityService->setFilters($filters);
        $result = $availabilityService->getFilters();

        // Assert
        $this->assertSame($filters, $result);
    }

    public function test_does_not_merge_non_adjacent_availabilities(): void
    {
        // Arrange - Créer une disponibilité existante
        $existingAvailability = AvailabilityFacade::for($this->testSchedulable)->create([
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
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = AvailabilityFacade::for($this->testSchedulable)->create($newData);

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
        // Arrange - Durée trop courte
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '09:04:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_validates_date_range_order(): void
    {
        // Arrange - Date de fin avant date de début
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-31',
            'validity_end' => '2038-07-01',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);

        // Act
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_cannot_update_schedulable_fields(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
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
        AvailabilityFacade::for($this->testSchedulable)->update($availability->id, $updateData);
    }

    public function test_can_get_availabilities_by_type_filter(): void
    {
        // Arrange - Créer plusieurs disponibilités de différents types
        AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act - Utiliser whereType
        $result = AvailabilityFacade::for($this->testSchedulable)
            ->whereType('consultation')
            ->all();

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('consultation', $result->first()->type);
    }

    public function test_can_reset_filters(): void
    {
        // Arrange
        $availabilityService = AvailabilityFacade::for($this->testSchedulable);
        $availabilityService->setFilters(['type' => 'consultation']);
        $availabilityService->setFilter('day', 'monday');

        // Act
        $availabilityService->resetFilters();

        $filters = $availabilityService->getFilters();

        // Assert
        $this->assertEmpty($filters);
    }

    public function test_can_filter_by_availability_id(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act - Filtrer par availability_id
        $result = AvailabilityFacade::for($this->testSchedulable)
            ->find($availability->id);
        // Assert
        $this->assertEquals($availability->id, $result->id);
    }

    public function test_validate_invalid_days_format(): void
    {
        // Arrange - Jours invalides
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => 'not-an-array',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage("Day 'not-an-array' is not a valid day of week");

        // Act
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_validate_empty_days_array(): void
    {
        // Arrange - Tableau de jours vide
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Days array cannot be empty');

        // Act
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_partial_update_allowed(): void
    {
        // Arrange - Créer une disponibilité
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mise à jour partielle
        $updateData = [
            'daily_end' => '18:00:00',
        ];

        // Act
        $result = AvailabilityFacade::for($this->testSchedulable)->update($availability->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
            'daily_start' => '09:00:00',
            'days' => json_encode(['monday', 'wednesday']),
        ]);
    }

    public function test_validate_invalid_day_value(): void
    {
        // Arrange - Jour invalide
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'invalid-day'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage("Day 'invalid-day' is not a valid day of week");

        // Act
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_validate_invalid_type(): void
    {
        config()->set('roster-validation.availability_types', [
            'consultation',
            'training',
            'coaching',
            'meeting',
            'support',
        ]);

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
        AvailabilityFacade::for($this->testSchedulable)->create($availabilityData);
    }

    public function test_validate_type_allowed_when_config_empty(): void
    {
        config()->set('roster-validation.availability_types', []);

        // Act - Type quelconque devrait être accepté
        $availability = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'anything',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Assert
        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals('anything', $availability->type);
    }

    public function test_update_does_not_trigger_merge(): void
    {
        // Arrange - Créer deux availabilities
        $availability1 = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = AvailabilityFacade::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Mettre à jour la deuxième pour qu'elle soit adjacente
        $updateData = [
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
        ];

        // Act
        $result = AvailabilityFacade::for($this->testSchedulable)->update($availability2->id, $updateData);

        // Assert - Mise à jour réussie mais pas de fusion
        $this->assertTrue($result);

        // Les deux doivent toujours exister
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability1->id,
            'daily_start' => '09:00:00',
        ]);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability2->id,
            'daily_start' => '12:00:00',
        ]);
    }
}
