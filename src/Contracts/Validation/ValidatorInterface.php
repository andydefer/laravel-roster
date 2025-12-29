<?php

declare(strict_types=1);

namespace Roster\Contracts\Validation;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\ValidationResult;

/**
 * Defines the contract for validation services within the roster system.
 *
 * Responsible for validating operations on entities using configurable rules
 * and providing validation results.
 */
interface ValidatorInterface
{
    /**
     * Validate an operation on an entity within the given context.
     *
     * @param ValidationContextInterface $validationContext Context containing entity and operation details
     * @param array<int, RuleInterface> $additionalRules Additional rules to apply for this validation
     * @return ValidationResult The result of the validation operation
     */
    public function validate(
        ValidationContextInterface $validationContext,
        array $additionalRules = []
    ): ValidationResult;

    /**
     * Register a new validation rule with the validator.
     *
     * @param RuleInterface $rule The rule instance to add
     */
    public function registerRule(RuleInterface $rule): void;

    /**
     * Get all rules applicable for a specific operation and entity type.
     *
     * @param OperationType $operationType Type of operation being performed
     * @param EntityType $entityType Type of entity being operated on
     * @return array<RuleInterface> Array of applicable rules
     */
    public function getRulesFor(OperationType $operationType, EntityType $entityType): array;

    /**
     * Check if any rules exist for a specific operation and entity type.
     *
     * @param OperationType $operationType Type of operation being performed
     * @param EntityType $entityType Type of entity being operated on
     * @return bool True if rules exist, false otherwise
     */
    public function hasRulesFor(OperationType $operationType, EntityType $entityType): bool;
}
