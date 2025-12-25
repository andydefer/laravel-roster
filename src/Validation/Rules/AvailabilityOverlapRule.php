<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Exception;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 80,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityOverlapRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $currentEntity = $validationContext->getCurrentEntity();

        // Pour UPDATE, si l'entité n'existe pas, on ne peut pas valider les chevauchements
        if ($operationType === OperationType::UPDATE && !$currentEntity) {
            return;
        }

        // Vérifier qu'on a tous les champs nécessaires pour la validation
        $requiredFields = ['daily_start', 'daily_end', 'days', 'validity_start', 'validity_end'];

        foreach ($requiredFields as $requiredField) {
            if (!$validationContext->has($requiredField)) {
                // Si en UPDATE et que le champ n'est pas fourni, on vérifie si l'entité existante l'a
                if ($operationType === OperationType::UPDATE && $currentEntity) {
                    // On peut continuer car la valeur sera récupérée depuis l'entité existante
                    continue;
                }

                // En CREATE ou si champ manquant en UPDATE sans entité, on ne peut pas valider
                return;
            }
        }

        try {
            $schedulable = $validationContext->getSchedulable();
            if (!$schedulable instanceof Model) {
                return;
            }

            $excludeId = $currentEntity ? $currentEntity->id : null;

            // Construire les données pour vérifier le chevauchement
            // Pour UPDATE, utiliser les valeurs fournies ou celles de l'entité existante
            $data = [
                'daily_start' => $this->getFieldValue($validationContext, $currentEntity, 'daily_start'),
                'daily_end' => $this->getFieldValue($validationContext, $currentEntity, 'daily_end'),
                'days' => $this->getFieldValue($validationContext, $currentEntity, 'days'),
                'validity_start' => $this->getFieldValue($validationContext, $currentEntity, 'validity_start'),
                'validity_end' => $this->getFieldValue($validationContext, $currentEntity, 'validity_end'),
                'type' => $this->getFieldValue($validationContext, $currentEntity, 'type'),
            ];

            // Vérifier qu'on a toutes les valeurs nécessaires
            foreach ($data as $key => $value) {
                if ($value === null && in_array($key, ['daily_start', 'daily_end', 'days', 'validity_start', 'validity_end'])) {
                    // Champ critique manquant, on ne peut pas valider
                    return;
                }
            }

            $availabilityRepository = app(AvailabilityRepositoryInterface::class);
            $overlapping = $availabilityRepository->findOverlapping($schedulable, $data, $excludeId);


            if ($overlapping->isNotEmpty()) {
                $firstOverlap = $overlapping->first();
                $validationContext->setViolation(
                    'overlap',
                    "Availability overlaps with an existing availability {#$firstOverlap->id} -> type : {$firstOverlap->type} {$firstOverlap->validity_start} - {$firstOverlap->validity_end} for {$firstOverlap->daily_start}- {$firstOverlap->daily_end} "
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }

    /**
     * Get field value from validation context or existing entity.
     */
    private function getFieldValue(ValidationContextInterface $validationContext, ?object $entity, string $field): mixed
    {
        if ($validationContext->has($field)) {
            return $validationContext->get($field);
        }

        if ($entity && isset($entity->{$field})) {
            return $entity->{$field};
        }

        return null;
    }
}
