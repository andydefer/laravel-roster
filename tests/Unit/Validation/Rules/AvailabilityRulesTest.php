<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Roster\Validation\DTOs\ViolationData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\RequiredFieldsRule;
use Roster\Validation\Rules\AvailabilityOverlapRule;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Unit tests for availability validation rules.
 *
 * Tests RequiredFieldsRule and AvailabilityOverlapRule validation logic
 * for availability entities in different operations and scenarios.
 */
final class AvailabilityRulesTest extends TestCase
{
    private RequiredFieldsRule $requiredFieldsRule;

    private AvailabilityOverlapRule $availabilityOverlapRule;

    private TestSchedulable $testSchedulable;

    /**
     * Set up the test case with fresh rule instances.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->requiredFieldsRule = new RequiredFieldsRule();
        $this->availabilityOverlapRule = new AvailabilityOverlapRule();
        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Test that required fields rule passes for complete availability data on CREATE operation.
     */
    public function test_required_fields_rule_valid_for_availability_create(): void
    {
        // Arrange: Create context with complete availability data
        $data = [
            'type' => 'consultation',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute required fields validation
        $this->requiredFieldsRule->validate($validationContext);

        // Assert: No violations should be present
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test that required fields rule fails when mandatory fields are missing for CREATE operation.
     */
    public function test_required_fields_rule_fails_when_missing_fields_for_availability_create(): void
    {
        // Arrange: Create context with incomplete availability data
        $data = [
            'type' => 'consultation',
            'days' => ['monday'],
            // missing validity_start
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            // missing daily_end
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute required fields validation
        $this->requiredFieldsRule->validate($validationContext);

        // Assert: Violations should be present for missing fields
        $this->assertTrue($validationContext->hasViolations());

        $violations = $validationContext->getViolations();

        $this->assertTrue($validationContext->hasViolationFor('validity_start'));
        $this->assertTrue($validationContext->hasViolationFor('daily_end'));

        $validityStartViolation = array_values(array_filter(
            $violations,
            fn(ViolationData $v): bool => $v->getField() === 'validity_start'
        ))[0] ?? null;
        $this->assertInstanceOf(ViolationData::class, $validityStartViolation);
        $this->assertStringContainsString('required', $validityStartViolation->getMessage());

        $dailyEndViolation = array_values(array_filter(
            $violations,
            fn(ViolationData $v): bool => $v->getField() === 'daily_end'
        ))[0] ?? null;
        $this->assertInstanceOf(ViolationData::class, $dailyEndViolation);
        $this->assertStringContainsString('required', $dailyEndViolation->getMessage());
    }

    /**
     * Test that required fields rule allows partial fields for UPDATE operation.
     */
    public function test_required_fields_rule_allows_partial_fields_for_availability_update(): void
    {
        // Arrange: Create context with partial update data
        $data = [
            'daily_end' => '18:00:00', // Only one field modified
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute required fields validation
        $this->requiredFieldsRule->validate($validationContext);

        // Assert: No violations should be present for partial update
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test that required fields rule prevents changing schedulable owner on UPDATE operation.
     */
    public function test_required_fields_rule_prevents_owner_change_on_update(): void
    {
        // Arrange: Create context with attempt to change owner
        $data = [
            'schedulable_id' => 999, // Attempt to change owner ID
            'schedulable_type' => 'DifferentSchedulable',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute required fields validation
        $this->requiredFieldsRule->validate($validationContext);

        // Assert: Violations should be present for owner change attempt
        $this->assertTrue($validationContext->hasViolations());

        $violations = $validationContext->getViolations();

        $this->assertTrue($validationContext->hasViolationFor('schedulable_id'));
        $this->assertTrue($validationContext->hasViolationFor('schedulable_type'));

        $schedulableIdViolation = array_values(array_filter(
            $violations,
            fn(ViolationData $v): bool => $v->getField() === 'schedulable_id'
        ))[0] ?? null;
        $this->assertInstanceOf(ViolationData::class, $schedulableIdViolation);
        $this->assertStringContainsString('cannot be changed', $schedulableIdViolation->getMessage());

        $schedulableTypeViolation = array_values(array_filter(
            $violations,
            fn(ViolationData $v): bool => $v->getField() === 'schedulable_type'
        ))[0] ?? null;
        $this->assertInstanceOf(ViolationData::class, $schedulableTypeViolation);
        $this->assertStringContainsString('cannot be changed', $schedulableTypeViolation->getMessage());
    }


    /**
     * Test that availability overlap rule skips validation when data is incomplete.
     */
    public function test_availability_overlap_rule_skips_when_incomplete_data(): void
    {
        // Arrange: Create context with incomplete overlap validation data
        $data = [
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            // missing days, validity_start, validity_end
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute overlap validation
        $this->availabilityOverlapRule->validate($validationContext);

        // Assert: No violations should be present for incomplete data (skipped validation)
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test that availability overlap rule requires all fields for validation.
     */
    public function test_availability_overlap_rule_requires_all_fields_for_validation(): void
    {
        // Arrange: Create context with complete overlap validation data
        $data = [
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'type' => 'consultation',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute overlap validation
        $this->availabilityOverlapRule->validate($validationContext);

        // Assert: No violations should be present when all required data is available
        // Note: Actual overlap detection depends on repository implementation
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test that required fields rule validates schedule entity correctly.
     */
    public function test_required_fields_for_schedule_create(): void
    {
        // Arrange: Create context with complete schedule data
        $data = [
            'title' => "Schedule title",
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute required fields validation
        $this->requiredFieldsRule->validate($validationContext);

        // Assert: No violations should be present for schedule creation
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test that required fields rule validates impediment entity correctly.
     */
    public function test_required_fields_for_impediment_create(): void
    {
        // Arrange: Create context with complete impediment data
        $data = [
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
            'reason' => 'Training',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute required fields validation
        $this->requiredFieldsRule->validate($validationContext);

        // Assert: No violations should be present for impediment creation
        $this->assertFalse($validationContext->hasViolations());
    }
}
