<?php

declare(strict_types=1);

namespace Roster\Contracts\Validation;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\ValidationResult;

interface ValidatorInterface
{
    public function validate(
        ValidationContextInterface $validationContext,
        array $additionalRules = []
    ): ValidationResult;

    public function addRule(RuleInterface $rule): void;

    /**
     * @return array<RuleInterface>
     */
    public function getRulesFor(OperationType $operationType, EntityType $entityType): array;

    public function hasRulesFor(OperationType $operationType, EntityType $entityType): bool;
}
