<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use stdClass;
use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Validation\Rules\TimeRangeRule;
use Tests\TestCase;

final class TimeRangeRuleTest extends TestCase
{
    private AvailabilityService|MockInterface $availabilityService;

    private TimeRangeRule $rule;

    /**
     * Set up the test environment with mocks.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityService = Mockery::mock(AvailabilityService::class);
        $this->rule = new TimeRangeRule();
    }

    /**
     * Clean up mockery after each test.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that validation passes when time range is within availability boundaries.
     */
    public function test_passes_when_time_range_is_valid_within_availability(): void
    {
        // Arrange: Time range completely within availability hours and validity period
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: No violations should be recorded for valid time range
    }

    /**
     * Test that validation fails when start time is before availability start.
     */
    public function test_fails_when_start_time_before_availability_start(): void
    {
        // Arrange: Start time earlier than availability's daily start time
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 07:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 10:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'start_datetime',
                'The selected start time 07:00 is before the availability start time 08:00'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for start time before availability
    }

    /**
     * Test that validation fails when end time is after availability end.
     */
    public function test_fails_when_end_time_after_availability_end(): void
    {
        // Arrange: End time later than availability's daily end time
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 16:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 19:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'end_datetime',
                'The selected end time 19:00 is after the availability end time 18:00'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for end time after availability
    }

    /**
     * Test that validation fails when event day is not allowed.
     */
    public function test_fails_when_day_not_allowed(): void
    {
        // Arrange: Event scheduled on a day not included in availability
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'], // Only Monday allowed
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-02 09:00:00', // Tuesday
            hasEndDatetime: true,
            endDatetime: '2024-01-02 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'start_datetime',
                'The selected date 2024-01-02 (tuesday) is not allowed. Allowed days: monday'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for invalid day
    }

    /**
     * Test that validation fails when start time is before validity period.
     */
    public function test_fails_when_start_before_validity(): void
    {
        // Arrange: Event scheduled before availability validity period starts
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['sunday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2023-12-31 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2023-12-31 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'start_datetime',
                'The selected start datetime 2023-12-31 09:00:00 is before the availability start datetime 2024-01-01 00:00:00'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for start before validity period
    }

    /**
     * Test that validation fails when end time is after validity period.
     */
    public function test_fails_when_end_after_validity(): void
    {
        // Arrange: Event scheduled after availability validity period ends
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['wednesday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2025-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2025-01-01 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'end_datetime',
                'The selected end datetime 2025-01-01 17:00:00 is after the availability end datetime 2024-12-31 23:59:59'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for end after validity period
    }

    /**
     * Test that validation fails when end time is before start time.
     */
    public function test_fails_when_end_before_start(): void
    {
        // Arrange: Event with end time chronologically before start time
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 12:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 08:00:00',
            hasAvailabilityId: false,
            availabilityId: null
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'end_datetime',
                'The end datetime must be after the start datetime'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for invalid time sequence
    }

    /**
     * Test that validation passes for impediment entity type.
     */
    public function test_passes_for_impediment_entity(): void
    {
        // Arrange: Impediment entity with valid time range
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 10:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 14:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule for impediment
        $this->rule->validate($context);

        // Assert: No violations for valid impediment time range
    }

    /**
     * Test that validation skips when availability is not found.
     */
    public function test_skips_validation_when_availability_not_found(): void
    {
        // Arrange: Non-existent availability ID
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 999
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: No violations when availability doesn't exist
    }

    /**
     * Test that validation handles malformed datetime strings gracefully.
     */
    public function test_handles_malformed_datetime_strings(): void
    {
        // Arrange: Invalid datetime format in input data
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: 'invalid-datetime',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule with invalid data
        try {
            $this->rule->validate($context);
        } catch (Exception $exception) {
            // Expected exception from invalid datetime parsing
        }

        // Assert: No violations recorded for malformed datetime
    }

    /**
     * Test that validation passes when availability has no validity period.
     */
    public function test_passes_when_availability_has_no_validity_period(): void
    {
        // Arrange: Availability without validity period constraints
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => null,
            'validity_end' => null,
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: No violations for time range when no validity period
    }

    /**
     * Test that validation fails when event spans across midnight.
     */
    public function test_fails_when_event_spans_midnight(): void
    {
        // Arrange: Event starting on one day and ending on the next
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 22:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-02 01:00:00',
            hasAvailabilityId: false,
            availabilityId: null
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'end_datetime',
                'Events cannot span across multiple days'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for multi-day event
    }

    /**
     * Test that validation fails when start and end times are equal.
     */
    public function test_fails_when_start_and_end_times_are_equal(): void
    {
        // Arrange: Event with identical start and end times
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 09:00:00',
            hasAvailabilityId: false,
            availabilityId: null
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'end_datetime',
                'The end datetime must be after the start datetime'
            );

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for equal start and end times
    }

    /**
     * Test that validation handles UPDATE operation correctly.
     */
    public function test_handles_update_operation_correctly(): void
    {
        // Arrange: UPDATE operation with valid time range
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 10:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 16:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule for UPDATE
        $this->rule->validate($context);

        // Assert: No violations for valid UPDATE operation
    }

    /**
     * Test that validation skips when datetime fields are missing.
     */
    public function test_skips_when_datetime_fields_are_missing(): void
    {
        // Arrange: Missing start datetime field
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false,
            startDatetime: null,
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00',
            hasAvailabilityId: true,
            availabilityId: 123
        );

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Validation should skip when required fields are missing
    }

