<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Carbon\Carbon;
use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\TimezoneValidationRule;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Test suite for TimezoneValidationRule.
 */
#[AllowMockObjectsWithoutExpectations]
final class TimezoneValidationRuleTest extends TestCase
{
    private TimezoneValidationRule $rule;
    private Model|MockInterface $schedulable;

    /**
     * Set up the test case with fresh instances.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = Mockery::mock(Model::class);
        $this->configureSchedulableMock();

        TimezoneHelper::resetUserTimezone();

        Config::set('app.timezone', 'UTC');
        Config::set('roster.timezone', 'Europe/Paris');
        TimezoneHelper::initialize();

        $this->rule = new TimezoneValidationRule();
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        TimezoneHelper::resetUserTimezone();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that validation works for all entity types.
     */
    public function test_works_for_all_entity_types(): void
    {
        $entityTypes = [
            EntityType::AVAILABILITY,
            EntityType::SCHEDULE,
            EntityType::IMPEDIMENT
        ];

        foreach ($entityTypes as $entityType) {
            // Arrange: Create mock context for each entity type
            $context = $this->createMock(ValidationContextInterface::class);
            $context->method('getEntityType')->willReturn($entityType);
            $context->method('getOperation')->willReturn(OperationType::CREATE);

            // Configure has() method to return false for all fields (no data)
            $context->method('has')->willReturn(false);

            // Configure get() method - shouldn't be called since has() returns false
            $context->method('get')->willReturnCallback(
                function (string $key): mixed {
                    $this->fail("get() should not be called when has() returns false for field '{$key}'");
                }
            );

            $context->expects($this->never())->method('setViolationFromRule');

            // Act: Validate the context
            $this->rule->validate($context);

            // Assert: Validation should pass for all entity types (no assertions needed, mock expectations verify)
        }
    }

    /**
     * Test that validation works for both create and update operations.
     */
    public function test_works_for_both_operations(): void
    {
        $operations = [OperationType::CREATE, OperationType::UPDATE];

        foreach ($operations as $operation) {
            // Arrange: Create mock context for each operation type
            $context = $this->createMock(ValidationContextInterface::class);
            $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
            $context->method('getOperation')->willReturn($operation);

            // Configure has() method to return false for all fields (no data)
            $context->method('has')->willReturn(false);

            // Configure get() method - shouldn't be called since has() returns false
            $context->method('get')->willReturnCallback(
                function (string $key): mixed {
                    $this->fail("get() should not be called when has() returns false for field '{$key}'");
                }
            );

            $context->expects($this->never())->method('setViolationFromRule');

            // Act: Validate the context
            $this->rule->validate($context);

            // Assert: Validation should pass for both operations (no assertions needed, mock expectations verify)
        }
    }

    /**
     * Test that validation skips DELETE operation.
     */
    public function test_skips_delete_operation(): void
    {
        // Arrange: Create context for DELETE operation
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::DELETE);

