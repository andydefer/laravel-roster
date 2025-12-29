<?php

declare(strict_types=1);

namespace Roster\Validation;

use ReflectionClass;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;
use Throwable;

/**
 * Main validator implementation that discovers, organizes, and executes validation rules.
 *
 * Automatically discovers rules using RuleScanner and organizes them by entity type
 * and operation type for efficient validation execution.
 */
class Validator implements ValidatorInterface
{
    /** @var array<string, array<int, RuleInterface>> Rules indexed by operation:entity key */
    private array $rulesByEntityOperation = [];

    /** @var array<int, RuleInterface> All registered rules */
    private array $allRules = [];

    private RuleScanner $ruleScanner;

    /**
     * Creates a new validator instance.
     *
     * @param RuleScanner|null $ruleScanner Optional custom rule scanner
     */
    public function __construct(?RuleScanner $ruleScanner = null)
    {
        $this->ruleScanner = $ruleScanner ?? new RuleScanner(
            directories: [__DIR__ . '/Rules']
        );

        $this->discoverAndRegisterRules();
    }

    /**
     * Discovers validation rules from configured directories and registers them.
     */
    private function discoverAndRegisterRules(): void
    {
        $rules = $this->ruleScanner->instantiateRules();

        foreach ($rules as $rule) {
            $this->registerRule($rule);
        }
    }

    /**
     * Validates the given context against applicable rules.
     *
     * @param ValidationContextInterface $validationContext The context to validate
     * @param array<int, RuleInterface> $additionalRules Additional rules to apply
     * @return ValidationResult The validation result with success status and violations
     */
    public function validate(
        ValidationContextInterface $validationContext,
        array $additionalRules = []
    ): ValidationResult {
        $operationType = $validationContext->getOperation();
        $entityType = $validationContext->getEntityType();

        $applicableRules = $this->getRulesFor($operationType, $entityType);

        if ($additionalRules !== []) {
            $applicableRules = array_merge($applicableRules, $additionalRules);
        }

        $this->sortRulesByPriority($applicableRules);

        foreach ($applicableRules as $applicableRule) {
            try {
                $applicableRule->validate($validationContext);
            } catch (Throwable $exception) {
                $this->handleRuleException($validationContext, $applicableRule, $exception);
            }
        }

        return new ValidationResult(
            !$validationContext->hasViolations(),
            $validationContext->getViolations()
        );
    }

    /**
     * Registers a validation rule and indexes it for quick retrieval.
     *
     * @param RuleInterface $rule The rule to register
     */
    public function registerRule(RuleInterface $rule): void
    {
        $this->allRules[] = $rule;

        $reflectionClass = new ReflectionClass($rule);
        $attributes = $reflectionClass->getAttributes(ValidationRule::class);

        if ($attributes !== []) {
            $this->indexRuleByAttribute($rule, $attributes[0]->newInstance());
        } else {
            $this->indexRuleBySupportsMethod($rule);
        }
    }

    /**
     * Gets all rules applicable to a specific operation and entity type.
     *
     * @param OperationType $operationType The operation type
     * @param EntityType $entityType The entity type
     * @return array<int, RuleInterface> Applicable rules
     */
    public function getRulesFor(OperationType $operationType, EntityType $entityType): array
    {
        $key = $this->createCacheKey($operationType, $entityType);
        return $this->rulesByEntityOperation[$key] ?? [];
    }

    /**
     * Checks if any rules exist for a specific operation and entity type.
     *
     * @param OperationType $operationType The operation type
     * @param EntityType $entityType The entity type
     * @return bool True if rules exist, false otherwise
     */
    public function hasRulesFor(OperationType $operationType, EntityType $entityType): bool
    {
        return $this->getRulesFor($operationType, $entityType) !== [];
    }

    /**
     * Gets all registered rules.
     *
     * @return array<int, RuleInterface> All registered rules
     */
    public function getAllRules(): array
    {
        return $this->allRules;
    }

    /**
     * Checks if a specific rule class is registered.
     *
     * @param string $ruleClass Fully qualified rule class name
     * @return bool True if the rule is registered, false otherwise
     */
    public function hasRule(string $ruleClass): bool
    {
        foreach ($this->allRules as $allRule) {
            if (get_class($allRule) === $ruleClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the total number of registered rules.
     *
     * @return int Rule count
     */
    public function getRuleCount(): int
    {
        return count($this->allRules);
    }

    /**
     * Sorts rules by priority in descending order.
     *
     * @param array<int, RuleInterface> &$rules Rules to sort
     */
    private function sortRulesByPriority(array &$rules): void
    {
        usort(
            $rules,
            fn(RuleInterface $a, RuleInterface $b): int => $b->getPriority() <=> $a->getPriority()
        );
    }

    /**
     * Handles exceptions thrown during rule validation.
     *
     * @param ValidationContextInterface $validationContext Current validation context
     * @param RuleInterface $rule The rule that failed
     * @param Throwable $throwable The thrown exception
     */
    private function handleRuleException(
        ValidationContextInterface $validationContext,
        RuleInterface $rule,
        Throwable $throwable
    ): void {
        $validationContext->setViolationFromRule(
            $rule,
            '_system',
            sprintf(
                'Validation rule %s failed: %s',
                $rule->getName(),
                $throwable->getMessage()
            )
        );
    }

    /**
     * Indexes a rule using its ValidationRule attribute metadata.
     *
     * @param RuleInterface $rule The rule to index
     * @param ValidationRule $validationRule The validation rule attribute
     */
    private function indexRuleByAttribute(RuleInterface $rule, ValidationRule $validationRule): void
    {
        foreach ($validationRule->entities as $entity) {
            if (!$entity instanceof EntityType) {
                continue;
            }

            foreach ($validationRule->operations as $operation) {
                if (!$operation instanceof OperationType) {
                    continue;
                }

                $this->registerRuleToIndex($rule, $operation, $entity);
            }
        }
    }

    /**
     * Indexes a rule by checking its supports() method against all possible combinations.
     *
     * @param RuleInterface $rule The rule to index
     */
    private function indexRuleBySupportsMethod(RuleInterface $rule): void
    {
        foreach (EntityType::cases() as $entity) {
            foreach (OperationType::cases() as $operation) {
                if ($rule->supports($operation, $entity)) {
                    $this->registerRuleToIndex($rule, $operation, $entity);
                }
            }
        }
    }

    /**
     * Adds a rule to the index for quick lookup.
     *
     * @param RuleInterface $rule The rule to add
     * @param OperationType $operationType The operation type
     * @param EntityType $entityType The entity type
     */
    private function registerRuleToIndex(
        RuleInterface $rule,
        OperationType $operationType,
        EntityType $entityType
    ): void {
        $key = $this->createCacheKey($operationType, $entityType);

        if (!isset($this->rulesByEntityOperation[$key])) {
            $this->rulesByEntityOperation[$key] = [];
        }

        $this->rulesByEntityOperation[$key][] = $rule;
    }

    /**
     * Creates a cache key for operation-entity combination.
     *
     * @param OperationType $operationType The operation type
     * @param EntityType $entityType The entity type
     * @return string The cache key
     */
    private function createCacheKey(OperationType $operationType, EntityType $entityType): string
    {
        return $operationType->value . ':' . $entityType->value;
    }
}
