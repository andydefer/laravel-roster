<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Carbon\Carbon;
use Exception;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Validation\Attributes\ValidationRule;

#[ValidationRule(
    priority: 100,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::UPDATE, OperationType::DELETE]
)]
class AvailabilityTemporalCoherenceRule extends AbstractRule
{
    /**
     * Validates temporal coherence for availability operations.
     *
     * Ensures availability modifications do not conflict with existing schedules or impediments
     * by checking date boundaries and day restrictions for future entities.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entity = $validationContext->getCurrentEntity();

        if (!$entity instanceof Availability) {
            return;
        }

        match ($validationContext->getOperation()) {
            OperationType::DELETE => $this->validateDeletion($entity, $validationContext),
            OperationType::UPDATE => $this->validateUpdate($entity, $validationContext),
            default => null
        };
    }

    /**
     * Validates availability deletion.
     *
     * Prevents deletion when future schedules or impediments exist.
     *
     * @param Availability $availability Availability to delete
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateDeletion(Availability $availability, ValidationContextInterface $validationContext): void
    {
        $now = Carbon::now();
        $hasSchedules = $this->hasFutureSchedules($availability, $now);
        $hasImpediments = $this->hasFutureImpediments($availability, $now);

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

    /**
     * Validates availability update.
     *
     * Ensures updates do not conflict with existing future schedules or impediments.
     *
     * @param Availability $availability Availability to update
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateUpdate(Availability $availability, ValidationContextInterface $validationContext): void
    {
        $newStart = $this->extractFieldValue($validationContext, 'validity_start', $availability->validity_start);
        $newEnd = $this->extractFieldValue($validationContext, 'validity_end', $availability->validity_end);
        $newDays = $this->extractFieldValue($validationContext, 'days', $availability->days);

        $normalizedStart = $this->normalizeDateValue($newStart);
        $normalizedEnd = $this->normalizeDateValue($newEnd);
        $normalizedDays = $this->normalizeDays($newDays);

        if (!$normalizedStart && !$normalizedEnd && !$normalizedDays) {
            return;
        }

        $now = Carbon::now();
        $this->validateAgainstFutureEntities(Schedule::class, $availability, $normalizedStart, $normalizedEnd, $normalizedDays, $validationContext, $now);
        $this->validateAgainstFutureEntities(Impediment::class, $availability, $normalizedStart, $normalizedEnd, $normalizedDays, $validationContext, $now);
    }

    /**
     * Checks if future schedules exist for an availability.
     *
     * @param Availability $availability Availability to check
     * @param Carbon $referenceTime Reference time for "future" determination
     * @return bool True if future schedules exist
     */
    private function hasFutureSchedules(Availability $availability, Carbon $referenceTime): bool
    {
        return $this->hasFutureEntities(Schedule::class, $availability, $referenceTime);
    }

    /**
     * Checks if future impediments exist for an availability.
     *
     * @param Availability $availability Availability to check
     * @param Carbon $referenceTime Reference time for "future" determination
     * @return bool True if future impediments exist
     */
    private function hasFutureImpediments(Availability $availability, Carbon $referenceTime): bool
    {
        return $this->hasFutureEntities(Impediment::class, $availability, $referenceTime);
    }

    /**
     * Checks if future entities exist for an availability.
     *
     * @param string $entityClass Entity class to check
     * @param Availability $availability Availability to check
     * @param Carbon $referenceTime Reference time for "future" determination
     * @return bool True if future entities exist
     */
    private function hasFutureEntities(string $entityClass, Availability $availability, Carbon $referenceTime): bool
    {
        return $entityClass::query()
            ->where('availability_id', $availability->id)
            ->where('end_datetime', '>=', $referenceTime)
            ->exists();
    }

    /**
     * Validates availability changes against future entities.
     *
     * @param string $entityClass Entity class to validate against
     * @param Availability $availability Availability being modified
     * @param string|null $newStart New start date
     * @param string|null $newEnd New end date
     * @param array|null $newDays New days array
     * @param ValidationContextInterface $validationContext Validation context
     * @param Carbon $referenceTime Reference time
     */
    private function validateAgainstFutureEntities(
        string $entityClass,
        Availability $availability,
        ?string $newStart,
        ?string $newEnd,
        ?array $newDays,
        ValidationContextInterface $validationContext,
        Carbon $referenceTime
    ): void {
        $futureEntities = $entityClass::query()
            ->where('availability_id', $availability->id)
            ->where('end_datetime', '>=', $referenceTime)
            ->get();

        foreach ($futureEntities as $entity) {
            $this->validateDateBoundary(
                boundaryType: 'start',
                entity: $entity,
                newDate: $newStart,
                entityClass: $entityClass,
                validationContext: $validationContext
            );

            $this->validateDateBoundary(
                boundaryType: 'end',
                entity: $entity,
                newDate: $newEnd,
                entityClass: $entityClass,
                validationContext: $validationContext
            );

            $this->validateDayAvailability(
                entity: $entity,
                newDays: $newDays,
                entityClass: $entityClass,
                validationContext: $validationContext
            );
        }
    }

