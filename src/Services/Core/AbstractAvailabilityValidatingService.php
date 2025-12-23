<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Abstract service for availability-dependent entities with centralized validation.
 * Supports both array-only and Availability+array signatures.
 */
abstract class AbstractAvailabilityValidatingService extends AbstractEntityScopingService
{
    protected ValidatorInterface $validator;

    protected ?Availability $currentAvailability = null;

    protected mixed $currentEntity = null;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate data using the centralized validation system.
     */
    protected function validate(array $data, OperationType $operationType, ?int $entityId = null): void
    {
        $entityType = $this->getEntityTypeEnum();
        $currentEntity = $entityId ? $this->find($entityId) : null;

        $validationContext = new ValidationContext(
            operationType: $operationType,
            entityType: $entityType,
            data: $data,
            model: $this->schedulable,
            currentEntity: $currentEntity
        );

        $validationResult = $this->validator->validate($validationContext);

        if (!$validationResult->isValid()) {
            throw ValidationFailedException::fromViolations(
                $validationResult->getViolations(),
                $operationType,
                $entityType
            );
        }

    }

    /**
     * Template method for DTO creation from array.
     */
    abstract protected function createDTOFromArray(array $data, OperationType $operationType): mixed;

    /**
     * Get the entity type as an enum.
     */
    abstract protected function getEntityTypeEnum(): EntityType;

    /**
     * Get entities between two dates.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @return Collection Entities within the date range
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateDateRange($start, $end);

        return $this->buildQueryWithFilters()
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Validate date range using validation rules.
     */
    protected function validateDateRange(Carbon $start, Carbon $end): void
    {
        // Créer un contexte de validation pour la plage de dates
        $data = [
            'start_datetime' => $start->toDateTimeString(),
            'end_datetime' => $end->toDateTimeString(),
        ];

        // Utiliser un type d'entité générique pour la validation
        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE, // Type générique pour validation
            data: $data,
            model: $this->schedulable
        );

        $validationResult = $this->validator->validate($validationContext);

        if (!$validationResult->isValid()) {
            throw ValidationFailedException::fromViolations(
                $validationResult->getViolations(),
                OperationType::CREATE,
                EntityType::SCHEDULE
            );
        }
    }

    // Abstract methods
    abstract protected function executeCreate(): mixed;

    abstract protected function executeUpdate(int $id): bool;

    abstract protected function executeDelete(int $id): bool;

    abstract public function find(int $id): mixed;

    abstract public function get(): Collection;

    abstract protected function buildQueryWithFilters(): Builder;

    abstract protected function clearEntityCache(int $entityId): void;

    abstract protected function getAvailabilityRepository(): mixed;

    abstract protected function getScheduleRepository(): mixed;

    abstract protected function getImpedimentRepository(): mixed;
}
