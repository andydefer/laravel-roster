<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Support\Facades\App;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that schedulable information is consistent between entities and their parent availability.
 *
 * Ensures that schedules and impediments belong to the same schedulable entity
 * as their parent availability to maintain data integrity.
 */
#[ValidationRule(
    priority: 95,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE]
)]
class SchedulableConsistencyRule extends AbstractRule
{
    /**
     * Validates schedulable consistency between entity and parent availability.
     *
     * Checks that:
     * 1. Schedulable information is provided
     * 2. The parent availability exists
     * 3. Both entities belong to the same schedulable
     *
     * @param ValidationContextInterface $validationContext Validation context with entity data
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entityType = $validationContext->getEntityType();

        if (!in_array($entityType, [EntityType::SCHEDULE, EntityType::IMPEDIMENT])) {
            return;
        }

        if (!$this->hasRequiredSchedulableData($validationContext)) {
            return;
        }

        $schedulableId = $validationContext->get('schedulable_id');
        $schedulableType = $validationContext->get('schedulable_type');

        $availability = $this->getParentAvailability($validationContext);
        if (!$availability instanceof Availability) {
            return; // AvailabilityOwnershipRule will handle missing availability
        }

        if (!$this->isSchedulableConsistent($availability, $schedulableId, $schedulableType)) {
            $this->addSchedulableMismatchViolation($validationContext);
        }
    }

    /**
     * Checks if required schedulable data is present.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return bool True if both schedulable_id and schedulable_type are present
     */
    private function hasRequiredSchedulableData(ValidationContextInterface $validationContext): bool
    {
        if (!$validationContext->has('schedulable_id') || !$validationContext->has('schedulable_type')) {
            $validationContext->setViolation(
                'schedulable',
                'Schedulable information is required'
            );
            return false;
        }

        return true;
    }

    /**
     * Retrieves the parent availability entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return Availability|null Parent availability or null if not found
     */
    private function getParentAvailability(ValidationContextInterface $validationContext): ?Availability
    {
        $availabilityId = $validationContext->get('availability_id');
        if (!$availabilityId) {
            return null; // AvailabilityOwnershipRule will handle missing availability_id
        }

        App::make(AvailabilityRepositoryInterface::class);
        return $validationContext->getAvailabilityService()->find($availabilityId);
    }

    /**
     * Checks if schedulable information is consistent between entities.
     *
     * @param Availability $availability Parent availability
     * @param mixed $schedulableId Entity schedulable identifier
     * @param string $schedulableType Entity schedulable type
     * @return bool True if schedulable information matches
     */
    private function isSchedulableConsistent(
        Availability $availability,
        mixed $schedulableId,
        string $schedulableType
    ): bool {
        return $availability->schedulable_id == $schedulableId
            && $availability->schedulable_type === $schedulableType;
    }

    /**
     * Adds a violation for schedulable mismatch.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function addSchedulableMismatchViolation(ValidationContextInterface $validationContext): void
    {
        $validationContext->setViolation(
            'schedulable',
            "Schedulable information does not match the availability's schedulable"
        );
    }
}
