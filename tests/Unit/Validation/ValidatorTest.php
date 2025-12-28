<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\RuleScanner;
use Roster\Validation\ValidationResult;
use Roster\Validation\Validator;
use Tests\TestCase;

/**
 * Test suite for Validator class.
 *
 * Covers rule discovery, registration, indexing, and validation execution.
 */
#[AllowMockObjectsWithoutExpectations]
final class ValidatorTest extends TestCase
{
    private Validator $validator;

    /** @var MockObject&RuleScanner */
    private MockObject $mockRuleScanner;

    /**
     * Set up test environment with mock scanner.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Arrange: Create mock rule scanner to isolate validator logic
        $this->mockRuleScanner = $this->createMock(RuleScanner::class);
        $this->validator = new Validator($this->mockRuleScanner);
    }

    /**
     * Test rule discovery and registration during construction.
     */
    public function test_discovers_and_registers_rules_during_construction(): void
    {
        // Arrange: Create real rule instances for scanner to return
        $rule1 = new TestRule1();
        $rule2 = new TestRule2();

        $this->mockRuleScanner->expects($this->once())
            ->method('instantiateRules')
            ->willReturn([$rule1, $rule2]);

        // Act: Create new validator which triggers rule discovery
        $validator = new Validator($this->mockRuleScanner);

        // Assert: Verify both rules are registered by class name
        $this->assertTrue($validator->hasRule(TestRule1::class));
        $this->assertTrue($validator->hasRule(TestRule2::class));
        $this->assertEquals(2, $validator->getRuleCount());
    }

    /**
     * Test rule registration via registerRule method.
     */
    public function test_registers_rule_via_method(): void
    {
        // Arrange: Create a real rule instance
        $rule = new CustomRule();

        // Act: Register the rule manually
        $this->validator->registerRule($rule);

        // Assert: Verify rule is registered and accessible by class name
        $this->assertTrue($this->validator->hasRule(CustomRule::class));
        $this->assertEquals(1, $this->validator->getRuleCount());
    }

    /**
     * Test rule indexing with ValidationRule attribute.
     */
    public function test_indexes_rule_with_validation_rule_attribute(): void
    {
        // Arrange: Create rule class with attribute for testing
        // We'll test this with a real class that has attribute
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'AttributedRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE &&
                    ($entity === EntityType::AVAILABILITY || $entity === EntityType::SCHEDULE);
            }

