<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 110,
    entities: [EntityType::AVAILABILITY, EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE]
)]
class SchedulableValidationRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $schedulable = $validationContext->getSchedulable();
        $entityType = $validationContext->getEntityType();
        $operationType = $validationContext->getOperation();
        $ownerFields = ['availability_id', 'schedulable_id', 'schedulable_type'];
        $safeData = $validationContext->safeData();

        if (!$schedulable instanceof Model) {
            $validationContext->setViolation(
                'schedulable',
                'No schedulable resource specified. Call for() with a schedulable entity before executing the operation.'
            );
            return;
        }

        // UPDATE : interdire modification des champs propriétaire
        if ($operationType === OperationType::UPDATE) {
            foreach ($ownerFields as $ownerField) {
                if (array_key_exists($ownerField, $safeData)) {
                    $validationContext->setViolation(
                        $ownerField,
                        sprintf("Field '%s' cannot be changed. The owner cannot be modified.", $ownerField)
                    );
                }
            }

            return; // pas besoin de vérifier la cohérence pour UPDATE
        }

        // CREATE ou DELETE : vérifier cohérence
        if (in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
            $this->validateSchedulableConsistency($validationContext, $schedulable);
        }

        if ($entityType === EntityType::AVAILABILITY) {
            $this->validateAvailabilitySchedulable($validationContext, $schedulable);
        }
    }

    private function validateSchedulableConsistency(
        ValidationContextInterface $validationContext,
        Model $model
    ): void {
        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        if (!$schedulableId || !$schedulableType) {
            $validationContext->setViolation(
                'schedulable',
                'Schedulable ID and type are required'
            );
            return;
        }

        if ($schedulableId != $model->getKey()) {
            $validationContext->setViolation(
                'schedulable_id',
                sprintf(
                    'Schedulable ID mismatch. Expected: %d, Got: %d',
                    $model->getKey(),
                    $schedulableId
                )
            );
        }

        if ($schedulableType !== get_class($model)) {
            $validationContext->setViolation(
                'schedulable_type',
                sprintf(
                    'Schedulable type mismatch. Expected: %s, Got: %s',
                    get_class($model),
                    $schedulableType
                )
            );
        }
    }

    private function validateAvailabilitySchedulable(
        ValidationContextInterface $validationContext,
        Model $model
    ): void {
        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        if ($schedulableId !== null || $schedulableType !== null) {
            if ($schedulableId != $model->getKey()) {
                $validationContext->setViolation(
                    'schedulable_id',
                    sprintf(
                        'Schedulable ID mismatch. Expected: %d, Got: %s',
                        $model->getKey(),
                        $schedulableId ?? 'null'
                    )
                );
            }

            if ($schedulableType !== null && $schedulableType !== get_class($model)) {
                $validationContext->setViolation(
                    'schedulable_type',
                    sprintf(
                        'Schedulable type mismatch. Expected: %s, Got: %s',
                        get_class($model),
                        $schedulableType
                    )
                );
            }
        }
    }
}
