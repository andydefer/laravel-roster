<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\RequiredFieldsRule;
use Tests\TestCase;

/**
 * Unit tests for RequiredFieldsRule validation logic.
 *
 * Tests validation of required fields for availability entities,
 * including CREATE/UPDATE operations and ownership field restrictions.
 */
final class RequiredFieldsRuleTest extends TestCase
{
    private RequiredFieldsRule $requiredFieldsRule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->requiredFieldsRule = new RequiredFieldsRule();
    }

    /**
     * Test that validation passes when all required fields are present for CREATE operation.
     */
    public function test_validation_passes_with_all_required_fields_for_create(): void
    {
        // Arrange: Create context with all required fields for availability entity
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => 'office',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations should be set
    }

    /**
     * Test that validation fails when a required field is missing for CREATE operation.
     */
    public function test_validation_fails_when_required_field_missing_for_create(): void
    {
        // Arrange: Create context with missing required field
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->requiredFieldsRule, 'type', "Field 'type' is required");

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violation should be set for missing field
    }

    /**
     * Test that validation passes for UPDATE operation with partial data.
     */
    public function test_validation_passes_for_update_with_partial_data(): void
    {
        // Arrange: Create context for UPDATE operation with partial data
        $context = $this->createValidationContextForUpdate();

        $context->method('safeData')->willReturn([
            'daily_start' => '10:00:00',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations expected for partial update
    }

    /**
     * Test that validation fails when trying to modify ownership fields during UPDATE.
     */
    public function test_validation_fails_when_modifying_ownership_fields_during_update(): void
    {
        // Arrange: Create context attempting to modify ownership field
        $context = $this->createValidationContextForUpdate();

        $context->method('safeData')->willReturn([
            'schedulable_id' => 999,
            'daily_start' => '10:00:00',
        ]);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->requiredFieldsRule,
                'schedulable_id',
                "Field 'schedulable_id' cannot be changed. Ownership cannot be modified."
            );

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violation should be set for ownership field modification
    }

    /**
     * Test that validation passes when not modifying ownership fields during UPDATE.
     */
    public function test_validation_passes_when_not_modifying_ownership_fields_during_update(): void
    {
        // Arrange: Create context with non-ownership field modifications
        $context = $this->createValidationContextForUpdate();

        $context->method('safeData')->willReturn([
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations expected
    }

    /**
     * Test that validation correctly handles empty array for 'days' field.
     */
    public function test_validation_handles_empty_array_for_days_field(): void
    {
        // Arrange: Create context with empty days array
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => 'office',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for empty array (field is present)
    }

    /**
     * Test that validation returns multiple violations for multiple missing fields.
     */
    public function test_validation_returns_multiple_violations_for_multiple_missing_fields(): void
    {
        // Arrange: Create context with multiple missing fields
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => 'office',
        ]);

        $violations = [];
        $context->expects($this->exactly(5))
            ->method('setViolationFromRule')
            ->willReturnCallback(function ($rule, $field, $message) use (&$violations): void {
                $this->assertSame($this->requiredFieldsRule, $rule);
                $violations[$field] = $message;
            });

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Verify all expected violations were called
        $this->assertCount(5, $violations);

        $expectedFields = ['daily_start', 'daily_end', 'days', 'validity_start', 'validity_end'];
        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $violations);
            $this->assertSame("Field '{$field}' is required", $violations[$field]);
        }
    }

    /**
     * Test that validation works correctly for DELETE operation.
     */
    public function test_validation_works_for_delete_operation(): void
    {
        // Arrange: Create context for DELETE operation
        $context = $this->createValidationContextForDelete();

        $context->method('safeData')->willReturn([]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for DELETE operation
    }

    /**
     * Test that validation does not apply for SCHEDULE entity type.
     */
    public function test_validation_applies_for_schedule_entity_type(): void
    {
        // Arrange: Create context for SCHEDULE entity type
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);

        $context->method('safeData')->willReturn([
            'title' => 'Meeting',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for valid schedule data
    }

    /**
     * Test that validation applies for IMPEDIMENT entity type.
     */
    public function test_validation_applies_for_impediment_entity_type(): void
    {
        // Arrange: Create context for IMPEDIMENT entity type
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);

        $context->method('safeData')->willReturn([
            'reason' => 'Maintenance',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for valid impediment data
    }

    /**
     * Test that validation handles null values in safeData correctly.
     */
    public function test_validation_handles_null_values_in_safe_data(): void
    {
        // Arrange: Create context with some fields missing (filtered as null)
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => 'office',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
        ]);

        $context->expects($this->exactly(2))->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violations for missing validity dates
    }

    /**
     * Test that validation checks all ownership fields during UPDATE.
     */
    public function test_validation_checks_all_ownership_fields_during_update(): void
    {
        // Arrange: Create context attempting to modify all ownership fields
        $context = $this->createValidationContextForUpdate();

        $context->method('safeData')->willReturn([
            'schedulable_id' => 999,
            'schedulable_type' => 'Different\Model',
            'daily_start' => '10:00:00',
        ]);

        $violations = [];
        $context->expects($this->exactly(2))
            ->method('setViolationFromRule')
            ->willReturnCallback(function ($rule, $field, $message) use (&$violations): void {
                $this->assertSame($this->requiredFieldsRule, $rule);
                $violations[$field] = $message;
            });

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violations for both ownership fields
        $this->assertCount(2, $violations);
        $this->assertArrayHasKey('schedulable_id', $violations);
        $this->assertArrayHasKey('schedulable_type', $violations);
    }

    /**
     * Test that validation handles empty string values for required fields.
     */
    public function test_validation_handles_empty_string_for_required_field(): void
    {
        // Arrange: Create context with empty string value
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => '',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for empty string (field is present)
    }

    /**
     * Test that validation passes with all fields including optional ones.
     */
    public function test_validation_passes_with_all_fields_including_optional_ones(): void
    {
        // Arrange: Create context with all required and optional fields
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => 'office',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
            'description' => 'Office hours',
            'priority' => 1,
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations with complete data
    }

    /**
     * Test that validation works correctly with mixed case in field names.
     */
    public function test_validation_works_with_mixed_case_field_names(): void
    {
        // Arrange: Create context with properly cased field names
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([
            'type' => 'office',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations with correct field names
    }

    /**
     * Test that validation fails for CREATE when no fields are provided.
     */
    public function test_validation_fails_for_create_when_no_fields_provided(): void
    {
        // Arrange: Create context with empty data
        $context = $this->createValidationContextForCreate();

        $context->method('safeData')->willReturn([]);

        $violationCount = 0;
        $context->expects($this->exactly(6))
            ->method('setViolationFromRule')
            ->willReturnCallback(function ($rule) use (&$violationCount): void {
                $this->assertSame($this->requiredFieldsRule, $rule);
                $violationCount++;
            });

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: All required fields should generate violations
        $this->assertSame(6, $violationCount);
    }

    /**
     * Test that validation handles UPDATE with ownership fields in data.
     */
    public function test_validation_handles_update_with_ownership_fields_present(): void
    {
        // Arrange: Create context with ownership field in update data
        $context = $this->createValidationContextForUpdate();

        $context->method('safeData')->willReturn([
            'schedulable_id' => 123,
            'daily_start' => '10:00:00',
        ]);

        $context->expects($this->once())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violation for ownership field presence
    }

    /**
     * Test that rule description is available.
     */
    public function test_has_description(): void
    {
        // Act: Get rule description
        $description = $this->requiredFieldsRule->getDescription();

        // Assert: Description should not be empty
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
    }

    /**
     * Test that validation fails for SCHEDULE entity with missing required fields.
     */
    public function test_validation_fails_for_schedule_entity_with_missing_required_fields(): void
    {
        // Arrange: Create context for SCHEDULE entity with missing fields
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);

        $context->method('safeData')->willReturn([
            'title' => 'Meeting',
        ]);

        $context->expects($this->exactly(2))->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violations for missing schedule fields
    }

    /**
     * Test that validation fails for IMPEDIMENT entity with missing required fields.
     */
    public function test_validation_fails_for_impediment_entity_with_missing_required_fields(): void
    {
        // Arrange: Create context for IMPEDIMENT entity with missing fields
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);

        $context->method('safeData')->willReturn([
            'reason' => 'Maintenance',
        ]);

        $context->expects($this->exactly(2))->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: Violations for missing impediment fields
    }

    /**
     * Test that validation allows partial UPDATE for SCHEDULE entity.
     */
    public function test_validation_allows_partial_update_for_schedule_entity(): void
    {
        // Arrange: Create context for SCHEDULE entity UPDATE
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);

        $context->method('safeData')->willReturn([
            'title' => 'Updated Meeting',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for partial schedule update
    }

    /**
     * Test that validation allows partial UPDATE for IMPEDIMENT entity.
     */
    public function test_validation_allows_partial_update_for_impediment_entity(): void
    {
        // Arrange: Create context for IMPEDIMENT entity UPDATE
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);

        $context->method('safeData')->willReturn([
            'reason' => 'Updated Reason',
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->requiredFieldsRule->validate($context);

        // Assert: No violations for partial impediment update
    }

    /**
     * Create a validation context mock for CREATE operation.
     *
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContextForCreate(): MockObject
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        return $context;
    }

    /**
     * Create a validation context mock for UPDATE operation.
     *
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContextForUpdate(): MockObject
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        return $context;
    }

    /**
     * Create a validation context mock for DELETE operation.
     *
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContextForDelete(): MockObject
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::DELETE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        return $context;
    }
}
