<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Domain\DTOs\ConflictResult;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\AvailabilityOverlapRule;
use Tests\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class AvailabilityOverlapRuleTest extends TestCase
{
    private AvailabilityOverlapRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AvailabilityOverlapRule();
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
        $this->assertStringContainsString('availability', $description);
        $this->assertStringContainsString('overlap', $description);
        $this->assertStringContainsString('new or updated', $description);
        $this->assertStringContainsString('temporal conflicts', $description);
    }

    /**
     * Test that validation passes when there are no overlaps.
     */
    public function test_passes_when_no_overlaps(): void
    {
        // Arrange: Create a mock context with complete availability data
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithCompleteAvailabilityData($context);

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willReturn(ConflictResult::noConflict());

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should be recorded when no overlaps exist
    }

    /**
     * Test that validation fails when overlaps are detected.
     */
    public function test_fails_when_overlaps_detected(): void
    {
        // Arrange: Create a mock context that will trigger an overlap conflict
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithCompleteAvailabilityData($context);

        $conflictResult = new ConflictResult(
            hasConflicts: true,
            conflictingSchedules: [],
            conflictingImpediments: [],
            message: 'Overlap detected with existing availability'
        );

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willReturn($conflictResult);

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('overlap'),
                $this->identicalTo('Overlap detected with existing availability')
            );

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for the detected overlap
    }

    /**
     * Test that validation passes when schedulable is not provided.
     */
    public function test_passes_when_schedulable_not_provided(): void
    {
        // Arrange: Context without schedulable (null value)
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: null,
            hasData: true,
            entity: null
        );

        $this->configureContextWithBasicAvailabilityData($context);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when schedulable is missing
    }

    /**
     * Test that validation passes when required fields are missing.
     */
    public function test_passes_when_required_fields_missing(): void
    {
        // Arrange: Context with incomplete data (daily_end missing)
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithMissingRequiredFields($context);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when required fields are incomplete
    }

    /**
     * Test that validation handles UPDATE operation with existing entity.
     */
    public function test_handles_update_operation_with_existing_entity(): void
    {
        // Arrange: UPDATE operation with complete existing entity data
        $schedulable = $this->createMock(Model::class);
        $entity = $this->createMockAvailabilityEntity();

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            hasData: false,
            entity: $entity
        );

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willReturn(ConflictResult::noConflict());

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule for UPDATE
        $this->rule->validate($context);

        // Assert: No violations should occur when using existing entity data
    }

    /**
     * Test that validation passes on UPDATE when current entity is missing.
     */
    public function test_passes_on_update_when_current_entity_missing(): void
    {
        // Arrange: UPDATE operation without existing entity
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: null,
            hasData: true,
            entity: null
        );

        $this->configureContextWithBasicAvailabilityData($context);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur on UPDATE without existing entity
    }

    /**
     * Test that validation handles mixed context and entity data for UPDATE.
     */
    public function test_handles_mixed_context_and_entity_data_for_update(): void
    {
        // Arrange: UPDATE with partial context data overriding entity data
        $schedulable = $this->createMock(Model::class);
        $entity = $this->createMockAvailabilityEntity();
        $entity->validity_end = '2038-06-30';

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            hasData: true,
            entity: $entity
        );

        $this->configureContextWithPartialUpdateData($context);

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willReturn(ConflictResult::noConflict());

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule with mixed data sources
        $this->rule->validate($context);

        // Assert: No violations should occur with mixed context and entity data
    }

    /**
     * Test that validation excludes current entity ID on UPDATE.
     */
    public function test_excludes_current_entity_id_on_update(): void
    {
        // Arrange: UPDATE operation that should exclude current entity from conflict check
        $schedulable = $this->createMock(Model::class);
        $entity = $this->createMockAvailabilityEntity();

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            schedulable: $schedulable,
            hasData: false,
            entity: $entity
        );

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->expects($this->once())
            ->method('checkAvailabilityConflicts')
            ->with(
                $this->identicalTo($schedulable),
                $this->identicalTo([
                    'daily_start' => '09:00:00',
                    'daily_end' => '17:00:00',
                    'days' => ['monday', 'tuesday'],
                    'validity_start' => '2038-01-01',
                    'validity_end' => '2038-12-31',
                    'type' => 'consultation',
                ]),
                $this->identicalTo(123) // Current entity ID should be excluded
            )
            ->willReturn(ConflictResult::noConflict());

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: Conflict check should exclude current entity ID on UPDATE
    }

    /**
     * Test that validation handles empty days array.
     */
    public function test_handles_empty_days_array(): void
    {
        // Arrange: Context with empty days array
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithEmptyDaysArray($context);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur with empty days array
    }

    /**
     * Test that validation handles exception gracefully.
     */
    public function test_handles_exception_gracefully(): void
    {
        // Arrange: Context that will trigger an exception in conflict service
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithBasicAvailabilityData($context);

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willThrowException(new Exception('Test exception'));

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule with exception
        $this->rule->validate($context);

        // Assert: No violations should be set when exception occurs
    }

    /**
     * Test that validation works with partial validity dates.
     */
    public function test_works_with_partial_validity_dates(): void
    {
        // Arrange: Context with only validity start date (no end date)
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithPartialValidityDates($context);

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willReturn(ConflictResult::noConflict());

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur with partial validity dates
    }

    /**
     * Test that validation works without type field.
     */
    public function test_works_without_type_field(): void
    {
        // Arrange: Context without type field
        $schedulable = $this->createMock(Model::class);
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            schedulable: $schedulable,
            hasData: true,
            entity: null
        );

        $this->configureContextWithoutTypeField($context);

        $conflictService = $this->createMock(TemporalConflictService::class);
        $conflictService->method('checkAvailabilityConflicts')
            ->willReturn(ConflictResult::noConflict());

        $this->app->instance(TemporalConflictService::class, $conflictService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the overlap validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when type field is missing
    }

    /**
     * Create a validation context mock with specified configuration.
     */
    private function createValidationContext(
        OperationType $operationType,
        ?Model $schedulable,
        bool $hasData,
        ?object $entity
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn($operationType);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getCurrentEntity')->willReturn($entity);
        $context->method('getSchedulable')->willReturn($schedulable);

        return $context;
    }

    /**
     * Configure context with complete availability data for testing.
     */
    private function configureContextWithCompleteAvailabilityData(MockObject $context): void
    {
        $context->method('has')->willReturn(true);

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2038-01-01',
                'validity_end' => '2038-12-31',
                'type' => 'consultation',
                default => null,
            }
        );
    }

    /**
     * Configure context with basic availability data (without validity dates).
     */
    private function configureContextWithBasicAvailabilityData(MockObject $context): void
    {
        $context->method('has')->willReturn(true);

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                default => null,
            }
        );
    }

    /**
     * Configure context with missing required fields.
     */
    private function configureContextWithMissingRequiredFields(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'daily_start' => true,
                'daily_end' => false,
                'days' => true,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'daily_start' => '09:00:00',
                'days' => ['monday', 'tuesday'],
                default => null,
            }
        );
    }

    /**
     * Configure context with partial update data (only validity_end changed).
     */
    private function configureContextWithPartialUpdateData(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'validity_end' => true,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'validity_end' => '2038-12-31',
                default => null,
            }
        );
    }

    /**
     * Configure context with empty days array.
     */
    private function configureContextWithEmptyDaysArray(MockObject $context): void
    {
        $context->method('has')->willReturn(true);

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [],
                default => null,
            }
        );
    }

    /**
     * Configure context with partial validity dates (only start date).
     */
    private function configureContextWithPartialValidityDates(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'daily_start' => true,
                'daily_end' => true,
                'days' => true,
                'validity_start' => true,
                'validity_end' => false,
                'type' => true,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2038-01-01',
                'type' => 'consultation',
                default => null,
            }
        );
    }

    /**
     * Configure context without type field.
     */
    private function configureContextWithoutTypeField(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'daily_start' => true,
                'daily_end' => true,
                'days' => true,
                'validity_start' => true,
                'validity_end' => true,
                'type' => false,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2038-01-01',
                'validity_end' => '2038-12-31',
                default => null,
            }
        );
    }

    /**
     * Create a mock availability entity with test data.
     */
    private function createMockAvailabilityEntity(): object
    {
        return new class {
            public $id = 123;
            public $daily_start = '09:00:00';
            public $daily_end = '17:00:00';
            public $days = ['monday', 'tuesday'];
            public $validity_start = '2038-01-01';
            public $validity_end = '2038-12-31';
            public $type = 'consultation';
        };
    }
}
