<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\DTOs\ViolationData;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;

/**
 * Test suite for ValidationFailedException.
 */
final class ValidationFailedExceptionTest extends TestCase
{
    /**
     * Test exception creation with ViolationData objects.
     */
    public function test_creates_exception_with_violation_data_objects(): void
    {
        // Arrange: Create violation data objects
        $violations = [
            new ViolationData('daily_start', 'Daily start is required', 'required'),
            new ViolationData('daily_end', 'Daily end must be after daily start', 'time_after', 'End time must be after start time'),
        ];

        $operation = OperationType::CREATE;
        $entityType = EntityType::AVAILABILITY;

        // Act: Create exception
        $exception = ValidationFailedException::fromViolations($violations, $operation, $entityType);

        // Assert: Verify exception properties
        $this->assertSame($violations, $exception->getViolations());
        $this->assertSame($operation, $exception->getOperation());
        $this->assertSame($entityType, $exception->getEntityType());
        $this->assertEquals(422, $exception->getCode());
        // Le message utilise ucfirst() pour l'opération et l'entité
        $this->assertStringContainsString('Create validation failed for Availability', $exception->getMessage());
    }

    /**
     * Test toDetailedArray method.
     */
    public function test_converts_to_detailed_array_with_descriptions(): void
    {
        // Arrange: Create violation data with descriptions
        $violations = [
            new ViolationData('start_datetime', 'Start datetime is required', 'required', 'Ensures field is not empty'),
            new ViolationData('end_datetime', 'End datetime must be after start', 'time_after', 'End time must be after start time'),
        ];

        $exception = new ValidationFailedException(
            $violations,
            OperationType::UPDATE,
            EntityType::SCHEDULE
        );

        // Act: Convert to detailed array
        $array = $exception->toDetailedArray();

        // Assert: Verify detailed array structure
        // Le message dans toDetailedArray() inclut les violations
        $this->assertEquals('Update validation failed for Schedule: Start datetime is required ; End datetime must be after start', $array['message']);
        $this->assertEquals('update', $array['operation']);
        $this->assertEquals('schedule', $array['entity_type']);
        $this->assertCount(2, $array['violations']);

        $this->assertEquals([
            'field' => 'start_datetime',
            'rule' => 'required',
            'message' => 'Start datetime is required',
            'rule_description' => 'Ensures field is not empty',
        ], $array['violations'][0]);

        $this->assertEquals([
            'field' => 'end_datetime',
            'rule' => 'time_after',
            'message' => 'End datetime must be after start',
            'rule_description' => 'End time must be after start time',
        ], $array['violations'][1]);
    }

    /**
     * Test getViolationsWithDescriptions method.
     */
    public function test_groups_violations_by_field_with_descriptions(): void
    {
        // Arrange: Create violations with some having same field
        $violations = [
            new ViolationData('start_datetime', 'Start datetime is required', 'required', 'Ensures field is not empty'),
            new ViolationData('start_datetime', 'Start datetime must be in the future', 'future_date', 'Date must be in the future'),
            new ViolationData('end_datetime', 'End datetime must be after start', 'time_after', 'End time must be after start time'),
        ];

        $exception = new ValidationFailedException(
            $violations,
            OperationType::CREATE,
            EntityType::IMPEDIMENT
        );

        // Act: Get grouped violations
        $grouped = $exception->getViolationsWithDescriptions();

        // Assert: Verify grouping
        $this->assertCount(2, $grouped); // start_datetime and end_datetime
        $this->assertArrayHasKey('start_datetime', $grouped);
        $this->assertArrayHasKey('end_datetime', $grouped);

        $this->assertCount(2, $grouped['start_datetime']);
        $this->assertEquals('required', $grouped['start_datetime'][0]['rule']);
        $this->assertEquals('Ensures field is not empty', $grouped['start_datetime'][0]['description']);

        $this->assertCount(1, $grouped['end_datetime']);
        $this->assertEquals('time_after', $grouped['end_datetime'][0]['rule']);
        $this->assertEquals('End time must be after start time', $grouped['end_datetime'][0]['description']);
    }

