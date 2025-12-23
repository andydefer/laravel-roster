<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 40,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE]
)]
class FutureDateRule extends AbstractRule
{
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

    private function validateFutureAvailability(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('start_date')) {
            return;
        }

        try {
            $date = Carbon::parse($validationContext->get('start_date'));

            if ($date->isPast()) {
                $validationContext->setViolation(
                    'start_date',
                    'Availability start date cannot be in the past'
                );
            }
        } catch (Exception $exception) {
            // La validation du format est gérée par d'autres règles
        }
    }

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
                    'start_datetime',
                    sprintf('%s start datetime cannot be in the past', $entityType->displayName())
                );
            }
        } catch (Exception $exception) {
            // La validation du format est gérée par d'autres règles
        }
    }
}