        // Pour DELETE, la règle ne devrait même pas appeler has() car elle vérifie d'abord l'opération
        // Mais on configure quand même has() pour être sûr
        $context->method('has')->willReturnCallback(
            function (string $key): bool {
                $this->fail("has() should not be called for DELETE operation, but was called with field '{$key}'");
            }
        );

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Validation should be skipped for DELETE operation (mock expectations verify this)
    }

    /**
     * Test that validation passes with valid timezone.
     */
    public function test_passes_with_valid_timezone(): void
    {
        // Arrange: Valid timezone data
        $context = $this->createMockValidationContext([
            'timezone' => 'Europe/Paris',
            'start_datetime' => '2024-01-01 09:00:00'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present (implicitly verified by mock expectations)
    }

    /**
     * Test that validation fails with invalid timezone.
     */
    public function test_fails_with_invalid_timezone(): void
    {
        // Arrange: Invalid timezone identifier
        $context = $this->createMockValidationContext([
            'timezone' => 'Invalid/Timezone',
            'start_datetime' => '2024-01-01 09:00:00',
        ]);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'timezone',
                "Invalid timezone: 'Invalid/Timezone'"
            );

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for timezone field (verified by mock expectations)
    }

    /**
     * Test that validation passes when timezone is null.
     */
    public function test_passes_when_timezone_is_null(): void
    {
        // Arrange: Null timezone value
        $context = $this->createMockValidationContext([
            'timezone' => null,
            'start_datetime' => '2024-01-01 09:00:00'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Null timezone should be allowed (verified by mock expectations)
    }

    /**
     * Test that validation passes when timezone field is not present.
     */
    public function test_passes_when_timezone_field_is_not_present(): void
    {
        // Arrange: No timezone field in context
        $context = $this->createMockValidationContext([
            'start_datetime' => '2024-01-01 09:00:00'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Missing timezone field should not cause violation
    }

    /**
     * Test that validation passes with valid datetime in current timezone.
     */
    public function test_passes_with_valid_datetime_in_current_timezone(): void
    {
        // Arrange: Valid datetime fields without explicit timezone
        $context = $this->createMockValidationContext([
            'start_datetime' => '2024-01-01 09:00:00',
            'end_datetime' => '2024-01-01 17:00:00'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: All datetime fields should be valid
    }

    /**
     * Test that validation fails with invalid datetime format.
     */
    public function test_fails_with_invalid_datetime_format(): void
    {
        // Arrange: Invalid datetime format
        $context = $this->createMockValidationContext([
            'start_datetime' => 'invalid-datetime-format',
            'end_datetime' => '2024-01-01 17:00:00'
        ]);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'start_datetime',
                "Invalid datetime format or timezone for field 'start_datetime'"
            );

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for invalid datetime
    }

    /**
     * Test that validation fails with invalid date-only format.
     */
    public function test_fails_with_invalid_date_only_format(): void
    {
        // Arrange: Invalid date format for validity fields
        $context = $this->createMockValidationContext([
            'validity_start' => '2024-13-01', // Invalid month
            'validity_end' => '2024-12-31'
        ]);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'validity_start',
                "Invalid datetime format or timezone for field 'validity_start'"
            );

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for invalid date
    }

    /**
     * Test that validation passes with all datetime fields valid.
     */
    public function test_passes_with_all_datetime_fields_valid(): void
    {
        // Arrange: All possible datetime fields with valid values
        $context = $this->createMockValidationContext([
            'start_datetime' => '2024-01-01 09:00:00',
            'end_datetime' => '2024-01-01 17:00:00',
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: All fields should pass validation
    }

    /**
     * Test that validation skips missing datetime fields.
     */
    public function test_skips_missing_datetime_fields(): void
    {
        // Arrange: Empty data array
        $context = $this->createMockValidationContext([]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Missing fields should not cause violations
    }

    /**
     * Test that validation handles null datetime values.
     */
    public function test_handles_null_datetime_values(): void
    {
        // Arrange: Null datetime values
        $context = $this->createMockValidationContext([
            'start_datetime' => null,
            'end_datetime' => null
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Null values should be allowed
    }

    /**
     * Test that validation passes with user timezone set.
     */
    public function test_passes_with_user_timezone_set(): void
    {
        // Arrange: Set user timezone and validate matching timezone
        TimezoneHelper::setUserTimezone('America/New_York');
        $context = $this->createMockValidationContext([
            'start_datetime' => '2024-01-01 09:00:00',
            'timezone' => 'America/New_York'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Matching timezone should pass
    }

    /**
     * Test that validation passes with datetime containing explicit timezone.
     */
    public function test_passes_with_datetime_containing_explicit_timezone(): void
    {
        // Arrange: Datetime with explicit timezone offset
        $context = $this->createMockValidationContext([
            'start_datetime' => '2024-01-01T09:00:00+05:00',
            'timezone' => 'Europe/Paris'
        ]);

        $context->expects($this->never())->method('setViolationFromRule');

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Should pass as Carbon can parse the format
    }

    /**
     * Test that validation fails with datetime that cannot be parsed in given timezone.
     */
    public function test_fails_with_datetime_unparseable_in_timezone(): void
    {
        // Arrange: Edge case datetime that might fail parsing
        $context = $this->createMockValidationContext([
            'start_datetime' => 'invalid-date-time-format',
            'timezone' => 'UTC'
        ]);

        $context->expects($this->once())
            ->method('setViolationFromRule')
            ->with(
                $this->rule,
                'start_datetime',
                "Invalid datetime format or timezone for field 'start_datetime'"
            );

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Should fail due to unparseable datetime
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
        $this->assertStringContainsString('timezone', strtolower($description));
    }

    /**
     * Test that validation handles edge cases for timezone validation.
     */
    public function test_handles_timezone_edge_cases(): void
    {
        // Test cases: valid and invalid timezone strings
        $testCases = [
            ['timezone' => 'UTC', 'shouldPass' => true],
            ['timezone' => 'Europe/London', 'shouldPass' => true],
            ['timezone' => 'America/New_York', 'shouldPass' => true],
            ['timezone' => 'Invalid/Timezone', 'shouldPass' => false],
            ['timezone' => 'Not/A_Real_Zone', 'shouldPass' => false],
            ['timezone' => '', 'shouldPass' => false], // Empty string
            ['timezone' => '   ', 'shouldPass' => false], // Whitespace only
        ];

        foreach ($testCases as $testCase) {
            // Arrange: Create context with test timezone
            $context = $this->createMockValidationContext([
                'timezone' => $testCase['timezone'],
                'start_datetime' => '2024-01-01 09:00:00'
            ]);

            if ($testCase['shouldPass']) {
                $context->expects($this->never())->method('setViolationFromRule');
            } else {
                $context->expects($this->once())
                    ->method('setViolationFromRule')
                    ->with(
                        $this->rule,
                        'timezone',
                        sprintf("Invalid timezone: '%s'", $testCase['timezone'])
                    );
            }

            // Act: Validate the context
            $this->rule->validate($context);

            // Assert: Expectations are verified by mock
        }
    }

    /**
     * Test that validation handles different date formats.
     */
    public function test_handles_different_date_formats(): void
    {
        $dateFormats = [
            '2024-01-01 09:00:00',
            '2024-01-01T09:00:00',
            '2024-01-01T09:00:00Z',
            '2024-01-01T09:00:00+00:00',
            '2024-01-01',
            '01/01/2024',
            'January 1, 2024',
        ];

        foreach ($dateFormats as $dateFormat) {
            // Arrange: Create context with different date format
            $context = $this->createMockValidationContext([
                'validity_start' => $dateFormat
            ]);

            // Some formats might fail, some might pass
            // We'll accept either outcome as long as no exception is thrown
            try {
                // Act: Validate the context
                $this->rule->validate($context);
                $this->addToAssertionCount(1); // Test executed without exception
            } catch (Exception $exception) {
                $this->fail("Validation threw exception for date format '{$dateFormat}': " . $exception->getMessage());
            }
        }
    }

    /**
     * Configure mock for schedulable model.
     */
    private function configureSchedulableMock(): void
    {
        $this->schedulable->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);

        $this->schedulable->shouldReceive('getMorphClass')
            ->andReturn('TestModel');
    }

    /**
     * Create a mock validation context with test data.
     *
     * @param array<string, mixed> $data
     * @return MockObject&ValidationContextInterface
     */
    private function createMockValidationContext(array $data): MockObject
    {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);

        // Configure has() method based on data keys
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => array_key_exists($key, $data)
        );

        // Configure get() method to return data values
        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => $data[$key] ?? null
        );

        return $context;
    }
}
