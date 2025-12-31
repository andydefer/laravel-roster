<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Support\Carbon;
use Mockery;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\TimeSlotDateTimeRule;
use Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;

/**
 * Unit tests for TimeSlotDateTimeRule validation logic.
 *
 * Tests datetime range validation for schedule and impediment entities,
 * including cross-day validations, timezone handling, and partial updates.
 */
final class TimeSlotDateTimeRuleTest extends TestCase
{
    private TimeSlotDateTimeRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new TimeSlotDateTimeRule();
    }

    /**
     * Clean up Mockery after each test.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that validation passes when CREATE operation has valid datetime range.
     */
    public function test_passes_when_create_operation_with_valid_datetime_range(): void
    {
        // Arrange: Create context with valid datetime range for CREATE operation
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
    }

    /**
     * Test that validation fails when CREATE operation has end datetime before start datetime.
     */
    public function test_fails_when_create_operation_with_end_before_start(): void
    {
        // Arrange: Create context with invalid datetime range (end before start)
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 12:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 08:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_range',
                'End datetime must be after start datetime'
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation fails when CREATE operation has end datetime equal to start datetime.
     */
    public function test_fails_when_create_operation_with_end_equal_to_start(): void
    {
        // Arrange: Create context with equal start and end datetimes
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 09:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_range',
                'End datetime must be after start datetime'
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation skips when CREATE operation is missing start datetime.
     */
    public function test_skips_validation_when_create_operation_missing_start_datetime(): void
    {
        // Arrange: Create context with missing start datetime
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false,
            startDatetime: null,
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation skips when CREATE operation is missing end datetime.
     */
    public function test_skips_validation_when_create_operation_missing_end_datetime(): void
    {
        // Arrange: Create context with missing end datetime
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: false,
            endDatetime: null
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation passes when UPDATE operation has both datetimes updated with valid range.
     */
    public function test_passes_when_update_operation_with_both_datetimes_updated_valid(): void
    {
        // Arrange: Create context with valid update of both datetimes
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 10:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 18:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation fails when UPDATE operation has both datetimes updated with invalid range.
     */
    public function test_fails_when_update_operation_with_both_datetimes_updated_invalid(): void
    {
        // Arrange: Create context with invalid update of both datetimes
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 12:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 10:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_range',
                'End datetime must be after start datetime'
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation passes when UPDATE operation has only start datetime updated with valid value.
     */
    public function test_passes_when_update_operation_with_only_start_updated_valid(): void
    {
        // Arrange: Create context with valid update of only start datetime
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 08:00:00',
            hasEndDatetime: false,
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation fails when UPDATE operation has only start datetime updated with invalid value.
     */
    public function test_fails_when_update_operation_with_only_start_updated_invalid(): void
    {
        // Arrange: Create context with invalid update of only start datetime
        $existingEnd = '2024-01-01 17:00:00';
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: $existingEnd,
            hasEndDatetime: false,
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: $existingEnd
            )
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_range',
                'End datetime must be after start datetime'
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation passes when UPDATE operation has only end datetime updated with valid value.
     */
    public function test_passes_when_update_operation_with_only_end_updated_valid(): void
    {
        // Arrange: Create context with valid update of only end datetime
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false,
            startDatetime: null,
            hasEndDatetime: true,
            endDatetime: '2024-01-01 19:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation fails when UPDATE operation has only end datetime updated with invalid value.
     */
    public function test_fails_when_update_operation_with_only_end_updated_invalid(): void
    {
        // Arrange: Create context with invalid update of only end datetime
        $existingStart = '2024-01-01 09:00:00';
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false,
            startDatetime: null,
            hasEndDatetime: true,
            endDatetime: '2024-01-01 08:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: $existingStart,
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_range',
                'End datetime must be after start datetime'
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation skips when UPDATE operation has no datetime updates.
     */
    public function test_skips_validation_when_update_operation_with_no_datetime_updates(): void
    {
        // Arrange: Create context with no datetime updates
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false,
            startDatetime: null,
            hasEndDatetime: false,
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation fails when datetime format is invalid.
     */
    public function test_fails_when_datetime_format_is_invalid(): void
    {
        // Arrange: Create context with invalid datetime format
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: 'invalid-date',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_format',
                $this->stringContains('Invalid datetime format')
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation works for SCHEDULE entity type.
     */
    public function test_works_for_schedule_entity(): void
    {
        // Arrange: Create context with SCHEDULE entity type
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation works for IMPEDIMENT entity type.
     */
    public function test_works_for_impediment_entity(): void
    {
        // Arrange: Create context with IMPEDIMENT entity type
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation handles Carbon instances in current entity.
     */
    public function test_handles_carbon_instances_in_current_entity(): void
    {
        // Arrange: Create context with current entity containing Carbon instances
        $startCarbon = Carbon::parse('2024-01-01 09:00:00');
        $endCarbon = Carbon::parse('2024-01-01 17:00:00');

        $entity = Mockery::mock();
        $entity->start_datetime = $startCarbon;
        $entity->end_datetime = $endCarbon;

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 08:00:00',
            hasEndDatetime: false,
            endDatetime: null,
            currentEntity: $entity
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation handles timezone-aware datetime strings.
     */
    public function test_handles_timezone_aware_datetimes(): void
    {
        // Arrange: Create context with timezone-aware datetime strings
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01T09:00:00+02:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01T17:00:00+02:00'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation validates across different dates.
     */
    public function test_validates_across_different_dates(): void
    {
        // Arrange: Create context with datetime range spanning multiple days
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 22:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-02 06:00:00'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
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
        $this->assertStringContainsString('datetime', $description);
        $this->assertStringContainsString('validate', $description);
    }

    /**
     * Test that validation passes for UPDATE with only start datetime when end datetime is null.
     */
    public function test_passes_for_update_with_only_start_when_end_is_null(): void
    {
        // Arrange: Create context with only start datetime update and null end datetime
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 08:00:00',
            hasEndDatetime: false,
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: null // Explicitement null
            )
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation passes for UPDATE with only end datetime when start datetime is null.
     */
    public function test_passes_for_update_with_only_end_when_start_is_null(): void
    {
        // Arrange: Create context with only end datetime update and null start datetime
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false,
            startDatetime: null,
            hasEndDatetime: true,
            endDatetime: '2024-01-01 19:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: null, // Explicitement null
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation skips for non-CREATE/UPDATE operations.
     */
    public function test_skips_for_non_create_update_operations(): void
    {
        // Arrange: Create context with DELETE operation
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::DELETE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);

        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Test that validation handles invalid datetime format in end datetime.
     */
    public function test_fails_when_end_datetime_format_is_invalid(): void
    {
        // Arrange: Create context with invalid end datetime format
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: 'invalid-date'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'datetime_format',
                $this->stringContains('Invalid datetime format')
            );

        // Act: Execute validation
        $this->rule->validate($context);
    }

    /**
     * Create a validation context mock with specified configuration.
     *
     * @param OperationType $operationType The operation type (CREATE or UPDATE)
     * @param EntityType $entityType The entity type
     * @param bool $hasStartDatetime Whether start_datetime is present
     * @param string|null $startDatetime The start datetime string or null
     * @param bool $hasEndDatetime Whether end_datetime is present
     * @param string|null $endDatetime The end datetime string or null
     * @param object|null $currentEntity The current entity for UPDATE operations
     */
    private function createValidationContext(
        OperationType $operationType,
        EntityType $entityType,
        bool $hasStartDatetime,
        ?string $startDatetime,
        bool $hasEndDatetime,
        ?string $endDatetime,
        ?object $currentEntity = null
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn($operationType);
        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getCurrentEntity')->willReturn($currentEntity);

        $context->method('has')->willReturnCallback(
            function (string $key) use ($hasStartDatetime, $hasEndDatetime): bool {
                return match ($key) {
                    'start_datetime' => $hasStartDatetime,
                    'end_datetime' => $hasEndDatetime,
                    default => false,
                };
            }
        );

        if ($hasStartDatetime || $hasEndDatetime) {
            $context->method('get')->willReturnCallback(
                function (string $key) use ($startDatetime, $endDatetime): mixed {
                    return match ($key) {
                        'start_datetime' => $startDatetime,
                        'end_datetime' => $endDatetime,
                        default => null,
                    };
                }
            );
        }

        return $context;
    }

    /**
     * Create a mock entity with the given datetime values.
     *
     * @param string|null $startDatetime The start datetime string or null
     * @param string|null $endDatetime The end datetime string or null
     */
    private function createMockEntity(?string $startDatetime, ?string $endDatetime): object
    {
        return new class($startDatetime, $endDatetime) {
            public function __construct(
                public ?string $start_datetime = null,
                public ?string $end_datetime = null
            ) {}
        };
    }
}
