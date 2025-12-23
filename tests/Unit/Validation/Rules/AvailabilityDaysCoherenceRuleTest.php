<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Roster\Validation\Rules\AvailabilityDaysCoherenceRule;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Tests\TestCase;

final class AvailabilityDaysCoherenceRuleTest extends TestCase
{
    private AvailabilityDaysCoherenceRule $availabilityDaysCoherenceRule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->availabilityDaysCoherenceRule = new AvailabilityDaysCoherenceRule();
    }

    public function test_passes_when_days_provided_and_within_period(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['thursday', 'friday'],
                'validity_start' => '2038-07-01', // Jeudi
                'validity_end' => '2038-07-04',   // Dimanche
                default => null
            };
        });

        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_sets_violation_when_day_not_in_period(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['monday', 'thursday'], // Lundi n'est pas dans la période
                'validity_start' => '2038-07-01', // Jeudi
                'validity_end' => '2038-07-04',   // Dimanche
                default => null
            };
        });

        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', "Day 'monday' is not within the validity period (Thursday to Sunday)");

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_no_validation_when_days_not_provided(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->with('days')->willReturn(false);
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_no_validation_when_validity_dates_not_provided(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturnCallback(function (string $key): bool {
            return match ($key) {
                'days' => true,
                'validity_start' => false,
                'validity_end' => false,
                default => false
            };
        });

        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['monday', 'tuesday'],
                default => null
            };
        });

        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_no_validation_when_only_one_validity_date_provided(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturnCallback(function (string $key): bool {
            return match ($key) {
                'days' => true,
                'validity_start' => true,
                'validity_end' => false, // Date de fin manquante
                default => false
            };
        });

        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2038-07-01',
                default => null
            };
        });

        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_handles_single_day_period(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['thursday'],
                'validity_start' => '2038-07-01', // Jeudi
                'validity_end' => '2038-07-01',   // Même jour
                default => null
            };
        });

        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_handles_period_spanning_week_boundary(): void
    {
        // Arrange - Période du samedi au lundi
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['saturday', 'sunday', 'monday'],
                'validity_start' => '2038-07-03', // Samedi
                'validity_end' => '2038-07-05',   // Lundi
                default => null
            };
        });

        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_sets_violation_for_multiple_invalid_days(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => ['monday', 'tuesday', 'friday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-04',
                default => null
            };
        });

        // Solution : utiliser un tableau pour capturer les appels
        $violations = [];
        $context->expects($this->exactly(2))
            ->method('setViolation')
            ->willReturnCallback(function ($field, $message) use (&$violations): void {
                $violations[] = ['field' => $field, 'message' => $message];
            });

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);

        // Assert - Vérifier les violations capturées
        $this->assertCount(2, $violations);
        $this->assertEquals('days', $violations[0]['field']);
        $this->assertStringContainsString("Day 'monday'", (string) $violations[0]['message']);
        $this->assertEquals('days', $violations[1]['field']);
        $this->assertStringContainsString("Day 'tuesday'", (string) $violations[1]['message']);
    }

    public function test_handles_empty_days_array(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => [],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-04',
                default => null
            };
        });

        $context->expects($this->never())->method('setViolation');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }

    public function test_sets_violation_when_days_is_not_an_array(): void
    {
        // Arrange
        $context = $this->createMock(ValidationContextInterface::class);

        // CORRIGÉ: Utiliser directement l'enum au lieu de le mock
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(function (string $key): mixed {
            return match ($key) {
                'days' => 'not-an-array', // Chaîne au lieu de tableau
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-04',
                default => null
            };
        });

        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', 'Days must be an array');

        // Act
        $this->availabilityDaysCoherenceRule->validate($context);
    }
}
