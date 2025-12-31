<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
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
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$this->shouldValidate($validationContext)) {
            return;
        }

        $days = $validationContext->get('days');

        // Stop immediately if format validation fails
        if (!$this->validateDaysFormat($validationContext, $days)) {
            return;
        }

        $this->validateDaysAgainstPeriod($validationContext, $days);
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates the coherence between specified days and the validity period of an availability entity, ensuring that all provided days are valid days of the week according to the system's enumeration and fall within the defined validity date range. It prevents scheduling inconsistencies by verifying that days like 'monday', 'tuesday', etc., actually exist within the availability's start and end dates, maintaining data integrity across CREATE and UPDATE operations.";
    }

    /**
     * Check if validation should proceed based on context.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return bool True if validation should proceed
     */
    private function shouldValidate(ValidationContextInterface $validationContext): bool
    {
        if (!$validationContext->has('days')) {
            return false;
        }

        $days = $validationContext->get('days');
        return $days !== null && $days !== [];
    }

    /**
     * Validate the format and basic validity of days.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param mixed $days Days data from context
     */
    private function validateDaysFormat(
        ValidationContextInterface $validationContext,
        mixed $days
    ): bool {
        // ✅ 1. Format check FIRST
        if (!is_array($days)) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: 'days',
                message: 'Days must be provided as an array'
            );
            return false;
        }

        // ✅ 2. Empty array is allowed (tests expect it)
        if ($days === []) {
            return true;
        }

        // ✅ 3. Validate each day value
        $validDays = DaysOfWeek::values();

        foreach ($days as $day) {
            if (!in_array($day, $validDays, true)) {
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'days',
                    message: sprintf("Day value '%s' is not recognized as a valid day of the week", $day)
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Validate days against validity period.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array $days Days to validate
     */
    private function validateDaysAgainstPeriod(ValidationContextInterface $validationContext, array $days): void
    {
        $validityPeriod = $this->extractValidityPeriod($validationContext);

        if ($validityPeriod === null || !$this->isValidPeriod($validityPeriod)) {
            return;
        }

        $this->checkDaysWithinPeriod($validationContext, $days, $validityPeriod);
    }

    /**
     * Extract validity period from context or existing entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return array|null Array with 'start' and 'end' keys, or null
     */
    private function extractValidityPeriod(ValidationContextInterface $validationContext): ?array
    {
        $entity = $validationContext->getCurrentEntity();

        $startDate = $this->getValidityDate(
            validationContext: $validationContext,
            field: 'validity_start',
            entity: $entity
        );

        $endDate = $this->getValidityDate(
            validationContext: $validationContext,
            field: 'validity_end',
            entity: $entity
        );

        if ($startDate === null || $endDate === null) {
            return null;
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /**
     * Check if validity period is valid (start < end and parseable).
     *
     * @param array<string, mixed> $period Validity period with 'start' and 'end'
     * @return bool True if period is valid
     */
    private function isValidPeriod(array $period): bool
    {
        try {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);

            return $end->gt($start);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Check if all days are within the validity period.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array $days Days to check
     * @param array<string, mixed> $period Validity period
     */
    private function checkDaysWithinPeriod(
        ValidationContextInterface $validationContext,
        array $days,
        array $period
    ): void {
        $daysInPeriod = roster_days_in_period($period['start'], $period['end']);

        foreach ($days as $day) {
            if (!in_array($day, $daysInPeriod, true)) {
                $periodDescription = roster_format_period_days_for_display($daysInPeriod);

                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'days',
                    message: sprintf(
                        "Day '%s' falls outside the validity period. Available days in period: %s",
                        $day,
                        $periodDescription
                    )
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
