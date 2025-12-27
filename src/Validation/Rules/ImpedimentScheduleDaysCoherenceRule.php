<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that impediments and schedules fall within authorized days of their parent availability.
 *
 * Ensures that scheduled time periods respect the day restrictions and validity period
 * of the associated availability entity.
 */
#[ValidationRule(
    priority: 95,
    entities: [EntityType::IMPEDIMENT, EntityType::SCHEDULE],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class ImpedimentScheduleDaysCoherenceRule extends AbstractRule
{
    /**
     * Validates that scheduled time periods respect availability day restrictions.
     *
     * @param ValidationContextInterface $validationContext Validation context with entity data
     * @throws Exception If date parsing fails
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$this->hasRequiredDatetimes($validationContext)) {
            return;
        }

        try {
            $start = Carbon::parse($validationContext->get('start_datetime'));
            $end = Carbon::parse($validationContext->get('end_datetime'));
        } catch (Exception) {
            return;
        }

        if ($end->lte($start)) {
            return;
        }

        $availability = $this->resolveParentAvailability($validationContext);
        if (!$availability instanceof Availability) {
            return;
        }

        $this->validateDaysAgainstAvailability(
            validationContext: $validationContext,
            start: $start,
            end: $end,
            availability: $availability
        );
    }

    /**
     * Checks if required datetime fields are present.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return bool True if both start and end datetimes are present
     */
    private function hasRequiredDatetimes(ValidationContextInterface $validationContext): bool
    {
        return $validationContext->has('start_datetime')
            && $validationContext->has('end_datetime');
    }

    /**
     * Resolves the parent availability entity from validation context.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return Availability|null Parent availability or null if not found
     */
    private function resolveParentAvailability(ValidationContextInterface $validationContext): ?Availability
    {
        $availabilityId = $validationContext->get('availability_id');
        if (!$availabilityId) {
            return null;
        }

        $availabilityService = $validationContext->getAvailabilityService();
        if (!$availabilityService) {
            return null;
        }

        $availability = $availabilityService->find($availabilityId);
        return $availability instanceof Availability ? $availability : null;
    }

    /**
     * Validates scheduled days against availability restrictions.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Carbon $start Scheduled period start
     * @param Carbon $end Scheduled period end
     * @param Availability $availability Parent availability
     */
    private function validateDaysAgainstAvailability(
        ValidationContextInterface $validationContext,
        Carbon $start,
        Carbon $end,
        Availability $availability
    ): void {
        $allowedDays = $this->calculateAllowedDays($availability);
        $scheduledDays = roster_days_in_period(
            startDate: $start->toDateString(),
            endDate: $end->toDateString()
        );

        $unauthorizedDays = array_diff($scheduledDays, $allowedDays);
        $this->reportUnauthorizedDays($validationContext, $unauthorizedDays, $allowedDays);
    }

    /**
     * Calculates allowed days based on availability configuration.
     *
     * @param Availability $availability Parent availability
     * @return array<string> Allowed day names
     */
    private function calculateAllowedDays(Availability $availability): array
    {
        $availabilityPeriodDays = roster_days_in_period(
            startDate: $availability->validity_start,
            endDate: $availability->validity_end
        );

        return array_values(
            array_intersect($availability->days, $availabilityPeriodDays)
        );
    }

    /**
     * Reports unauthorized days as validation violations.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array<string> $unauthorizedDays Days not allowed by availability
     * @param array<string> $allowedDays Authorized days
     */
    private function reportUnauthorizedDays(
        ValidationContextInterface $validationContext,
        array $unauthorizedDays,
        array $allowedDays
    ): void {
        foreach ($unauthorizedDays as $unauthorizedDay) {
            $validationContext->setViolation(
                field: 'start_datetime',
                message: sprintf(
                    "Selected date '%s' is not allowed because this availability only permits: %s",
                    $unauthorizedDay,
                    implode(', ', $allowedDays)
                )
            );
        }
    }
}
