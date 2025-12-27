<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

#[ValidationRule(
    priority: 110,
    entities: [EntityType::AVAILABILITY, EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE]
)]
class SchedulableValidationRule extends AbstractRule
{
    /**
     * Validates schedulable entity consistency across operations.
     *
     * Ensures proper ownership and prevents unauthorized modifications
     * of schedulable relationships for all entity types.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $schedulable = $validationContext->getSchedulable();
        $entityType = $validationContext->getEntityType();
        $operationType = $validationContext->getOperation();
        $ownerFields = ['schedulable_id', 'schedulable_type'];
        $safeData = $validationContext->safeData();

        if (!$schedulable instanceof Model) {
            $validationContext->setViolation(
                'schedulable',
                'No schedulable resource specified. Call for() with a schedulable entity before executing the operation.'
            );
            return;
        }

        if ($operationType === OperationType::UPDATE) {
            $this->validateOwnerFieldsImmutable($validationContext, $ownerFields, $safeData);
            return;
        }

        $this->validateSchedulableConsistency($validationContext, $schedulable, $entityType);
    }

    /**
     * Validates that owner fields cannot be modified during updates.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array<string> $ownerFields Fields representing ownership
     * @param array<string, mixed> $safeData Validated input data
     */
    private function validateOwnerFieldsImmutable(
        ValidationContextInterface $validationContext,
        array $ownerFields,
        array $safeData
    ): void {
        foreach ($ownerFields as $ownerField) {
            if (array_key_exists($ownerField, $safeData)) {
                $validationContext->setViolation(
                    $ownerField,
                    sprintf("Field '%s' cannot be changed. The owner cannot be modified.", $ownerField)
                );
            }
        }
    }

    /**
     * Validates schedulable consistency for create and delete operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $schedulable Schedulable entity
     * @param EntityType $entityType Type of entity being validated
     */
    private function validateSchedulableConsistency(
        ValidationContextInterface $validationContext,
        Model $schedulable,
        EntityType $entityType
    ): void {
        if (in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
            $this->validateChildEntitySchedulable($validationContext, $schedulable);
            return;
        }

        if ($entityType === EntityType::AVAILABILITY) {
            $this->validateAvailabilitySchedulable($validationContext, $schedulable);
        }
    }

    /**
     * Validates schedulable consistency for child entities (Schedule, Impediment).
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $schedulable Schedulable entity
     */
    private function validateChildEntitySchedulable(
        ValidationContextInterface $validationContext,
        Model $schedulable
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

        $this->validateSchedulableId($validationContext, $schedulable, $schedulableId);
        $this->validateSchedulableType($validationContext, $schedulable, $schedulableType);
    }

    /**
     * Validates schedulable identifier matches expected value.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $schedulable Expected schedulable entity
     * @param mixed $providedId Provided schedulable identifier
     */
    private function validateSchedulableId(
        ValidationContextInterface $validationContext,
        Model $schedulable,
        mixed $providedId
    ): void {
        if ($providedId != $schedulable->getKey()) {
            $validationContext->setViolation(
                'schedulable_id',
                sprintf(
                    'Schedulable ID mismatch. Expected: %d, Got: %d',
                    $schedulable->getKey(),
                    $providedId
                )
            );
        }
    }

    /**
     * Validates schedulable type matches expected value.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $schedulable Expected schedulable entity
     * @param string|null $providedType Provided schedulable type
     */
    private function validateSchedulableType(
        ValidationContextInterface $validationContext,
        Model $schedulable,
        ?string $providedType
    ): void {
        if ($providedType !== get_class($schedulable)) {
            $validationContext->setViolation(
                'schedulable_type',
                sprintf(
                    'Schedulable type mismatch. Expected: %s, Got: %s',
                    get_class($schedulable),
                    $providedType
                )
            );
        }
    }

    /**
     * Validates schedulable consistency for Availability entities.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $schedulable Schedulable entity
     */
    private function validateAvailabilitySchedulable(
        ValidationContextInterface $validationContext,
        Model $schedulable
    ): void {
        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        if ($schedulableId !== null) {
            $this->validateSchedulableId($validationContext, $schedulable, $schedulableId);
        }

        if ($schedulableType !== null) {
            $this->validateSchedulableType($validationContext, $schedulable, $schedulableType);
        }
    }
}
