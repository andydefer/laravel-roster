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
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'schedulable',
                message: 'No schedulable resource specified. Call for() with a schedulable entity before executing the operation.'
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
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates the consistency and integrity of schedulable entity relationships across availability, schedule, and impediment operations. It ensures that the specified schedulable resource (provided via the for() method) matches the entity's ownership fields (schedulable_id and schedulable_type), prevents unauthorized modification of ownership relationships during UPDATE operations, and maintains proper reference consistency for all entity types and operations (CREATE, UPDATE, DELETE). The rule also validates that child entities (schedules and impediments) have properly defined ownership fields while allowing more flexible ownership specification for availability entities.";
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
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: $ownerField,
                    message: sprintf("Field '%s' cannot be changed. The owner cannot be modified.", $ownerField)
                );
            }
        }
    }

    /**
     * Validates schedulable consistency for create and delete operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $model Schedulable entity
     * @param EntityType $entityType Type of entity being validated
     */
    private function validateSchedulableConsistency(
        ValidationContextInterface $validationContext,
        Model $model,
        EntityType $entityType
    ): void {
        if ($validationContext->getOperation() === OperationType::DELETE) {
            return;
        }

        if (in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
            $this->validateChildEntitySchedulable($validationContext, $model);
            return;
        }

        $this->validateAvailabilitySchedulable($validationContext, $model);
    }

    /**
     * Validates schedulable consistency for child entities (Schedule, Impediment).
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $model Schedulable entity
     */
    private function validateChildEntitySchedulable(
        ValidationContextInterface $validationContext,
        Model $model
    ): void {
        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        if (!$schedulableId || !$schedulableType) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'schedulable',
                message: 'Schedulable ID and type are required'
            );
            return;
        }

        $this->validateSchedulableId($validationContext, $model, $schedulableId);
        $this->validateSchedulableType($validationContext, $model, $schedulableType);
    }

    /**
     * Validates schedulable identifier matches expected value.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $model Expected schedulable entity
     * @param mixed $providedId Provided schedulable identifier
     */
    private function validateSchedulableId(
        ValidationContextInterface $validationContext,
        Model $model,
        mixed $providedId
    ): void {
        if ($providedId != $model->getKey()) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'schedulable_id',
                message: sprintf(
                    'Schedulable ID mismatch. Expected: %d, Got: %d',
                    $model->getKey(),
                    $providedId
                )
            );
        }
    }

    /**
     * Validates schedulable type matches expected value.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $model Expected schedulable entity
     * @param string|null $providedType Provided schedulable type
     */
    private function validateSchedulableType(
        ValidationContextInterface $validationContext,
        Model $model,
        ?string $providedType
    ): void {
        if ($providedType !== get_class($model)) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'schedulable_type',
                message: sprintf(
                    'Schedulable type mismatch. Expected: %s, Got: %s',
                    get_class($model),
                    $providedType
                )
            );
        }
    }

    /**
     * Validates schedulable consistency for Availability entities.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Model $model Schedulable entity
     */
    private function validateAvailabilitySchedulable(
        ValidationContextInterface $validationContext,
        Model $model
    ): void {
        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        if ($schedulableId !== null) {
            $this->validateSchedulableId($validationContext, $model, $schedulableId);
        }

        if ($schedulableType !== null) {
            $this->validateSchedulableType($validationContext, $model, $schedulableType);
        }
    }
}
