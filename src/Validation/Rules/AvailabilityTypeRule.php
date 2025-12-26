<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 80,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityTypeRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        // Si le champ n’est pas présent (PATCH / UPDATE partiel)
        if (!$validationContext->has('type')) {
            return;
        }

        $type = $validationContext->get('type');

        // null = absent (safeData semantics)
        if ($type === null) {
            return;
        }

        // Charger les types autorisés
        $allowedTypes = config('roster.availability.types', []);

        // Si la config est vide → tout est permis
        if ($allowedTypes === []) {
            return;
        }


        // Validation stricte
        if (!in_array($type, $allowedTypes, true)) {
            $validationContext->setViolation(
                'type',
                sprintf("Invalid type '%s'", $type)
            );
        }
    }
}
