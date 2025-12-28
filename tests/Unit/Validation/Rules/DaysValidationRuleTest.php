<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\DaysOfWeek;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\DaysValidationRule;
use Tests\TestCase;

final class DaysValidationRuleTest extends TestCase
{
    private DaysValidationRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new DaysValidationRule();
    }

    /**
     * Test that validation passes for valid days array on CREATE.
     */
    public function test_passes_for_valid_days_array_on_create(): void
    {
        // Arrange: Create operation with a valid array of days
        $validDays = [DaysOfWeek::MONDAY->value, DaysOfWeek::WEDNESDAY->value, DaysOfWeek::FRIDAY->value];
        $context = $this->createValidationContext(OperationType::CREATE, true, $validDays);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: No violations should be recorded for valid days array
    }

    /**
     * Test that validation fails when days is not an array.
     */
    public function test_fails_when_days_is_not_array(): void
    {
        // Arrange: Context with days as a string instead of an array
        $context = $this->createValidationContext(OperationType::CREATE, true, 'monday,tuesday');

        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', 'Days must be an array');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: One violation should be set for non-array days value
    }

    /**
     * Test that validation fails when days array is empty.
     */
    public function test_fails_when_days_array_is_empty(): void
    {
        // Arrange: Context with an empty days array
        $context = $this->createValidationContext(OperationType::CREATE, true, []);

        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', 'Days array cannot be empty');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: One violation should be set for empty days array
    }

    /**
     * Test that validation fails when contains invalid day.
     */
    public function test_fails_when_contains_invalid_day(): void
    {
        // Arrange: Context with mixed valid and invalid day values
        $invalidDays = [DaysOfWeek::MONDAY->value, 'invalid_day', DaysOfWeek::FRIDAY->value];
        $context = $this->createValidationContext(OperationType::CREATE, true, $invalidDays);

        $validDaysList = implode(', ', DaysOfWeek::values());
        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', "Invalid day 'invalid_day'. Valid days are: " . $validDaysList);

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: One violation should be set for the invalid day value
    }

    /**
     * Test that validation fails when contains invalid day (case sensitive).
     */
    public function test_fails_when_contains_invalid_case(): void
    {
        // Arrange: Context with incorrect case for day names (should be lowercase)
        $invalidCaseDays = [DaysOfWeek::MONDAY->value, 'Monday'];
        $context = $this->createValidationContext(OperationType::CREATE, true, $invalidCaseDays);

        $validDaysList = implode(', ', DaysOfWeek::values());
        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', "Invalid day 'Monday'. Valid days are: " . $validDaysList);

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: One violation should be set for incorrectly cased day value
    }

    /**
     * Test that validation passes when days field is not present.
     */
    public function test_passes_when_days_field_not_present(): void
    {
        // Arrange: Context without the days field
        $context = $this->createValidationContext(OperationType::CREATE, false, null);

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when days field is missing
    }

    /**
     * Test that validation passes for UPDATE operation (should not validate days).
     */
    public function test_passes_for_update_operation(): void
    {
        // Arrange: UPDATE operation should bypass day validation entirely
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule for UPDATE
        $this->rule->validate($context);

        // Assert: UPDATE operations should not trigger day validation
    }

    /**
     * Test that validation passes for DELETE operation (should not validate days).
     */
    public function test_passes_for_delete_operation(): void
    {
        // Arrange: DELETE operation should bypass day validation entirely
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::DELETE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->expects($this->never())->method('has');
        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule for DELETE
        $this->rule->validate($context);

        // Assert: DELETE operations should not trigger day validation
    }

    /**
     * Test that validation works with all valid days.
     */
    public function test_passes_with_all_valid_days(): void
    {
        // Arrange: Context with all possible valid day values from enum
        $allDays = DaysOfWeek::values();
        $context = $this->createValidationContext(OperationType::CREATE, true, $allDays);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur with complete set of valid days
    }

    /**
     * Test that validation passes with single valid day.
     */
    public function test_passes_with_single_valid_day(): void
    {
        // Arrange: Context with only one valid day in the array
        $singleDay = [DaysOfWeek::MONDAY->value];
        $context = $this->createValidationContext(OperationType::CREATE, true, $singleDay);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur with single valid day
    }

    /**
     * Test that validation stops at first invalid day.
     */
    public function test_stops_at_first_invalid_day(): void
    {
        // Arrange: Context with multiple invalid days in array
        $multipleInvalidDays = ['invalid1', 'invalid2', DaysOfWeek::MONDAY->value];
        $context = $this->createValidationContext(OperationType::CREATE, true, $multipleInvalidDays);

        $validDaysList = implode(', ', DaysOfWeek::values());
        $context->expects($this->once())
            ->method('setViolation')
            ->with('days', "Invalid day 'invalid1'. Valid days are: " . $validDaysList);

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: Only one violation should be set for first invalid day encountered
    }

    /**
     * Test that validation works for availability entity type.
     */
    public function test_works_for_availability_entity_type(): void
    {
        // Arrange: Context with valid days for availability entity type
        $validDays = [DaysOfWeek::TUESDAY->value, DaysOfWeek::THURSDAY->value];
        $context = $this->createValidationContext(OperationType::CREATE, true, $validDays);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the days validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur for availability entity type with valid days
    }

    /**
     * Create a validation context mock with specified configuration.
     */
    private function createValidationContext(
        OperationType $operationType,
        bool $hasDays,
        mixed $daysValue
    ): MockObject&ValidationContextInterface {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn($operationType);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);

        $context->method('has')->willReturnCallback(
            fn(string $key): bool => $key === 'days' && $hasDays
        );

        if ($hasDays) {
            $context->method('get')->willReturnCallback(
                fn(string $key): mixed => $key === 'days' ? $daysValue : null
            );
        }

        return $context;
    }
}
