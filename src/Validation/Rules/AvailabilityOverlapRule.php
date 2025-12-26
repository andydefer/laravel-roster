<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Roster\Domain\Services\TemporalConflictService;
use Exception;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 80,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityOverlapRule extends AbstractRule
{
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

            $data = [
                'daily_start' => $this->getFieldValue($validationContext, $currentEntity, 'daily_start'),
                'daily_end' => $this->getFieldValue($validationContext, $currentEntity, 'daily_end'),
                'days' => $this->getFieldValue($validationContext, $currentEntity, 'days'),
                'validity_start' => $this->getFieldValue($validationContext, $currentEntity, 'validity_start'),
                'validity_end' => $this->getFieldValue($validationContext, $currentEntity, 'validity_end'),
                'type' => $this->getFieldValue($validationContext, $currentEntity, 'type'),
            ];

            // Vérifier les champs critiques
            foreach (['daily_start', 'daily_end', 'days'] as $field) {
                if (empty($data[$field])) {
                    return;
                }
            }

            $excludeId = $currentEntity ? $currentEntity->id : null;
            $conflictService = app(TemporalConflictService::class);
            $conflictResult = $conflictService->checkAvailabilityConflicts(
                $schedulable,
                $data,
                $excludeId
            );

            if ($conflictResult->hasConflicts) {
                $validationContext->setViolation('overlap', $conflictResult->message);
            }
        } catch (Exception $exception) {
            report($exception);
        }
    }

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