    /**
     * Validates date boundary changes against existing entities.
     *
     * @param string $boundaryType 'start' or 'end'
     * @param object $entity Existing entity to check
     * @param string|null $newDate New date value
     * @param string $entityClass Entity class name
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateDateBoundary(
        string $boundaryType,
        object $entity,
        ?string $newDate,
        string $entityClass,
        ValidationContextInterface $validationContext
    ): void {
        if (!$newDate) {
            return;
        }

        $entityDate = $boundaryType === 'start' ? $entity->start_datetime : $entity->end_datetime;
        $field = $boundaryType === 'start' ? 'validity_start' : 'validity_end';

        $entityCarbon = $this->ensureCarbon($entityDate);
        $newCarbon = $this->ensureCarbon($newDate);

        if (!$entityCarbon instanceof Carbon || !$newCarbon instanceof Carbon) {
            return;
        }

        $isConflict = $boundaryType === 'start'
            ? $entityCarbon->lt($newCarbon)
            : $entityCarbon->gt($newCarbon);

        if ($isConflict) {
            $validationContext->setViolation(
                $field,
                sprintf(
                    "Cannot set %s to '%s' because it conflicts with existing future %s %s at '%s'",
                    $field,
                    $newDate,
                    strtolower(class_basename($entityClass)),
                    $boundaryType === 'start' ? 'starting' : 'ending',
                    $entityDate
                )
            );
        }
    }

    /**
     * Validates day availability changes against existing entities.
     *
     * @param object $entity Existing entity to check
     * @param array|null $newDays New days array
     * @param string $entityClass Entity class name
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateDayAvailability(
        object $entity,
        ?array $newDays,
        string $entityClass,
        ValidationContextInterface $validationContext
    ): void {
        if ($newDays === null || $newDays === [] || empty($entity->start_datetime) || empty($entity->end_datetime)) {
            return;
        }

        $startString = $entity->start_datetime instanceof Carbon
            ? $entity->start_datetime->toDateTimeString()
            : (string) $entity->start_datetime;

        $endString = $entity->end_datetime instanceof Carbon
            ? $entity->end_datetime->toDateTimeString()
            : (string) $entity->end_datetime;

        $entityDays = $this->extractDaysFromPeriod($startString, $endString);

        foreach ($entityDays as $day) {
            if (!in_array($day, $newDays, true)) {
                $validationContext->setViolation(
                    'days',
                    sprintf(
                        "Cannot remove '%s' from days because it is used by a future %s from '%s' to '%s'",
                        ucfirst($day),
                        strtolower(class_basename($entityClass)),
                        $entity->start_datetime,
                        $entity->end_datetime
                    )
                );
                break;
            }
        }
    }

    /**
     * Extracts days from a date period.
     *
     * @param string $start Start date string
     * @param string $end End date string
     * @return array<string> Lowercase day names
     */
    private function extractDaysFromPeriod(string $start, string $end): array
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
            return [];
        }
    }

    /**
     * Extracts field value from context or entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param string $field Field name
     * @param mixed $defaultValue Default value if field not in context
     * @return mixed Field value
     */
    private function extractFieldValue(ValidationContextInterface $validationContext, string $field, mixed $defaultValue): mixed
    {
        return $validationContext->has($field)
            ? $validationContext->get($field)
            : $defaultValue;
    }

    /**
     * Normalizes date values to string format.
     *
     * @param mixed $value Date value to normalize
     * @return string|null Normalized date string or null
     */
    private function normalizeDateValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        return is_string($value) ? $value : null;
    }

    /**
     * Normalizes days array to lowercase strings.
     *
     * @param mixed $days Days array to normalize
     * @return array|null Normalized days array or null
     */
    private function normalizeDays(mixed $days): ?array
    {
        if (empty($days) || !is_array($days)) {
            return null;
        }

        return array_map(
            fn($day) => strtolower(trim((string) $day)),
            $days
        );
    }

    /**
     * Ensures value is a Carbon instance.
     *
     * @param mixed $value Value to convert
     * @return Carbon|null Carbon instance or null
     */
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
