<?php

declare(strict_types=1);

namespace Roster\Contracts\Validation;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

/**
 * Contract for validation rules in the roster system.
 *
 * Defines the interface that all validation rules must implement
 * to be used within the roster validation pipeline.
 */
interface RuleInterface
{
    /**
     * Execute validation logic for the given context.
     *
     * @param ValidationContextInterface $validationContext Context containing data to validate
     * @throws ValidationExceptionInterface When validation fails
     */
    public function validate(ValidationContextInterface $validationContext): void;

    /**
     * Determine if this rule supports the given operation and entity combination.
     *
     * @param OperationType $operationType Type of operation being performed
     * @param EntityType $entityType Type of entity being validated
     * @return bool True if the rule applies to this operation/entity combination
     */
    public function supports(OperationType $operationType, EntityType $entityType): bool;

    /**
     * Get the priority of this rule for execution order.
     *
     * Rules with higher priority values are executed first.
     *
     * @return int Priority value (higher = earlier execution)
     */
    public function getPriority(): int;

    /**
     * Get the unique name identifier for this rule.
     *
     * Used for debugging, logging, and rule identification.
     *
     * @return string Unique rule identifier
     */
    public function getName(): string;

    /**
     * Get a detailed description of what this rule validates and what it prevents.
     *
     * @return string Detailed description of the rule's purpose and validations
     */
    public function getDescription(): string;
}
