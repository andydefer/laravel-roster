<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates date-time pairs for Schedule and Impediment entities.
 *
 * Ensures that start and end datetime values form valid chronological ranges
 * and handles partial updates intelligently by preserving existing values.
 */
#[ValidationRule(
    priority: 60,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class TimeSlotDateTimeRule extends AbstractRule
{
    /**
     * Validates datetime pairs for Schedule and Impediment operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @throws Exception If datetime parsing fails
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $currentEntity = $validationContext->getCurrentEntity();

        match ($operationType) {
            OperationType::CREATE => $this->validateCreateOperation($validationContext),
            OperationType::UPDATE => $this->validateUpdateOperation($validationContext, $currentEntity),
            default => null
        };
    }

    /**
     * Validates datetime pairs for creation operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateCreateOperation(ValidationContextInterface $validationContext): void
    {
        if (!$this->hasRequiredDateTimeFields($validationContext)) {
            return; // Required field validation handled by another rule
        }

        $startValue = $validationContext->get('start_datetime');
        $endValue = $validationContext->get('end_datetime');

        $this->validateDateTimeChronology($validationContext, $startValue, $endValue);
    }

    /**
     * Validates datetime pairs for update operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param object|null $currentEntity Existing entity
     */
    private function validateUpdateOperation(ValidationContextInterface $validationContext, ?object $currentEntity): void
    {
        $hasStartUpdate = $validationContext->has('start_datetime');
        $hasEndUpdate = $validationContext->has('end_datetime');

        if (!$hasStartUpdate && !$hasEndUpdate) {
            return; // No datetime fields being updated
        }

        $startValue = $this->resolveDateTimeValue(
            context: $validationContext,
            fieldName: 'start_datetime',
            hasUpdate: $hasStartUpdate,
            entityValue: $currentEntity?->start_datetime
        );

        $endValue = $this->resolveDateTimeValue(
            context: $validationContext,
            fieldName: 'end_datetime',
            hasUpdate: $hasEndUpdate,
            entityValue: $currentEntity?->end_datetime
        );

        $this->validateDateTimeChronology($validationContext, $startValue, $endValue);
    }

    /**
     * Validates that end datetime occurs after start datetime.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param mixed $startValue Start datetime value
     * @param mixed $endValue End datetime value
     */
    private function validateDateTimeChronology(
        ValidationContextInterface $validationContext,
        mixed $startValue,
        mixed $endValue
    ): void {
        if ($startValue === null || $endValue === null) {
            return;
        }

        try {
            $startDateTime = Carbon::parse($startValue);
            $endDateTime = Carbon::parse($endValue);

            if ($endDateTime->lte($startDateTime)) {
                $validationContext->setViolation(
                    'datetime_range',
                    'End datetime must be after start datetime'
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation(
                'datetime_format',
                "Invalid datetime format: " . $exception->getMessage()
            );
        }
    }

    /**
     * Checks if required datetime fields are present.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return bool True if both datetime fields are present
     */
    private function hasRequiredDateTimeFields(ValidationContextInterface $validationContext): bool
    {
        return $validationContext->has('start_datetime') && $validationContext->has('end_datetime');
    }

    /**
     * Resolves datetime value from update context or existing entity.
     *
     * @param ValidationContextInterface $context Validation context
     * @param string $fieldName Datetime field name
     * @param bool $hasUpdate Whether field is being updated
     * @param mixed $entityValue Existing entity value
     * @return mixed Resolved datetime value
     */
    private function resolveDateTimeValue(
        ValidationContextInterface $context,
        string $fieldName,
        bool $hasUpdate,
        mixed $entityValue
    ): mixed {
        return $hasUpdate ? $context->get($fieldName) : $entityValue;
    }
}
