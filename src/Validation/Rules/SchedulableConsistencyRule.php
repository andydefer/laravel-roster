<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Models\Availability;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Illuminate\Support\Facades\App;
use Roster\Contracts\Validation\ValidationContextInterface;

#[ValidationRule(
    priority: 95,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE]
)]
class SchedulableConsistencyRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entityType = $validationContext->getEntityType();

        if (!in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
            return;
        }

        // Vérifier que schedulable_id et schedulable_type sont présents
        if (!$validationContext->has('schedulable_id') || !$validationContext->has('schedulable_type')) {
            $validationContext->setViolation(
                'schedulable',
                'Schedulable information is required'
            );
            return;
        }

        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        // Vérifier que l'availability appartient au même schedulable
        $availabilityId = $validationContext->get('availability_id');
        if (!$availabilityId) {
            return; // AvailabilityOwnershipRule gérera cela
        }

        App::make(AvailabilityRepositoryInterface::class);
        $availability = $validationContext->getAvailabilityService()->find($availabilityId);

        if (!$availability instanceof Availability) {
            return; // AvailabilityOwnershipRule gérera cela
        }

        // Vérifier la cohérence
        if (
            $availability->schedulable_id != $schedulableId
            || $availability->schedulable_type !== $schedulableType
        ) {
            $validationContext->setViolation(
                'schedulable',
                "Schedulable information does not match the availability's schedulable"
            );
        }
    }
}
