<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\MockInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\TimeSlotDateTimeRule;
use Tests\TestCase;

/**
 * Unit tests for TimeSlotDateTimeRule validation logic.
 *
 * Tests datetime range validation for schedule and impediment entities,
 * including cross-day validations, timezone handling, and partial updates.
 */
final class TimeSlotDateTimeRuleTest extends TestCase
{
    private TimeSlotDateTimeRule $rule;
    private Model|MockInterface $schedulable;

    /**
     * Set up the test case with a fresh rule instance and mock schedulable model.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = Mockery::mock(Model::class);
        $this->schedulable->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->schedulable->shouldReceive('getMorphClass')->andReturn('TestModel');

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
            startDatetime: '2024-01-01 09:00:00',
            endDatetime: '2024-01-01 17:00:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation fails when CREATE operation has end datetime before start datetime.
     */
    public function test_fails_when_create_operation_with_end_before_start(): void
    {
        // Arrange: Create context with invalid datetime range (end before start)
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: '2024-01-01 12:00:00',
            endDatetime: '2024-01-01 08:00:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be present for datetime range
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('datetime_range', $context->getViolations());
        $this->assertSame(
            'End datetime must be after start datetime',
            $context->getViolations()['datetime_range']
        );
    }

    /**
     * Test that validation fails when CREATE operation has end datetime equal to start datetime.
     */
    public function test_fails_when_create_operation_with_end_equal_to_start(): void
    {
        // Arrange: Create context with equal start and end datetimes
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: '2024-01-01 09:00:00',
            endDatetime: '2024-01-01 09:00:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be present for datetime range
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('datetime_range', $context->getViolations());
    }

    /**
     * Test that validation skips when CREATE operation is missing start datetime.
     */
    public function test_skips_validation_when_create_operation_missing_start_datetime(): void
    {
        // Arrange: Create context with missing start datetime
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: null,
            endDatetime: '2024-01-01 17:00:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present (validation skipped)
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation skips when CREATE operation is missing end datetime.
     */
    public function test_skips_validation_when_create_operation_missing_end_datetime(): void
    {
        // Arrange: Create context with missing end datetime
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: '2024-01-01 09:00:00',
            endDatetime: null
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present (validation skipped)
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation passes when UPDATE operation has both datetimes updated with valid range.
     */
    public function test_passes_when_update_operation_with_both_datetimes_updated_valid(): void
    {
        // Arrange: Create context with valid update of both datetimes
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            startDatetime: '2024-01-01 10:00:00',
            endDatetime: '2024-01-01 18:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation fails when UPDATE operation has both datetimes updated with invalid range.
     */
    public function test_fails_when_update_operation_with_both_datetimes_updated_invalid(): void
    {
        // Arrange: Create context with invalid update of both datetimes
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            startDatetime: '2024-01-01 12:00:00',
            endDatetime: '2024-01-01 10:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be present for datetime range
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('datetime_range', $context->getViolations());
    }

    /**
     * Test that validation passes when UPDATE operation has only start datetime updated with valid value.
     */
    public function test_passes_when_update_operation_with_only_start_updated_valid(): void
    {
        // Arrange: Create context with valid update of only start datetime
        $existingEnd = '2024-01-01 17:00:00';
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            startDatetime: '2024-01-01 08:00:00',
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: $existingEnd
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
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
            startDatetime: $existingEnd,
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: $existingEnd
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be present for datetime range
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('datetime_range', $context->getViolations());
    }

    /**
     * Test that validation passes when UPDATE operation has only end datetime updated with valid value.
     */
    public function test_passes_when_update_operation_with_only_end_updated_valid(): void
    {
        // Arrange: Create context with valid update of only end datetime
        $existingStart = '2024-01-01 09:00:00';
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            startDatetime: null,
            endDatetime: '2024-01-01 19:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: $existingStart,
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
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
            startDatetime: null,
            endDatetime: '2024-01-01 08:00:00',
            currentEntity: $this->createMockEntity(
                startDatetime: $existingStart,
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be present for datetime range
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('datetime_range', $context->getViolations());
    }

    /**
     * Test that validation skips when UPDATE operation has no datetime updates.
     */
    public function test_skips_validation_when_update_operation_with_no_datetime_updates(): void
    {
        // Arrange: Create context with no datetime updates
        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            startDatetime: null,
            endDatetime: null,
            currentEntity: $this->createMockEntity(
                startDatetime: '2024-01-01 09:00:00',
                endDatetime: '2024-01-01 17:00:00'
            )
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present (validation skipped)
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation fails when datetime format is invalid.
     */
    public function test_fails_when_datetime_format_is_invalid(): void
    {
        // Arrange: Create context with invalid datetime format
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: 'invalid-date',
            endDatetime: '2024-01-01 17:00:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be present for datetime format
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('datetime_format', $context->getViolations());
        $this->assertStringContainsString(
            'Invalid datetime format',
            (string) $context->getViolations()['datetime_format']
        );
    }

    /**
     * Test that validation works for SCHEDULE entity type.
     */
    public function test_works_for_schedule_entity(): void
    {
        // Arrange: Create context with SCHEDULE entity type
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'start_datetime' => '2024-01-01 09:00:00',
                'end_datetime' => '2024-01-01 17:00:00',
            ],
            model: $this->schedulable
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation works for IMPEDIMENT entity type.
     */
    public function test_works_for_impediment_entity(): void
    {
        // Arrange: Create context with IMPEDIMENT entity type
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: [
                'start_datetime' => '2024-01-01 09:00:00',
                'end_datetime' => '2024-01-01 17:00:00',
            ],
            model: $this->schedulable
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
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
            startDatetime: '2024-01-01 08:00:00',
            endDatetime: null,
            currentEntity: $entity
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation handles timezone-aware datetime strings.
     */
    public function test_handles_timezone_aware_datetimes(): void
    {
        // Arrange: Create context with timezone-aware datetime strings
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: '2024-01-01T09:00:00+02:00',
            endDatetime: '2024-01-01T17:00:00+02:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation validates across different dates.
     */
    public function test_validates_across_different_dates(): void
    {
        // Arrange: Create context with datetime range spanning multiple days
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            startDatetime: '2024-01-01 22:00:00',
            endDatetime: '2024-01-02 06:00:00'
        );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Create a validation context with the given parameters.
     *
     * @param OperationType $operationType The operation type (CREATE or UPDATE)
     * @param string|null $startDatetime The start datetime string or null if not provided
     * @param string|null $endDatetime The end datetime string or null if not provided
     * @param object|null $currentEntity The current entity for UPDATE operations
     *
     * @return ValidationContext
     */
    private function createValidationContext(
        OperationType $operationType,
        ?string $startDatetime,
        ?string $endDatetime,
        ?object $currentEntity = null
    ): ValidationContext {
        $data = [];

        if ($startDatetime !== null) {
            $data['start_datetime'] = $startDatetime;
        }

        if ($endDatetime !== null) {
            $data['end_datetime'] = $endDatetime;
        }

        return new ValidationContext(
            operationType: $operationType,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->schedulable,
            currentEntity: $currentEntity
        );
    }

    /**
     * Create a mock entity with the given datetime values.
     *
     * @param string|null $startDatetime The start datetime string or null
     * @param string|null $endDatetime The end datetime string or null
     *
     * @return object
     */
    private function createMockEntity(?string $startDatetime, ?string $endDatetime): object
    {
        $entity = Mockery::mock();

        if ($startDatetime !== null) {
            $entity->start_datetime = $startDatetime;
        }

        if ($endDatetime !== null) {
            $entity->end_datetime = $endDatetime;
        }

        return $entity;
    }
}
