<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that availability periods do not overlap with existing ones.
 *
 * Ensures that new or updated availability periods do not conflict with
 * existing availability periods for the same schedulable entity, considering
 * daily times, days of week, and validity periods.
 */
#[ValidationRule(
    priority: 80,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityOverlapRule extends AbstractRule
{
    /**
     * Validates availability overlap constraints.
     *
     * Checks if the proposed availability period overlaps with existing
     * availability periods for the same schedulable entity, considering
     * daily schedules, active days, and validity ranges.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $currentEntity = $validationContext->getCurrentEntity();

        if ($operationType === OperationType::UPDATE && !$currentEntity) {
            return;
        }

        try {
            $schedulable = $validationContext->getSchedulable();
            if (!$schedulable instanceof Model) {
                return;
            }

            $data = $this->extractAvailabilityData($validationContext, $currentEntity);

            if (!$this->hasRequiredFields($data)) {
                return;
            }

            $excludeId = $currentEntity ? $currentEntity->id : null;
            $conflictService = app(TemporalConflictService::class);
            $conflictResult = $conflictService->checkAvailabilityConflicts(
                model: $schedulable,
                availabilityData: $data,
                excludeId: $excludeId
            );

            if ($conflictResult->hasConflicts) {
                $validationContext->setViolation('overlap', $conflictResult->message);
            }
        } catch (Exception $exception) {
            report($exception);
        }
    }

    /**
     * Extracts availability data from validation context and current entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param object|null $currentEntity Current availability entity
     * @return array<string, mixed> Extracted availability data
     */
    private function extractAvailabilityData(
        ValidationContextInterface $validationContext,
        ?object $currentEntity
    ): array {
        return [
            'daily_start' => $this->getFieldValue($validationContext, $currentEntity, 'daily_start'),
            'daily_end' => $this->getFieldValue($validationContext, $currentEntity, 'daily_end'),
            'days' => $this->getFieldValue($validationContext, $currentEntity, 'days'),
            'validity_start' => $this->getFieldValue($validationContext, $currentEntity, 'validity_start'),
            'validity_end' => $this->getFieldValue($validationContext, $currentEntity, 'validity_end'),
            'type' => $this->getFieldValue($validationContext, $currentEntity, 'type'),
        ];
    }

    /**
     * Checks if all required availability fields are present.
     *
     * @param array<string, mixed> $data Availability data
     * @return bool True if all required fields are present
     */
    private function hasRequiredFields(array $data): bool
    {
        $requiredFields = ['daily_start', 'daily_end', 'days'];

        foreach ($requiredFields as $requiredField) {
            if (empty($data[$requiredField])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves field value from either validation context or current entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param object|null $entity Current entity
     * @param string $field Field name
     * @return mixed Field value or null
     */
    private function getFieldValue(
        ValidationContextInterface $validationContext,
        ?object $entity,
        string $field
    ): mixed {
        if ($validationContext->has($field)) {
            return $validationContext->get($field);
        }

        if ($entity && isset($entity->{$field})) {
            return $entity->{$field};
        }

        return null;
    }
}
