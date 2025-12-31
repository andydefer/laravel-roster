<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Illuminate\Support\Carbon;
use Exception;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates temporal coherence for availability operations.
 *
 * Ensures availability modifications do not conflict with existing schedules or impediments
 * by checking date boundaries and day restrictions for future entities.
 */
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
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entity = $validationContext->getCurrentEntity();

        if (!$entity instanceof Availability) {
            return;
        }

        $this->validateBasedOnOperation($validationContext, $entity);
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates temporal coherence during availability modifications to prevent conflicts with existing schedules and impediments. It ensures that availability updates (validity dates or days) do not break existing future bookings and that deletions are only allowed when no future schedules or impediments exist. This maintains data integrity and prevents orphaned time slots in the scheduling system.";
    }

    /**
     * Routes validation to appropriate method based on operation type.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Availability $availability Availability being validated
     */
    private function validateBasedOnOperation(ValidationContextInterface $validationContext, Availability $availability): void
    {
        match ($validationContext->getOperation()) {
            OperationType::DELETE => $this->validateDeletion($availability, $validationContext),
            OperationType::UPDATE => $this->validateUpdate($availability, $validationContext),
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

        $hasSchedules = $this->hasFutureEntities(Schedule::class, $availability, $now);
        $hasImpediments = $this->hasFutureEntities(Impediment::class, $availability, $now);

        $this->addDeletionViolationIfNeeded($validationContext, $hasSchedules, $hasImpediments);
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
        $updateData = $this->extractUpdateData($validationContext, $availability);

        if (!$this->hasRelevantChanges($updateData)) {
            return;
        }

        $now = Carbon::now();

        $this->validateAgainstEntityType(
            entityClass: Schedule::class,
            availability: $availability,
            updateData: $updateData,
            validationContext: $validationContext,
            referenceTime: $now
        );

        $this->validateAgainstEntityType(
            entityClass: Impediment::class,
            availability: $availability,
            updateData: $updateData,
            validationContext: $validationContext,
            referenceTime: $now
        );
    }

    /**
     * Extract and normalize update data from context.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Availability $availability Original availability
     * @return array<string, string|mixed[]|null> Normalized update data
     */
    private function extractUpdateData(ValidationContextInterface $validationContext, Availability $availability): array
    {
        $newStart = $this->extractFieldValue($validationContext, 'validity_start', $availability->validity_start);
        $newEnd = $this->extractFieldValue($validationContext, 'validity_end', $availability->validity_end);
        $newDays = $this->extractFieldValue($validationContext, 'days', $availability->days);

        return [
            'validity_start' => $this->normalizeDateValue($newStart),
            'validity_end' => $this->normalizeDateValue($newEnd),
            'days' => $this->normalizeDays($newDays),
        ];
    }

    /**
     * Check if update contains changes that require validation.
     *
     * @param array $updateData Normalized update data
     * @return bool True if validation is needed
     */
    private function hasRelevantChanges(array $updateData): bool
    {
        return array_filter($updateData, fn($value): bool => $value !== null) !== [];
    }

    /**
     * Add appropriate violation message for deletion based on existing entities.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param bool $hasSchedules Whether future schedules exist
     * @param bool $hasImpediments Whether future impediments exist
     */
    private function addDeletionViolationIfNeeded(
        ValidationContextInterface $validationContext,
        bool $hasSchedules,
        bool $hasImpediments
    ): void {
        if ($hasSchedules && $hasImpediments) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: '_system',
                message: "Cannot delete availability with future schedules and impediments"
            );
        } elseif ($hasSchedules) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: '_system',
                message: "Cannot delete availability with future schedules"
            );
        } elseif ($hasImpediments) {
            $validationContext->setViolationFromRule(
                rule: $this,
                field: '_system',
                message: "Cannot delete availability with future impediments"
            );
        }
    }

    /**
     * Validates availability changes against specific entity type.
     *
     * @param string $entityClass Entity class to validate against
     * @param Availability $availability Availability being modified
     * @param array<string, mixed> $updateData Normalized update data
     * @param ValidationContextInterface $validationContext Validation context
     * @param Carbon $referenceTime Reference time for "future" determination
     */
    private function validateAgainstEntityType(
        string $entityClass,
        Availability $availability,
        array $updateData,
        ValidationContextInterface $validationContext,
        Carbon $referenceTime
    ): void {
        $futureEntities = $entityClass::query()
            ->where('availability_id', $availability->id)
            ->where('end_datetime', '>=', $referenceTime)
            ->get();

        foreach ($futureEntities as $futureEntity) {
            $this->validateEntityDateBoundaries($futureEntity, $updateData, $entityClass, $validationContext);
            $this->validateEntityDays($futureEntity, $updateData['days'], $entityClass, $validationContext);
        }
    }

    /**
     * Validate date boundaries for a specific entity.
     *
     * @param object $entity Existing entity to check
     * @param array<string, mixed> $updateData Normalized update data
     * @param string $entityClass Entity class name
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateEntityDateBoundaries(
        object $entity,
        array $updateData,
        string $entityClass,
        ValidationContextInterface $validationContext
    ): void {
        $this->validateDateBoundary(
            boundaryType: 'start',
            entity: $entity,
            newDate: $updateData['validity_start'],
            entityClass: $entityClass,
            validationContext: $validationContext
        );

        $this->validateDateBoundary(
            boundaryType: 'end',
            entity: $entity,
            newDate: $updateData['validity_end'],
            entityClass: $entityClass,
            validationContext: $validationContext
        );
    }

    /**
     * Validates day availability changes against a specific entity.
     *
     * @param object $entity Existing entity to check
     * @param array|null $newDays New days array
     * @param string $entityClass Entity class name
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateEntityDays(
        object $entity,
        ?array $newDays,
        string $entityClass,
        ValidationContextInterface $validationContext
    ): void {
        if ($newDays === null || $newDays === [] || empty($entity->start_datetime) || empty($entity->end_datetime)) {
            return;
        }

        $entityDays = $this->extractDaysFromPeriod(
            start: $this->ensureCarbon($entity->start_datetime),
            end: $this->ensureCarbon($entity->end_datetime)
        );

        $this->checkForMissingDays($entityDays, $newDays, $entity, $entityClass, $validationContext);
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
            $validationContext->setViolationFromRule(
                rule: $this,
                field: $field,
                message: sprintf(
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
     * Check if specific days are missing from new days array.
     *
     * @param string[] $entityDays Days used by the entity
     * @param array $newDays New days array
     * @param object $entity Existing entity
     * @param string $entityClass Entity class name
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function checkForMissingDays(
        array $entityDays,
        array $newDays,
        object $entity,
        string $entityClass,
        ValidationContextInterface $validationContext
    ): void {
        foreach ($entityDays as $entityDay) {
            if (!in_array($entityDay, $newDays, true)) {
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'days',
                    message: sprintf(
                        "Cannot remove '%s' from days because it is used by a future %s from '%s' to '%s'",
                        ucfirst($entityDay),
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
     * Extracts days from a date period.
     *
     * @param Carbon|null $start Start date
     * @param Carbon|null $end End date
     * @return array<string> Lowercase day names
     */
    private function extractDaysFromPeriod(?Carbon $start, ?Carbon $end): array
    {
        if (!$start instanceof Carbon || !$end instanceof Carbon || $end->lt($start)) {
            return [];
        }

        try {
            $current = $start->copy();
            $days = [];

            while ($current->lte($end)) {
                $day = strtolower($current->format('l'));
                if (!in_array($day, $days, true)) {
                    $days[] = $day;
                }

                $current->addDay();
            }

            return $days;
        } catch (Exception) {
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
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }
}
