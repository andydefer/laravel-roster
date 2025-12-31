<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\AvailabilityDaysCoherenceRule;
use Tests\TestCase;

/**
 * Unit tests for AvailabilityDaysCoherenceRule validation logic.
 *
 * Tests coherence between specified days and validity period for availability entities.
 */
final class AvailabilityDaysCoherenceRuleTest extends TestCase
{
    private AvailabilityDaysCoherenceRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new AvailabilityDaysCoherenceRule();
    }

    /**
     * Test that validation passes when days are valid and within period.
     */
    public function test_passes_with_valid_days_within_period(): void
    {
        // Arrange: Valid days array that falls within the validity period
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: ['monday', 'tuesday'],
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should be recorded
    }

    /**
     * Test that validation passes when days are not provided.
     */
    public function test_passes_when_days_not_provided(): void
    {
        // Arrange: Context without days field
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithoutDays($context);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when days field is absent
    }

    /**
     * Test that validation fails when days is not an array.
     */
    public function test_fails_when_days_is_not_an_array(): void
    {
        // Arrange: Context with days as a non-array value
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithNonArrayDays($context);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('days'),
                $this->identicalTo('Days must be provided as an array')
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: One violation should be set for invalid days format
    }

    /**
     * Test that validation fails with invalid day of week.
     */
    public function test_fails_with_invalid_day_of_week(): void
    {
        // Arrange: Context with an invalid day name in the days array
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithInvalidDay(
            context: $context,
            days: ['monday', 'invalidday']
        );

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->identicalTo($this->rule),
                $this->identicalTo('days'),
                $this->identicalTo("Day value 'invalidday' is not recognized as a valid day of the week")
            );

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: One violation should be set for the invalid day name
    }

    /**
     * Test that validation fails when day is not within validity period.
     */
    public function test_fails_when_day_not_within_validity_period(): void
    {
        // Arrange: Days that fall outside the specified validity period
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: ['monday', 'friday'],
            validityStart: '2038-01-05',
            validityEnd: '2038-01-07'
        );

        $violationCount = 0;
        $context->expects($this->exactly(2))
            ->method('setViolationFromRule')
            ->willReturnCallback(function ($rule, string $field, string $message) use (&$violationCount): void {
                $this->assertSame($this->rule, $rule);
                $this->assertSame('days', $field);
                $this->assertStringContainsString('falls outside the validity period', $message);
                ++$violationCount;
            });

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Two violations should be recorded for two out-of-period days
        $this->assertSame(2, $violationCount);
    }

    /**
     * Test that validation passes with empty days array.
     */
    public function test_passes_with_empty_days_array(): void
    {
        // Arrange: Empty days array with a validity period
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: [],
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur with an empty days array
    }

    /**
     * Test that validation passes when validity dates not provided.
     */
    public function test_passes_when_validity_dates_not_provided(): void
    {
        // Arrange: Days without any validity period dates
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysOnly(
            context: $context,
            days: ['monday', 'tuesday']
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur when validity dates are missing
    }

    /**
     * Test that validation passes when only one validity date provided.
     */
    public function test_passes_when_only_one_validity_date_provided(): void
    {
        // Arrange: Context with days and only start date (no end date)
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithPartialValidity(
            context: $context,
            days: ['monday', 'tuesday'],
            validityStart: '2038-01-01'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur with partial validity dates
    }

    /**
     * Test that validation handles single day period.
     */
    public function test_passes_with_single_day_period(): void
    {
        // Arrange: Single day validity period with matching day
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: ['friday'],
            validityStart: '2038-01-01',
            validityEnd: '2038-01-01'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur for a single day period
    }

    /**
     * Test that validation handles period spanning week boundary.
     */
    public function test_passes_with_period_spanning_week_boundary(): void
    {
        // Arrange: Validity period that crosses Sunday-Monday boundary
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: ['saturday', 'sunday', 'monday'],
            validityStart: '2038-01-02',
            validityEnd: '2038-01-04'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations should occur for period spanning week boundary
    }

    /**
     * Test that validation sets violations for multiple invalid days.
     */
    public function test_fails_with_multiple_invalid_days(): void
    {
        // Arrange: Multiple days that are outside the validity period
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: ['monday', 'tuesday', 'saturday'],
            validityStart: '2038-01-07',
            validityEnd: '2038-01-09'
        );

        $capturedMessages = [];
        $context->expects($this->exactly(2))
            ->method('setViolationFromRule')
            ->willReturnCallback(function ($rule, string $field, string $message) use (&$capturedMessages): void {
                $capturedMessages[] = $message;
                $this->assertSame($this->rule, $rule);
                $this->assertSame('days', $field);
                $this->assertStringContainsString('falls outside the validity period', $message);
            });

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: Two violations should be captured with appropriate messages
        $this->assertCount(2, $capturedMessages);
        $allMessages = implode(' ', $capturedMessages);
        $this->assertStringContainsString("Day 'monday'", $allMessages);
        $this->assertStringContainsString("Day 'tuesday'", $allMessages);
    }

    /**
     * Test that validation handles UPDATE operation with existing entity.
     */
    public function test_handles_update_operation_with_existing_entity(): void
    {
        // Arrange: UPDATE operation with existing entity providing validity period
        $entity = $this->createEntityStub(
            validityStart: '2038-01-01',
            validityEnd: '2038-12-31'
        );

        $context = $this->createValidationContextWithUpdateOperation($entity);
        $this->configureContextWithDaysOnly(
            context: $context,
            days: ['monday', 'tuesday']
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations when using existing entity validity period
    }

    /**
     * Test that validation handles UPDATE with partial validity dates.
     */
    public function test_handles_update_with_partial_validity_dates(): void
    {
        // Arrange: UPDATE operation with mixed existing and new validity dates
        $entity = $this->createEntityStub(
            validityStart: '2038-01-01'
        );

        $context = $this->createValidationContextWithUpdateOperation($entity);
        $this->configureContextWithPartialValidity(
            context: $context,
            days: ['monday', 'tuesday'],
            validityEnd: '2038-12-31'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations with mixed existing and new validity dates
    }

    /**
     * Test that validation passes when validity end is before start.
     */
    public function test_passes_when_validity_end_before_start(): void
    {
        // Arrange: Invalid period where end date is before start date
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithDaysAndPeriod(
            context: $context,
            days: ['monday', 'tuesday'],
            validityStart: '2038-12-31',
            validityEnd: '2038-01-01'
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations for invalid period (handled by another rule)
    }

    /**
     * Test that validation handles invalid date format gracefully.
     */
    public function test_handles_invalid_date_format_gracefully(): void
    {
        // Arrange: Context with invalid date format in validity period
        $context = $this->createValidationContextWithCreateOperation();
        $this->configureContextWithInvalidDateFormat(
            context: $context,
            days: ['monday', 'tuesday']
        );

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No violations for invalid date format (handled by another rule)
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
        $this->assertStringContainsString('coherence', $description);
        $this->assertStringContainsString('days', $description);
        $this->assertStringContainsString('validity period', $description);
        $this->assertStringContainsString('CREATE and UPDATE', $description);
    }

    /**
     * Create a validation context mock for CREATE operation.
     */
    private function createValidationContextWithCreateOperation(): MockObject&ValidationContextInterface
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getCurrentEntity')->willReturn(null);

        return $context;
    }

    /**
     * Create a validation context mock for UPDATE operation.
     */
    private function createValidationContextWithUpdateOperation(?Model $existingEntity): MockObject&ValidationContextInterface
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getCurrentEntity')->willReturn($existingEntity);

        return $context;
    }

    /**
     * Configure context with days and validity period.
     *
     * @param array<string> $days
     */
    private function configureContextWithDaysAndPeriod(
        MockObject $context,
        array $days,
        ?string $validityStart = null,
        ?string $validityEnd = null
    ): void {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => in_array($key, ['days', 'validity_start', 'validity_end'], true)
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'days' => $days,
                'validity_start' => $validityStart,
                'validity_end' => $validityEnd,
                default => null,
            }
        );
    }

    /**
     * Configure context with days only (no validity dates).
     *
     * @param array<string> $days
     */
    private function configureContextWithDaysOnly(MockObject $context, array $days): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => $key === 'days'
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => $key === 'days' ? $days : null
        );
    }

    /**
     * Configure context with partial validity dates.
     *
     * @param array<string> $days
     */
    private function configureContextWithPartialValidity(
        MockObject $context,
        array $days,
        ?string $validityStart = null,
        ?string $validityEnd = null
    ): void {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => in_array($key, ['days', 'validity_start', 'validity_end'], true)
                && (($key === 'validity_start' && $validityStart !== null)
                    || ($key === 'validity_end' && $validityEnd !== null)
                    || $key === 'days')
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'days' => $days,
                'validity_start' => $validityStart,
                'validity_end' => $validityEnd,
                default => null,
            }
        );
    }

    /**
     * Configure context with non-array days.
     */
    private function configureContextWithNonArrayDays(MockObject $context): void
    {
        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => $key === 'days' ? 'not-an-array' : null
        );
    }

    /**
     * Configure context with invalid day name.
     *
     * @param array<string> $days
     */
    private function configureContextWithInvalidDay(MockObject $context, array $days): void
    {
        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => $key === 'days' ? $days : null
        );
    }

    /**
     * Configure context without days.
     */
    private function configureContextWithoutDays(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => $key !== 'days'
        );
    }

    /**
     * Configure context with invalid date format.
     *
     * @param array<string> $days
     */
    private function configureContextWithInvalidDateFormat(MockObject $context, array $days): void
    {
        $context->method('has')->willReturn(true);
        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'days' => $days,
                'validity_start' => 'invalid-date',
                'validity_end' => '2038-12-31',
                default => null,
            }
        );
    }

    /**
     * Create a stub entity with validity dates.
     */
    private function createEntityStub(?string $validityStart = null, ?string $validityEnd = null): Model
    {
        $entity = new class extends Model {
            public $validity_start;

            public $validity_end;

            public function __construct()
            {
                parent::__construct();
            }
        };

        if ($validityStart !== null) {
            $entity->validity_start = Carbon::parse($validityStart);
        }

        if ($validityEnd !== null) {
            $entity->validity_end = Carbon::parse($validityEnd);
        }

        return $entity;
    }
}
