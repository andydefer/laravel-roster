<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\DaysOfWeek;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 90,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class DaysValidationRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();

        // Valider uniquement lors de la création
        if ($operationType === OperationType::CREATE) {
            $this->validateForCreate($validationContext);
        }
    }

    private function validateForCreate(ValidationContextInterface $validationContext): void
    {
        // Si days n'est pas fourni, le DTO appliquera la valeur par défaut
        if (!$validationContext->has('days')) {
            return;
        }

        $days = $validationContext->get('days');
        $this->validateDaysArray($days, $validationContext);
    }

    private function validateDaysArray(mixed $days, ValidationContextInterface $validationContext): void
    {
        if (!is_array($days)) {
            $validationContext->setViolation(
                'days',
                'Days must be an array'
            );
            return;
        }

        if ($days === []) {
            $validationContext->setViolation(
                'days',
                'Days array cannot be empty'
            );
            return;
        }

        // Valider que chaque jour est valide
        $validDays = DaysOfWeek::values();
        foreach ($days as $day) {
            if (!in_array($day, $validDays, true)) {
                $validationContext->setViolation(
                    'days',
                    sprintf("Invalid day '%s'. Valid days are: %s", $day, implode(', ', $validDays))
                );
                return;
            }
        }
    }
}