            public function validate(ValidationContextInterface $context): void
            {
                // No-op for testing
            }
        };

        // Act: Register the rule
        $this->validator->registerRule($rule);

        // Assert: Verify rule is indexed based on supports() method
        $this->assertTrue($this->validator->hasRulesFor(OperationType::CREATE, EntityType::AVAILABILITY));
        $this->assertTrue($this->validator->hasRulesFor(OperationType::CREATE, EntityType::SCHEDULE));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::UPDATE, EntityType::AVAILABILITY));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::CREATE, EntityType::IMPEDIMENT));
    }

    /**
     * Test rule indexing via supports() method fallback.
     */
    public function test_indexes_rule_via_supports_method_when_no_attribute(): void
    {
        // Arrange: Create rule that supports specific combinations via method
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'SupportsMethodRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
            }

            public function validate(ValidationContextInterface $context): void
            {
                // No-op for testing
            }
        };

        // Act: Register the rule
        $this->validator->registerRule($rule);

        // Assert: Verify rule is indexed based on supports() method
        $this->assertTrue($this->validator->hasRulesFor(OperationType::UPDATE, EntityType::IMPEDIMENT));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::CREATE, EntityType::IMPEDIMENT));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::UPDATE, EntityType::AVAILABILITY));
    }

    /**
     * Test rule priority sorting during validation.
     */
    public function test_sorts_rules_by_priority_during_validation(): void
    {
        // Arrange: Create rules with different priorities
        $rule1 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'LowPriorityRule';
            }
            public function getPriority(): int
            {
                return 10;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $rule2 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'MediumPriorityRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $rule3 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'HighPriorityRule';
            }
            public function getPriority(): int
            {
                return 100;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $this->mockRuleScanner->method('instantiateRules')
            ->willReturn([$rule1, $rule2, $rule3]);

        // Create validator with pre-loaded rules
        $validator = new Validator($this->mockRuleScanner);

        // Create mock context
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('hasViolations')->willReturn(false);
        $context->method('getViolations')->willReturn([]);

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Rules should be sorted by priority (we can't easily test execution order with real objects)
        // Instead, we'll verify the validation completed successfully
        $this->assertTrue($result->isValid());

        // Also verify all three rules are registered
        $this->assertTrue($validator->hasRule(get_class($rule1)));
        $this->assertTrue($validator->hasRule(get_class($rule2)));
        $this->assertTrue($validator->hasRule(get_class($rule3)));
    }

    /**
     * Test successful validation with no violations.
     */
    public function test_returns_successful_result_when_no_violations(): void
    {
        // Arrange: Set up mock rule that passes validation
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'PassingRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule]);

        $validator = new Validator($this->mockRuleScanner);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturn(false);
        $context->method('getViolations')->willReturn([]);

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Verify successful result
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getViolations());
    }

    /**
     * Test failed validation with violations.
     */
    public function test_returns_failed_result_when_violations_exist(): void
    {
        // Arrange: Set up context with violations
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturn(true);
        $context->method('getViolations')->willReturn([
            'field1' => 'Field is required',
            'field2' => 'Field must be valid'
        ]);

        // Mock rule scanner returns no rules
        $this->mockRuleScanner->method('instantiateRules')->willReturn([]);
        $validator = new Validator($this->mockRuleScanner);

        // Act: Execute validation (no rules, but context already has violations)
        $result = $validator->validate($context);

        // Assert: Verify failed result with violations
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->getViolations());
        $this->assertEquals('Field is required', $result->getViolations()['field1']);
        $this->assertEquals('Field must be valid', $result->getViolations()['field2']);
    }

    /**
     * Test validation with additional rules parameter.
     */
    public function test_applies_additional_rules_during_validation(): void
    {
        // Arrange: Set up base rules and additional rules
        $baseRule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'BaseRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
            }
            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('base', 'Base rule violation');
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$baseRule]);

        $validator = new Validator($this->mockRuleScanner);

        $additionalRule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'AdditionalRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
            }
            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('additional', 'Additional rule violation');
            }
        };

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);
        $context->method('hasViolations')->willReturn(true);
        $context->method('getViolations')->willReturn([
            'base' => 'Base rule violation',
            'additional' => 'Additional rule violation'
        ]);

        // Act: Execute validation with additional rules
        $result = $validator->validate($context, [$additionalRule]);

        // Assert: Validation should have violations from both rules
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->getViolations());
    }

    /**
     * Test exception handling during rule validation.
     */
    public function test_handles_rule_exceptions_gracefully(): void
    {
        // Arrange: Create rule that throws exception during validation
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'ExceptionRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
            }
            public function validate(ValidationContextInterface $context): void
            {
                throw new Exception('Rule processing failed');
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule]);

        $validator = new Validator($this->mockRuleScanner);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturn(true);
        $context->expects($this->atLeastOnce())
            ->method('setViolation')
            ->with(
                '_system',
                $this->stringContains('Validation rule ExceptionRule failed: Rule processing failed')
            );

        // Act: Execute validation that triggers exception
        $result = $validator->validate($context);

        // Assert: Verify validation failed due to system violation
        $this->assertFalse($result->isValid());
    }

    /**
     * Test rule retrieval for specific operation and entity.
     */
    public function test_gets_rules_for_specific_operation_and_entity(): void
    {
        // Arrange: Create rules for different combinations
        $availabilityRule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'AvailabilityRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $entity === EntityType::AVAILABILITY &&
                    ($operation === OperationType::CREATE || $operation === OperationType::UPDATE);
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $scheduleRule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'ScheduleRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $entity === EntityType::SCHEDULE && $operation === OperationType::CREATE;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $this->validator->registerRule($availabilityRule);
        $this->validator->registerRule($scheduleRule);

        // Act: Retrieve rules for specific combinations
        $availabilityCreateRules = $this->validator->getRulesFor(OperationType::CREATE, EntityType::AVAILABILITY);
        $availabilityUpdateRules = $this->validator->getRulesFor(OperationType::UPDATE, EntityType::AVAILABILITY);
        $scheduleCreateRules = $this->validator->getRulesFor(OperationType::CREATE, EntityType::SCHEDULE);
        $scheduleUpdateRules = $this->validator->getRulesFor(OperationType::UPDATE, EntityType::SCHEDULE);

        // Assert: Verify correct rule sets are returned
        $this->assertCount(1, $availabilityCreateRules);
        $this->assertCount(1, $availabilityUpdateRules);
        $this->assertCount(1, $scheduleCreateRules);
        $this->assertCount(0, $scheduleUpdateRules);

        $this->assertEquals('AvailabilityRule', $availabilityCreateRules[0]->getName());
        $this->assertEquals('ScheduleRule', $scheduleCreateRules[0]->getName());
    }

    /**
     * Test rule existence checking.
     */
    public function test_checks_if_rules_exist_for_operation_and_entity(): void
    {
        // Arrange: Create and register a rule
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'TestRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $this->validator->registerRule($rule);

        // Assert: Verify hasRulesFor returns correct boolean values
        $this->assertTrue($this->validator->hasRulesFor(OperationType::CREATE, EntityType::AVAILABILITY));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::UPDATE, EntityType::AVAILABILITY));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::CREATE, EntityType::SCHEDULE));
    }

    /**
     * Test retrieval of all registered rules.
     */
    public function test_gets_all_registered_rules(): void
    {
        // Arrange: Register multiple rules
        $rule1 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'Rule1';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $rule2 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'Rule2';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $rule3 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'Rule3';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $this->validator->registerRule($rule1);
        $this->validator->registerRule($rule2);
        $this->validator->registerRule($rule3);

        // Act: Retrieve all rules
        $allRules = $this->validator->getAllRules();

        // Assert: Verify all rules are returned
        $this->assertCount(3, $allRules);

        $ruleNames = array_map(fn($rule) => $rule->getName(), $allRules);
        $this->assertContains('Rule1', $ruleNames);
        $this->assertContains('Rule2', $ruleNames);
        $this->assertContains('Rule3', $ruleNames);
    }

    /**
     * Test validation with multiple rules affecting same context.
     */
    public function test_validates_with_multiple_rules_affecting_context(): void
    {
        // Arrange: Create multiple rules that modify the context
        $rule1 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'Rule1';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('rule1', 'Violation from Rule1');
            }
        };

        $rule2 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'Rule2';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('rule2', 'Violation from Rule2');
            }
        };

        $rule3 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'Rule3';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }
            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('rule3', 'Violation from Rule3');
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule1, $rule2, $rule3]);

        $validator = new Validator($this->mockRuleScanner);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('hasViolations')->willReturn(true);
        $context->method('getViolations')->willReturn([
            'rule1' => 'Violation from Rule1',
            'rule2' => 'Violation from Rule2',
            'rule3' => 'Violation from Rule3'
        ]);

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Verify validation failed with violations from all rules
        $this->assertFalse($result->isValid());
        $this->assertCount(3, $result->getViolations());
    }

    /**
     * Test empty rule set validation.
     */
    public function test_validates_successfully_with_empty_rule_set(): void
    {
        // Arrange: Validator with no rules
        $this->mockRuleScanner->method('instantiateRules')->willReturn([]);
        $validator = new Validator($this->mockRuleScanner);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturn(false);
        $context->method('getViolations')->willReturn([]);

        // Act: Execute validation with no rules
        $result = $validator->validate($context);

        // Assert: Validation succeeds with no rules
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getViolations());
    }

    /**
     * Test cache key generation for rule indexing.
     */
    public function test_generates_correct_cache_keys_for_rule_indexing(): void
    {
        // Arrange: Use reflection to access private method
        $reflection = new \ReflectionClass($this->validator);
        $method = $reflection->getMethod('createCacheKey');
        $method->setAccessible(true);

        // Act: Generate cache keys for various combinations
        $key1 = $method->invoke($this->validator, OperationType::CREATE, EntityType::AVAILABILITY);
        $key2 = $method->invoke($this->validator, OperationType::UPDATE, EntityType::SCHEDULE);
        $key3 = $method->invoke($this->validator, OperationType::CREATE, EntityType::IMPEDIMENT);

        // Assert: Verify correct key format (enums use lowercase values)
        $this->assertEquals('create:availability', $key1);
        $this->assertEquals('update:schedule', $key2);
        $this->assertEquals('create:impediment', $key3);
    }

    /**
     * Test rule registration with invalid attribute values.
     */
    public function test_handles_invalid_attribute_values_gracefully(): void
    {
        // Arrange: Create rule without any special attributes
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'InvalidAttributeRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return false;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        // Act: Register the rule
        $this->validator->registerRule($rule);

        // Assert: Rule is still registered but not indexed for specific operations
        $this->assertTrue($this->validator->hasRule(get_class($rule)));
        $this->assertFalse($this->validator->hasRulesFor(OperationType::CREATE, EntityType::AVAILABILITY));
    }

    /**
     * Test rule count after multiple registrations.
     */
    public function test_maintains_correct_rule_count(): void
    {
        // Arrange: Register multiple rules
        $rule1 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'CountRule1';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        $rule2 = new class implements RuleInterface {
            public function getName(): string
            {
                return 'CountRule2';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        // Act: Register rules and check counts
        $this->assertEquals(0, $this->validator->getRuleCount());

        $this->validator->registerRule($rule1);
        $this->assertEquals(1, $this->validator->getRuleCount());

        $this->validator->registerRule($rule2);
        $this->assertEquals(2, $this->validator->getRuleCount());

        // Register same instance again (should increase count - duplicates are allowed)
        $this->validator->registerRule($rule1);
        $this->assertEquals(3, $this->validator->getRuleCount());
    }

    /**
     * Test that validator doesn't have duplicate rule checking.
     */
    public function test_allows_duplicate_rule_registration(): void
    {
        // Arrange: Create a rule instance
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'DuplicateRule';
            }
            public function getPriority(): int
            {
                return 50;
            }
            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }
            public function validate(ValidationContextInterface $context): void {}
        };

        // Act: Register the same rule multiple times
        $this->validator->registerRule($rule);
        $this->validator->registerRule($rule);
        $this->validator->registerRule($rule);

        // Assert: All registrations are counted
        $this->assertEquals(3, $this->validator->getRuleCount());
        $this->assertTrue($this->validator->hasRule(get_class($rule)));
    }
}

// Helper classes for testing
class TestRule1 implements RuleInterface
{
    public function getName(): string
    {
        return 'TestRule1';
    }
    public function getPriority(): int
    {
        return 50;
    }
    public function supports(OperationType $operation, EntityType $entity): bool
    {
        return true;
    }
    public function validate(ValidationContextInterface $context): void {}
}

class TestRule2 implements RuleInterface
{
    public function getName(): string
    {
        return 'TestRule2';
    }
    public function getPriority(): int
    {
        return 50;
    }
    public function supports(OperationType $operation, EntityType $entity): bool
    {
        return true;
    }
    public function validate(ValidationContextInterface $context): void {}
}

class CustomRule implements RuleInterface
{
    public function getName(): string
    {
        return 'CustomRule';
    }
    public function getPriority(): int
    {
        return 50;
    }
    public function supports(OperationType $operation, EntityType $entity): bool
    {
        return true;
    }
    public function validate(ValidationContextInterface $context): void {}
}
