<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\AbstractRule;
use Tests\TestCase;

/**
 * Test suite for AbstractRule.
 */
final class AbstractRuleTest extends TestCase
{
    /**
     * Test getDescription returns default description.
     */
    public function test_returns_default_description(): void
    {
        // Arrange: Create concrete rule instance
        $rule = new class extends AbstractRule {
            public function validate(ValidationContextInterface $validationContext): void {}
        };

        // Act: Get description
        $description = $rule->getDescription();

        // Assert: Verify default description format
        $this->assertStringContainsString('Validates', $description);
        $this->assertStringContainsString($rule->getName(), $description);
        $this->assertStringContainsString('ensures data integrity', $description);
    }

    /**
     * Test concrete rule can override getDescription.
     */
    public function test_concrete_rule_can_override_description(): void
    {
        // Arrange: Create concrete rule with custom description
        $customDescription = 'Custom rule that validates specific business logic';

        $rule = new class($customDescription) extends AbstractRule {
            private string $customDescription;

            public function __construct(string $customDescription)
            {
                $this->customDescription = $customDescription;
            }

            public function getDescription(): string
            {
                return $this->customDescription;
            }

            public function validate(ValidationContextInterface $validationContext): void {}
        };

        // Act: Get description
        $description = $rule->getDescription();

        // Assert: Verify custom description is returned
        $this->assertSame($customDescription, $description);
    }

    /**
     * Test getName returns class basename.
     */
    public function test_returns_class_basename_as_name(): void
    {
        // Arrange: Create concrete rule with specific class name
        $rule = new class extends AbstractRule {
            public function validate(ValidationContextInterface $validationContext): void {}
        };

        // Act: Get name
        $name = $rule->getName();

        // Assert: Verify name is class basename
        // The anonymous class will have a generated name like "class@anonymous"
        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    /**
     * Test rule with custom name in description.
     */
    public function test_description_includes_rule_name(): void
    {
        // Arrange: Create concrete rule
        $rule = new class extends AbstractRule {
            public function getName(): string
            {
                return 'CustomRuleName';
            }

            public function validate(ValidationContextInterface $validationContext): void {}
        };

        // Act: Get description
        $description = $rule->getDescription();

        // Assert: Verify description includes the custom rule name
        $this->assertStringContainsString('CustomRuleName', $description);
        $this->assertStringContainsString('Validates CustomRuleName entity data', $description);
    }

    /**
     * Test that AbstractRule implements RuleInterface fully.
     */
    public function test_implements_all_rule_interface_methods(): void
    {
        // Arrange: Create concrete rule
        $rule = new class extends AbstractRule {
            public function validate(ValidationContextInterface $validationContext): void {}
        };

        // Act & Assert: Verify all required methods exist
        $this->assertIsString($rule->getName());
        $this->assertIsInt($rule->getPriority());
        $this->assertIsString($rule->getDescription());

        // Test supports method (default behavior)
        $this->assertTrue($rule->supports(OperationType::CREATE, EntityType::AVAILABILITY));
        $this->assertTrue($rule->supports(OperationType::UPDATE, EntityType::SCHEDULE));
        $this->assertTrue($rule->supports(OperationType::CREATE, EntityType::IMPEDIMENT));
    }
}
