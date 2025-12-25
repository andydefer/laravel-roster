<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Exceptions\InvalidServiceContextException;
use Roster\Models\Availability;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Abstract service for availability-dependent entities with centralized validation.
 * Supports both array-only and Availability+array signatures.
 */
abstract class AbstractAvailabilityValidatingService extends AbstractEntityScopingService
{

    protected ?Availability $currentAvailability = null;

    protected mixed $currentEntity = null;

    /**
     * Validate data using the centralized validation system.
     */
    protected function validate(array $data, OperationType $operationType, ?int $entityId = null, ?object $currentEntity = null): void
    {
        $entityType = $this->getEntityTypeEnum();

        // Si currentEntity n'est pas fourni mais entityId l'est, essayer de le trouver
        if ($currentEntity === null && $entityId !== null) {
            $currentEntity = $this->find($entityId);
        }

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

    protected function requireContext(): void
    {
        // Appelle d'abord la validation du parent (schedulable)
        parent::requireContext();

        // Services dérivés (Schedule, Impediment) nécessitent un owner
        if (!$this->owner instanceof Model) {
            throw InvalidServiceContextException::forService(static::class);
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




    abstract protected function clearEntityCache(int $entityId): void;
}
