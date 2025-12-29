<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Validation\Rules\SchedulableConsistencyRule;
use Tests\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class SchedulableConsistencyRuleTest extends TestCase
{
    private SchedulableConsistencyRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new SchedulableConsistencyRule();
    }

    /**
     * Test that validation passes when schedulable info matches availability.
     */
    public function test_passes_when_schedulable_info_matches(): void
    {
        // Arrange: Create context with schedulable info that matches availability
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when schedulable info matches availability
    }

    /**
     * Test that validation fails when schedulable_id does not match.
     */
    public function test_fails_when_schedulable_id_mismatch(): void
    {
        // Arrange: Context where schedulable_id differs from availability's schedulable_id
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 999,
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', "Schedulable information does not match the availability's schedulable");

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for mismatched schedulable_id
    }

    /**
     * Test that validation fails when schedulable_type does not match.
     */
    public function test_fails_when_schedulable_type_mismatch(): void
    {
        // Arrange: Context where schedulable_type differs from availability's schedulable_type
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: 'App\\Models\\Team'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', "Schedulable information does not match the availability's schedulable");

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for mismatched schedulable_type
    }

    /**
     * Test that validation fails when both schedulable fields mismatch.
     */
    public function test_fails_when_both_schedulable_fields_mismatch(): void
    {
        // Arrange: Context where both schedulable_id and schedulable_type mismatch
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 999,
            schedulableType: 'App\\Models\\Team'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', "Schedulable information does not match the availability's schedulable");

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for mismatched schedulable info
    }

    /**
     * Test that validation fails when schedulable_id is missing.
     */
    public function test_fails_when_schedulable_id_missing(): void
    {
        // Arrange: Context missing schedulable_id field
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasSchedulableId: false,
            hasSchedulableType: true,
            schedulableType: 'App\\Models\\User'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', 'Schedulable information is required');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for missing schedulable_id
    }

    /**
     * Test that validation fails when schedulable_type is missing.
     */
    public function test_fails_when_schedulable_type_missing(): void
    {
        // Arrange: Context missing schedulable_type field
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasSchedulableId: true,
            hasSchedulableType: false,
            schedulableId: 123
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', 'Schedulable information is required');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for missing schedulable_type
    }

    /**
     * Test that validation passes when availability_id is missing.
     */
    public function test_passes_when_availability_id_missing(): void
    {
        // Arrange: Context missing availability_id field
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: false,
            hasSchedulableId: true,
            hasSchedulableType: true,
            schedulableId: 123,
            schedulableType: 'App\\Models\\User'
        );

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when availability_id is missing
    }

    /**
     * Test that validation passes when availability is not found.
     */
    public function test_passes_when_availability_not_found(): void
    {
        // Arrange: Context referencing non-existent availability
        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn(null);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 999,
            schedulableId: 123,
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when availability is not found
    }

    /**
     * Test that validation passes for IMPEDIMENT entity type.
     */
    public function test_passes_for_impediment_entity_type(): void
    {
        // Arrange: Context for IMPEDIMENT entity type with matching schedulable info
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::IMPEDIMENT,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule for IMPEDIMENT
        $this->rule->validate($context);

        // Assert: No violations should occur for IMPEDIMENT entity type
    }

    /**
     * Test that validation does not apply for AVAILABILITY entity type.
     */
    public function test_does_not_apply_for_availability_entity_type(): void
    {
        // Arrange: Context for AVAILABILITY entity type
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);

        $context->expects($this->once())->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Validation should skip early for AVAILABILITY entity type
    }

    /**
     * Test that validation does not apply for UPDATE operation.
     */
    public function test_does_not_apply_for_update_operation(): void
    {
        // Arrange: Context for UPDATE operation on SCHEDULE entity
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);

        $context->expects($this->once())->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->expects($this->once())->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule for UPDATE
        $this->rule->validate($context);

        // Assert: Validation should skip early for UPDATE operation (CREATE only rule)
    }

    /**
     * Test that validation does not apply for DELETE operation.
     */
    public function test_does_not_apply_for_delete_operation(): void
    {
        // Arrange: Context for DELETE operation on SCHEDULE entity
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::DELETE);

        $context->expects($this->once())->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->expects($this->once())->method('getOperation')->willReturn(OperationType::DELETE);
        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule for DELETE
        $this->rule->validate($context);

        // Assert: Validation should skip early for DELETE operation (CREATE only rule)
    }

    /**
     * Test that validation handles integer schedulable_id comparison.
     */
    public function test_handles_integer_schedulable_id_comparison(): void
    {
        // Arrange: Context with string schedulable_id that matches integer in availability
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: '123', // String version
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should pass with loose comparison of numeric values
    }

    /**
     * Test that validation handles different backslash representations.
     */
    public function test_handles_different_backslash_representations(): void
    {
        // Arrange: Context with single backslash vs double backslash in availability
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: 'App\Models\User' // Single backslash
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should pass with normalized backslash comparison
    }

    /**
     * Test that validation works with availability service from context.
     */
    public function test_works_with_availability_service_from_context(): void
    {
        // Arrange: Context that uses getAvailabilityService() method
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->expects($this->once())
            ->method('find')
            ->with(456)
            ->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: AvailabilityService::find() should be called with correct ID
    }

    /**
     * Test that validation works with App::make for repository.
     */
    public function test_works_with_app_make_for_repository(): void
    {
        // Arrange: Standard test setup to verify App::make doesn't break validation
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur despite App::make calls
    }

    /**
     * Test that validation handles different numeric types for schedulable_id.
     */
    public function test_handles_different_numeric_types_for_schedulable_id(): void
    {
        // Arrange: Integer in availability, string in context
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => 'App\\Models\\User',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: '123',
            schedulableType: 'App\\Models\\User'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should pass with loose numeric comparison
    }

    /**
     * Test that validation works with empty string schedulable_type.
     */
    public function test_fails_with_empty_string_schedulable_type(): void
    {
        // Arrange: Both availability and context have empty string schedulable_type
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 123,
            'schedulable_type' => '',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 123,
            schedulableType: ''
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);

        // Expect violation for empty schedulable_type
        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', 'Schedulable information cannot be empty');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should fail with empty schedulable_type
    }
    /**
     * Test that rule description is available.
     */
    public function test_has_description(): void
    {
        // Act: Get rule description
        $description = $this->rule->getDescription();

        // Assert: Description should not be empty
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
    }

    /**
     * Test that validation works with null schedulable values.
     */
    public function test_fails_with_null_schedulable_values(): void
    {
        // Arrange: Context with null schedulable values
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: false,
            hasSchedulableId: true,
            hasSchedulableType: true,
            schedulableId: null,
            schedulableType: null
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', 'Schedulable information cannot be empty');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should fail with null values
    }

    /**
     * Test that validation works with numeric zero schedulable_id.
     */
    public function test_passes_with_numeric_zero_schedulable_id(): void
    {
        // Arrange: Context with zero schedulable_id
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 0,
            'schedulable_type' => 'App\\Models\\System',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 0,
            schedulableType: 'App\\Models\\System'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should pass with zero value (0 is not empty() in PHP for integers)
    }

    /**
     * Test that validation fails when both schedulable fields are empty.
     */
    public function test_fails_when_both_schedulable_fields_empty(): void
    {
        // Arrange: Context with empty schedulable_id and schedulable_type
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: false,
            hasSchedulableId: true,
            hasSchedulableType: true,
            schedulableId: '',
            schedulableType: ''
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with($this->rule, 'schedulable', 'Schedulable information cannot be empty');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should fail with empty strings
    }

    /**
     * Test that validation works with null schedulable values.
     */
    public function test_works_with_null_schedulable_values(): void
    {
        // Arrange: Context with null schedulable values
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: false,
            hasSchedulableId: true,
            hasSchedulableType: true,
            schedulableId: null,
            schedulableType: null
        );

        // Assert: setViolationFromRule should be called with correct arguments
        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'schedulable', // champ attendu
                'Schedulable information cannot be empty' // message attendu
            );

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should fail with null values
    }

    /**
     * Test that validation works with numeric zero schedulable_id.
     */
    public function test_works_with_numeric_zero_schedulable_id(): void
    {
        // Arrange: Context with zero schedulable_id
        $availability = $this->createMockAvailability([
            'id' => 456,
            'schedulable_id' => 0,
            'schedulable_type' => 'App\\Models\\System',
        ]);

        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn($availability);

        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasAvailabilityId: true,
            availabilityId: 456,
            schedulableId: 0,
            schedulableType: 'App\\Models\\System'
        );

        $context->method('getAvailabilityService')->willReturn($availabilityService);
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the consistency validation rule
        $this->rule->validate($context);

        // Assert: Should pass with zero value
    }

    /**
     * Create a validation context mock with specified configuration.
     *
     * @param array<string, bool|int|string|null> $configuration
     */
    private function createValidationContext(
        EntityType $entityType,
        OperationType $operationType,
        ?bool $hasAvailabilityId = null,
        ?bool $hasSchedulableId = null,
        ?bool $hasSchedulableType = null,
        mixed $availabilityId = null,
        mixed $schedulableId = null,
        mixed $schedulableType = null
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);

        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getOperation')->willReturn($operationType);

        $context->method('has')->willReturnCallback(
            function (string $key) use ($hasAvailabilityId, $hasSchedulableId, $hasSchedulableType): bool {
                return match ($key) {
                    'availability_id' => $hasAvailabilityId ?? true,
                    'schedulable_id' => $hasSchedulableId ?? true,
                    'schedulable_type' => $hasSchedulableType ?? true,
                    default => false,
                };
            }
        );

        $context->method('get')->willReturnCallback(
            function (string $key) use ($availabilityId, $schedulableId, $schedulableType): mixed {
                return match ($key) {
                    'availability_id' => $availabilityId,
                    'schedulable_id' => $schedulableId,
                    'schedulable_type' => $schedulableType,
                    default => null,
                };
            }
        );

        return $context;
    }

    /**
     * Create a mock availability with specific attributes.
     *
     * @param array<string, mixed> $attributes
     */
    private function createMockAvailability(array $attributes = []): Availability
    {
        return new class($attributes) extends Availability {
            public function __construct(array $attributes = [])
            {
                parent::__construct();

                foreach ($attributes as $key => $value) {
                    $this->$key = $value;
                }
            }
        };
    }
}
