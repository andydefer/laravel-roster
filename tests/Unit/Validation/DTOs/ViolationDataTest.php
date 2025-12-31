<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\DTOs;

use Roster\Validation\DTOs\ViolationData;
use Tests\TestCase;

/**
 * Test suite for ViolationData DTO.
 */
final class ViolationDataTest extends TestCase
{
    /**
     * Test basic violation data creation.
     */
    public function test_creates_violation_data_with_basic_fields(): void
    {
        // Arrange: Data for violation
        $field = 'email';
        $message = 'The email field is required';
        $rule = 'required';

        // Act: Create ViolationData instance
        $violation = new ViolationData($field, $message, $rule);

        // Assert: Verify all fields are correctly set
        $this->assertSame($field, $violation->getField());
        $this->assertSame($message, $violation->getMessage());
        $this->assertSame($rule, $violation->getRule());
        $this->assertNull($violation->getRuleDescription());
        $this->assertFalse($violation->hasRuleDescription());
    }

    /**
     * Test violation data creation with rule description.
     */
    public function test_creates_violation_data_with_rule_description(): void
    {
        // Arrange: Data for violation with description
        $field = 'email';
        $message = 'The email field is required';
        $rule = 'required';
        $description = 'Ensures the field is not empty or null';

        // Act: Create ViolationData instance with description
        $violation = new ViolationData($field, $message, $rule, $description);

        // Assert: Verify all fields including description
        $this->assertSame($field, $violation->getField());
        $this->assertSame($message, $violation->getMessage());
        $this->assertSame($rule, $violation->getRule());
        $this->assertSame($description, $violation->getRuleDescription());
        $this->assertTrue($violation->hasRuleDescription());
    }

    /**
     * Test violation data creation without rule name.
     */
    public function test_creates_violation_data_without_rule_name(): void
    {
        // Arrange: Data for violation without rule
        $field = 'email';
        $message = 'The email field is required';

        // Act: Create ViolationData instance without rule
        $violation = new ViolationData($field, $message);

        // Assert: Verify default rule name
        $this->assertSame('unknown', $violation->getRule());
        $this->assertNull($violation->getRuleDescription());
    }

    /**
     * Test violation data creation with empty description.
     */
    public function test_creates_violation_data_with_empty_description(): void
    {
        // Arrange: Data for violation with empty description
        $field = 'email';
        $message = 'The email field is required';
        $rule = 'required';
        $description = '';

        // Act: Create ViolationData instance with empty description
        $violation = new ViolationData($field, $message, $rule, $description);

        // Assert: Verify empty description is not considered as having description
        $this->assertSame('', $violation->getRuleDescription());
        $this->assertFalse($violation->hasRuleDescription());
    }

    /**
     * Test toArray method without description.
     */
    public function test_converts_to_array_without_description(): void
    {
        // Arrange: Create ViolationData instance
        $violation = new ViolationData('email', 'Required field', 'required');

        // Act: Convert to array
        $array = $violation->toArray();

        // Assert: Verify array structure
        $this->assertEquals([
            'field' => 'email',
            'rule' => 'required',
            'message' => 'Required field',
            'rule_description' => null,
        ], $array);
    }

    /**
     * Test toArray method with description.
     */
    public function test_converts_to_array_with_description(): void
    {
        // Arrange: Create ViolationData instance with description
        $description = 'Ensures the field is not empty or null';
        $violation = new ViolationData('email', 'Required field', 'required', $description);

        // Act: Convert to array
        $array = $violation->toArray();

        // Assert: Verify array structure with description
        $this->assertSame([
            'field' => 'email',
            'rule' => 'required',
            'message' => 'Required field',
            'rule_description' => $description,
        ], $array);
    }

    /**
     * Test that rule description preserves new lines.
     */
    public function test_preserves_new_lines_in_rule_description(): void
    {
        // Arrange: Create description with new lines
        $description = "Ensures the field is not empty or null.\nAlso validates format.\nPrevents injection attacks.";

        // Act: Create ViolationData instance
        $violation = new ViolationData('email', 'Invalid field', 'format', $description);

        // Assert: Verify new lines are preserved
        $this->assertSame($description, $violation->getRuleDescription());
    }

    /**
     * Test multiple violation data instances with different configurations.
     */
    public function test_handles_multiple_violation_configurations(): void
    {
        // Arrange: Create multiple violation instances
        $violations = [
            new ViolationData('email', 'Required', 'required'),
            new ViolationData('email', 'Invalid format', 'email', 'Validates email format'),
            new ViolationData('password', 'Too short', 'min:8', 'Ensures minimum length of 8 characters'),
            new ViolationData('name', 'Required'), // No rule specified
        ];

        // Act & Assert: Verify each instance
        $this->assertSame('email', $violations[0]->getField());
        $this->assertSame('required', $violations[0]->getRule());
        $this->assertNull($violations[0]->getRuleDescription());

        $this->assertSame('email', $violations[1]->getField());
        $this->assertSame('email', $violations[1]->getRule());
        $this->assertSame('Validates email format', $violations[1]->getRuleDescription());

        $this->assertSame('password', $violations[2]->getField());
        $this->assertSame('min:8', $violations[2]->getRule());
        $this->assertSame('Ensures minimum length of 8 characters', $violations[2]->getRuleDescription());

        $this->assertSame('name', $violations[3]->getField());
        $this->assertSame('unknown', $violations[3]->getRule()); // Default when null
        $this->assertNull($violations[3]->getRuleDescription());
    }
}
