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
    priority: 70,
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class DurationRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $entityType = $validationContext->getEntityType();
        $operationType = $validationContext->getOperation();
        $data = $validationContext->safeData(); // données non null et définies

        if ($entityType === EntityType::AVAILABILITY) {
            $this->validateAvailabilityDuration($validationContext, $data, $operationType);
        } else {
            $this->validateDateTimeDuration($validationContext, $data, $operationType);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateAvailabilityDuration(ValidationContextInterface $validationContext, array $data, OperationType $operationType): void
    {
        // CREATE : les deux champs doivent être présents
        if ($operationType === OperationType::CREATE && (!isset($data['start_time']) || !isset($data['end_time']))) {
            return;
        }

        // UPDATE : ne vérifier que si l'un des deux champs est fourni
        if ($operationType === OperationType::UPDATE && !isset($data['start_time']) && !isset($data['end_time'])) {
            return;
        }

        try {
            $start = isset($data['start_time']) ? Carbon::parse($data['start_time']) : null;
            $end = isset($data['end_time']) ? Carbon::parse($data['end_time']) : null;

            if (!$start instanceof Carbon || !$end instanceof Carbon) {
                return; // on ne peut pas calculer la durée
            }

            $minimumMinutes = $this->getMinimumDuration(EntityType::AVAILABILITY);

            if ($start->diffInMinutes($end) < $minimumMinutes) {
                $validationContext->setViolation(
                    'duration',
                    sprintf(
                        "Minimum duration of %d minutes required for availability. Got %d minutes",
                        $minimumMinutes,
                        $start->diffInMinutes($end)
                    )
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation('time_format', "Invalid time format: " . $exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateDateTimeDuration(ValidationContextInterface $validationContext, array $data, OperationType $operationType): void
    {
        // CREATE : les deux champs doivent être présents
        if ($operationType === OperationType::CREATE && (!isset($data['start_datetime']) || !isset($data['end_datetime']))) {
            return;
        }

        // UPDATE : ne vérifier que si l'un des deux champs est fourni
        if ($operationType === OperationType::UPDATE && !isset($data['start_datetime']) && !isset($data['end_datetime'])) {
            return;
        }

        try {
            $start = isset($data['start_datetime']) ? Carbon::parse($data['start_datetime']) : null;
            $end = isset($data['end_datetime']) ? Carbon::parse($data['end_datetime']) : null;

            if (!$start instanceof Carbon || !$end instanceof Carbon) {
                return; // on ne peut pas calculer la durée
            }

            $entityType = $validationContext->getEntityType();
            $minimumMinutes = $this->getMinimumDuration($entityType);

            if ($start->diffInMinutes($end) < $minimumMinutes) {
                $validationContext->setViolation(
                    'duration',
                    sprintf(
                        "Minimum duration of %d minutes required for %s. Got %d minutes",
                        $minimumMinutes,
                        $entityType->displayName(),
                        $start->diffInMinutes($end)
                    )
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation('datetime_format', "Invalid datetime format: " . $exception->getMessage());
        }
    }
}
