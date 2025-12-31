<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\AvailabilityDateRangeRule;
use Tests\TestCase;

/**
 * Unit tests for AvailabilityDateRangeRule validation logic.
 *
 * Tests date and time range validation for availability entities,
 * including validity periods, daily time slots, and duration constraints.
 */
final class AvailabilityDateRangeRuleTest extends TestCase
{
    private AvailabilityDateRangeRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AvailabilityDateRangeRule();
    }

    /**
     * Test that validation passes when all date ranges are valid for CREATE operation.
     */
    public function test_valid_date_ranges_for_create_operation_passes(): void
    {
        // Arrange: Create context with valid data for CREATE operation
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithValidData($context);

        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be set
    }

    /**
     * Test that validation fails when validity end date is before start date.
     */
    public function test_validity_end_date_before_start_date_fails(): void
    {
        // Arrange: Configure context with invalid date range
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-12-31',
            validityEnd: '2038-01-01',
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'validity_date_range',
                'Validity end date must be after start date'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid date range
    }

    /**
     * Test that validation fails when daily end time is before start time.
     */
    public function test_daily_end_time_before_start_time_fails(): void
    {
        // Arrange: Configure context with invalid time range
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31',
            dailyStart: '17:00:00',
            dailyEnd: '09:00:00'
        );

        $violationCount = 0;
        $context->expects($this->exactly(2))
            ->method('setViolationFromRule')
            ->willReturnCallback(function ($rule, string $field, string $message) use (&$violationCount): void {
                ++$violationCount;
                $this->assertSame($this->rule, $rule);

                if ($violationCount === 1) {
                    $this->assertSame('daily_time_range', $field);
                    $this->assertSame('Daily end time must be after start time', $message);
                } else {
                    $this->assertSame('min_duration', $field);
                    $this->assertSame('Daily time slot duration must be at least 15 minutes', $message);
                }
            });

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Two violations should be set for invalid time range and short duration
    }

    /**
     * Test that validation fails when daily duration is too short.
     */
    public function test_daily_duration_too_short_fails(): void
    {
        // Arrange: Configure context with insufficient duration
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31',
            dailyStart: '09:00:00',
            dailyEnd: '09:14:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'min_duration',
                'Daily time slot duration must be at least 15 minutes'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for minimum duration
    }

    /**
     * Test that validation fails when validity period exceeds maximum days.
     */
    public function test_validity_period_exceeds_maximum_days_fails(): void
    {
        // Arrange: Configure context with period exceeding maximum days
        config(['roster.validation.max_availability_days' => 365]);

        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-01-01',
            validityEnd: '2039-01-02',
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'max_duration',
                'Availability validity period cannot exceed 365 days'
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for maximum duration
    }

    /**
     * Test that validation passes with partial dates for UPDATE operation.
     */
    public function test_partial_dates_for_update_operation_passes(): void
    {
        // Arrange: Create context with partial update data
        $existingEntity = $this->createEntityStub(
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31',
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );

        $context = $this->createValidationContextWithUpdateOperation($existingEntity);
        $this->configureContextWithPartialData(
            context: $context,
            fieldsToProvide: ['daily_start'],
            dailyStart: '10:00:00'
        );

        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for partial update
    }

    /**
     * Test that validation fails with invalid date format.
     */
    public function test_invalid_date_format_fails(): void
    {
        // Arrange: Configure context with invalid date format
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: 'invalid-date',
            validityEnd: '2038-12-31',
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'date_format',
                $this->stringContains('Invalid date format provided:')
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid date format
    }

    /**
     * Test that validation fails with invalid time format.
     */
    public function test_invalid_time_format_fails(): void
    {
        // Arrange: Configure context with invalid time format
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31',
            dailyStart: 'invalid-time',
            dailyEnd: '17:00:00'
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'time_format',
                $this->stringContains('Invalid time format provided:')
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid time format
    }

    /**
     * Test that validation passes when no dates are provided for CREATE.
     */
    public function test_no_dates_provided_for_create_passes(): void
    {
        // Arrange: Configure context with no data
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithNoData($context);

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected when no data provided
    }

    /**
     * Test that validation passes when only validity dates are provided.
     */
    public function test_only_validity_dates_provided_passes(): void
    {
        // Arrange: Configure context with only validity dates
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithPartialData(
            context: $context,
            fieldsToProvide: ['validity_start', 'validity_end'],
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31'
        );

        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with only validity dates
    }

    /**
     * Test that validation passes when only daily times are provided.
     */
    public function test_only_daily_times_provided_passes(): void
    {
        // Arrange: Configure context with only daily times
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithPartialData(
            context: $context,
            fieldsToProvide: ['daily_start', 'daily_end'],
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );

        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with only daily times
    }

    /**
     * Test that validation handles mixed new and existing values for UPDATE.
     */
    public function test_mixed_new_and_existing_values_for_update_handles(): void
    {
        // Arrange: Create context with mixed update values
        $existingEntity = $this->createEntityStub(
            validityStart: '2038-01-01',
            validityEnd: '2038-06-30',
            dailyStart: '08:00:00',
            dailyEnd: '16:00:00'
        );

        $context = $this->createValidationContextWithUpdateOperation($existingEntity);
        $this->configureContextWithPartialData(
            context: $context,
            fieldsToProvide: ['validity_end', 'daily_start'],
            validityEnd: '2038-12-31',
            dailyStart: '09:00:00'
        );

        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected with mixed update values
    }

    /**
     * Test that validation passes for exact 365 days duration.
     */
    public function test_exact_maximum_days_duration_passes(): void
    {
        // Arrange: Configure context with exact maximum duration
        config(['roster.validation.max_availability_days' => 365]);

        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31',
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );

        $context->expects($this->never())->method('setViolation');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for exact maximum duration
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
        $this->assertStringContainsString('date and time ranges', $description);
        $this->assertStringContainsString('CREATE and UPDATE', $description);
    }

    /**
     * Create a validation context mock for CREATE operation.
     *
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContextWithCreateOperation(): MockObject
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getCurrentEntity')->willReturn(null);

        return $context;
    }

    /**
     * Create a validation context mock for UPDATE operation.
     *
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContextWithUpdateOperation(?Model $existingEntity): MockObject
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getCurrentEntity')->willReturn($existingEntity);

        return $context;
    }

    /**
     * Configure context with all data fields.
     *
     * @param MockObject&ValidationContextInterface $context
     */
    private function configureContextWithData(
        MockObject $context,
        string $validityStart,
        string $validityEnd,
        string $dailyStart,
        string $dailyEnd
    ): void {
        $context->method('has')->willReturn(true);

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'validity_start' => $validityStart,
                'validity_end' => $validityEnd,
                'daily_start' => $dailyStart,
                'daily_end' => $dailyEnd,
                default => null,
            }
        );
    }

    /**
     * Configure context with valid data for CREATE operation.
     *
     * @param MockObject&ValidationContextInterface $context
     */
    private function configureContextWithValidData(MockObject $context): void
    {
        $this->configureContextWithData(
            context: $context,
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31',
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00'
        );
    }

    /**
     * Configure context with partial data.
     *
     * @param MockObject&ValidationContextInterface $context
     * @param array<int, string> $fieldsToProvide
     */
    private function configureContextWithPartialData(
        MockObject $context,
        array $fieldsToProvide,
        ?string $validityStart = null,
        ?string $validityEnd = null,
        ?string $dailyStart = null,
        ?string $dailyEnd = null
    ): void {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => in_array($key, $fieldsToProvide, true)
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'validity_start' => $validityStart,
                'validity_end' => $validityEnd,
                'daily_start' => $dailyStart,
                'daily_end' => $dailyEnd,
                default => null,
            }
        );
    }

    /**
     * Configure context with no data.
     *
     * @param MockObject&ValidationContextInterface $context
     */
    private function configureContextWithNoData(MockObject $context): void
    {
        $context->method('has')->willReturn(false);
    }

    /**
     * Create a stub entity with the given date and time values.
     *
     *
     */
    private function createEntityStub(
        string $validityStart,
        string $validityEnd,
        string $dailyStart,
        string $dailyEnd
    ): Model {
        $entity = new class extends Model {
            public $validity_start;

            public $validity_end;

            public $daily_start;

            public $daily_end;

            public function __construct()
            {
                parent::__construct();
            }
        };

        $entity->validity_start = Carbon::parse($validityStart);
        $entity->validity_end = Carbon::parse($validityEnd);
        $entity->daily_start = Carbon::parse($dailyStart);
        $entity->daily_end = Carbon::parse($dailyEnd);

        return $entity;
    }
}
