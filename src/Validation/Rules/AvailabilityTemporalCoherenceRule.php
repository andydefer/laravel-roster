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
use Roster\Models\Schedule;
use Roster\Models\Impediment;

#[ValidationRule(
    priority: 100,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::UPDATE, OperationType::DELETE]
)]
class AvailabilityTemporalCoherenceRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entity = $validationContext->getCurrentEntity();

        if (!($entity instanceof Availability)) {
            return;
        }

        $operationType = $validationContext->getOperation();

        if ($operationType === OperationType::DELETE) {
            $this->validateDeleteOperation($entity, $validationContext);
            return;
        }

        // Pour UPDATE
        $newStart = $this->getFieldValue($validationContext, 'validity_start', $entity);
        $newEnd   = $this->getFieldValue($validationContext, 'validity_end', $entity);
        $newDays  = $this->getFieldValue($validationContext, 'days', $entity);

        // Normaliser les valeurs
        $newStart = $this->normalizeDateValue($newStart);
        $newEnd = $this->normalizeDateValue($newEnd);
        $newDays = $this->normalizeDays($newDays);

        if (!$newStart && !$newEnd && !$newDays) {
            return;
        }

        $now = Carbon::now();

        $this->validateFutureEntities(Schedule::class, $entity, $newStart, $newEnd, $newDays, $validationContext, $now);
        $this->validateFutureEntities(Impediment::class, $entity, $newStart, $newEnd, $newDays, $validationContext, $now);
    }

    private function validateDeleteOperation(Availability $availability, ValidationContextInterface $validationContext): void
    {
        $now = Carbon::now();

        $hasSchedules = $this->hasFutureEntities(Schedule::class, $availability, $now);
        $hasImpediments = $this->hasFutureEntities(Impediment::class, $availability, $now);

        if ($hasSchedules && $hasImpediments) {
            $validationContext->setViolation(
                '_system',
                "Cannot delete availability with future schedules and impediments"
            );
        } elseif ($hasSchedules) {
            $validationContext->setViolation(
                '_system',
                "Cannot delete availability with future schedules"
            );
        } elseif ($hasImpediments) {
            $validationContext->setViolation(
                '_system',
                "Cannot delete availability with future impediments"
            );
        }
    }

    private function hasFutureEntities(string $modelClass, Availability $availability, Carbon $now): bool
    {
        return $modelClass::query()
            ->where('availability_id', $availability->id)
            ->where('end_datetime', '>=', $now)
            ->exists();
    }

    private function validateFutureEntities(
        string $modelClass,
        Availability $availability,
        ?string $newStart,
        ?string $newEnd,
        ?array $newDays,
        ValidationContextInterface $validationContext,
        Carbon $now
    ): void {
        $entities = $modelClass::query()
            ->where('availability_id', $availability->id)
            ->where('end_datetime', '>=', $now)
            ->get();

        foreach ($entities as $entity) {
            $this->validateDateBoundary('start', $entity, $newStart, $modelClass, $validationContext);
            $this->validateDateBoundary('end', $entity, $newEnd, $modelClass, $validationContext);
            $this->validateDays($entity, $newDays, $modelClass, $validationContext);
        }
    }

    private function validateDateBoundary(
        string $type, // 'start' ou 'end'
        object $entity,
        ?string $newDate,
        string $modelClass,
        ValidationContextInterface $validationContext
    ): void {
        if (!$newDate) {
            return;
        }

        $entityDate = $type === 'start' ? $entity->start_datetime : $entity->end_datetime;
        $field = $type === 'start' ? 'validity_start' : 'validity_end';

        // S'assurer que les dates sont des objets Carbon
        $entityCarbon = $this->ensureCarbon($entityDate);
        $newCarbon = $this->ensureCarbon($newDate);

        if (!$entityCarbon instanceof Carbon || !$newCarbon instanceof Carbon) {
            return;
        }

        $isConflict = $type === 'start'
            ? $entityCarbon->lt($newCarbon)
            : $entityCarbon->gt($newCarbon);

        if ($isConflict) {
            $validationContext->setViolation(
                $field,
                sprintf(
                    "Cannot set %s to '%s' because it conflicts with existing future %s %s at '%s'",
                    $field,
                    $newDate,
                    strtolower(class_basename($modelClass)),
                    $type === 'start' ? 'starting' : 'ending',
                    $entityDate
                )
            );
        }
    }

    private function validateDays(
        object $entity,
        ?array $newDays,
        string $modelClass,
        ValidationContextInterface $validationContext
    ): void {
        if ($newDays === null || $newDays === [] || empty($entity->start_datetime) || empty($entity->end_datetime)) {
            return;
        }

        // Obtenir les dates sous forme de string
        $startStr = $entity->start_datetime instanceof Carbon
            ? $entity->start_datetime->toDateTimeString()
            : (string) $entity->start_datetime;

        $endStr = $entity->end_datetime instanceof Carbon
            ? $entity->end_datetime->toDateTimeString()
            : (string) $entity->end_datetime;

        $daysInEntity = $this->daysInPeriod($startStr, $endStr);

        foreach ($daysInEntity as $dayInEntity) {
            if (!in_array($dayInEntity, $newDays, true)) {
                $validationContext->setViolation(
                    'days',
                    sprintf(
                        "Cannot remove '%s' from days because it is used by a future %s from '%s' to '%s'",
                        ucfirst($dayInEntity),
                        strtolower(class_basename($modelClass)),
                        $entity->start_datetime,
                        $entity->end_datetime
                    )
                );
                // Arrêter après la première violation pour éviter les doublons
                break;
            }
        }
    }

    /**
     * @return lowercase-string[]
     */
    private function daysInPeriod(string $start, string $end): array
    {
        try {
            $current = Carbon::parse($start);
            $endDate = Carbon::parse($end);
            $days = [];

            while ($current->lte($endDate)) {
                $day = strtolower($current->format('l'));
                if (!in_array($day, $days, true)) {
                    $days[] = $day;
                }

                $current->addDay();
            }

            return $days;
        } catch (Exception $exception) {
            // Si les dates sont invalides, retourner un tableau vide
            return [];
        }
    }

    private function getFieldValue(ValidationContextInterface $validationContext, string $field, Availability $availability): mixed
    {
        if ($validationContext->has($field)) {
            return $validationContext->get($field);
        }

        return match ($field) {
            'validity_start' => $availability->validity_start,
            'validity_end' => $availability->validity_end,
            'days' => $availability->days,
            default => null,
        };
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    private function normalizeDays(mixed $days): ?array
    {
        if (empty($days)) {
            return null;
        }

        if (!is_array($days)) {
            return null;
        }

        // Normaliser les jours en lowercase
        return array_map(fn($day) => strtolower(trim((string) $day)), $days);
    }

    private function ensureCarbon(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value);
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }
}