    /**
     * Test getFormattedMessage without descriptions.
     */
    public function test_formats_message_without_descriptions(): void
    {
        // Arrange: Create violations
        $violations = [
            new ViolationData('validity_start', 'Validity start is required', 'required'),
            new ViolationData('validity_end', 'Validity end must be after start', 'time_after'),
        ];

        $exception = new ValidationFailedException(
            $violations,
            OperationType::CREATE,
            EntityType::AVAILABILITY
        );

        // Act: Get formatted message without descriptions
        $message = $exception->getFormattedMessage(false);

        // Assert: Verify message format
        $expectedLines = [
            'Create validation failed for Availability:',
            '- [required] validity_start: Validity start is required',
            '- [time_after] validity_end: Validity end must be after start',
        ];

        $this->assertSame(implode("\n", $expectedLines), $message);
    }

    /**
     * Test getFormattedMessage with descriptions.
     */
    public function test_formats_message_with_descriptions(): void
    {
        // Arrange: Create violations with descriptions
        $violations = [
            new ViolationData(
                'reason',
                'Reason is required',
                'required',
                "Ensures the field is not empty or null.\nPrevents incomplete data submission."
            ),
            new ViolationData(
                'end_datetime',
                'End datetime must be after start',
                'time_after',
                'End time must be chronologically after start time for temporal coherence'
            ),
        ];

        $exception = new ValidationFailedException(
            $violations,
            OperationType::CREATE,
            EntityType::IMPEDIMENT
        );

        // Act: Get formatted message with descriptions
        $message = $exception->getFormattedMessage(true);

        // Assert: Verify message includes descriptions with proper formatting
        $expectedLines = [
            'Create validation failed for Impediment:',
            '- [required] reason: Reason is required',
            '  Description: Ensures the field is not empty or null.',
            '  Prevents incomplete data submission.',
            '',
            '- [time_after] end_datetime: End datetime must be after start',
            '  Description: End time must be chronologically after start time for temporal coherence',
            '',
        ];

        $this->assertSame(implode("\n", $expectedLines), $message);
    }

    /**
     * Test getFirstViolation method.
     */
    public function test_gets_first_violation_message(): void
    {
        // Arrange: Create violations
        $violations = [
            new ViolationData('daily_start', 'Daily start is required', 'required'),
            new ViolationData('daily_end', 'Daily end must be after start', 'time_after'),
        ];

        $exception = new ValidationFailedException(
            $violations,
            OperationType::CREATE,
            EntityType::AVAILABILITY
        );

        // Act: Get first violation
        $firstViolation = $exception->getFirstViolation();

        // Assert: Verify first violation message
        $this->assertSame('Daily start is required', $firstViolation);
    }

    /**
     * Test getFirstViolation returns null for empty violations.
     */
    public function test_returns_null_for_first_violation_when_no_violations(): void
    {
        // Arrange: Create exception with empty violations
        $exception = new ValidationFailedException(
            [],
            OperationType::CREATE,
            EntityType::SCHEDULE
        );

        // Act: Get first violation
        $firstViolation = $exception->getFirstViolation();

        // Assert: Verify null is returned
        $this->assertNull($firstViolation);
    }

    /**
     * Test keepLatestViolationPerField method behavior.
     */
    public function test_keeps_only_latest_violation_per_field_in_message(): void
    {
        // Arrange: Create multiple violations for same field
        $violations = [
            new ViolationData('start_datetime', 'First error', 'rule1'),
            new ViolationData('start_datetime', 'Second error', 'rule2'),
            new ViolationData('start_datetime', 'Third error', 'rule3'),
            new ViolationData('end_datetime', 'End datetime error', 'rule4'),
        ];

        $exception = new ValidationFailedException(
            $violations,
            OperationType::CREATE,
            EntityType::SCHEDULE
        );

        // Act: Get message
        $message = $exception->getMessage();

        // Assert: Verify only latest violation per field is in message
        // Should contain "Third error" and "End datetime error" but not "First error" or "Second error"
        $this->assertStringContainsString('Third error', $message);
        $this->assertStringContainsString('End datetime error', $message);
        $this->assertStringNotContainsString('First error', $message);
        $this->assertStringNotContainsString('Second error', $message);
    }
}
