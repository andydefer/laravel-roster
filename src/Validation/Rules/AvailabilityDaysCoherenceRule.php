<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Carbon\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\DaysOfWeek;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that provided days are coherent with the availability validity period.
 *
 * Ensures that days specified for availability fall within the validity date range
 * and are valid days of the week according to the DaysOfWeek enum.
 */
#[ValidationRule(
    priority: 85,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityDaysCoherenceRule extends AbstractRule
{
    /**
     * Validates day coherence with validity period.
     *
     * Checks that all provided days are valid days of the week and
     * fall within the specified validity start and end dates.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return void
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('days')) {
            return;
        }

        $days = $validationContext->get('days');

        if ($days === null) {
            return;
        }

        if (!is_array($days)) {
            $validationContext->setViolation('days', 'Days must be an array');
            return;
        }

        if ($days === []) {
            return;
        }

        $validDays = DaysOfWeek::values();

        foreach ($days as $day) {
            if (!in_array($day, $validDays, true)) {
                $validationContext->setViolation(
                    'days',
                    sprintf("Day '%s' is not a valid day of week", $day)
                );
                return;
            }
        }

        $entity = $validationContext->getCurrentEntity();

        $validityStart = $this->getValidityDate(
            validationContext: $validationContext,
            field: 'validity_start',
            entity: $entity
        );

        $validityEnd = $this->getValidityDate(
            validationContext: $validationContext,
            field: 'validity_end',
            entity: $entity
        );

        if ($validityStart === null || $validityEnd === null) {
            return;
        }

        try {
            $start = Carbon::parse($validityStart);
            $end = Carbon::parse($validityEnd);

            if ($end->lte($start)) {
                return;
            }
        } catch (\Exception) {
            return;
        }

        $daysInPeriod = roster_days_in_period($validityStart, $validityEnd);

        foreach ($days as $day) {
            if (!in_array($day, $daysInPeriod, true)) {
                $periodDescription = roster_format_period_days_for_display($daysInPeriod);

                $validationContext->setViolation(
                    'days',
                    sprintf("Day '%s' is not within the validity period (%s)", $day, $periodDescription)
                );
            }
        }
    }

    /**
     * Retrieves a validity date from context or existing entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param string $field Date field name
     * @param object|null $entity Existing entity for updates
     * @return mixed Date value or null
     */
    private function getValidityDate(
        ValidationContextInterface $validationContext,
        string $field,
        ?object $entity
    ): mixed {
        if ($validationContext->has($field)) {
            return $validationContext->get($field);
        }

        if ($validationContext->getOperation() === OperationType::UPDATE && $entity !== null) {
            return match ($field) {
                'validity_start' => $entity->validity_start ?? null,
                'validity_end' => $entity->validity_end ?? null,
                default => null
            };
        }

        return null;
    }
}
