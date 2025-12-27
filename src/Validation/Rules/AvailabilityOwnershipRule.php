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
     * Validates availability ownership for schedule and impediment entities.
     *
     * @param ValidationContextInterface $validationContext Validation context with entity data
     * @return void
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $availabilityId = $validationContext->get('availability_id');
        $schedulable = $validationContext->getSchedulable();

        if (!$schedulable instanceof Model) {
            return; // SchedulableValidationRule handles this case
        }

        $availabilityId = $this->resolveAvailabilityId(
            operationType: $operationType,
            providedAvailabilityId: $availabilityId,
            currentEntity: $validationContext->getCurrentEntity()
        );

        if ($operationType === OperationType::CREATE && !$availabilityId) {
            $validationContext->setViolation(
                'availability_id',
                'Must be linked to an availability'
            );
            return;
        }

        if (!$availabilityId) {
            return; // No availability to validate
        }

        $this->validateAvailabilityOwnership(
            validationContext: $validationContext,
            availabilityId: $availabilityId,
            schedulable: $schedulable
        );
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
            return $currentEntity->availability_id ?? null;
        }

        return $providedAvailabilityId;
    }

    /**
     * Validates that the availability belongs to the schedulable.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param mixed $availabilityId Availability identifier
     * @param Model $schedulable Schedulable entity
     * @return void
     */
    private function validateAvailabilityOwnership(
        ValidationContextInterface $validationContext,
        mixed $availabilityId,
        Model $schedulable
    ): void {
        $availability = $validationContext->getAvailabilityService()->find($availabilityId);

        if (!$availability instanceof Availability) {
            $validationContext->setViolation(
                'availability_id',
                'Invalid availability ID'
            );
            return;
        }

        $isOwner = $availability->schedulable_id === $schedulable->id
            && $availability->schedulable_type === get_class($schedulable);

        if (!$isOwner) {
            $validationContext->setViolation(
                'availability_id',
                'Availability does not belong to this schedulable'
            );
        }
    }
}
