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
 * Validates time ranges for Schedule and Impediment entities against their associated Availability.
 *
 * Ensures that start and end times are within the permitted days, daily time windows,
 * and validity periods of the parent Availability.
 */
#[ValidationRule(
    priority: 85,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class TimeRangeRule extends AbstractRule
{
    /**
     * Validates time range constraints for Schedule and Impediment entities.
     *
     * @param ValidationContextInterface $validationContext Validation context with entity data
     * @throws Exception If date parsing fails (handled internally)
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $currentEntity = $validationContext->getCurrentEntity();

        try {
            $start = $this->resolveDateTimeValue($validationContext, 'start_datetime', $currentEntity);
            $end = $this->resolveDateTimeValue($validationContext, 'end_datetime', $currentEntity);

            if (!$start instanceof Carbon && !$end instanceof Carbon) {
                return;
            }

            $availabilityId = $this->resolveAvailabilityId($validationContext, $currentEntity);

            if (!$availabilityId) {
                return;
            }

            $availability = $validationContext->getAvailabilityService()->find($availabilityId);

            if (!$availability) {
                return;
            }

            $this->validateTimeRange($validationContext, $availability, $start, $end);
        } catch (Exception $exception) {
            // Date format validation is handled by other rules
        }
    }

    /**
     * Resolves a datetime value from validation context or existing entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param string $field Datetime field name
     * @param object|null $existingEntity Existing entity for update operations
     * @return Carbon|null Parsed datetime or null if not present/invalid
     */
    private function resolveDateTimeValue(
        ValidationContextInterface $validationContext,
        string $field,
        ?object $existingEntity
    ): ?Carbon {
        if ($validationContext->has($field)) {
            $value = $validationContext->get($field);

            if ($value === null) {
                return null;
            }

            try {
                return Carbon::parse($value);
            } catch (Exception $exception) {
                return null;
            }
        }

        if ($validationContext->getOperation() === OperationType::UPDATE && $existingEntity !== null) {
            $value = $existingEntity->$field ?? null;

            if ($value === null) {
                return null;
            }

            try {
                return $value instanceof Carbon ? $value : Carbon::parse($value);
            } catch (Exception $exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * Resolves availability identifier from validation context or existing entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param object|null $existingEntity Existing entity for update operations
     * @return int|null Availability identifier or null if not present
     */
    private function resolveAvailabilityId(
        ValidationContextInterface $validationContext,
        ?object $existingEntity
    ): ?int {
        if ($validationContext->has('availability_id')) {
            $value = $validationContext->get('availability_id');
            return $value !== null ? (int) $value : null;
        }

        if ($validationContext->getOperation() === OperationType::UPDATE && $existingEntity !== null) {
            return $existingEntity->availability_id ?? null;
        }

        return null;
    }

    /**
     * Validates time range against availability constraints.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Availability $availability Parent availability entity
     * @param Carbon|null $start Start datetime
     * @param Carbon|null $end End datetime
     */
    private function validateTimeRange(
        ValidationContextInterface $validationContext,
        Availability $availability,
        ?Carbon $start,
        ?Carbon $end
    ): void {
        if ($start instanceof Carbon && $end instanceof Carbon && $start->gte($end)) {
            $validationContext->setViolation(
                'end_datetime',
                'The end datetime must be after the start datetime'
            );
        }

        if ($start instanceof Carbon) {
            $this->validateStartDateTime($validationContext, $availability, $start);
        }

        if ($end instanceof Carbon) {
            $this->validateEndDateTime($validationContext, $availability, $end, $start);
        }
    }

    /**
     * Validates start datetime against availability constraints.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Availability $availability Parent availability entity
     * @param Carbon $start Start datetime to validate
     */
    private function validateStartDateTime(
        ValidationContextInterface $validationContext,
        Availability $availability,
        Carbon $start
    ): void {
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

    /**
     * Validates end datetime against availability constraints.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param Availability $availability Parent availability entity
     * @param Carbon $end End datetime to validate
     * @param Carbon|null $start Start datetime for cross-day validation
     */
    private function validateEndDateTime(
        ValidationContextInterface $validationContext,
        Availability $availability,
        Carbon $end,
        ?Carbon $start
    ): void {
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

        if ($start instanceof Carbon) {
            if ($end->lte($start)) {
                $validationContext->setViolation(
                    'end_datetime',
                    'The end datetime must be after the start datetime'
                );
            }

            if (
                !$start->isSameDay($end)
                && $availabilityEndTime->format('H:i') === '00:00'
                && $end->copy()->startOfDay()->gt($start->copy()->startOfDay())
            ) {
                $validationContext->setViolation(
                    'end_datetime',
                    'Events cannot span across midnight when availability ends at 00:00'
                );
            }
        }
    }
}
