<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use Roster\Validation\DTOs\ViolationData;
use ReflectionClass;
use Exception;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\RuleScanner;
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
        $this->assertSame(2, $validator->getRuleCount());
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
        $this->assertSame(1, $this->validator->getRuleCount());
    }

    /**
     * Test rule indexing with ValidationRule attribute.
     */
    public function test_indexes_rule_with_validation_rule_attribute(): void
    {
        // Arrange: Create rule class with attribute for testing
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'AttributedRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function getDescription(): string
            {
                return 'Test rule with validation rule attribute';
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

            public function getDescription(): string
            {
                return 'Test rule using supports method for indexing';
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
        $executionOrder = [];

        $rule1 = new class($executionOrder) implements RuleInterface {
            private array $executionOrder;

            public function __construct(array &$executionOrder)
            {
                $this->executionOrder = &$executionOrder;
            }

            public function getName(): string
            {
                return 'LowPriorityRule';
            }

            public function getPriority(): int
            {
                return 10;
            }

            public function getDescription(): string
            {
                return 'Low priority test rule';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }

            public function validate(ValidationContextInterface $context): void
            {
                $this->executionOrder[] = 'LowPriorityRule';
            }
        };

        $rule2 = new class($executionOrder) implements RuleInterface {
            private array $executionOrder;

            public function __construct(array &$executionOrder)
            {
                $this->executionOrder = &$executionOrder;
            }

            public function getName(): string
            {
                return 'MediumPriorityRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function getDescription(): string
            {
                return 'Medium priority test rule';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }

            public function validate(ValidationContextInterface $context): void
            {
                $this->executionOrder[] = 'MediumPriorityRule';
            }
        };

        $rule3 = new class($executionOrder) implements RuleInterface {
            private array $executionOrder;

            public function __construct(array &$executionOrder)
            {
                $this->executionOrder = &$executionOrder;
            }

            public function getName(): string
            {
                return 'HighPriorityRule';
            }

            public function getPriority(): int
            {
                return 100;
            }

            public function getDescription(): string
            {
                return 'High priority test rule';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }

            public function validate(ValidationContextInterface $context): void
            {
                $this->executionOrder[] = 'HighPriorityRule';
            }
        };

        $this->mockRuleScanner->method('instantiateRules')
            ->willReturn([$rule1, $rule2, $rule3]);

        // Create validator with pre-loaded rules
        $validator = new Validator($this->mockRuleScanner);

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Rules should be sorted by priority (higher priority first)
        $this->assertSame(['HighPriorityRule', 'MediumPriorityRule', 'LowPriorityRule'], $executionOrder);
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

            public function getDescription(): string
            {
                return 'Test rule that always passes validation';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
            }

            public function validate(ValidationContextInterface $context): void
            {
                // No violation added
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule]);

        $validator = new Validator($this->mockRuleScanner);

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
            $violations[] = new ViolationData($field, $message, $rule);
        });

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Verify successful result
        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getViolations());
    }

    /**
     * Test validation with setViolationFromRule method.
     */
    public function test_validation_with_set_violation_from_rule_method(): void
    {
        // Arrange: Create rule that uses setViolationFromRule
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'DescriptiveRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function getDescription(): string
            {
                return 'Rule that demonstrates setViolationFromRule usage';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }

            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolationFromRule($this, 'test_field', 'Test violation message');
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule]);

        $validator = new Validator($this->mockRuleScanner);

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolationFromRule')->willReturnCallback(function ($ruleArg, string $field, string $message) use (&$violations): void {
            $violations[] = new ViolationData(
                $field,
                $message,
                $ruleArg->getName(),
                $ruleArg->getDescription()
            );
        });

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Verify validation failed with violation
        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getViolations());
        $this->assertEquals('DescriptiveRule', $result->getViolations()[0]->getRule());
    }

    /**
     * Test failed validation with violations.
     */
    public function test_returns_failed_result_when_violations_exist(): void
    {
        // Arrange: Create rule that sets violations
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'ViolatingRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function getDescription(): string
            {
                return 'Test rule that always creates violations';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
            }

            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('field1', 'Field is required');
                $context->setViolation('field2', 'Field must be valid', 'required');
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule]);

        $validator = new Validator($this->mockRuleScanner);

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
            $violations[] = new ViolationData($field, $message, $rule);
        });

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Verify failed result with violations
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->getViolations());
        $this->assertEquals('field1', $result->getViolations()[0]->getField());
        $this->assertEquals('Field is required', $result->getViolations()[0]->getMessage());
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

            public function getDescription(): string
            {
                return 'Base test rule';
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

            public function getDescription(): string
            {
                return 'Additional test rule';
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

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
            $violations[] = new ViolationData($field, $message, $rule);
        });

        // Act: Execute validation with additional rules
        $result = $validator->validate($context, [$additionalRule]);

        // Assert: Validation should have violations from both rules
        $this->assertFalse($result->isValid());
        $this->assertCount(2, $result->getViolations());

        $violationFields = array_map(fn(ViolationData $v): string => $v->getField(), $result->getViolations());
        $this->assertContains('base', $violationFields);
        $this->assertContains('additional', $violationFields);
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

            public function getDescription(): string
            {
                return 'Test rule that throws exception';
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

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolationFromRule')->willReturnCallback(function ($ruleArg, string $field, string $message) use (&$violations): void {
            $violations[] = new ViolationData(
                $field,
                $message,
                $ruleArg->getName(),
                $ruleArg->getDescription()
            );
        });

        // Act: Execute validation that triggers exception
        $result = $validator->validate($context);

        // Assert: Verify validation failed due to system violation
        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getViolations());
        $this->assertEquals('_system', $result->getViolations()[0]->getField());
        $this->assertStringContainsString('Validation rule ExceptionRule failed:', $result->getViolations()[0]->getMessage());
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

            public function getDescription(): string
            {
                return 'Rule for availability entities';
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

            public function getDescription(): string
            {
                return 'Rule for schedule entities';
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

            public function getDescription(): string
            {
                return 'Generic test rule';
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

            public function getDescription(): string
            {
                return 'First test rule';
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

            public function getDescription(): string
            {
                return 'Second test rule';
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

            public function getDescription(): string
            {
                return 'Third test rule';
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

        $ruleNames = array_map(fn(RuleInterface $rule): string => $rule->getName(), $allRules);
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

            public function getDescription(): string
            {
                return 'First multiple rule';
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

            public function getDescription(): string
            {
                return 'Second multiple rule';
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

            public function getDescription(): string
            {
                return 'Third multiple rule';
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

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
            $violations[] = new ViolationData($field, $message, $rule);
        });

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

        // Create mock context with no violations
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
        $reflection = new ReflectionClass($this->validator);
        $method = $reflection->getMethod('createCacheKey');
        $method->setAccessible(true);

        // Act: Generate cache keys for various combinations
        $key1 = $method->invoke($this->validator, OperationType::CREATE, EntityType::AVAILABILITY);
        $key2 = $method->invoke($this->validator, OperationType::UPDATE, EntityType::SCHEDULE);
        $key3 = $method->invoke($this->validator, OperationType::CREATE, EntityType::IMPEDIMENT);

        // Assert: Verify correct key format
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

            public function getDescription(): string
            {
                return 'Rule with invalid attribute handling';
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

            public function getDescription(): string
            {
                return 'First count test rule';
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

            public function getDescription(): string
            {
                return 'Second count test rule';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return true;
            }

            public function validate(ValidationContextInterface $context): void {}
        };

        // Act: Register rules and check counts
        $this->assertSame(0, $this->validator->getRuleCount());

        $this->validator->registerRule($rule1);
        $this->assertSame(1, $this->validator->getRuleCount());

        $this->validator->registerRule($rule2);
        $this->assertSame(2, $this->validator->getRuleCount());

        // Register same instance again (should increase count - duplicates are allowed)
        $this->validator->registerRule($rule1);
        $this->assertSame(3, $this->validator->getRuleCount());
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

            public function getDescription(): string
            {
                return 'Duplicate rule test';
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
        $this->assertSame(3, $this->validator->getRuleCount());
        $this->assertTrue($this->validator->hasRule(get_class($rule)));
    }

    /**
     * Test validation result structure with ViolationData objects.
     */
    public function test_returns_validation_result_with_violation_data_objects(): void
    {
        // Arrange: Create rule that sets violations
        $rule = new class implements RuleInterface {
            public function getName(): string
            {
                return 'ViolationDataRule';
            }

            public function getPriority(): int
            {
                return 50;
            }

            public function getDescription(): string
            {
                return 'Rule demonstrating ViolationData objects';
            }

            public function supports(OperationType $operation, EntityType $entity): bool
            {
                return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
            }

            public function validate(ValidationContextInterface $context): void
            {
                $context->setViolation('field1', 'Required field', 'required');
                $context->setViolation('field2', 'Invalid format', 'format');
            }
        };

        $this->mockRuleScanner->method('instantiateRules')->willReturn([$rule]);

        $validator = new Validator($this->mockRuleScanner);

        // Create mock context that tracks violations
        $violations = [];
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('hasViolations')->willReturnCallback(function () use (&$violations): bool {
            return $violations !== [];
        });
        $context->method('getViolations')->willReturnCallback(function () use (&$violations): array {
            return $violations;
        });
        $context->method('setViolation')->willReturnCallback(function (string $field, string $message, ?string $rule = null) use (&$violations): void {
            $violations[] = new ViolationData($field, $message, $rule);
        });

        // Act: Execute validation
        $result = $validator->validate($context);

        // Assert: Verify result contains ViolationData objects
        $this->assertFalse($result->isValid());
        $violations = $result->getViolations();
        $this->assertCount(2, $violations);
        $this->assertInstanceOf(ViolationData::class, $violations[0]);
        $this->assertSame('field1', $violations[0]->getField());
        $this->assertSame('required', $violations[0]->getRule());
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

    public function getDescription(): string
    {
        return 'First test rule for validation testing';
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

    public function getDescription(): string
    {
        return 'Second test rule for validation testing';
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

    public function getDescription(): string
    {
        return 'Custom test rule for validation testing';
    }

    public function supports(OperationType $operation, EntityType $entity): bool
    {
        return true;
    }

    public function validate(ValidationContextInterface $context): void {}
}
