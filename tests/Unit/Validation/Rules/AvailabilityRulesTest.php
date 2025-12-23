<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\RequiredFieldsRule;
use Roster\Validation\Rules\AvailabilityOverlapRule;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

final class AvailabilityRulesTest extends TestCase
{
    private RequiredFieldsRule $requiredFieldsRule;

    private AvailabilityOverlapRule $availabilityOverlapRule;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requiredFieldsRule = new RequiredFieldsRule();
        $this->availabilityOverlapRule = new AvailabilityOverlapRule();
        $this->testSchedulable = TestSchedulable::create();
    }


    public function test_required_fields_rule_valid_for_availability_create(): void
    {
        $data = [
            'type' => 'consultation',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->requiredFieldsRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_required_fields_rule_fails_when_missing_fields_for_availability_create(): void
    {
        $data = [
            'type' => 'consultation',
            'days' => ['monday'],
            // missing validity_start
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            // missing daily_end
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->requiredFieldsRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $violations = $validationContext->getViolations();
        $this->assertArrayHasKey('validity_start', $violations);
        $this->assertArrayHasKey('daily_end', $violations);
        $this->assertStringContainsString('required', (string) $violations['validity_start']);
        $this->assertStringContainsString('required', (string) $violations['daily_end']);
    }

    public function test_required_fields_rule_allows_partial_fields_for_availability_update(): void
    {
        $data = [
            'daily_end' => '18:00:00', // Seulement un champ modifié
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->requiredFieldsRule->validate($validationContext);

        // Ne devrait pas avoir d'erreur de champ requis
        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_required_fields_rule_prevents_owner_change_on_update(): void
    {
        $data = [
            'schedulable_id' => 999, // Tentative de changer le propriétaire
            'schedulable_type' => 'DifferentSchedulable',
        ];

        $validationContext = new ValidationContext(
            OperationType::UPDATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->requiredFieldsRule->validate($validationContext);

        $this->assertTrue($validationContext->hasViolations());
        $violations = $validationContext->getViolations();
        $this->assertArrayHasKey('schedulable_id', $violations);
        $this->assertArrayHasKey('schedulable_type', $violations);
        $this->assertStringContainsString('cannot be changed', (string) $violations['schedulable_id']);
        $this->assertStringContainsString('cannot be changed', (string) $violations['schedulable_type']);
    }

    public function test_availability_overlap_rule_skips_when_incomplete_data(): void
    {
        $data = [
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            // missing days, validity_start, validity_end
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->availabilityOverlapRule->validate($validationContext);

        // Doit passer sans erreur car données incomplètes
        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_availability_overlap_rule_requires_all_fields_for_validation(): void
    {
        $data = [
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'type' => 'consultation',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::AVAILABILITY,
            $data,
            $this->testSchedulable
        );

        $this->availabilityOverlapRule->validate($validationContext);

        // Pas d'erreur car toutes les données nécessaires sont présentes
        // (note: le test réel dépendrait du repository pour trouver les chevauchements)
        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_required_fields_for_schedule_create(): void
    {
        $data = [
            'title' => "Title de la schedule",
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::SCHEDULE,
            $data,
            $this->testSchedulable
        );

        $this->requiredFieldsRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }

    public function test_required_fields_for_impediment_create(): void
    {
        $data = [
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
            'reason' => 'Training',
        ];

        $validationContext = new ValidationContext(
            OperationType::CREATE,
            EntityType::IMPEDIMENT,
            $data,
            $this->testSchedulable
        );

        $this->requiredFieldsRule->validate($validationContext);

        $this->assertFalse($validationContext->hasViolations());
    }
}
