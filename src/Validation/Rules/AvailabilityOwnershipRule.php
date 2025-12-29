<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that entities are properly linked to an availability owned by the schedulable.
 *
 * Ensures that schedules and impediments reference valid availability periods
 * that belong to the current schedulable entity.
 */
#[ValidationRule(
    priority: 90,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityOwnershipRule extends AbstractRule
{
    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates that schedule and impediment entities are properly linked to availability periods owned by the schedulable entity, ensuring referential integrity and ownership consistency. It verifies that the referenced availability exists, belongs to the same schedulable (matching both ID and type), and prevents unauthorized cross-entity references across CREATE and UPDATE operations.";
    }

    /**
     * Validates availability ownership for schedule and impediment entities.
     *
     * @param ValidationContextInterface $validationContext Validation context with entity data
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $availabilityId = $validationContext->get('availability_id');
        $schedulable = $validationContext->getSchedulable();

        if (!$schedulable instanceof Model) {
            return;
        }

        $resolved = $this->resolveAvailabilityId(
            operationType: $operationType,
            providedAvailabilityId: $availabilityId,
            currentEntity: $validationContext->getCurrentEntity()
        );

        $availabilityId = is_array($resolved) ? $resolved['id'] : $resolved;
        $fromExisting = is_array($resolved) ? $resolved['from_existing'] : false;

        if ($operationType === OperationType::CREATE && !$availabilityId) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'availability_id',
                message: 'Schedule or impediment must be linked to an availability period'
            );
            return;
        }

        if (!$availabilityId) {
            return;
        }

        if (!$fromExisting) {
            $this->validateAvailabilityOwnership(
                validationContext: $validationContext,
                availabilityId: $availabilityId,
                model: $schedulable
            );
        }
    }

    /**
     * Resolves the availability ID based on operation type and entity state.
     *
     * @param OperationType $operationType Current operation type
     * @param mixed $providedAvailabilityId Availability ID from request data
     * @param mixed $currentEntity Current entity instance
     * @return mixed Resolved availability ID
     */
    private function resolveAvailabilityId(
        OperationType $operationType,
        mixed $providedAvailabilityId,
        mixed $currentEntity
    ): mixed {
        if ($operationType === OperationType::UPDATE && !$providedAvailabilityId && $currentEntity) {
            return [
                'id' => $currentEntity->availability_id ?? null,
                'from_existing' => true
            ];
        }

        return $providedAvailabilityId;
    }

    /**
     * Validates that the availability belongs to the schedulable.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param mixed $availabilityId Availability identifier
     * @param Model $model Schedulable entity
     */
    private function validateAvailabilityOwnership(
        ValidationContextInterface $validationContext,
        mixed $availabilityId,
        Model $model
    ): void {
        $availability = $validationContext->getAvailabilityService()->find($availabilityId);

        if (!$availability instanceof Availability) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'availability_id',
                message: 'Referenced availability period does not exist or is invalid'
            );
            return;
        }

        $isOwner = $availability->schedulable_id === $model->id
            && $availability->schedulable_type === get_class($model);

        if (!$isOwner) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'availability_id',
                message: 'Referenced availability period does not belong to this schedulable entity'
            );
        }
    }
}
