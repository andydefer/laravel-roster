<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that date/times are not in the past for new entities.
 *
 * Ensures schedules and impediments are created with future start times,
 * unless explicitly configured to allow past dates.
 */
#[ValidationRule(
    priority: 40,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT, EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class FutureDateRule extends AbstractRule
{
    /**
     * Validates that date/times are not in the past.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$this->shouldValidateFutureDates()) {
            return;
        }


        if ($this->allowPastDates()) {
            return;
        }

        $entityType = $validationContext->getEntityType();

        if ($entityType === EntityType::AVAILABILITY) {
            $this->validateFutureAvailability($validationContext);
        } else {
            $this->validateFutureDateTime($validationContext);
        }
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule ensures that all time-based entities (schedules, impediments, and availabilities) are created or updated with start dates/times that are in the future. For schedules and impediments, it validates that the 'start_datetime' field is not in the past. For availabilities, it validates that the combined datetime of 'validity_start' and 'daily_start' is not in the past. The rule respects configuration settings that may allow past dates or disable future date validation entirely. It applies to both CREATE and UPDATE operations, but only validates when relevant fields are being modified during updates.";
    }

    /**
     * Validates availability start date is not in the past.
     * Checks that the combination of validity_start + daily_start is in the future.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateFutureAvailability(ValidationContextInterface $validationContext): void
    {
        if ($validationContext->getOperation() === OperationType::CREATE) {
            $this->validateNewAvailability($validationContext);
            return;
        }

        if (
            $validationContext->getOperation() === OperationType::UPDATE &&
            $validationContext->has('validity_start')
        ) {
            $this->validateUpdatedAvailability($validationContext);
        }
    }

    /**
     * Validates new availability creation.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateNewAvailability(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('validity_start')) {
            return;
        }

        try {
            $validityStart = Carbon::parse($validationContext->get('validity_start'));

            if (!$validationContext->has('daily_start')) {
                $dailyStart = '00:00:00';
            } else {
                $dailyStart = $validationContext->get('daily_start');
            }

            $combinedDateTime = $this->combineDateAndTime($validityStart, $dailyStart);

            if ($combinedDateTime->isPast()) {

                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'validity_start',
                    message: 'Availability start date/time cannot be in the past. ' .
                        'The combination of validity_start and daily_start must be in the future.'
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }


    /**
     * Validates updated availability when validity_start is changed.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateUpdatedAvailability(ValidationContextInterface $validationContext): void
    {
        try {
            $validityStart = Carbon::parse($validationContext->get('validity_start'));

            $dailyStart = $validationContext->has('daily_start')
                ? $validationContext->get('daily_start')
                : $this->getCurrentDailyStart($validationContext);

            if (empty($dailyStart)) {
                $dailyStart = '00:00:00';
            }

            $combinedDateTime = $this->combineDateAndTime($validityStart, $dailyStart);

            if ($combinedDateTime->isPast()) {
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'validity_start',
                    message: 'Availability start date/time cannot be moved to the past. ' .
                        'The combination of validity_start and daily_start must be in the future.'
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }

    /**
     * Validates schedule/impediment start datetime is not in the past.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateFutureDateTime(ValidationContextInterface $validationContext): void
    {
        if ($validationContext->getOperation() === OperationType::CREATE) {
            $this->validateNewDateTime($validationContext);
            return;
        }

        if (
            $validationContext->getOperation() === OperationType::UPDATE &&
            $validationContext->has('start_datetime')
        ) {
            $this->validateUpdatedDateTime($validationContext);
        }
    }

    /**
     * Validates new schedule/impediment creation.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateNewDateTime(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('start_datetime')) {
            return;
        }

        try {
            $date = Carbon::parse($validationContext->get('start_datetime'));

            if ($date->isPast()) {
                $entityType = $validationContext->getEntityType();
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'start_datetime',
                    message: sprintf('%s start datetime cannot be in the past', $entityType->displayName())
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }

    /**
     * Validates updated schedule/impediment when start_datetime is changed.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateUpdatedDateTime(ValidationContextInterface $validationContext): void
    {
        try {
            $date = Carbon::parse($validationContext->get('start_datetime'));

            if ($date->isPast()) {
                $entityType = $validationContext->getEntityType();
                $validationContext->setViolationFromRule(
                    rule: $this,
                    field: 'start_datetime',
                    message: sprintf('%s start datetime cannot be moved to the past', $entityType->displayName())
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }

    /**
     * Combines date and time strings into a Carbon instance with midnight crossing support.
     *
     * Handles the edge case where time is from previous day (23:xx:xx) when current time is between 00:00-01:00.
     *
     * @param Carbon $date
     * @param string|null $time
     * @return Carbon
     */
    private function combineDateAndTime(Carbon $date, ?string $time): Carbon
    {
        if (!$time) {
            return $date->copy();
        }

        // Parse l'heure pour avoir un objet Carbon
        $timeCarbon = Carbon::parse($time);

        // Vérifie si on est dans la fenêtre critique (00:00-01:00)
        $now = Carbon::now();
        $isMidnightWindow = $now->hour === 0 && $now->minute >= 0 && $now->minute < 60;

        // Vérifie si l'heure fournie est >= 23:00:00
        $isLateTime = $timeCarbon->hour >= 23;

        // Si on est entre 00:00 et 01:00 ET que l'heure est tardive (≥23h), on recule d'un jour
        if ($isMidnightWindow && $isLateTime) {
            return $date->copy()
                ->subDay()
                ->setTimeFrom($timeCarbon);
        }

        // Comportement normal : combinaison directe
        return $date->copy()->setTimeFrom($timeCarbon);
    }

    /**
     * Gets current daily_start from database (for update operations).
     *
     * @param ValidationContextInterface $validationContext
     * @return string|null
     */
    private function getCurrentDailyStart(ValidationContextInterface $validationContext): ?string
    {
        $entity = $validationContext->getCurrentEntity();

        if (!$entity || !isset($entity->daily_start)) {
            return null;
        }

        return $entity->daily_start->format('Y-m-d H:i:s');
    }
}
