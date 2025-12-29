<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\DaysOfWeek;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates days configuration for Availability entities.
 *
 * Ensures days arrays are properly formatted, non-empty, and contain
 * valid day names according to the DaysOfWeek enum.
 */
#[ValidationRule(
    priority: 90,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class DaysValidationRule extends AbstractRule
{
    /**
     * Validates days configuration based on operation type.
     *
     * @param ValidationContextInterface $validationContext Validation context with data
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();

        if ($operationType === OperationType::CREATE) {
            $this->validateForCreate($validationContext);
        }
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates the 'days' configuration for Availability entities, ensuring that the days array is properly formatted, contains valid day names according to the DaysOfWeek enum, and is non-empty. It applies strict validation for CREATE operations to maintain data integrity from the outset, allowing only lowercase day names that match the predefined enum values.";
    }

    /**
     * Validates days array for creation operations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateForCreate(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('days')) {
            return;
        }

        $days = $validationContext->get('days');
        $this->validateDaysArray($days, $validationContext);
    }

    /**
     * Validates the structure and content of a days array.
     *
     * @param mixed $days Days data to validate
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateDaysArray(mixed $days, ValidationContextInterface $validationContext): void
    {
        if (!is_array($days)) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'days',
                message: 'Days must be an array'
            );
            return;
        }

        if ($days === []) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'days',
                message: 'Days array cannot be empty'
            );
            return;
        }

        $validDays = DaysOfWeek::values();
        foreach ($days as $day) {
            if (!in_array($day, $validDays, true)) {
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'days',
                    message: sprintf("Invalid day '%s'. Valid days are: %s", $day, implode(', ', $validDays))
                );
                return;
            }
        }
    }
}
