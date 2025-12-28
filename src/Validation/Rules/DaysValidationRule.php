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
            $validationContext->setViolation(
                'days',
                'Days must be an array'
            );
            return;
        }

        if ($days === []) {
            $validationContext->setViolation(
                'days',
                'Days array cannot be empty'
            );
            return;
        }

        $validDays = DaysOfWeek::values();
        foreach ($days as $day) {
            if (!in_array($day, $validDays, true)) {
                $validationContext->setViolation(
                    'days',
                    sprintf("Invalid day '%s'. Valid days are: %s", $day, implode(', ', $validDays))
                );
                return;
            }
        }
    }
}
