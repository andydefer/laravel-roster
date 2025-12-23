<?php

declare(strict_types=1);

namespace Roster\Validation\Attributes;

use Attribute;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[Attribute(Attribute::TARGET_CLASS)]
class ValidationRule
{
    /**
     * @param int|null $priority Priorité d'exécution (plus haut = exécuté en premier)
     * @param array<EntityType> $entities Types d'entités supportés
     * @param array<OperationType> $operations Types d'opérations supportés
     */
    public function __construct(
        public ?int $priority = 50,
        public array $entities = [EntityType::AVAILABILITY, EntityType::SCHEDULE, EntityType::IMPEDIMENT],
        public array $operations = [OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE]
    ) {}
}
