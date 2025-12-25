<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Exceptions\InvalidServiceContextException;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Abstract service with centralized validation system.
 * Extends AbstractEntityScopingService to add validation capabilities.
 */
abstract class AbstractValidatingService extends AbstractEntityScopingService
{
    /**
     * Validate data against rules.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $flags Additional flags to pass to validation context
     */
    protected function validate(
        array $data,
        OperationType $operationType,
        ?int $entityId = null,
    ): void {
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


        // End date must be after start date
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
}
