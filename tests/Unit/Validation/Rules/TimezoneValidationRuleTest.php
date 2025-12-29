<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Mockery;
use Mockery\MockInterface;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\TimezoneValidationRule;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Test suite for TimezoneValidationRule.
 */
final class TimezoneValidationRuleTest extends TestCase
{
    private TimezoneValidationRule $rule;
    private Model|MockInterface $schedulable;

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

    protected function tearDown(): void
    {
        TimezoneHelper::resetUserTimezone();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test validation passes with valid timezone.
     */
    public function test_passes_with_valid_timezone(): void
    {
        // Arrange: Valid timezone data
        $context = $this->createValidationContext([
            'timezone' => 'Europe/Paris',
            'start_datetime' => '2024-01-01 09:00:00'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation fails with invalid timezone.
     */
    public function test_fails_with_invalid_timezone(): void
    {
        // Arrange: Invalid timezone identifier
        $context = $this->createValidationContext([
            'timezone' => 'Invalid/Timezone',
            'start_datetime' => '2024-01-01 09:00:00'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for timezone field
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('timezone', $context->getViolations());
        $this->assertStringContainsString(
            'Invalid timezone',
            (string) $context->getViolations()['timezone']
        );
    }

    /**
     * Test validation passes when timezone is null.
     */
    public function test_passes_when_timezone_is_null(): void
    {
        // Arrange: Null timezone value
        $context = $this->createValidationContext([
            'timezone' => null,
            'start_datetime' => '2024-01-01 09:00:00'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Null timezone should be allowed
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation passes with valid datetime in current timezone.
     */
    public function test_passes_with_valid_datetime_in_current_timezone(): void
    {
        // Arrange: Valid datetime fields without explicit timezone
        $context = $this->createValidationContext([
            'start_datetime' => '2024-01-01 09:00:00',
            'end_datetime' => '2024-01-01 17:00:00'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: All datetime fields should be valid
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation fails with invalid datetime format.
     */
    public function test_fails_with_invalid_datetime_format(): void
    {
        // Arrange: Invalid datetime format
        $context = $this->createValidationContext([
            'start_datetime' => 'invalid-datetime-format',
            'end_datetime' => '2024-01-01 17:00:00'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for invalid datetime
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('start_datetime', $context->getViolations());
    }

    /**
     * Test validation passes with all datetime fields valid.
     */
    public function test_passes_with_all_datetime_fields_valid(): void
    {
        // Arrange: All possible datetime fields with valid values
        $context = $this->createValidationContext([
            'start_datetime' => '2024-01-01 09:00:00',
            'end_datetime' => '2024-01-01 17:00:00',
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: All fields should pass validation
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation skips missing datetime fields.
     */
    public function test_skips_missing_datetime_fields(): void
    {
        // Arrange: Empty data array
        $context = $this->createValidationContext([]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Missing fields should not cause violations
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation handles null datetime values.
     */
    public function test_handles_null_datetime_values(): void
    {
        // Arrange: Null datetime values
        $context = $this->createValidationContext([
            'start_datetime' => null,
            'end_datetime' => null
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Null values should be allowed
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation passes with user timezone set.
     */
    public function test_passes_with_user_timezone_set(): void
    {
        // Arrange: Set user timezone and validate matching timezone
        TimezoneHelper::setUserTimezone('America/New_York');
        $context = $this->createValidationContext([
            'start_datetime' => '2024-01-01 09:00:00',
            'timezone' => 'America/New_York'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Matching timezone should pass
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation passes with datetime containing explicit timezone.
     */
    public function test_passes_with_datetime_containing_explicit_timezone(): void
    {
        // Arrange: Datetime with explicit timezone offset
        $context = $this->createValidationContext([
            'start_datetime' => '2024-01-01T09:00:00+05:00',
            'timezone' => 'Europe/Paris'
        ]);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Should pass as Carbon can parse the format
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test validation works for all entity types.
     */
    public function test_works_for_all_entity_types(): void
    {
        $entityTypes = [
            EntityType::AVAILABILITY,
            EntityType::SCHEDULE,
            EntityType::IMPEDIMENT
        ];

        foreach ($entityTypes as $entityType) {
            // Arrange: Create context for each entity type
            $context = new ValidationContext(
                operationType: OperationType::CREATE,
                entityType: $entityType,
                data: [
                    'timezone' => 'Europe/Paris',
                    'start_datetime' => '2024-01-01 09:00:00'
                ],
                model: $this->schedulable
            );

            // Act: Validate the context
            $this->rule->validate($context);

            // Assert: Validation should pass for all entity types
            $this->assertFalse(
                $context->hasViolations(),
                "Timezone validation should pass for {$entityType->value}"
            );
        }
    }

    /**
     * Test validation works for both create and update operations.
     */
    public function test_works_for_both_operations(): void
    {
        $operations = [OperationType::CREATE, OperationType::UPDATE];

        foreach ($operations as $operation) {
            // Arrange: Create context for each operation type
            $context = new ValidationContext(
                operationType: $operation,
                entityType: EntityType::SCHEDULE,
                data: [
                    'timezone' => 'Europe/Paris',
                    'start_datetime' => '2024-01-01 09:00:00'
                ],
                model: $this->schedulable
            );

            // Act: Validate the context
            $this->rule->validate($context);

            // Assert: Validation should pass for both operations
            $this->assertFalse(
                $context->hasViolations(),
                "Timezone validation should pass for {$operation->value} operation"
            );
        }
    }

    /**
     * Test rule priority is correctly set.
     */
    public function test_rule_has_correct_priority(): void
    {
        // Arrange: Test priority constant
        $expectedPriority = 30;

        // Act: Get rule priority
        $actualPriority = $this->rule->getPriority();

        // Assert: Priority should match expected value
        $this->assertSame($expectedPriority, $actualPriority);
    }

    /**
     * Test rule name is correctly set.
     */
    public function test_rule_has_correct_name(): void
    {
        // Arrange: Expected rule name
        $expectedName = 'TimezoneValidationRule';

        // Act: Get rule name
        $actualName = $this->rule->getName();

        // Assert: Name should match expected value
        $this->assertSame($expectedName, $actualName);
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
     * Create validation context with test data.
     */
    private function createValidationContext(array $data): ValidationContext
    {
        return new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->schedulable
        );
    }
}
