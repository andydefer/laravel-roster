<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 60,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class TimeSlotDateTimeRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $entity = $validationContext->getCurrentEntity();

        if ($operationType === OperationType::CREATE) {
            $this->validateCreate($validationContext);
        } else {
            $this->validateUpdate($validationContext, $entity);
        }
    }

    private function validateCreate(ValidationContextInterface $validationContext): void
    {
        // Pour CREATE : les deux datetime doivent être fournies
        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
            return; // Validation des champs requis gérée par une autre règle
        }

        $startValue = $validationContext->get('start_datetime');
        $endValue = $validationContext->get('end_datetime');

        $this->validateDateTimePair($validationContext, $startValue, $endValue);
    }

    private function validateUpdate(ValidationContextInterface $validationContext, ?object $entity): void
    {
        // UPDATE : logique intelligente
        $hasStart = $validationContext->has('start_datetime');
        $hasEnd = $validationContext->has('end_datetime');

        // Si aucune des deux datetime n'est modifiée, on ne valide pas
        if (!$hasStart && !$hasEnd) {
            return;
        }

        // Récupérer les valeurs
        $startValue = $hasStart
            ? $validationContext->get('start_datetime')
            : ($entity?->start_datetime ?? null);

        $endValue = $hasEnd
            ? $validationContext->get('end_datetime')
            : ($entity?->end_datetime ?? null);

        // Maintenant on a les deux valeurs (soit du contexte, soit de l'entité)
        $this->validateDateTimePair($validationContext, $startValue, $endValue);
    }

    private function validateDateTimePair(
        ValidationContextInterface $validationContext,
        mixed $startValue,
        mixed $endValue
    ): void {
        // Maintenant on devrait toujours avoir les deux valeurs
        if ($startValue === null || $endValue === null) {
            return;
        }

        try {
            $start = Carbon::parse($startValue);
            $end = Carbon::parse($endValue);

            if ($end->lte($start)) {
                $validationContext->setViolation(
                    'datetime_range',
                    'End datetime must be after start datetime'
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation('datetime_format', "Invalid datetime format: " . $exception->getMessage());
        }
    }
}
