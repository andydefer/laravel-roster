<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Validation\Rules\AvailabilityOwnershipRule;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for AvailabilityOwnershipRule validation logic.
 *
 * Tests ownership validation between availability entities and schedulable resources,
 * ensuring that availabilities belong to the correct schedulable owners.
 */
#[AllowMockObjectsWithoutExpectations]
final class AvailabilityOwnershipRuleTest extends TestCase
{
    private AvailabilityOwnershipRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AvailabilityOwnershipRule();
    }

    /**
     * Test that validation passes when availability belongs to schedulable for CREATE operation.
     */
    public function test_passes_when_availability_belongs_to_schedulable_for_create(): void
    {
        // Arrange: Create a schedulable and an availability that belongs to it
        $schedulable = $this->createSchedulableMock(id: 123);
        $availability = $this->createAvailabilityMock(
            id: 456,
            schedulableId: 123,
            schedulableClass: get_class($schedulable)
        );

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 456,
            returnValue: $availability
        );

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            availabilityService: $availabilityService
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 456);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should be set for valid ownership
    }

    /**
     * Test that validation fails when availability does not belong to schedulable.
     */
    public function test_fails_when_availability_does_not_belong_to_schedulable(): void
    {
        // Arrange: Create availability with different schedulable owner
        $schedulable = $this->createSchedulableMock(id: 123);
        $availability = $this->createAvailabilityMock(
            id: 456,
            schedulableId: 999, // Different owner
            schedulableClass: get_class($schedulable)
        );

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 456,
            returnValue: $availability
        );

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            availabilityService: $availabilityService
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 456);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('availability_id'),
                $this->identicalTo('Referenced availability period does not belong to this schedulable entity')
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid ownership
    }

    /**
     * Test that validation fails with invalid availability ID.
     */
    public function test_fails_with_invalid_availability_id(): void
    {
        // Arrange: Configure service to return null for non-existent availability
        $schedulable = $this->createSchedulableMock(id: 123);

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 999,
            returnValue: null
        );

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            availabilityService: $availabilityService
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 999);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('availability_id'),
                $this->identicalTo('Referenced availability period does not exist or is invalid')
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid availability ID
    }

    /**
     * Test that validation passes when schedulable is not a Model.
     */
    public function test_passes_when_schedulable_is_not_model(): void
    {
        // Arrange: Create context with null schedulable
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: null
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 456);

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur when schedulable is not a Model
    }

    /**
     * Test that validation fails when availability_id is missing for CREATE operation.
     */
    public function test_fails_when_availability_id_missing_for_create(): void
    {
        // Arrange: Create context without availability_id for CREATE operation
        $schedulable = $this->createSchedulableMock(id: 123);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: false);
        $this->configureContextGetMethod($context, availabilityId: null);

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('availability_id'),
                $this->identicalTo('Schedule or impediment must be linked to an availability period')
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Violation should be set for missing availability_id in CREATE
    }

    /**
     * Test that validation passes when availability_id is missing for UPDATE with existing entity.
     */
    public function test_passes_when_availability_id_missing_for_update_with_existing_entity(): void
    {
        // Arrange: Create UPDATE context with existing entity having availability_id
        $schedulable = $this->createSchedulableMock(id: 123);
        $existingEntity = $this->createScheduleEntityStub(availabilityId: 456);

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            currentEntity: $existingEntity
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: false);
        $this->configureContextGetMethod($context, availabilityId: null);

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation needed when keeping existing availability_id in UPDATE
    }

    /**
     * Test that validation handles UPDATE with new availability_id.
     */
    public function test_handles_update_with_new_availability_id(): void
    {
        // Arrange: Create UPDATE context with new availability_id
        $schedulable = $this->createSchedulableMock(id: 123);
        $existingEntity = $this->createScheduleEntityStub(availabilityId: 456);

        $availability = $this->createAvailabilityMock(
            id: 789,
            schedulableId: 123,
            schedulableClass: get_class($schedulable)
        );

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 789,
            returnValue: $availability
        );

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            currentEntity: $existingEntity,
            availabilityService: $availabilityService
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 789);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: New availability_id should be validated against schedulable
    }

    /**
     * Test that validation passes for IMPEDIMENT entity type.
     */
    public function test_passes_for_impediment_entity_type(): void
    {
        // Arrange: Test with IMPEDIMENT entity type
        $schedulable = $this->createSchedulableMock(id: 123);
        $availability = $this->createAvailabilityMock(
            id: 456,
            schedulableId: 123,
            schedulableClass: get_class($schedulable)
        );

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 456,
            returnValue: $availability
        );

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);
        $context->method('getSchedulable')->willReturn($schedulable);
        $context->method('getCurrentEntity')->willReturn(null);
        $context->method('getAvailabilityService')->willReturn($availabilityService);

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 456);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Ownership validation should work for IMPEDIMENT entity type
    }

    /**
     * Test that validation fails when availability has wrong schedulable type.
     */
    public function test_fails_when_availability_has_wrong_schedulable_type(): void
    {
        // Arrange: Create availability with incorrect schedulable type
        $schedulable = $this->createSchedulableMock(id: 123);
        $availability = $this->createAvailabilityMock(
            id: 456,
            schedulableId: 123,
            schedulableClass: 'Different\Model\Class' // Wrong type
        );

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 456,
            returnValue: $availability
        );

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            availabilityService: $availabilityService
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 456);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('availability_id'),
                $this->identicalTo('Referenced availability period does not belong to this schedulable entity')
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Violation should be set for mismatched schedulable type
    }

    /**
     * Test that validation passes when no availability ID is provided for UPDATE.
     */
    public function test_passes_when_no_availability_id_provided_for_update(): void
    {
        // Arrange: Create UPDATE context without availability_id and no current entity
        $schedulable = $this->createSchedulableMock(id: 123);

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            currentEntity: null
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: false);
        $this->configureContextGetMethod($context, availabilityId: null);

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation needed when updating without changing availability
    }

    /**
     * Test that validation handles UPDATE with current entity having no availability_id.
     */
    public function test_handles_update_with_current_entity_having_no_availability_id(): void
    {
        // Arrange: Create UPDATE context with entity missing availability_id
        $schedulable = $this->createSchedulableMock(id: 123);
        $existingEntity = $this->createScheduleEntityStub(availabilityId: null);

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            currentEntity: $existingEntity
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: false);
        $this->configureContextGetMethod($context, availabilityId: null);

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation needed when entity has no availability_id
    }

    /**
     * Test that validation handles availability service returning null gracefully.
     */
    public function test_handles_availability_service_returning_null_gracefully(): void
    {
        // Arrange: Configure service to return null (availability not found)
        $schedulable = $this->createSchedulableMock(id: 123);

        $availabilityService = $this->configureAvailabilityServiceWithFind(
            availabilityId: 456,
            returnValue: null
        );

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            availabilityService: $availabilityService
        );

        $this->configureContextHasMethod($context, hasAvailabilityId: true);
        $this->configureContextGetMethod($context, availabilityId: 456);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('availability_id'),
                $this->identicalTo('Referenced availability period does not exist or is invalid')
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Violation should be set when availability is not found
    }

    /**
     * Test that getDescription returns a detailed description.
     */
    public function test_get_description_returns_detailed_information(): void
    {
        // Act: Get description
        $description = $this->rule->getDescription();

        // Assert: Verify description contains key information
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
        $this->assertStringContainsString('validates', $description);
        $this->assertStringContainsString('schedule', $description);
        $this->assertStringContainsString('impediment', $description);
        $this->assertStringContainsString('availability', $description);
        $this->assertStringContainsString('ownership', $description);
        $this->assertStringContainsString('CREATE and UPDATE', $description);
    }

    /**
     * Create a schedulable model mock.
     *
     * @param int $id
     * @return Model
     */
    private function createSchedulableMock(int $id): Model
    {
        $mock = $this->createMock(Model::class);

        $mock->method('__get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'id' => $id,
                default => null,
            }
        );

        $mock->id = $id;

        return $mock;
    }

    /**
     * Create an availability model mock.
     *
     * @param int $id
     * @param int $schedulableId
     * @param string|null $schedulableClass
     * @return Availability
     */
    private function createAvailabilityMock(int $id, int $schedulableId, ?string $schedulableClass = null): Availability
    {
        $mock = $this->createMock(Availability::class);

        $mock->method('__get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'id' => $id,
                'schedulable_id' => $schedulableId,
                'schedulable_type' => $schedulableClass,
                default => null,
            }
        );

        $mock->id = $id;
        $mock->schedulable_id = $schedulableId;

        if ($schedulableClass !== null) {
            $mock->schedulable_type = $schedulableClass;
        }

        return $mock;
    }

    /**
     * Create a stub schedule entity.
     *
     * @param int|null $availabilityId
     * @return object
     */
    private function createScheduleEntityStub(?int $availabilityId): object
    {
        return new class($availabilityId) {
            public $availability_id;

            public function __construct(?int $availabilityId)
            {
                $this->availability_id = $availabilityId;
            }
        };
    }

    /**
     * Create a validation context mock with given parameters.
     *
     * @param OperationType $operationType
     * @param Model|null $schedulable
     * @param object|null $currentEntity
     * @param AvailabilityService|null $availabilityService
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContext(
        OperationType $operationType,
        ?Model $schedulable = null,
        ?object $currentEntity = null,
        ?AvailabilityService $availabilityService = null
    ): MockObject {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn($operationType);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getSchedulable')->willReturn($schedulable);
        $context->method('getCurrentEntity')->willReturn($currentEntity);

        if ($availabilityService !== null) {
            $context->method('getAvailabilityService')->willReturn($availabilityService);
        }

        return $context;
    }

    /**
     * Configure the has() method on the validation context.
     *
     * @param MockObject&ValidationContextInterface $context
     * @param bool $hasAvailabilityId
     */
    private function configureContextHasMethod(MockObject $context, bool $hasAvailabilityId): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'availability_id' => $hasAvailabilityId,
                default => false,
            }
        );
    }

    /**
     * Configure the get() method on the validation context.
     *
     * @param MockObject&ValidationContextInterface $context
     * @param int|null $availabilityId
     */
    private function configureContextGetMethod(MockObject $context, ?int $availabilityId): void
    {
        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'availability_id' => $availabilityId,
                default => null,
            }
        );
    }

    /**
     * Configure an availability service mock with find() method expectation.
     *
     * @param int $availabilityId
     * @param Availability|null $returnValue
     * @return AvailabilityService
     */
    private function configureAvailabilityServiceWithFind(int $availabilityId, ?Availability $returnValue): AvailabilityService
    {
        $availabilityService = $this->createMock(AvailabilityService::class);

        $availabilityService->expects($this->once())
            ->method('find')
            ->with($availabilityId)
            ->willReturn($returnValue);

        return $availabilityService;
    }
}
