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
class TimeRangeRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operation = $validationContext->getOperation();
        $currentEntity = $validationContext->getCurrentEntity();

        try {
            // Récupérer ou déterminer les dates de début et fin
            $start = $this->getDateTimeValue($validationContext, 'start_datetime', $currentEntity);
            $end = $this->getDateTimeValue($validationContext, 'end_datetime', $currentEntity);

            // Si les deux dates sont absentes, pas de validation
            if ($start === null && $end === null) {
                return;
            }

            // Récupérer l'ID de l'Availability
            $availabilityId = $this->getAvailabilityId($validationContext, $currentEntity);

            if (!$availabilityId) {
                return; // AvailabilityOwnershipRule devrait déjà avoir échoué
            }

            /** @var Availability|null $availability */
            $availability = $validationContext->getAvailabilityService()->find($availabilityId);

            if (!$availability) {
                return; // AvailabilityOwnershipRule devrait déjà avoir échoué
            }

            $this->validateTimeRange($validationContext, $availability, $start, $end, $currentEntity);
        } catch (Exception $exception) {
            // La validation de format est gérée par d'autres règles
        }
    }

    /**
     * Récupère une valeur datetime en priorisant le contexte, puis l'entité
     */
    private function getDateTimeValue(ValidationContextInterface $validationContext, string $field, ?object $entity): ?Carbon
    {
        // Si la date est fournie dans le contexte, l'utiliser
        if ($validationContext->has($field)) {
            $value = $validationContext->get($field);
            if ($value === null) {
                return null;
            }
            try {
                return Carbon::parse($value);
            } catch (Exception $e) {
                return null; // Format invalide, sera géré par une autre règle
            }
        }

        // Pour l'UPDATE, récupérer depuis l'entité existante
        if ($validationContext->getOperation() === OperationType::UPDATE && $entity !== null) {
            $value = $entity->$field ?? null;
            if ($value === null) {
                return null;
            }
            try {
                return $value instanceof Carbon ? $value : Carbon::parse($value);
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Récupère l'ID de l'Availability en priorisant le contexte, puis l'entité
     */
    private function getAvailabilityId(ValidationContextInterface $validationContext, ?object $entity): ?int
    {
        // Si availability_id est fourni dans le contexte, l'utiliser
        if ($validationContext->has('availability_id')) {
            $value = $validationContext->get('availability_id');
            return $value !== null ? (int) $value : null;
        }

        // Pour l'UPDATE, récupérer depuis l'entité existante
        if ($validationContext->getOperation() === OperationType::UPDATE && $entity !== null) {
            return $entity->availability_id ?? null;
        }

        return null;
    }

    private function validateTimeRange(
        ValidationContextInterface $validationContext,
        Availability $availability,
        ?Carbon $start,
        ?Carbon $end,
        ?object $currentEntity
    ): void {
        /**
         * 1. Vérifie que les deux dates sont présentes pour certaines validations
         */
        if ($start !== null && $end !== null) {
            // Vérification de cohérence start < end
            if ($start->gte($end)) {
                $validationContext->setViolation(
                    'end_datetime',
                    'The end datetime must be after the start datetime'
                );
            }
        }

        /**
         * 2. Validation pour la date de début si fournie
         */
        if ($start !== null) {
            $this->validateStartDateTime($validationContext, $availability, $start);
        }

        /**
         * 3. Validation pour la date de fin si fournie
         */
        if ($end !== null) {
            $this->validateEndDateTime($validationContext, $availability, $end, $start);
        }
    }

    private function validateStartDateTime(
        ValidationContextInterface $validationContext,
        Availability $availability,
        Carbon $start
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
         * 2. Vérifie la plage horaire de début
         */
        $availabilityStartTime = Carbon::parse($availability->daily_start);
        $availabilityStart = $start->copy()->setTimeFrom($availabilityStartTime);

        if ($start->lt($availabilityStart)) {
            $validationContext->setViolation(
                'start_datetime',
                sprintf(
                    'The selected start time %s is before the availability start time %s',
                    $start->format('H:i'),
                    $availabilityStartTime->format('H:i')
                )
            );
        }

        /**
         * 3. Vérifie la plage de dates (validity_start)
         */
        if ($availability->validity_start) {
            $validityStart = Carbon::parse($availability->validity_start)->startOfDay();
            if ($start->lt($validityStart)) {
                $validationContext->setViolation(
                    'start_datetime',
                    sprintf(
                        'The selected start datetime %s is before the availability start datetime %s',
                        $start->toDateTimeString(),
                        $validityStart->toDateTimeString()
                    )
                );
            }
        }
    }

    private function validateEndDateTime(
        ValidationContextInterface $validationContext,
        Availability $availability,
        Carbon $end,
        ?Carbon $start
    ): void {
        /**
         * 1. Vérifie la plage horaire de fin
         */
        $availabilityEndTime = Carbon::parse($availability->daily_end);
        $availabilityEnd = $end->copy()->setTimeFrom($availabilityEndTime);

        if ($end->gt($availabilityEnd)) {
            $validationContext->setViolation(
                'end_datetime',
                sprintf(
                    'The selected end time %s is after the availability end time %s',
                    $end->format('H:i'),
                    $availabilityEndTime->format('H:i')
                )
            );
        }

        /**
         * 2. Vérifie la plage de dates (validity_end)
         */
        if ($availability->validity_end) {
            $validityEnd = Carbon::parse($availability->validity_end)->endOfDay();
            if ($end->gt($validityEnd)) {
                $validationContext->setViolation(
                    'end_datetime',
                    sprintf(
                        'The selected end datetime %s is after the availability end datetime %s',
                        $end->toDateTimeString(),
                        $validityEnd->toDateTimeString()
                    )
                );
            }
        }

        /**
         * 3. Si start est fourni, vérifie que end est le même jour ou après
         */
        if ($start !== null) {
            // Vérifie que end n'est pas avant start
            if ($end->lte($start)) {
                $validationContext->setViolation(
                    'end_datetime',
                    'The end datetime must be after the start datetime'
                );
            }

            // Vérifie que si start est un jour permis, end ne dépasse pas minuit du jour suivant
            // (pour empêcher les événements qui traversent minuit)
            if (!$start->isSameDay($end) && $availabilityEndTime->format('H:i') === '00:00') {
                // Si la disponibilité finit à minuit, vérifier que l'événement ne traverse pas minuit
                if ($end->copy()->startOfDay()->gt($start->copy()->startOfDay())) {
                    $validationContext->setViolation(
                        'end_datetime',
                        'Events cannot span across midnight when availability ends at 00:00'
                    );
                }
            }
        }
    }
}
