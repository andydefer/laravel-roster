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

#[ValidationRule(
    priority: 85,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class TimeRangeRule extends AbstractRule
{
    public function validate(ValidationContextInterface $context): void
    {
        $entity = $context->getCurrentEntity();

        try {
            $start = $this->resolveDateTimeValue($context, 'start_datetime', $entity);
            $end   = $this->resolveDateTimeValue($context, 'end_datetime', $entity);

            // ⛔ Dates invalides → une autre règle s’en occupe
            if (!$start instanceof Carbon || !$end instanceof Carbon) {
                return;
            }

            // ⛔ End <= Start
            if ($start->gte($end)) {
                $context->setViolation(
                    'end_datetime',
                    'The end datetime must be after the start datetime'
                );
                return;
            }

            // 🔒 RÈGLE ABSOLUE : PAS DE PROGRAMME MULTI-JOURS
            if (!$start->isSameDay($end)) {
                $context->setViolation(
                    'end_datetime',
                    'Events cannot span across multiple days'
                );
                return;
            }

            $availabilityId = $this->resolveAvailabilityId($context, $entity);
            if (!$availabilityId) {
                return;
            }

            $availability = $context->getAvailabilityService()->find($availabilityId);
            if (!$availability) {
                return;
            }

            $this->validateStart($context, $availability, $start);
            $this->validateEnd($context, $availability, $end);
        } catch (Exception) {
            // Les erreurs de parsing sont gérées ailleurs
        }
    }

    /* -----------------------------------------------------------------
     |  Start validation
     | -----------------------------------------------------------------
     */

    private function validateStart(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $start
    ): void {
        $day = strtolower($start->englishDayOfWeek);

        if (!in_array($day, $availability->days, true)) {
            $context->setViolation(
                'start_datetime',
                sprintf(
                    'The selected date %s (%s) is not allowed. Allowed days: %s',
                    $start->toDateString(),
                    $day,
                    implode(', ', $availability->days)
                )
            );
            return;
        }

        $dailyStart = Carbon::parse($availability->daily_start);
        $startLimit = $start->copy()->setTimeFrom($dailyStart);

        if ($start->lt($startLimit)) {
            $context->setViolation(
                'start_datetime',
                sprintf(
                    'The selected start time %s is before the availability start time %s',
                    $start->format('H:i'),
                    $dailyStart->format('H:i')
                )
            );
            return;
        }

        if ($availability->validity_start) {
            $validityStart = Carbon::parse($availability->validity_start)->startOfDay();

            if ($start->lt($validityStart)) {
                $context->setViolation(
                    'start_datetime',
                    sprintf(
                        'The selected start datetime %s is before the availability start datetime %s',
                        $start->toDateTimeString(),
                        $validityStart->toDateTimeString()
                    )
                );
                return;
            }
        }
    }

    /* -----------------------------------------------------------------
     |  End validation
     | -----------------------------------------------------------------
     */

    private function validateEnd(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $end
    ): void {
        $dailyEnd = Carbon::parse($availability->daily_end);
        $endLimit = $end->copy()->setTimeFrom($dailyEnd);

        if ($end->gt($endLimit)) {
            $context->setViolation(
                'end_datetime',
                sprintf(
                    'The selected end time %s is after the availability end time %s',
                    $end->format('H:i'),
                    $dailyEnd->format('H:i')
                )
            );
            return;
        }

        if ($availability->validity_end) {
            $validityEnd = Carbon::parse($availability->validity_end)->endOfDay();

            if ($end->gt($validityEnd)) {
                $context->setViolation(
                    'end_datetime',
                    sprintf(
                        'The selected end datetime %s is after the availability end datetime %s',
                        $end->toDateTimeString(),
                        $validityEnd->toDateTimeString()
                    )
                );
                return;
            }
        }
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | -----------------------------------------------------------------
     */

    private function resolveDateTimeValue(
        ValidationContextInterface $context,
        string $field,
        ?object $entity
    ): ?Carbon {
        if ($context->has($field)) {
            try {
                return Carbon::parse($context->get($field));
            } catch (Exception) {
                return null;
            }
        }

        if ($context->getOperation() === OperationType::UPDATE && $entity) {
            try {
                return Carbon::parse($entity->$field);
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }

    private function resolveAvailabilityId(
        ValidationContextInterface $context,
        ?object $entity
    ): ?int {
        if ($context->has('availability_id')) {
            return (int) $context->get('availability_id');
        }

        if ($context->getOperation() === OperationType::UPDATE && $entity) {
            return $entity->availability_id ?? null;
        }

        return null;
    }
}
