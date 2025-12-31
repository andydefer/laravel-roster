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
    /**
     * Validates time range constraints for schedule and impediment entities.
     *
     * This rule ensures that:
     * - Time ranges are valid (end > start)
     * - Events do not span multiple days
     * - Events fit within availability time windows
     * - Events occur on allowed days
     * - Events are within validity periods
     *
     * @param ValidationContextInterface $context The validation context
     */
    public function validate(ValidationContextInterface $context): void
    {
        try {
            $startDatetime = $this->resolveDateTimeValue($context, 'start_datetime');
            $endDatetime = $this->resolveDateTimeValue($context, 'end_datetime');

            if (!$this->hasValidDatetimes($startDatetime, $endDatetime)) {
                return;
            }

            $this->validateTimeRangeOrder($context, $startDatetime, $endDatetime);
            $this->validateSingleDayEvent($context, $startDatetime, $endDatetime);

            $availability = $this->resolveAvailability($context);
            if (!$availability instanceof Availability) {
                return;
            }

            $this->validateAvailabilityConstraints($context, $availability, $startDatetime, $endDatetime);
        } catch (Exception $exception) {
            // Date parsing errors are handled by other validation rules
            // This prevents breaking the entire validation chain
        }
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates the temporal constraints for schedule and impediment entities, ensuring that time ranges are logically consistent and respect availability boundaries. It validates that the end datetime occurs after the start datetime, events do not span multiple days, events fit within the parent availability's daily time windows, occur on permitted days of the week, and fall within the availability's validity period (if defined). The rule applies to both CREATE and UPDATE operations to maintain consistent temporal logic.";
    }

    /**
     * Validates that end datetime occurs after start datetime.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Carbon $startDatetime Start datetime
     * @param Carbon $endDatetime End datetime
     */
    private function validateTimeRangeOrder(
        ValidationContextInterface $context,
        Carbon $startDatetime,
        Carbon $endDatetime
    ): void {
        if ($startDatetime->gte($endDatetime)) {
            $context->setViolationFromRule(
                rule: $this,
                field: 'end_datetime',
                message: 'The end datetime must be after the start datetime'
            );
        }
    }

    /**
     * Validates that events do not span multiple days.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Carbon $startDatetime Start datetime
     * @param Carbon $endDatetime End datetime
     */
    private function validateSingleDayEvent(
        ValidationContextInterface $context,
        Carbon $startDatetime,
        Carbon $endDatetime
    ): void {
        if (!$startDatetime->isSameDay($endDatetime)) {
            $context->setViolationFromRule(
                rule: $this,
                field: 'end_datetime',
                message: 'Events cannot span across multiple days'
            );
        }
    }

    /**
     * Validates all availability constraints for the event.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Availability $availability The parent availability
     * @param Carbon $startDatetime Start datetime
     * @param Carbon $endDatetime End datetime
     */
    private function validateAvailabilityConstraints(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $startDatetime,
        Carbon $endDatetime
    ): void {
        $this->validateDayOfWeek($context, $availability, $startDatetime);
        $this->validateStartTimeWithinAvailability($context, $availability, $startDatetime);
        $this->validateEndTimeWithinAvailability($context, $availability, $endDatetime);
        $this->validateWithinValidityStart($context, $availability, $startDatetime);
        $this->validateWithinValidityEnd($context, $availability, $endDatetime);
    }

    /**
     * Validates that the event occurs on an allowed day of the week.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Availability $availability The parent availability
     * @param Carbon $startDatetime Start datetime
     */
    private function validateDayOfWeek(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $startDatetime
    ): void {
        $day = strtolower($startDatetime->englishDayOfWeek);

        if (!in_array($day, $availability->days, true)) {
            $context->setViolationFromRule(
                rule: $this,
                field: 'start_datetime',
                message: sprintf(
                    'The selected date %s (%s) is not allowed. Allowed days: %s',
                    $startDatetime->toDateString(),
                    $day,
                    implode(', ', $availability->days)
                )
            );
        }
    }

    /**
     * Validates that start time is within availability's daily start time.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Availability $availability The parent availability
     * @param Carbon $startDatetime Start datetime
     */
    private function validateStartTimeWithinAvailability(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $startDatetime
    ): void {
        $dailyStart = Carbon::parse($availability->daily_start);
        $startLimit = $startDatetime->copy()->setTimeFrom($dailyStart);

        if ($startDatetime->lt($startLimit)) {
            $context->setViolationFromRule(
                rule: $this,
                field: 'start_datetime',
                message: sprintf(
                    'The selected start time %s is before the availability start time %s',
                    $startDatetime->format('H:i'),
                    $dailyStart->format('H:i')
                )
            );
        }
    }

    /**
     * Validates that end time is within availability's daily end time.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Availability $availability The parent availability
     * @param Carbon $endDatetime End datetime
     */
    private function validateEndTimeWithinAvailability(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $endDatetime
    ): void {
        $dailyEnd = Carbon::parse($availability->daily_end);
        $endLimit = $endDatetime->copy()->setTimeFrom($dailyEnd);

        if ($endDatetime->gt($endLimit)) {
            $context->setViolationFromRule(
                rule: $this,
                field: 'end_datetime',
                message: sprintf(
                    'The selected end time %s is after the availability end time %s',
                    $endDatetime->format('H:i'),
                    $dailyEnd->format('H:i')
                )
            );
        }
    }

    /**
     * Validates that event starts within availability's validity period.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Availability $availability The parent availability
     * @param Carbon $startDatetime Start datetime
     */
    private function validateWithinValidityStart(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $startDatetime
    ): void {
        if ($availability->validity_start !== null) {
            $validityStart = Carbon::parse($availability->validity_start)->startOfDay();

            if ($startDatetime->lt($validityStart)) {
                $context->setViolationFromRule(
                    rule: $this,
                    field: 'start_datetime',
                    message: sprintf(
                        'The selected start datetime %s is before the availability start datetime %s',
                        $startDatetime->toDateTimeString(),
                        $validityStart->toDateTimeString()
                    )
                );
            }
        }
    }

    /**
     * Validates that event ends within availability's validity period.
     *
     * @param ValidationContextInterface $context The validation context
     * @param Availability $availability The parent availability
     * @param Carbon $endDatetime End datetime
     */
    private function validateWithinValidityEnd(
        ValidationContextInterface $context,
        Availability $availability,
        Carbon $endDatetime
    ): void {
        if ($availability->validity_end !== null) {
            $validityEnd = Carbon::parse($availability->validity_end)->endOfDay();

            if ($endDatetime->gt($validityEnd)) {
                $context->setViolationFromRule(
                    rule: $this,
                    field: 'end_datetime',
                    message: sprintf(
                        'The selected end datetime %s is after the availability end datetime %s',
                        $endDatetime->toDateTimeString(),
                        $validityEnd->toDateTimeString()
                    )
                );
            }
        }
    }

    /**
     * Checks if both datetime values are valid Carbon instances.
     *
     * @param Carbon|null $startDatetime Start datetime
     * @param Carbon|null $endDatetime End datetime
     * @return bool True if both are valid Carbon instances
     */
    private function hasValidDatetimes(?Carbon $startDatetime, ?Carbon $endDatetime): bool
    {
        return $startDatetime instanceof Carbon && $endDatetime instanceof Carbon;
    }

    /**
     * Resolves datetime value from context or existing entity.
     *
     * @param ValidationContextInterface $context The validation context
     * @param string $field The datetime field name
     * @return Carbon|null Carbon instance or null if not available/invalid
     */
    private function resolveDateTimeValue(ValidationContextInterface $context, string $field): ?Carbon
    {
        if ($context->has($field)) {
            try {
                return Carbon::parse($context->get($field));
            } catch (Exception) {
                return null;
            }
        }

        if ($context->getOperation() === OperationType::UPDATE) {
            $entity = $context->getCurrentEntity();
            if ($entity !== null && property_exists($entity, $field)) {
                try {
                    return Carbon::parse($entity->$field);
                } catch (Exception) {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Resolves availability from context or existing entity.
     *
     * @param ValidationContextInterface $context The validation context
     * @return Availability|null Availability instance or null if not found
     */
    private function resolveAvailability(ValidationContextInterface $context): ?Availability
    {
        $availabilityId = $this->resolveAvailabilityId($context);

        if ($availabilityId === null) {
            return null;
        }

        return $context->getAvailabilityService()->find($availabilityId);
    }

    /**
     * Resolves availability ID from context or existing entity.
     *
     * @param ValidationContextInterface $context The validation context
     * @return int|null Availability ID or null if not available
     */
    private function resolveAvailabilityId(ValidationContextInterface $context): ?int
    {
        if ($context->has('availability_id')) {
            return (int) $context->get('availability_id');
        }

        if ($context->getOperation() === OperationType::UPDATE) {
            $entity = $context->getCurrentEntity();
            if ($entity !== null && property_exists($entity, 'availability_id')) {
                return $entity->availability_id;
            }
        }

        return null;
    }
}
