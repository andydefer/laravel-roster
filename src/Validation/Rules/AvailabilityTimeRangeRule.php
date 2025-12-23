<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Models\Availability;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 85,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityTimeRangeRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
            return;
        }

        try {
            $start = Carbon::parse($validationContext->get('start_datetime'));
            $end = Carbon::parse($validationContext->get('end_datetime'));
            $availabilityId = $validationContext->get('availability_id');

            if (!$availabilityId) {
                return; // AvailabilityOwnershipRule devrait déjà avoir échoué
            }

            /** @var Availability|null $availability */
            $availabilityRepository = app(AvailabilityRepositoryInterface::class);
            $availability = $availabilityRepository->find($availabilityId);

            if (!$availability) {
                return; // AvailabilityOwnershipRule devrait déjà avoir échoué
            }

            $this->validateTimeRange($validationContext, $availability, $start, $end);
        } catch (Exception $exception) {
            // La validation de format est gérée par d'autres règles
        }
    }

    private function validateTimeRange(
        ValidationContextInterface $validationContext,
        $availability,
        Carbon $start,
        Carbon $end
    ): void {
        // 1. Vérifie le jour de la semaine
        $dayOfWeek = strtolower($start->englishDayOfWeek);
        if (!in_array($dayOfWeek, $availability->days, true)) {
            $validationContext->setViolation(
                'start_datetime',
                sprintf('Day %s is not available in this availability', $dayOfWeek)
            );
        }

        // 2. Vérifie la plage horaire
        $availabilityStart = $start->copy()->setTimeFrom(Carbon::parse($availability->daily_start));
        $availabilityEnd = $start->copy()->setTimeFrom(Carbon::parse($availability->daily_end));

        if ($start->lt($availabilityStart)) {
            $validationContext->setViolation(
                'start_datetime',
                'Start time is before availability start time'
            );
        }

        if ($end->gt($availabilityEnd)) {
            $validationContext->setViolation(
                'end_datetime',
                'End time is after availability end time'
            );
        }

        // 3. Vérifie la plage de dates (validity_start / validity_end)
        if ($availability->validity_start && $start->lt(Carbon::parse($availability->validity_start))) {
            $validationContext->setViolation(
                'start_datetime',
                'Start date is before availability start date'
            );
        }

        if ($availability->validity_end && $end->gt(Carbon::parse($availability->validity_end))) {
            $validationContext->setViolation(
                'end_datetime',
                'End date is after availability end date'
            );
        }
    }
}
