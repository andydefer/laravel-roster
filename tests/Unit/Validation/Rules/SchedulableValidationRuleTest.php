<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\SchedulableValidationRule;
use Tests\TestCase;

/**
 * Unit tests for SchedulableValidationRule validation logic.
 *
 * Tests validation of schedulable resources across different entity types
 * and operations, ensuring proper ownership and reference consistency.
 */
final class SchedulableValidationRuleTest extends TestCase
{
    private SchedulableValidationRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new SchedulableValidationRule();
    }

    /**
     * Test that validation fails when no schedulable is provided.
     */
    public function test_fails_when_no_schedulable_provided(): void
    {
        // Arrange: Create context without schedulable resource
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getSchedulable')->willReturn(null);
        $context->method('safeData')->willReturn([]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable',
                'No schedulable resource specified. Call for() with a schedulable entity before executing the operation.'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for missing schedulable
    }

    /**
     * Test that validation passes for CREATE when schedulable is provided.
     */
    public function test_passes_for_create_when_schedulable_provided(): void
    {
        // Arrange: Create context with schedulable resource
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with valid schedulable
    }

    /**
     * Test that validation fails for CREATE when schedulable_id does not match.
     */
    public function test_fails_for_create_when_schedulable_id_mismatch(): void
    {
        // Arrange: Create context with mismatched schedulable ID
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: [
                'schedulable_id' => 999,
                'schedulable_type' => get_class($model),
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 999,
            schedulableType: get_class($model)
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_id',
                'Schedulable ID mismatch. Expected: 123, Got: 999'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for ID mismatch
    }

    /**
     * Test that validation fails for CREATE when schedulable_type does not match.
     */
    public function test_fails_for_create_when_schedulable_type_mismatch(): void
    {
        // Arrange: Create context with mismatched schedulable type
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: [
                'schedulable_id' => 123,
                'schedulable_type' => 'Different\\Model\\Class',
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 123,
            schedulableType: 'Different\\Model\\Class'
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_type',
                sprintf(
                    'Schedulable type mismatch. Expected: %s, Got: Different\\Model\\Class',
                    get_class($model)
                )
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for type mismatch
    }

    /**
     * Test that validation passes for CREATE when schedulable info matches (Schedule entity).
     */
    public function test_passes_for_create_when_schedulable_info_matches_schedule(): void
    {
        // Arrange: Create context with matching schedulable data for Schedule entity
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: [
                'schedulable_id' => 123,
                'schedulable_type' => get_class($model),
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 123,
            schedulableType: get_class($model)
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with matching data
    }

    /**
     * Test that validation passes for CREATE when schedulable info matches (Impediment entity).
     */
    public function test_passes_for_create_when_schedulable_info_matches_impediment(): void
    {
        // Arrange: Create context with matching schedulable data for Impediment entity
        $model = $this->createModelStub(id: 456);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::IMPEDIMENT,
            schedulable: $model,
            data: [
                'schedulable_id' => 456,
                'schedulable_type' => get_class($model),
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 456,
            schedulableType: get_class($model)
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with matching data
    }

    /**
     * Test that validation fails for CREATE when schedulable_id is missing (Schedule).
     */
    public function test_fails_for_create_when_schedulable_id_missing_schedule(): void
    {
        // Arrange: Create context with missing schedulable ID for Schedule entity
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: ['schedulable_type' => get_class($model)]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: null,
            schedulableType: get_class($model)
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable',
                'Schedulable ID and type are required'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for missing required fields
    }

    /**
     * Test that validation fails for CREATE when schedulable_type is missing (Schedule).
     */
    public function test_fails_for_create_when_schedulable_type_missing_schedule(): void
    {
        // Arrange: Create context with missing schedulable type for Schedule entity
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: ['schedulable_id' => 123]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 123,
            schedulableType: null
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable',
                'Schedulable ID and type are required'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for missing required fields
    }

    /**
     * Test that validation fails for UPDATE when trying to change schedulable_id.
     */
    public function test_fails_for_update_when_changing_schedulable_id(): void
    {
        // Arrange: Create context attempting to change schedulable_id during update
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForUpdateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: ['schedulable_id' => 999]
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_id',
                "Field 'schedulable_id' cannot be changed. The owner cannot be modified."
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for immutable field
    }

    /**
     * Test that validation fails for UPDATE when trying to change schedulable_type.
     */
    public function test_fails_for_update_when_changing_schedulable_type(): void
    {
        // Arrange: Create context attempting to change schedulable_type during update
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForUpdateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: ['schedulable_type' => 'Different\\Model\\Class']
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_type',
                "Field 'schedulable_type' cannot be changed. The owner cannot be modified."
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for immutable field
    }

    /**
     * Test that validation passes for UPDATE when not changing owner fields.
     */
    public function test_passes_for_update_when_not_changing_owner_fields(): void
    {
        // Arrange: Create context with non-owner field changes during update
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForUpdateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: [
                'type' => 'remote',
                'daily_start' => '09:00:00',
            ]
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for non-owner fields
    }

    /**
     * Test that validation passes for DELETE operation.
     */
    public function test_passes_for_delete_operation(): void
    {
        // Arrange: Create context for DELETE operation
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForDeleteOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for DELETE
    }

    /**
     * Test that validation passes for Availability CREATE when schedulable fields are null.
     */
    public function test_passes_for_availability_create_when_schedulable_fields_null(): void
    {
        // Arrange: Create context for Availability CREATE with null schedulable fields
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: null,
            schedulableType: null
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for null fields in Availability
    }

    /**
     * Test that validation passes for Availability CREATE when schedulable fields match.
     */
    public function test_passes_for_availability_create_when_schedulable_fields_match(): void
    {
        // Arrange: Create context for Availability CREATE with matching schedulable fields
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: [
                'schedulable_id' => 123,
                'schedulable_type' => get_class($model),
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 123,
            schedulableType: get_class($model)
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with matching fields
    }

    /**
     * Test that validation fails for Availability CREATE when schedulable_id does not match.
     */
    public function test_fails_for_availability_create_when_schedulable_id_mismatch(): void
    {
        // Arrange: Create context for Availability CREATE with mismatched schedulable_id
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: ['schedulable_id' => 999]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 999,
            schedulableType: null
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_id',
                'Schedulable ID mismatch. Expected: 123, Got: 999'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for ID mismatch
    }

    /**
     * Test that validation fails for Availability CREATE when schedulable_type does not match.
     */
    public function test_fails_for_availability_create_when_schedulable_type_mismatch(): void
    {
        // Arrange: Create context for Availability CREATE with mismatched schedulable_type
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: ['schedulable_type' => 'Different\\Model\\Class']
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: null,
            schedulableType: 'Different\\Model\\Class'
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_type',
                sprintf(
                    'Schedulable type mismatch. Expected: %s, Got: Different\\Model\\Class',
                    get_class($model)
                )
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for type mismatch
    }

    /**
     * Test that validation uses loose comparison for schedulable_id (string vs int).
     */
    public function test_uses_loose_comparison_for_schedulable_id(): void
    {
        // Arrange: Create context with string schedulable_id that should match integer model ID
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: [
                'schedulable_id' => '123',
                'schedulable_type' => get_class($model),
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: '123',
            schedulableType: get_class($model)
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Should pass with loose comparison (123 == '123')
    }

    /**
     * Test that validation uses strict comparison for schedulable_type.
     */
    public function test_uses_strict_comparison_for_schedulable_type(): void
    {
        // Arrange: Create context with exact schedulable_type match
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: [
                'schedulable_id' => 123,
                'schedulable_type' => get_class($model),
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 123,
            schedulableType: get_class($model)
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Should pass with strict comparison (===)
    }

    /**
     * Test that validation handles UPDATE for Schedule entity (owner fields immutable).
     */
    public function test_handles_update_for_schedule_entity(): void
    {
        // Arrange: Create context attempting to change schedulable_id during Schedule update
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForUpdateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: ['schedulable_id' => 999]
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_id',
                "Field 'schedulable_id' cannot be changed. The owner cannot be modified."
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for immutable field
    }

    /**
     * Test that validation handles UPDATE for Impediment entity (owner fields immutable).
     */
    public function test_handles_update_for_impediment_entity(): void
    {
        // Arrange: Create context attempting to change schedulable_type during Impediment update
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForUpdateOperation(
            entityType: EntityType::IMPEDIMENT,
            schedulable: $model,
            data: ['schedulable_type' => 'Different\\Model\\Class']
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_type',
                "Field 'schedulable_type' cannot be changed. The owner cannot be modified."
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for immutable field
    }

    /**
     * Test that validation fails for UPDATE when both owner fields are changed.
     */
    public function test_fails_for_update_when_both_owner_fields_changed(): void
    {
        // Arrange: Create context attempting to change both owner fields during update
        $model = $this->createModelStub(id: 123);
        $context = $this->createContextForUpdateOperation(
            entityType: EntityType::AVAILABILITY,
            schedulable: $model,
            data: [
                'schedulable_id' => 999,
                'schedulable_type' => 'Different\\Model\\Class',
            ]
        );

        $violations = [];
        $context->expects($this->exactly(2))
            ->method('setViolation')
            ->willReturnCallback(function (string $field, string $message) use (&$violations): void {
                $violations[$field] = $message;
            });

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Two violations should be set for both fields
        $this->assertCount(2, $violations);
        $this->assertArrayHasKey('schedulable_id', $violations);
        $this->assertArrayHasKey('schedulable_type', $violations);
        $this->assertSame(
            "Field 'schedulable_id' cannot be changed. The owner cannot be modified.",
            $violations['schedulable_id']
        );
        $this->assertSame(
            "Field 'schedulable_type' cannot be changed. The owner cannot be modified.",
            $violations['schedulable_type']
        );
    }

    /**
     * Test that validation handles different backslash representations in schedulable_type.
     */
    public function test_handles_different_backslash_representations(): void
    {
        // Arrange: Create context with escaped backslashes in schedulable_type
        $model = $this->createModelStub(id: 123);
        $modelClass = get_class($model);
        $modelClassDoubleBackslash = str_replace('\\', '\\\\', $modelClass);

        $context = $this->createContextForCreateOperation(
            entityType: EntityType::SCHEDULE,
            schedulable: $model,
            data: [
                'schedulable_id' => 123,
                'schedulable_type' => $modelClassDoubleBackslash,
            ]
        );

        $this->configureContextGetMethod(
            context: $context,
            schedulableId: 123,
            schedulableType: $modelClassDoubleBackslash
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'schedulable_type',
                sprintf(
                    'Schedulable type mismatch. Expected: %s, Got: %s',
                    get_class($model),
                    $modelClassDoubleBackslash
                )
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for different string representation
    }

    /**
     * Create a mock model stub with specified ID.
     *
     * @param int $id
     * @return Model
     */
    private function createModelStub(int $id): Model
    {
        // Create a simple stub without custom constructor to avoid Laravel's constructor issues
        $model = new class extends Model {
            public function getKey()
            {
                return $this->getAttribute('id') ?? 0;
            }
        };

        // Set the ID as an attribute instead of constructor parameter
        $model->setAttribute('id', $id);

        return $model;
    }

    /**
     * Create a validation context mock for CREATE operation.
     *
     * @param EntityType $entityType
     * @param Model|null $schedulable
     * @param array<string, mixed> $data
     * @return MockObject&ValidationContextInterface
     */
    private function createContextForCreateOperation(
        EntityType $entityType,
        ?Model $schedulable,
        array $data = []
    ): MockObject {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getSchedulable')->willReturn($schedulable);
        $context->method('safeData')->willReturn($data);

        return $context;
    }

    /**
     * Create a validation context mock for UPDATE operation.
     *
     * @param EntityType $entityType
     * @param Model $schedulable
     * @param array<string, mixed> $data
     * @return MockObject&ValidationContextInterface
     */
    private function createContextForUpdateOperation(
        EntityType $entityType,
        Model $schedulable,
        array $data = []
    ): MockObject {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getSchedulable')->willReturn($schedulable);
        $context->method('safeData')->willReturn($data);

        return $context;
    }

    /**
     * Create a validation context mock for DELETE operation.
     *
     * @param EntityType $entityType
     * @param Model $schedulable
     * @return MockObject&ValidationContextInterface
     */
    private function createContextForDeleteOperation(
        EntityType $entityType,
        Model $schedulable
    ): MockObject {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getOperation')->willReturn(OperationType::DELETE);
        $context->method('getSchedulable')->willReturn($schedulable);
        $context->method('safeData')->willReturn([]);

        return $context;
    }

    /**
     * Configure context get method for schedulable fields.
     *
     * @param MockObject&ValidationContextInterface $context
     * @param mixed $schedulableId
     * @param string|null $schedulableType
     */
    private function configureContextGetMethod(
        MockObject $context,
        mixed $schedulableId,
        ?string $schedulableType
    ): void {
        $context->method('get')->willReturnCallback(function (string $key) use ($schedulableId, $schedulableType): mixed {
            return match ($key) {
                'schedulable_id' => $schedulableId,
                'schedulable_type' => $schedulableType,
                default => null,
            };
        });
    }
}
