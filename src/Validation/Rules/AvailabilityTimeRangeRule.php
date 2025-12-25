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
        Availability $availability,
        Carbon $start,
        Carbon $end
    ): void {
        /**
         * 1. Vérifie le jour de la semaine
         */
        $dayOfWeek = strtolower($start->englishDayOfWeek);

        if (!in_array($dayOfWeek, $availability->days, true)) {
            $validationContext->setViolation(
                'start_datetime',
                sprintf(
                    'The selected date %s (%s) is not allowed because this availability only permits the following days: %s',
                    $start->toDateString(),
                    $dayOfWeek,
                    implode(', ', $availability->days)
                )
            );
        }

        /**
         * 2. Vérifie la plage horaire
         */
        $availabilityStart = $start->copy()->setTimeFrom(Carbon::parse($availability->daily_start));
        $availabilityEnd = $start->copy()->setTimeFrom(Carbon::parse($availability->daily_end));

        if ($start->lt($availabilityStart)) {
            $validationContext->setViolation(
                'start_datetime',
                sprintf(
                    'The selected start time %s is before the availability start time %s',
                    $start->format('H:i'),
                    Carbon::parse($availability->daily_start)->format('H:i')
                )
            );
        }

        if ($end->gt($availabilityEnd)) {
            $validationContext->setViolation(
                'end_datetime',
                sprintf(
                    'The selected end time %s is after the availability end time %s',
                    $end->format('H:i'),
                    Carbon::parse($availability->daily_end)->format('H:i')
                )
            );
        }

        /**
         * 3. Vérifie la plage de dates (validity_start / validity_end)
         */
        if ($availability->validity_start && $start->lt(Carbon::parse($availability->validity_start))) {
            $validationContext->setViolation(
                'start_datetime',
                sprintf(
                    'The selected start datetime %s is before the availability start datetime %s',
                    $start->toDateTimeString(), // Modifier ici
                    Carbon::parse($availability->validity_start)->toDateTimeString() // Modifier ici
                )
            );
        }

        if ($availability->validity_end && $end->gt(Carbon::parse($availability->validity_end))) {
            $validationContext->setViolation(
                'end_datetime',
                sprintf(
                    'The selected end datetime %s is after the availability end datetime %s',
                    $end->toDateTimeString(), // Modifier ici
                    Carbon::parse($availability->validity_end)->toDateTimeString() // Modifier ici
                )
            );
        }
    }
}
