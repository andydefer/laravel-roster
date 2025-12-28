<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\FutureDateRule;
use Tests\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class FutureDateRuleTest extends TestCase
{
    private FutureDateRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new FutureDateRule();
    }

    /**
     * Test that validation passes when schedule start datetime is in the future.
     */
    public function test_passes_when_schedule_start_datetime_is_future(): void
    {
        // Arrange: Future start datetime for SCHEDULE entity CREATE operation
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $futureDate
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: No violation should be recorded for future datetime
    }

    /**
     * Test that validation fails when schedule start datetime is in the past.
     */
    public function test_fails_when_schedule_start_datetime_is_past(): void
    {
        // Arrange: Past start datetime for SCHEDULE entity CREATE operation
        $pastDate = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $pastDate
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'start_datetime',
                'Schedule start datetime cannot be in the past'
            );

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for past datetime
    }

    /**
     * Test that validation fails when impediment start datetime is in the past.
     */
    public function test_fails_when_impediment_start_datetime_is_past(): void
    {
        // Arrange: Past start datetime for IMPEDIMENT entity CREATE operation
        $pastDate = Carbon::now()->subHours(2)->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::IMPEDIMENT,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $pastDate
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'start_datetime',
                'Impediment start datetime cannot be in the past'
            );

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: One violation should be recorded for past impediment datetime
    }

    /**
     * Test that validation passes when impediment start datetime is in the future.
     */
    public function test_passes_when_impediment_start_datetime_is_future(): void
    {
        // Arrange: Future start datetime for IMPEDIMENT entity CREATE operation
        $futureDate = Carbon::now()->addHours(2)->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::IMPEDIMENT,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $futureDate
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: No violation should be recorded for future impediment datetime
    }

    /**
     * Test that validation passes when start_datetime field is not present.
     */
    public function test_passes_when_start_datetime_not_present(): void
    {
        // Arrange: Missing start_datetime field for SCHEDULE entity
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: false,
            startDatetime: null
        );

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur when field is missing
    }

    /**
     * Test that validation does not apply for UPDATE operation.
     */
    public function test_does_not_validate_for_update_operation(): void
    {
        // Arrange: UPDATE operation with past datetime should not be validated
        $pastDate = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::UPDATE,
            hasStartDatetime: true,
            startDatetime: $pastDate
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: No validation should apply to UPDATE operations
    }

    /**
     * Test that validation does not apply for DELETE operation.
     */
    public function test_does_not_validate_for_delete_operation(): void
    {
        // Arrange: DELETE operation should bypass validation
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::DELETE,
            hasStartDatetime: false,
            startDatetime: null
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: No validation should apply to DELETE operations
    }

    /**
     * Test that validation handles AVAILABILITY entity type.
     */
    public function test_does_not_validate_for_availability_entity(): void
    {
        // Arrange: AVAILABILITY entity uses start_date field, not start_datetime
        $context = $this->createValidationContext(
            entityType: EntityType::AVAILABILITY,
            operationType: OperationType::CREATE,
            hasStartDatetime: false,
            startDatetime: null
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: AVAILABILITY entity validation is handled separately
    }

    /**
     * Test that validation handles invalid datetime format gracefully.
     */
    public function test_handles_invalid_datetime_format_gracefully(): void
    {
        // Arrange: Invalid datetime format should not trigger violation
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: 'invalid-datetime-format'
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Invalid format errors are handled by other validation rules
    }

    /**
     * Test that validation uses correct entity display names in error messages.
     */
    public function test_uses_correct_display_names_in_error_messages(): void
    {
        // Arrange: IMPEDIMENT entity should show specific error message
        $pastDate = Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::IMPEDIMENT,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $pastDate
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'start_datetime',
                'Impediment start datetime cannot be in the past'
            );

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Error message should match entity type
    }

    /**
     * Test that validation fails for current time.
     */
    public function test_fails_for_current_time(): void
    {
        // Arrange: Current time is considered past for validation purposes
        $currentTime = Carbon::now()->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $currentTime
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'start_datetime',
                'Schedule start datetime cannot be in the past'
            );

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Current time should be treated as past time
    }

    /**
     * Test that validation respects shouldValidateFutureDates configuration.
     */
    public function test_respects_should_validate_future_dates_configuration(): void
    {
        // Arrange: Mock rule with disabled future date validation
        $rule = $this->createMockWithConfigMethods(shouldValidateFutureDates: false);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);

        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $rule->validate($context);

        // Assert: No validation should occur when disabled by configuration
    }

    /**
     * Test that validation respects allowPastDates configuration.
     */
    public function test_respects_allow_past_dates_configuration(): void
    {
        // Arrange: Mock rule allowing past dates
        $rule = $this->createMockWithConfigMethods(
            shouldValidateFutureDates: true,
            allowPastDates: true
        );

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);

        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $rule->validate($context);

        // Assert: No validation should occur when past dates are allowed
    }

    /**
     * Test that validation fails for one second in the past.
     */
    public function test_fails_for_one_second_in_past(): void
    {
        // Arrange: Datetime one second in the past
        $oneSecondAgo = Carbon::now()->subSecond()->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $oneSecondAgo
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'start_datetime',
                'Schedule start datetime cannot be in the past'
            );

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Even one second in the past should trigger violation
    }

    /**
     * Test that validation passes for one second in the future.
     */
    public function test_passes_for_one_second_in_future(): void
    {
        // Arrange: Datetime one second in the future
        $oneSecondFuture = Carbon::now()->addSecond()->format('Y-m-d H:i:s');
        $context = $this->createValidationContext(
            entityType: EntityType::SCHEDULE,
            operationType: OperationType::CREATE,
            hasStartDatetime: true,
            startDatetime: $oneSecondFuture
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Even one second in the future should pass validation
    }

    /**
     * Test that validation for AVAILABILITY with future start_date passes.
     */
    public function test_passes_for_availability_with_future_start_date(): void
    {
        // Arrange: Future start_date for AVAILABILITY entity
        $futureDate = Carbon::now()->addDays(1)->format('Y-m-d');
        $context = $this->createValidationContextForAvailability(
            operationType: OperationType::CREATE,
            hasStartDate: true,
            startDate: $futureDate
        );

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Future start_date should pass AVAILABILITY validation
    }

    /**
     * Test that validation for AVAILABILITY with past start_date fails.
     */
    public function test_fails_for_availability_with_past_start_date(): void
    {
        // Arrange: Past start_date for AVAILABILITY entity
        $pastDate = Carbon::now()->subDays(1)->format('Y-m-d');
        $context = $this->createValidationContextForAvailability(
            operationType: OperationType::CREATE,
            hasStartDate: true,
            startDate: $pastDate
        );

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'start_date',
                'Availability start date cannot be in the past'
            );

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: Past start_date should trigger AVAILABILITY violation
    }

    /**
     * Test that validation for AVAILABILITY without start_date passes.
     */
    public function test_passes_for_availability_without_start_date(): void
    {
        // Arrange: Missing start_date for AVAILABILITY entity
        $context = $this->createValidationContextForAvailability(
            operationType: OperationType::CREATE,
            hasStartDate: false,
            startDate: null
        );

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the future date validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur when start_date is missing
    }

    /**
     * Create a validation context mock for SCHEDULE/IMPEDIMENT entities.
     */
    private function createValidationContext(
        EntityType $entityType,
        OperationType $operationType,
        bool $hasStartDatetime,
        ?string $startDatetime
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getOperation')->willReturn($operationType);

        $context->method('has')->willReturnCallback(
            fn(string $key): bool => $key === 'start_datetime' && $hasStartDatetime
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => $key === 'start_datetime' ? $startDatetime : null
        );

        return $context;
    }

    /**
     * Create a validation context mock for AVAILABILITY entities.
     */
    private function createValidationContextForAvailability(
        OperationType $operationType,
        bool $hasStartDate,
        ?string $startDate
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn($operationType);

        $context->method('has')->willReturnCallback(
            fn(string $key): bool => $key === 'start_date' && $hasStartDate
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => $key === 'start_date' ? $startDate : null
        );

        return $context;
    }

    /**
     * Create a mocked FutureDateRule with specific configuration methods.
     */
    private function createMockWithConfigMethods(
        bool $shouldValidateFutureDates,
        bool $allowPastDates = false
    ): MockObject&FutureDateRule {
        $mock = $this->getMockBuilder(FutureDateRule::class)
            ->onlyMethods(['shouldValidateFutureDates', 'allowPastDates'])
            ->getMock();

        $mock->method('shouldValidateFutureDates')->willReturn($shouldValidateFutureDates);
        $mock->method('allowPastDates')->willReturn($allowPastDates);

        return $mock;
    }
}
