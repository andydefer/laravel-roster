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
 * Validates minimum duration requirements for time-based entities.
 *
 * Applies different duration checks based on entity type:
 * - Availability: Validates daily time windows
 * - Schedule/Impediment: Validates datetime ranges
 */
#[ValidationRule(
    priority: 70,
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class DurationRule extends AbstractRule
{
    /**
     * Validates duration constraints for the given entity.
     *
     * @param ValidationContextInterface $validationContext Validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entityType = $validationContext->getEntityType();
        $operationType = $validationContext->getOperation();
        $data = $validationContext->safeData();

        if ($entityType === EntityType::AVAILABILITY) {
            $this->validateAvailabilityDuration(
                validationContext: $validationContext,
                data: $data,
                operationType: $operationType
            );
        } else {
            $this->validateDateTimeDuration(
                validationContext: $validationContext,
                data: $data,
                operationType: $operationType
            );
        }
    }

    /**
     * Validates minimum duration for availability daily time windows.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array<string, mixed> $data Safe validation data
     * @param OperationType $operationType Current operation
     */
    private function validateAvailabilityDuration(
        ValidationContextInterface $validationContext,
        array $data,
        OperationType $operationType
    ): void {
        if ($operationType === OperationType::CREATE && (!isset($data['start_time']) || !isset($data['end_time']))) {
            return;
        }

        if ($operationType === OperationType::UPDATE && !isset($data['start_time']) && !isset($data['end_time'])) {
            return;
        }

        try {
            $start = isset($data['start_time']) ? Carbon::parse($data['start_time']) : null;
            $end = isset($data['end_time']) ? Carbon::parse($data['end_time']) : null;

            if (!$start instanceof Carbon || !$end instanceof Carbon) {
                return;
            }

            $minimumMinutes = $this->getMinimumDuration(EntityType::AVAILABILITY);

            if ($start->diffInMinutes($end) < $minimumMinutes) {
                $validationContext->setViolation(
                    field: 'duration',
                    message: sprintf(
                        "Minimum duration of %d minutes required for availability. Got %d minutes",
                        $minimumMinutes,
                        $start->diffInMinutes($end)
                    )
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation(
                field: 'time_format',
                message: "Invalid time format: " . $exception->getMessage()
            );
        }
    }

    /**
     * Validates minimum duration for datetime-based entities (Schedule/Impediment).
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param array<string, mixed> $data Safe validation data
     * @param OperationType $operationType Current operation
     */
    private function validateDateTimeDuration(
        ValidationContextInterface $validationContext,
        array $data,
        OperationType $operationType
    ): void {
        if ($operationType === OperationType::CREATE && (!isset($data['start_datetime']) || !isset($data['end_datetime']))) {
            return;
        }

        if ($operationType === OperationType::UPDATE && !isset($data['start_datetime']) && !isset($data['end_datetime'])) {
            return;
        }

        try {
            $start = isset($data['start_datetime']) ? Carbon::parse($data['start_datetime']) : null;
            $end = isset($data['end_datetime']) ? Carbon::parse($data['end_datetime']) : null;

            if (!$start instanceof Carbon || !$end instanceof Carbon) {
                return;
            }

            $entityType = $validationContext->getEntityType();
            $minimumMinutes = $this->getMinimumDuration($entityType);

            if ($start->diffInMinutes($end) < $minimumMinutes) {
                $validationContext->setViolation(
                    field: 'duration',
                    message: sprintf(
                        "Minimum duration of %d minutes required for %s. Got %d minutes",
                        $minimumMinutes,
                        $entityType->displayName(),
                        $start->diffInMinutes($end)
                    )
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation(
                field: 'datetime_format',
                message: "Invalid datetime format: " . $exception->getMessage()
            );
        }
    }
}
