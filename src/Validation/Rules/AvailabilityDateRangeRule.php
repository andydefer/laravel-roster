<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

/**
 * Validates date and time ranges for availability entities.
 *
 * Ensures validity dates and daily time windows are properly ordered
 * and respect business constraints like minimum/maximum durations.
 */
#[ValidationRule(
    priority: 60,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityDateRangeRule extends AbstractRule
{
    /**
     * Validates date and time ranges for availability operations.
     *
     * @param ValidationContextInterface $validationContext The validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $entity = $validationContext->getCurrentEntity();

        $this->validateValidityDates($validationContext, $entity, $operationType);
        $this->validateDailyTimes($validationContext, $entity, $operationType);
    }

    /**
     * Validates validity start and end dates.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param Model|null $entity The entity being validated
     * @param OperationType $operationType The operation type
     */
    private function validateValidityDates(
        ValidationContextInterface $validationContext,
        ?Model $entity,
        OperationType $operationType
    ): void {
        if ($operationType === OperationType::CREATE) {
            $this->validateCreateValidityDates($validationContext);
        } else {
            $this->validateUpdateValidityDates($validationContext, $entity);
        }
    }

    /**
     * Validates validity dates for CREATE operations.
     *
     * @param ValidationContextInterface $validationContext The validation context
     */
    private function validateCreateValidityDates(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('validity_start') || !$validationContext->has('validity_end')) {
            return;
        }

        $startValue = $validationContext->get('validity_start');
        $endValue = $validationContext->get('validity_end');

        $this->validateDateRange(
            validationContext: $validationContext,
            startValue: $startValue,
            endValue: $endValue,
            violationKey: 'validity_date_range',
            violationMessage: 'End date must be after start date',
            checkMaxDuration: true
        );
    }

    /**
     * Validates validity dates for UPDATE operations.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param Model|null $entity The entity being validated
     */
    private function validateUpdateValidityDates(
        ValidationContextInterface $validationContext,
        ?Model $entity
    ): void {
        $hasStart = $validationContext->has('validity_start');
        $hasEnd = $validationContext->has('validity_end');

        if (!$hasStart && !$hasEnd) {
            return;
        }

        $startValue = $hasStart
            ? $validationContext->get('validity_start')
            : ($entity?->validity_start ?? null);

        $endValue = $hasEnd
            ? $validationContext->get('validity_end')
            : ($entity?->validity_end ?? null);

        $this->validateDateRange(
            validationContext: $validationContext,
            startValue: $startValue,
            endValue: $endValue,
            violationKey: 'validity_date_range',
            violationMessage: 'End date must be after start date',
            checkMaxDuration: true
        );
    }

    /**
     * Validates daily start and end times.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param Model|null $entity The entity being validated
     * @param OperationType $operationType The operation type
     */
    private function validateDailyTimes(
        ValidationContextInterface $validationContext,
        ?Model $entity,
        OperationType $operationType
    ): void {
        if ($operationType === OperationType::CREATE) {
            $this->validateCreateDailyTimes($validationContext);
        } else {
            $this->validateUpdateDailyTimes($validationContext, $entity);
        }
    }

    /**
     * Validates daily times for CREATE operations.
     *
     * @param ValidationContextInterface $validationContext The validation context
     */
    private function validateCreateDailyTimes(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('daily_start') || !$validationContext->has('daily_end')) {
            return;
        }

        $startValue = $validationContext->get('daily_start');
        $endValue = $validationContext->get('daily_end');

        $this->validateTimeRange(
            validationContext: $validationContext,
            startValue: $startValue,
            endValue: $endValue,
            violationKey: 'daily_time_range',
            violationMessage: 'End time must be after start time'
        );
    }

    /**
     * Validates daily times for UPDATE operations.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param Model|null $entity The entity being validated
     */
    private function validateUpdateDailyTimes(
        ValidationContextInterface $validationContext,
        ?Model $entity
    ): void {
        $hasStart = $validationContext->has('daily_start');
        $hasEnd = $validationContext->has('daily_end');

        if (!$hasStart && !$hasEnd) {
            return;
        }

        $startValue = $hasStart
            ? $validationContext->get('daily_start')
            : ($entity?->daily_start ?? null);

        $endValue = $hasEnd
            ? $validationContext->get('daily_end')
            : ($entity?->daily_end ?? null);

        $this->validateTimeRange(
            validationContext: $validationContext,
            startValue: $startValue,
            endValue: $endValue,
            violationKey: 'daily_time_range',
            violationMessage: 'End time must be after start time'
        );
    }

    /**
     * Validates a pair of dates with optional maximum duration check.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param mixed $startValue Start date value
     * @param mixed $endValue End date value
     * @param string $violationKey Key for violation reporting
     * @param string $violationMessage Message for violation reporting
     * @param bool $checkMaxDuration Whether to check maximum duration
     */
    private function validateDateRange(
        ValidationContextInterface $validationContext,
        mixed $startValue,
        mixed $endValue,
        string $violationKey,
        string $violationMessage,
        bool $checkMaxDuration = false
    ): void {
        if ($startValue === null || $endValue === null) {
            return;
        }

        try {
            $start = Carbon::parse($startValue);
            $end = Carbon::parse($endValue);

            if ($end->lt($start)) {
                $validationContext->setViolation(
                    field: $violationKey,
                    message: $violationMessage
                );
            }

            if ($checkMaxDuration) {
                $this->validateMaxDuration($validationContext, $start, $end);
            }
        } catch (Exception $exception) {
            $validationContext->setViolation(
                field: 'date_format',
                message: sprintf('Invalid date format: %s', $exception->getMessage())
            );
        }
    }

    /**
     * Validates a pair of times with minimum duration check.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param mixed $startValue Start time value
     * @param mixed $endValue End time value
     * @param string $violationKey Key for violation reporting
     * @param string $violationMessage Message for violation reporting
     */
    private function validateTimeRange(
        ValidationContextInterface $validationContext,
        mixed $startValue,
        mixed $endValue,
        string $violationKey,
        string $violationMessage
    ): void {
        if ($startValue === null || $endValue === null) {
            return;
        }

        try {
            $start = Carbon::parse($startValue);
            $end = Carbon::parse($endValue);

            if ($end->lte($start)) {
                $validationContext->setViolation(
                    field: $violationKey,
                    message: $violationMessage
                );
            }

            $this->validateMinDuration($validationContext, $start, $end);
        } catch (Exception $exception) {
            $validationContext->setViolation(
                field: 'time_format',
                message: sprintf('Invalid time format: %s', $exception->getMessage())
            );
        }
    }

    /**
     * Validates that the duration between dates does not exceed maximum allowed days.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param Carbon $start Start date
     * @param Carbon $end End date
     */
    private function validateMaxDuration(
        ValidationContextInterface $validationContext,
        Carbon $start,
        Carbon $end
    ): void {
        $maxDays = $this->getMaxDays();

        if ($start->diffInDays($end) > $maxDays) {
            $validationContext->setViolation(
                field: 'max_duration',
                message: sprintf('Availability period cannot exceed %d days', $maxDays)
            );
        }
    }

    /**
     * Validates that the duration between times meets minimum requirements.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param Carbon $start Start time
     * @param Carbon $end End time
     */
    private function validateMinDuration(
        ValidationContextInterface $validationContext,
        Carbon $start,
        Carbon $end
    ): void {
        $durationInMinutes = $start->diffInMinutes($end);
        $minimumMinutes = 15;

        if ($durationInMinutes < $minimumMinutes) {
            $validationContext->setViolation(
                field: 'min_duration',
                message: 'Minimum duration must be at least 15 minutes'
            );
        }
    }

    /**
     * Gets the maximum allowed availability duration in days.
     *
     * @return int Maximum days from configuration or default
     */
    protected function getMaxDays(): int
    {
        return config('roster.validation.max_availability_days', 365);
    }
}
