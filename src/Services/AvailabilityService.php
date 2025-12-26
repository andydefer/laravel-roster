<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\DTOs\AvailabilityData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;

class AvailabilityService extends AbstractService
{
    protected ?Availability $pendingDeletion = null;

    protected function createDTOFromArray(array $data, OperationType $operationType): AvailabilityData
    {
        $dto = AvailabilityData::fromArray($data);

        // Handle days automatically
        if ($operationType === OperationType::CREATE) {
            $adjustedDays = $dto->getAutoAdjustedDays();
            $dto = $dto->withDaysInfo($adjustedDays);
        } elseif ($operationType === OperationType::UPDATE && isset($data['id'])) {
            $entity = $this->find($data['id']);
            if ($entity instanceof Availability) {
                if (array_key_exists('days', $data)) {
                    $dto = $dto->withDaysInfo($data['days']);
                } else {
                    $dto = $dto->withAutoFilteredDaysForUpdate(
                        $entity->days,
                        $entity->validity_start,
                        $entity->validity_end
                    );
                }
            }
        }

        return $dto;
    }

    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::AVAILABILITY;
    }

    protected function addSchedulableInfoToDto(mixed $dto): mixed
    {
        if (method_exists($dto, 'withSchedulableInfo')) {
            $dto = $dto->withSchedulableInfo(
                $this->schedulable->id,
                get_class($this->schedulable)
            );
        }

        // Auto-adjust days for creation
        if ($dto instanceof AvailabilityData) {
            $adjustedDays = $dto->getAutoAdjustedDays();
            $dto = $dto->withDaysInfo($adjustedDays);
        }

        return $dto;
    }

    // Override create to add auto-adjusted days
    public function create(array $data = []): mixed
    {
        $this->requireContext();

        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

        return parent::create($data);
    }

    // Override update to handle days properly
    public function update(int $id, array $data): bool
    {
        $entity = $this->find($id);
        if (!$entity instanceof Availability) {
            throw \Roster\Validation\Exceptions\ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::AVAILABILITY->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::AVAILABILITY
            );
        }

        $this->data = $data;
        $data['id'] = $id;

        return parent::update($id, $data);
    }
}
