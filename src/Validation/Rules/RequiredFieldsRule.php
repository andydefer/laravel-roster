<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 100,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class RequiredFieldsRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entityType = $validationContext->getEntityType();
        $operationType = $validationContext->getOperation();

        $ownerFields = ['schedulable_id', 'schedulable_type'];
        $safeData = $validationContext->safeData();

        // Vérifier les champs propriétaires pour CREATE et UPDATE
        if ($operationType === OperationType::UPDATE) {

            foreach ($ownerFields as $ownerField) {
                if (array_key_exists($ownerField, $safeData)) {
                    $validationContext->setViolation(
                        $ownerField,
                        sprintf("Field '%s' cannot be changed. The owner cannot be modified.", $ownerField)
                    );
                }
            }
        }

        if ($operationType === OperationType::UPDATE) {
            // Pour UPDATE, tout est optionnel (mises à jour partielles permises)
            return;
        }

        // CREATE : tous les champs doivent être présents
        $requiredFields = $this->getRequiredFields($entityType);
        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField, $safeData)) {
                $validationContext->setViolation(
                    $requiredField,
                    sprintf("Field '%s' is required", $requiredField)
                );
            }
        }
    }

    private function getRequiredFields(EntityType $entityType): array
    {
        return match ($entityType) {
            EntityType::AVAILABILITY => [
                'type',            // Type de disponibilité
                'daily_start',     // Heure de début quotidienne
                'daily_end',       // Heure de fin quotidienne
                'days',            // Jours de la semaine
                'validity_start',  // Date de début de validité
                'validity_end',    // Date de fin de validité
            ],
            EntityType::SCHEDULE => [
                'title',            // Titre du schedule (NOT NULL dans migration)
                'start_datetime',   // Date/heure de début
                'end_datetime',   // Date/heure de fin

            ],
            EntityType::IMPEDIMENT => [
                'start_datetime',   // Date/heure de début
                'end_datetime',     // Date/heure de fin
                'reason',           // Raison de l'impediment
            ],
        };
    }
}
