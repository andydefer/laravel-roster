<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use Roster\Validation\DTOs\ViolationData;
use Roster\Validation\ValidationResult;
use Tests\TestCase;

/**
 * Test suite for ValidationResult.
 */
final class ValidationResultTest extends TestCase
{
    /**
     * Test toArray method with rule descriptions.
     */
    public function test_converts_to_array_with_rule_descriptions(): void
    {
        // Arrange: Create violations with descriptions
        $violations = [
            new ViolationData('email', 'Email is required', 'required', 'Ensures field is not empty'),
            new ViolationData('password', 'Password too short', 'min:8', 'Minimum 8 characters required'),
        ];

        $result = ValidationResult::failed($violations);

        // Act: Convert to array with descriptions
        $array = $result->toArray(true);

        // Assert: Verify array includes descriptions
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $array['violations']);

        $this->assertEquals([
            'field' => 'email',
            'rule' => 'required',
            'message' => 'Email is required',
            'rule_description' => 'Ensures field is not empty',
        ], $array['violations'][0]);

        $this->assertEquals([
            'field' => 'password',
            'rule' => 'min:8',
            'message' => 'Password too short',
            'rule_description' => 'Minimum 8 characters required',
        ], $array['violations'][1]);
    }

    /**
     * Test toArray method without rule descriptions.
     */
    public function test_converts_to_array_without_rule_descriptions(): void
    {
        // Arrange: Create violations with descriptions
        $violations = [
            new ViolationData('email', 'Email is required', 'required', 'Ensures field is not empty'),
            new ViolationData('password', 'Password too short', 'min:8'),
        ];

        $result = ValidationResult::failed($violations);

        // Act: Convert to array without descriptions
        $array = $result->toArray(false);

        // Assert: Verify array excludes descriptions
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $array['violations']);

        $this->assertEquals([
            'field' => 'email',
            'rule' => 'required',
            'message' => 'Email is required',
        ], $array['violations'][0]);

        $this->assertEquals([
            'field' => 'password',
            'rule' => 'min:8',
            'message' => 'Password too short',
        ], $array['violations'][1]);
    }

    /**
     * Test successful result creation with ViolationData objects.
     */
    public function test_creates_successful_result(): void
    {
        // Act: Create successful result via constructor
        $result = new ValidationResult(true, []);

        // Assert: Verify result properties
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getViolations());
        $this->assertFalse($result->hasViolations());
        $this->assertSame(0, $result->countViolations());

        // Vérification du tableau
        $array = $result->toArray();
        $this->assertTrue($array['success']);
        $this->assertEmpty($array['violations']);
    }

    /**
     * Test failed result creation with ViolationData objects.
     */
    public function test_creates_failed_result_with_violation_data(): void
    {
        // Arrange: Create violations
        $violations = [
            new ViolationData('email', 'Email is required', 'required'),
            new ViolationData('password', 'Password too short', 'min:8'),
        ];

        // Act: Create failed result using static method
        $result = ValidationResult::failed($violations);

        // Assert: Verify result properties
        $this->assertFalse($result->isValid());
        $this->assertSame($violations, $result->getViolations());
        $this->assertTrue($result->hasViolations());
        $this->assertSame(2, $result->countViolations());

        // Vérification du tableau
        $array = $result->toArray();
        $this->assertFalse($array['success']);
        $this->assertCount(2, $array['violations']);
    }

    /**
     * Test result with mixed violation data (some with descriptions, some without).
     */
    public function test_handles_mixed_violation_data(): void
    {
        // Arrange: Create mixed violations
        $violations = [
            new ViolationData('email', 'Email is required', 'required'), // No description
            new ViolationData('password', 'Password too short', 'min:8', 'Minimum 8 characters'), // With description
            new ViolationData('name', 'Name is required', 'required'), // With rule but no description
        ];

        $result = ValidationResult::failed($violations);

        // Act: Get array with descriptions
        $array = $result->toArray(true);

        // Assert: Verify mixed data is handled correctly
        $this->assertFalse($result->isValid());
        $this->assertCount(3, $array['violations']);

        $this->assertNull($array['violations'][0]['rule_description']);
        $this->assertEquals('Minimum 8 characters', $array['violations'][1]['rule_description']);
        $this->assertNull($array['violations'][2]['rule_description']);
    }

    /**
     * Test empty result array conversion.
     */
    public function test_converts_empty_result_to_array(): void
    {
        // Arrange: Create successful result
        $result = new ValidationResult(true, []);

        // Act: Convert to array
        $array = $result->toArray();

        // Assert: Verify empty array structure
        $this->assertTrue($result->isValid());
        $this->assertEquals([
            'success' => true,
            'violations' => [],
        ], $array);
    }

    /**
     * Test failed() method returns correct result.
     */
    public function test_failed_static_method(): void
    {
        // Arrange
        $violations = [
            new ViolationData('field', 'Error message', 'rule'),
        ];

        // Act
        $result = ValidationResult::failed($violations);

        // Assert
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertSame($violations, $result->getViolations());
    }

    /**
     * Test hasViolations method with empty violations.
     */
    public function test_has_violations_with_empty_array(): void
    {
        // Arrange
        $result = new ValidationResult(true, []);

        // Assert
        $this->assertFalse($result->hasViolations());
    }

    /**
     * Test hasViolations method with violations.
     */
    public function test_has_violations_with_violations(): void
    {
        // Arrange
        $violations = [new ViolationData('field', 'Error', 'rule')];
        $result = ValidationResult::failed($violations);

        // Assert
        $this->assertTrue($result->hasViolations());
    }

    /**
     * Test countViolations method.
     */
    public function test_count_violations(): void
    {
        // Arrange
        $violations = [
            new ViolationData('field1', 'Error 1', 'rule1'),
            new ViolationData('field2', 'Error 2', 'rule2'),
            new ViolationData('field3', 'Error 3', 'rule3'),
        ];
        $result = ValidationResult::failed($violations);

        // Assert
        $this->assertSame(3, $result->countViolations());
    }
}
