<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Models\Availability;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 90,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityOwnershipRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $availabilityId = $validationContext->get('availability_id');

        // Si CREATE, availability_id doit être présent
        if ($operationType === OperationType::CREATE && !$availabilityId) {
            $validationContext->setViolation(
                'availability_id',
                'Must be linked to an availability'
            );
            return;
        }

        // Pour UPDATE, on utilise l'entité courante si availability_id n'est pas fourni
        $currentEntity = $validationContext->getCurrentEntity();
        if (($operationType === OperationType::UPDATE) && !$availabilityId && $currentEntity) {
            $availabilityId = $currentEntity->availability_id ?? null;
        }

        if (!$availabilityId) {
            // Rien à vérifier si aucun availability_id
            return;
        }


        $schedulable = $validationContext->getSchedulable();
        if (!$schedulable instanceof Model) {
            return; // SchedulableValidationRule doit déjà gérer ça
        }

        $validationContext->getCurrentEntity();


        $availability = $validationContext->getAvailabilityService()->find($availabilityId);

        if (!$availability instanceof Availability) {
            $validationContext->setViolation(
                'availability_id',
                'Invalid availability ID'
            );
            return;
        }


        if (
            $availability->schedulable_id !== $schedulable->id
            || $availability->schedulable_type !== get_class($schedulable)
        ) {
            $validationContext->setViolation(
                'availability_id',
                'Availability does not belong to this schedulable'
            );
        }
    }
}
