<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

#[ValidationRule(
    priority: 90,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::UPDATE]
)]
class AvailabilityDaysInPeriodRule extends AbstractRule
{
    /**
     * Validates that provided days are within the new validity period.
     *
     * @param ValidationContextInterface $validationContext
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        // No days provided → nothing to validate
        if (! $validationContext->has('days')) {
            return;
        }

        $days = $validationContext->rawGet('days');

        if (! is_array($days) || $days === []) {
            return;
        }

        $validityStart = $validationContext->has('validity_start')
            ? Carbon::parse($validationContext->get('validity_start'))
            : $validationContext->getCurrentEntity()?->validity_start;

        $validityEnd = $validationContext->has('validity_end')
            ? Carbon::parse($validationContext->get('validity_end'))
            : $validationContext->getCurrentEntity()?->validity_end;

        if (! $validityStart || ! $validityEnd) {
            return;
        }


        $periodDays = roster_days_in_period($validityStart, $validityEnd);

        $invalidDays = array_diff($days, $periodDays);

        if ($invalidDays !== []) {
            $this->setDaysViolation($validationContext, $invalidDays, $periodDays);
        }
    }

    /**
     * Returns a detailed description of the rule.
     */
    public function getDescription(): string
    {
        return "This rule ensures that, during an availability update, all provided days belong to the new validity period. It prevents invalid day assignments and guarantees temporal consistency without mutating the payload.";
    }

    /**
     * Sets a validation violation for invalid days.
     */
    private function setDaysViolation(
        ValidationContextInterface $validationContext,
        array $invalidDays,
        array $periodDays
    ): void {
        $validationContext->setViolationFromRule(
            rule: $this,
            field: 'days',
            message: sprintf(
                "Invalid day(s): [%s]. Allowed days in the new validity period are: [%s].",
                implode(', ', $invalidDays),
                implode(', ', $periodDays)
            )
        );
    }
}
