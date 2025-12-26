<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;

#[ValidationRule(
    priority: 95,
    entities: [EntityType::IMPEDIMENT, EntityType::SCHEDULE],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class ImpedimentScheduleDaysCoherenceRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        // Vérifier qu'on a start et end
        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
            return;
        }

        $startDatetime = $validationContext->get('start_datetime');
        $endDatetime = $validationContext->get('end_datetime');

        try {
            $start = Carbon::parse($startDatetime);
            $end = Carbon::parse($endDatetime);
        } catch (Exception) {
            return;
        }

        if ($end->lte($start)) {
            return;
        }

        // Récupérer l'Availability via availability_id
        $availabilityId = $validationContext->get('availability_id') ?? null;
        if (!$availabilityId) {
            return;
        }

        $availability = $validationContext->getAvailabilityService()->find($availabilityId);


        if (!$availability instanceof Availability) {
            return;
        }

        // Obtenir les jours autorisés et période de l'Availability
        $availabilityDays = $availability->days;
        $validityStart = $availability->validity_start;
        $validityEnd = $availability->validity_end;

        $availabilityPeriodDays = roster_days_in_period($validityStart, $validityEnd);
        $allowedDays = array_values(array_intersect($availabilityDays, $availabilityPeriodDays));

        // Jours couverts par le nouvel impediment / schedule
        $coveredDays = roster_days_in_period($start->toDateString(), $end->toDateString());

        foreach ($coveredDays as $coveredDay) {
            if (!in_array($coveredDay, $allowedDays, true)) {
                $validationContext->setViolation(
                    'start_datetime',
                    sprintf(
                        "Selected date '%s' is not allowed because this availability only permits: %s",
                        $coveredDay,
                        implode(', ', $allowedDays)
                    )
                );
            }
        }
    }
}
