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
    priority: 60,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityDateRangeRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        $operationType = $validationContext->getOperation();
        $entity = $validationContext->getCurrentEntity();

        $this->validateValidityDates($validationContext, $entity, $operationType);
        $this->validateDailyTimes($validationContext, $entity, $operationType);
    }

    private function validateValidityDates(ValidationContextInterface $validationContext, ?object $entity, OperationType $operationType): void
    {
        if ($operationType === OperationType::CREATE) {
            // CREATE : les deux dates doivent être fournies dans le contexte

            if (!$validationContext->has('validity_start') || !$validationContext->has('validity_end')) {
                return; // Validation des champs requis gérée par une autre règle
            }

            $startValue = $validationContext->get('validity_start');
            $endValue = $validationContext->get('validity_end');

            $this->validateDatePair($validationContext, $startValue, $endValue, 'validity_date_range', 'End date must be after start date', true);
        } else {
            // UPDATE : logique intelligente
            $hasStart = $validationContext->has('validity_start');
            $hasEnd = $validationContext->has('validity_end');

            // Si aucune des deux dates n'est modifiée, on ne valide pas
            if (!$hasStart && !$hasEnd) {
                return;
            }

            // Récupérer les valeurs
            $startValue = $hasStart
                ? $validationContext->get('validity_start')
                : ($entity?->validity_start ?? null);

            $endValue = $hasEnd
                ? $validationContext->get('validity_end')
                : ($entity?->validity_end ?? null);

            // Maintenant on a les deux valeurs (soit du contexte, soit de l'entité)
            $this->validateDatePair($validationContext, $startValue, $endValue, 'validity_date_range', 'End date must be after start date', true);
        }
    }

    private function validateDailyTimes(ValidationContextInterface $validationContext, ?object $entity, OperationType $operationType): void
    {
        if ($operationType === OperationType::CREATE) {
            // CREATE : les deux heures doivent être fournies dans le contexte
            if (!$validationContext->has('daily_start') || !$validationContext->has('daily_end')) {
                return; // Validation des champs requis gérée par une autre règle
            }

            $startValue = $validationContext->get('daily_start');
            $endValue = $validationContext->get('daily_end');

            $this->validateTimePair($validationContext, $startValue, $endValue, 'daily_time_range', 'End time must be after start time');
        } else {
            // UPDATE : logique intelligente
            $hasStart = $validationContext->has('daily_start');
            $hasEnd = $validationContext->has('daily_end');

            // Si aucune des deux heures n'est modifiée, on ne valide pas
            if (!$hasStart && !$hasEnd) {
                return;
            }

            // Récupérer les valeurs
            $startValue = $hasStart
                ? $validationContext->get('daily_start')
                : ($entity?->daily_start ?? null);

            $endValue = $hasEnd
                ? $validationContext->get('daily_end')
                : ($entity?->daily_end ?? null);

            // Maintenant on a les deux valeurs (soit du contexte, soit de l'entité)
            $this->validateTimePair($validationContext, $startValue, $endValue, 'daily_time_range', 'End time must be after start time');
        }
    }

    private function validateDatePair(
        ValidationContextInterface $validationContext,
        mixed $startValue,
        mixed $endValue,
        string $violationKey,
        string $violationMessage,
        bool $checkMaxDuration = false
    ): void {
        // Maintenant on devrait toujours avoir les deux valeurs
        // (soit du contexte, soit de l'entité pour UPDATE)
        if ($startValue === null || $endValue === null) {
            // Ce cas ne devrait normalement pas arriver si les autres règles font leur travail
            return;
        }

        $start = Carbon::parse($startValue);
        $end = Carbon::parse($endValue);
        try {

            if ($end->lt($start)) {
                $validationContext->setViolation(

                    $violationKey,
                    $violationMessage
                );
            }

            // Vérifier la durée maximale si demandé
            if ($checkMaxDuration) {
                $maxDays = $this->getMaxDays();
                if ($start->diffInDays($end) > $maxDays) {
                    $validationContext->setViolation(

                        'max_duration',
                        sprintf("Availability period cannot exceed %d days", $maxDays)
                    );
                }
            }
        } catch (Exception $exception) {
            $validationContext->setViolation(

                'date_format',
                "Invalid date format: " . $exception->getMessage()
            );
        }
    }

    private function validateTimePair(
        ValidationContextInterface $validationContext,
        mixed $startValue,
        mixed $endValue,
        string $violationKey,
        string $violationMessage
    ): void {
        // Maintenant on devrait toujours avoir les deux valeurs
        if ($startValue === null || $endValue === null) {
            return;
        }

        try {
            $start = Carbon::parse($startValue);
            $end = Carbon::parse($endValue);
            if ($end->lte($start)) {
                $validationContext->setViolation(
                    $violationKey,
                    $violationMessage
                );
            }

            // Vérifier la durée minimale (15 minutes)
            $durationInMinutes = $start->diffInMinutes($end);
            if ($durationInMinutes < 15) {

                $validationContext->setViolation(

                    'min_duration',
                    "Minimum duration must be at least 15 minutes"
                );
            }
        } catch (Exception $exception) {
            $validationContext->setViolation(

                'time_format',
                "Invalid time format: " . $exception->getMessage()
            );
        }
    }

    protected function getMaxDays(): int
    {
        return config('roster.validation.max_availability_days', 365);
    }
}
