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
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE]
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
        if ($validationContext->getOperation() !== OperationType::CREATE) {
            return;
        }

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
     * Validates availability start date is not in the past.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    private function validateFutureAvailability(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('start_date')) {
            return;
        }

        try {
            $date = Carbon::parse($validationContext->get('start_date'));

            if ($date->isPast()) {
                $validationContext->setViolation(
                    field: 'start_date',
                    message: 'Availability start date cannot be in the past'
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
        if (!$validationContext->has('start_datetime')) {
            return;
        }

        try {
            $date = Carbon::parse($validationContext->get('start_datetime'));

            if ($date->isPast()) {
                $entityType = $validationContext->getEntityType();
                $validationContext->setViolation(
                    field: 'start_datetime',
                    message: sprintf('%s start datetime cannot be in the past', $entityType->displayName())
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }
}
