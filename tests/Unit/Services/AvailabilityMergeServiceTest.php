<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Exceptions\MergeConflictException;
use Roster\Facades\Availability;
use Roster\Facades\Schedule;
use Roster\Facades\Impediment;
use Roster\Services\AvailabilityMergeService;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityMergeServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityMergeService $availabilityMergeService;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityMergeService = app(AvailabilityMergeService::class);
        $this->testSchedulable = TestSchedulable::create();
    }

    public function test_merges_adjacent_availabilities_safely(): void
    {
        // Arrange - Créer une disponibilité existante via le service
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $result = $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);

        // Assert - L'ancienne entité doit être mise à jour
        $this->assertArrayHasKey('_should_delete', $result);
        $this->assertTrue($result['_should_delete']);
        $this->assertEquals($availability->id, $result['_merged_into']);

        // Vérifier que l'existante a été mise à jour
        $availability->refresh();
        $this->assertEquals('09:00:00', $availability->daily_start->format('H:i:s'));
        $this->assertEquals('17:00:00', $availability->daily_end->format('H:i:s'));
        $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
    }

    public function test_does_not_merge_with_different_type(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'training', // Type différent
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $result = $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);

        // Assert - Pas de fusion
        $this->assertArrayNotHasKey('_should_delete', $result);
        $this->assertArrayNotHasKey('_merged_into', $result);
    }

    public function test_does_not_merge_with_different_schedulable(): void
    {
        // Arrange
        $otherSchedulable = TestSchedulable::create();

        $availability = Availability::for($otherSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $result = $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);

        // Assert - Pas de fusion
        $this->assertArrayNotHasKey('_should_delete', $result);
        $this->assertArrayNotHasKey('_merged_into', $result);
    }

    public function test_throws_exception_when_existing_has_schedules(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Créer un schedule dépendant via le service
        $availabilityForSchedule = Availability::for($this->testSchedulable)->find($availability->id);
        Schedule::for($this->testSchedulable)->owner($availabilityForSchedule)->create([
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(MergeConflictException::class);
        $this->expectExceptionMessageMatches('/schedule\(s\) depend/');

        // Act
        $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);
    }

    public function test_throws_exception_when_existing_has_impediments(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Créer un impediment dépendant via le service
        $availabilityForImpediment = Availability::for($this->testSchedulable)->find($availability->id);

        Impediment::for($this->testSchedulable)
            ->owner($availabilityForImpediment)
            ->create([
                'reason' => 'Test Impediment',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(MergeConflictException::class);
        $this->expectExceptionMessageMatches('/impediment\(s\) depend/');

        // Act
        $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);
    }

    public function test_does_not_merge_non_adjacent_availabilities(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '14:00:00', // 2h après, pas adjacent
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $result = $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);

        // Assert - Pas de fusion
        $this->assertArrayNotHasKey('_should_delete', $result);
        $this->assertArrayNotHasKey('_merged_into', $result);
    }

    public function test_merges_days_correctly(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['wednesday', 'friday'], // Mercredi en commun, vendredi nouveau
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $result = $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);

        // Assert
        $this->assertArrayHasKey('_should_delete', $result);

        $availability->refresh();
        $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
    }

    public function test_merges_validity_periods_correctly(): void
    {
        // Arrange
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-10', // Chevauchement partiel
            'validity_end' => '2038-07-31',
        ];

        // Act
        $result = $this->availabilityMergeService->mergeIfAdjacent($newData, $availability, $this->testSchedulable);

        // Assert
        $this->assertArrayHasKey('_should_delete', $result);

        $availability->refresh();
        $this->assertEquals('2038-07-01', $availability->validity_start->format('Y-m-d'));
        $this->assertEquals('2038-07-31', $availability->validity_end->format('Y-m-d'));
    }

    public function test_finds_adjacent_availabilities(): void
    {
        // Arrange
        Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        Availability::for($this->testSchedulable)->create([
            'type' => 'training', // Type différent
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $adjacent = $this->availabilityMergeService->findAdjacentAvailabilities($newData, $this->testSchedulable);

        // Assert - Ne doit trouver que celles du même type
        $this->assertCount(1, $adjacent);
        $this->assertEquals('consultation', $adjacent[0]->type);
    }
}
