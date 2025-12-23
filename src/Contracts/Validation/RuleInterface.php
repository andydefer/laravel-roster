<?php

declare(strict_types=1);

namespace Roster\Contracts\Validation;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

interface RuleInterface
{
    public function validate(ValidationContextInterface $validationContext): void;

    public function supports(OperationType $operationType, EntityType $entityType): bool;

    public function getPriority(): int;

    public function getName(): string;
}
