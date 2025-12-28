<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates required fields for entity creation and update operations.
 *
 * Ensures that all mandatory fields are present for creation operations
 * and prevents modification of ownership fields during updates.
 */
#[ValidationRule(
    priority: 100,
    entities: [EntityType::AVAILABILITY, EntityType::IMPEDIMENT, EntityType::SCHEDULE],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class RequiredFieldsRule extends AbstractRule
{
    /**
     * Validates required fields based on entity type and operation.
     *
     * For CREATE operations, ensures all mandatory fields are present.
     * For UPDATE operations, allows partial updates but prevents modification
     * of ownership-related fields.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $safeData = $validationContext->safeData();


        $this->validateOwnershipFields($validationContext, $safeData, $operationType);

        if ($operationType === OperationType::CREATE) {
            $this->validateRequiredFields($validationContext, $safeData);
        }
    }

    /**
     * Validates that ownership fields cannot be modified during updates.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array<string, mixed> $safeData Validated input data
     * @param OperationType $operationType Current operation
     */
    private function validateOwnershipFields(
        ValidationContextInterface $validationContext,
        array $safeData,
        OperationType $operationType
    ): void {
        if ($operationType !== OperationType::UPDATE) {
            return;
        }

        $ownershipFields = ['schedulable_id', 'schedulable_type'];

        foreach ($ownershipFields as $ownershipField) {
            if (array_key_exists($ownershipField, $safeData)) {
                $validationContext->setViolation(
                    $ownershipField,
                    sprintf("Field '%s' cannot be changed. Ownership cannot be modified.", $ownershipField)
                );
            }
        }
    }

    /**
     * Validates all required fields are present for creation operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array<string, mixed> $safeData Validated input data
     */
    private function validateRequiredFields(
        ValidationContextInterface $validationContext,
        array $safeData
    ): void {
        $entityType = $validationContext->getEntityType();
        $requiredFields = $this->getRequiredFields($entityType);

        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField, $safeData)) {
                $validationContext->setViolation(
                    $requiredField,
                    sprintf("Field '%s' is required", $requiredField)
                );
            }
        }
    }

    /**
     * Returns required fields for each entity type.
     *
     * @param EntityType $entityType Type of entity
     * @return array<string> Required field names
     */
    private function getRequiredFields(EntityType $entityType): array
    {
        return match ($entityType) {
            EntityType::AVAILABILITY => [
                'type',
                'daily_start',
                'daily_end',
                'days',
                'validity_start',
                'validity_end',
            ],
            EntityType::SCHEDULE => [
                'title',
                'start_datetime',
                'end_datetime',
            ],
            EntityType::IMPEDIMENT => [
                'start_datetime',
                'end_datetime',
                'reason',
            ],
        };
    }
}
