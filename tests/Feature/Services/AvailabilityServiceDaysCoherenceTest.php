<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Roster\Enums\DaysOfWeek;
use Roster\Services\AvailabilityService;
use Roster\Validation\Exceptions\ValidationFailedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Models\Availability;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityServiceDaysCoherenceTest extends TestCase
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

    public function test_auto_adjusts_days_when_not_provided_and_period_less_than_7_days(): void
    {
        // Arrange - Période de 4 jours seulement (Jeudi à Dimanche)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-04',   // Dimanche
            // 'days' non fourni
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Devrait ajuster automatiquement aux jours de la période
        $expectedDays = ['thursday', 'friday', 'saturday', 'sunday'];
        $this->assertEquals($expectedDays, $availability->days);
    }

    public function test_uses_provided_days_when_period_less_than_7_days(): void
    {
        // Arrange - Période de 4 jours, l'utilisateur spécifie des jours
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['thursday', 'friday'], // Seulement jeudi et vendredi
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-04',   // Dimanche
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Utilise les jours fournis par l'utilisateur
        $this->assertEquals(['thursday', 'friday'], $availability->days);
    }

    public function test_validation_fails_when_provided_days_not_in_period(): void
    {
        // Arrange - Période de 4 jours (Jeudi à Dimanche)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'thursday'], // Lundi n'est pas dans la période
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-04',   // Dimanche
        ];

        // Expect exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'monday' is not within the validity period/");

        // Act
        $this->availabilityService->create($availabilityData);
    }

    public function test_auto_adjusts_days_for_exact_week_period(): void
    {
        // Arrange - Période exactement de 7 jours
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-07',   // Mercredi suivant (7 jours)
            // 'days' non fourni
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Devrait utiliser tous les jours
        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    public function test_period_more_than_7_days_uses_all_days_by_default(): void
    {
        // Arrange - Période de plus de 7 jours
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15', // 15 jours
            // 'days' non fourni
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Devrait utiliser tous les jours par défaut
        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    public function test_update_removes_days_not_in_new_period(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday', 'sunday'],
            'validity_start' => '2038-07-05', // Lundi
            'validity_end' => '2038-07-18',   // Dimanche
        ]);

        // Act - Réduire la période pour exclure TOUS les dimanches
        // Terminer le samedi 10 juillet pour exclure le dimanche 11
        $updateData = [
            'validity_end' => '2038-07-10', // Samedi (exclut le dimanche 11)
            // 'days' non fourni - devrait automatiquement filtrer les jours
        ];

        $result = $this->availabilityService->update($availability->id, $updateData);

        // Assert - Le dimanche devrait être retiré automatiquement
        $this->assertTrue($result);
        $availability->refresh();
        $this->assertNotContains('sunday', $availability->days);
        $this->assertContains('monday', $availability->days);
        $this->assertContains('wednesday', $availability->days);
        $this->assertContains('friday', $availability->days);
    }

    public function test_update_does_not_add_new_days_even_if_in_period(): void
    {
        // Arrange - Créer une disponibilité avec seulement lundi et mercredi
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-05', // Lundi
            'validity_end' => '2038-07-11',   // Jeudi suivant
        ]);

        // Act - Étendre la période pour inclure plus de jours
        $updateData = [
            'validity_end' => '2038-07-18', // S'étend sur plus de jours
            // 'days' non fourni - ne devrait PAS ajouter 'friday' même s'il est dans la période
        ];

        $result = $this->availabilityService->update($availability->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $availability->refresh();
        $this->assertEquals(['monday', 'wednesday'], $availability->days); // Inchangé
    }

    public function test_update_validation_fails_when_new_days_not_in_new_period(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-05', // Lundi
            'validity_end' => '2038-07-18',   // Lundi suivant
        ]);

        // Act - Essayer d'ajouter un jour qui n'est pas dans la nouvelle période
        // On réduit la période ET on ajoute un jour invalide
        $updateData = [
            'days' => ['monday', 'saturday'], // Samedi n'est pas dans la nouvelle période
            'validity_end' => '2038-07-09',   // Seulement jusqu'au vendredi (pas de samedi)
        ];

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'saturday' is not within the validity period/");

        $this->availabilityService->update($availability->id, $updateData);
    }

    public function test_auto_adjusts_days_for_single_day_period(): void
    {
        // Arrange - Période d'un seul jour (utiliser start = end)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-01',   // Même jour (1 jour seulement)
            // 'days' non fourni
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Devrait utiliser seulement le jour de la date
        $this->assertEquals(['thursday'], $availability->days);
    }

    public function test_no_auto_adjustment_when_days_explicitly_provided(): void
    {
        // Arrange - L'utilisateur fournit explicitement des jours DANS la période
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['thursday', 'friday'], // Jours dans la période
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-04',   // Dimanche
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Utilise les jours fournis
        $this->assertEquals(['thursday', 'friday'], $availability->days);
    }

    public function test_update_with_days_array_replaces_all_days(): void
    {
        // Arrange - Créer une disponibilité
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-18',
        ]);

        // Act - Changer complètement les jours
        $updateData = [
            'days' => ['tuesday', 'thursday'], // Remplace tous les jours
        ];

        $result = $this->availabilityService->update($availability->id, $updateData);

        // Assert
        $this->assertTrue($result);
        $availability->refresh();
        $this->assertEquals(['tuesday', 'thursday'], $availability->days);
    }

    public function test_period_spanning_multiple_weeks_with_auto_adjustment(): void
    {
        // Arrange - Période de 10 jours (moins de 2 semaines complètes)
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01', // Jeudi
            'validity_end' => '2038-07-10',   // Samedi suivant (10 jours)
            // 'days' non fourni
        ];

        // Act
        $availability = $this->availabilityService->create($availabilityData);

        // Assert - Devrait ajuster aux jours de la période
        // Du jeudi 1er au samedi 10 juillet 2038
        $expectedDays = ['thursday', 'friday', 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday'];
        sort($expectedDays);

        $actualDays = $availability->days;
        sort($actualDays);

        $this->assertEquals($expectedDays, $actualDays);
    }
}