    /**
     * Test that validation skips when availability ID is missing.
     */
    public function test_skips_when_availability_id_is_missing(): void
    {
        // Arrange: Missing availability ID
        $context = $this->createValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: true,
            startDatetime: '2024-01-01 09:00:00',
            hasEndDatetime: true,
            endDatetime: '2024-01-01 17:00:00',
            hasAvailabilityId: false,
            availabilityId: null
        );

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Validation should skip when availability ID is missing
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
        $this->assertStringContainsString('time range', $description);
        $this->assertStringContainsString('availability', $description);
    }

    /**
     * Test that validation handles update with existing entity data.
     */
    public function test_handles_update_with_existing_entity_data(): void
    {
        // Arrange: UPDATE operation with existing entity that has datetime values
        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $context = $this->createValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            hasStartDatetime: false, // Not in update data, should come from entity
            startDatetime: null,
            hasEndDatetime: false, // Not in update data, should come from entity
            endDatetime: null,
            hasAvailabilityId: false, // Not in update data, should come from entity
            availabilityId: null
        );

        // Mock existing entity with datetime values
        $entity = new stdClass();
        $entity->start_datetime = '2024-01-01 10:00:00';
        $entity->end_datetime = '2024-01-01 16:00:00';
        $entity->availability_id = 123;

        $context
            ->method('getCurrentEntity')
            ->willReturn($entity);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        $context
            ->method('getAvailabilityService')
            ->willReturn($this->availabilityService);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Should use entity data for UPDATE operation
    }

    /**
     * Create a validation context mock with specified configuration.
     *
     * @return MockInterface&ValidationContextInterface
     */
    private function createValidationContext(
        OperationType $operationType,
        EntityType $entityType,
        bool $hasStartDatetime,
        ?string $startDatetime,
        bool $hasEndDatetime,
        ?string $endDatetime,
        bool $hasAvailabilityId,
        ?int $availabilityId
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);

        $context->method('getOperation')->willReturn($operationType);
        $context->method('getEntityType')->willReturn($entityType);

        $context->method('has')->willReturnCallback(
            function (string $field) use ($hasStartDatetime, $hasEndDatetime, $hasAvailabilityId): bool {
                return match ($field) {
                    'start_datetime' => $hasStartDatetime,
                    'end_datetime' => $hasEndDatetime,
                    'availability_id' => $hasAvailabilityId,
                    default => false,
                };
            }
        );

        $context->method('get')->willReturnCallback(
            function (string $field) use ($startDatetime, $endDatetime, $availabilityId): mixed {
                return match ($field) {
                    'start_datetime' => $startDatetime,
                    'end_datetime' => $endDatetime,
                    'availability_id' => $availabilityId,
                    default => null,
                };
            }
        );

        return $context;
    }

    /**
     * Create an Availability model instance with test data.
     *
     * @param array{
     *     daily_start?: string,
     *     daily_end?: string,
     *     days?: string[],
     *     validity_start?: string|null,
     *     validity_end?: string|null
     * } $properties
     */
    private function createAvailability(array $properties): Availability
    {
        $availability = new Availability();

        $availability->daily_start = $properties['daily_start'] ?? '08:00:00';
        $availability->daily_end = $properties['daily_end'] ?? '18:00:00';
        $availability->days = $properties['days'] ?? ['monday'];
        $availability->validity_start = $properties['validity_start'] ?? '2024-01-01';
        $availability->validity_end = $properties['validity_end'] ?? '2024-12-31';

        return $availability;
    }
}
