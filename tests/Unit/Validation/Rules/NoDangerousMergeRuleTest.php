<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Facades\Availability;
use Roster\Facades\Schedule;
use Roster\Facades\Impediment;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\NoDangerousMergeRule;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class NoDangerousMergeRuleTest extends TestCase
{
    private NoDangerousMergeRule $noDangerousMergeRule;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->noDangerousMergeRule = app(NoDangerousMergeRule::class);
        $this->testSchedulable = TestSchedulable::create();
    }

    public function test_does_not_validate_for_update_operation(): void
    {
        // Arrange
        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::AVAILABILITY,
            [],
            $this->testSchedulable
        );

        // Act
        $this->noDangerousMergeRule->validate($validationContext);

        // Assert - Pas de violations pour UPDATE
        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_detects_dangerous_merge_with_schedules(): void
    {
        // Arrange - Créer une availability uniquement le lundi
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'], // uniquement lundi
            'validity_start' => '2038-07-05', // lundi
            'validity_end' => '2038-07-31',
        ]);

        // Crée un schedule sur un jour valide (lundi)
        Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => '2038-07-05 10:00:00', // lundi
                'end_datetime' => '2038-07-05 11:00:00',
            ]);

        // Données pour la nouvelle availability qui pourrait fusionner
        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'], // même jour
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-31',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $newData,
            $this->testSchedulable
        );

        // Act
        $this->noDangerousMergeRule->validate($validationContext);

        // Assert - Doit détecter la fusion dangereuse
        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('merge', $validationContext->getViolations());
        $this->assertStringContainsString('schedule', (string) $validationContext->getViolations()['merge']);
        $this->assertTrue($validationContext->hasFlag('dangerous_merge_attempted'));
    }


    public function test_detects_dangerous_merge_with_impediments(): void
    {
        // Arrange - Créer une availability uniquement le lundi
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'], // uniquement lundi
            'validity_start' => '2038-07-05', // lundi
            'validity_end' => '2038-07-31',
        ]);

        // Crée un impediment sur un jour valide (lundi)
        Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Test Impediment',
                'start_datetime' => '2038-07-05 10:00:00', // lundi
                'end_datetime' => '2038-07-05 11:00:00',
            ]);

        // Données pour la nouvelle availability qui pourrait fusionner
        $newData = [
            'type' => 'consultation',
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'], // même jour
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-31',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $newData,
            $this->testSchedulable
        );

        // Act
        $this->noDangerousMergeRule->validate($validationContext);

        // Assert - Doit détecter la fusion dangereuse
        $this->assertTrue($validationContext->hasViolations());
        $this->assertArrayHasKey('merge', $validationContext->getViolations());
        $this->assertStringContainsString('impediment', (string) $validationContext->getViolations()['merge']);
    }


    public function test_allows_safe_merge_without_dependencies(): void
    {
        // Arrange - Créer une availability sans dépendances via le service
        Availability::for($this->testSchedulable)->create([
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

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $newData,
            $this->testSchedulable
        );

        // Act
        $this->noDangerousMergeRule->validate($validationContext);

        // Assert - Pas de violations pour fusion sécurisée
        $this->assertFalse($validationContext->hasViolations());
        $this->assertFalse($validationContext->hasFlag('dangerous_merge_attempted'));
    }
}
