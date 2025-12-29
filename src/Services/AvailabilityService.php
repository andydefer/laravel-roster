<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;
use Roster\Validation\DTOs\ViolationData;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing Availability entities.
 *
 * Handles creation, updating, and validation of availability periods
 * with automatic day adjustment based on validity periods.
 */
class AvailabilityService extends AbstractService
{
    /**
     * Entity awaiting deletion (for cascade operations).
     */
    protected ?Availability $pendingDeletion = null;

    /**
     * Creates a new availability with automatic schedulable context.
     *
     * @param array $data Availability data
     * @return mixed Created availability entity
     * @throws ValidationFailedException If validation fails
     */
    public function create(array $data = []): mixed
    {
        $this->requireContext();

        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

        return parent::create($data);
    }

    /**
     * Updates an existing availability.
     *
     * @param int $id Availability identifier
     * @param array $data Update data
     * @return bool True if update successful
     * @throws ValidationFailedException If validation fails or entity not found
     */
    public function update(int $id, array $data): bool
    {
        $entity = $this->find($id);
        $this->assertAvailabilityExists($entity, OperationType::UPDATE);

        $this->data = $data;
        $data['id'] = $id;

        return parent::update($id, $data);
    }

    /**
     * Returns the entity type for this service.
     *
     * @return EntityType Availability entity type
     */
    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::AVAILABILITY;
    }

    /**
     * Validates that an availability entity exists.
     *
     * @param mixed $entity Entity to validate
     * @param OperationType $operationType Current operation
     * @return Availability Validated availability entity
     * @throws ValidationFailedException If entity does not exist
     */
    protected function assertAvailabilityExists(mixed $entity, OperationType $operationType): Availability
    {
        if ($entity instanceof Availability) {
            return $entity;
        }

        throw ValidationFailedException::fromViolations(
            [
                new ViolationData(
                    field: 'id',
                    message: sprintf(
                        '%s with given ID does not exist',
                        EntityType::AVAILABILITY->displayName()
                    )
                )
            ],
            $operationType,
            EntityType::AVAILABILITY
        );
    }
}
