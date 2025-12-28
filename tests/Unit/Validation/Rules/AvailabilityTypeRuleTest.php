<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\AvailabilityTypeRule;
use Tests\TestCase;

final class AvailabilityTypeRuleTest extends TestCase
{
    private AvailabilityTypeRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AvailabilityTypeRule();
    }

    /**
     * Test that validation passes when type is allowed.
     */
    public function test_passes_when_type_is_allowed(): void
    {
        // Arrange: Configure allowed types and create context with a valid type
        config(['roster.allowed_types' => ['office', 'remote', 'meeting']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: 'office'
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when type is in allowed list
    }

    /**
     * Test that validation fails when type is not allowed.
     */
    public function test_fails_when_type_is_not_allowed(): void
    {
        // Arrange: Configure allowed types and create context with an invalid type
        config(['roster.allowed_types' => ['office', 'remote', 'meeting']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: 'invalid'
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'type',
                "Invalid type 'invalid'. Allowed types: office, remote, meeting"
            );

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for the invalid type
    }

    /**
     * Test that validation passes when no allowed types are configured.
     */
    public function test_passes_when_no_allowed_types_configured(): void
    {
        // Arrange: Empty allowed types configuration
        config(['roster.allowed_types' => []]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: 'any_type'
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule with empty configuration
        $this->rule->validate($context);

        // Assert: No violations should occur when allowed types list is empty
    }

    /**
     * Test that validation passes when type is null.
     */
    public function test_passes_when_type_is_null(): void
    {
        // Arrange: Context with null type value
        config(['roster.allowed_types' => ['office', 'remote']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: null
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when type value is null
    }

    /**
     * Test that validation passes when type field is not present.
     */
    public function test_passes_when_type_field_is_not_present(): void
    {
        // Arrange: Context without type field
        config(['roster.allowed_types' => ['office', 'remote']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: false,
            typeValue: null
        );

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when type field is absent
    }

    /**
     * Test that validation shows truncated list when there are many allowed types.
     */
    public function test_shows_truncated_list_when_many_allowed_types(): void
    {
        // Arrange: Large list of allowed types to trigger truncation
        $manyTypes = [
            'office',
            'remote',
            'meeting',
            'training',
            'support',
            'maintenance',
            'project',
            'emergency',
            'holiday',
            'sick',
            'vacation',
            'break',
            'travel',
            'conference',
            'workshop'
        ];

        config(['roster.allowed_types' => $manyTypes]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: 'invalid_type'
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'type',
                "Invalid type 'invalid_type'. Allowed types: office, remote, meeting, training, support, maintenance, project, emergency, holiday, sick (see more in configuration: roster.allowed_types)"
            );

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: Violation message should show truncated list of allowed types
    }

    /**
     * Test that validation works for UPDATE operation.
     */
    public function test_works_for_update_operation(): void
    {
        // Arrange: UPDATE operation with valid type
        config(['roster.allowed_types' => ['office', 'remote']]);

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            hasTypeField: true,
            typeValue: 'remote'
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule for UPDATE operation
        $this->rule->validate($context);

        // Assert: No violations should occur for valid type in UPDATE operation
    }

    /**
     * Test that validation uses strict type checking.
     */
    public function test_uses_strict_type_checking(): void
    {
        // Arrange: Numeric string types to test strict comparison
        config(['roster.allowed_types' => ['1', '2', '3']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: '1'
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur for exact string match (strict comparison)
    }

    /**
     * Test that validation passes for correct entity type (AVAILABILITY).
     */
    public function test_only_validates_for_availability_entity_type(): void
    {
        // Arrange: Valid type for AVAILABILITY entity
        config(['roster.allowed_types' => ['office', 'remote']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: 'office'
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur for AVAILABILITY entity type validation
    }

    /**
     * Test that validation message is formatted correctly.
     */
    public function test_formats_error_message_correctly(): void
    {
        // Arrange: Three allowed types for testing message formatting
        config(['roster.allowed_types' => ['type_a', 'type_b', 'type_c']]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            hasTypeField: true,
            typeValue: 'wrong_type'
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'type',
                "Invalid type 'wrong_type'. Allowed types: type_a, type_b, type_c"
            );

        // Act: Execute the type validation rule
        $this->rule->validate($context);

        // Assert: Error message should be properly formatted with all allowed types
    }

    /**
     * Create a validation context mock with specified configuration.
     */
    private function createValidationContext(
        OperationType $operationType,
        bool $hasTypeField,
        ?string $typeValue
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn($operationType);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturnCallback(
            fn(string $key): bool => $key === 'type' && $hasTypeField
        );

        if ($hasTypeField) {
            $context->method('get')->willReturnCallback(
                fn(string $key): mixed => $key === 'type' ? $typeValue : null
            );
        }

        return $context;
    }
}
