<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Enums\DaysOfWeek;

#[ValidationRule(
    priority: 85,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityDaysCoherenceRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {
        // Si les jours ne sont pas fournis, pas de validation
        if (!$validationContext->has('days')) {
            return;
        }

        $days = $validationContext->get('days');

        // null = absent (safeData semantics)
        if ($days === null) {
            return;
        }

        // Vérification du type avant traitement métier
        if (!is_array($days)) {
            $validationContext->setViolation(
                'days',
                'Days must be an array'
            );
            return;
        }

        // Vérification que le tableau n'est pas vide
        if ($days === []) {
            // Ne pas valider ici - une autre règle (DaysValidationRule) gère cela
            return;
        }

        // Vérifier que tous les jours sont valides selon l'enum DaysOfWeek
        $validDays = DaysOfWeek::values();

        foreach ($days as $day) {
            if (!in_array($day, $validDays, true)) {
                $validationContext->setViolation(
                    'days',
                    sprintf("Day '%s' is not a valid day of week", $day)
                );
                return;
            }
        }

        // Récupérer l'entité actuelle
        $entity = $validationContext->getCurrentEntity();

        // Déterminer les dates de validité à utiliser
        $validityStart = $this->getValidityDate($validationContext, 'validity_start', $entity);
        $validityEnd = $this->getValidityDate($validationContext, 'validity_end', $entity);

        // Si une des dates est null, pas de validation de cohérence
        if ($validityStart === null || $validityEnd === null) {
            return;
        }

        // Vérifier que les dates sont valides avant de continuer
        try {
            $start = Carbon::parse($validityStart);
            $end = Carbon::parse($validityEnd);

            // Si end <= start, pas de validation de cohérence des jours
            if ($end->lte($start)) {
                return;
            }
        } catch (Exception $exception) {
            // Dates invalides, une autre règle s'en occupera
            return;
        }

        // Utiliser les helpers pour la validation
        $daysInPeriod = roster_days_in_period($validityStart, $validityEnd);

        // Vérifier chaque jour fourni
        foreach ($days as $day) {
            if (!in_array($day, $daysInPeriod, true)) {
                $periodDescription = roster_format_period_days_for_display($daysInPeriod);

                $validationContext->setViolation(
                    'days',
                    sprintf("Day '%s' is not within the validity period (%s)", $day, $periodDescription)
                );
                // Pas de return ici pour collecter toutes les violations
            }
        }
    }

    /**
     * Récupère une date de validité en priorisant le contexte, puis l'entité
     */
    private function getValidityDate(ValidationContextInterface $validationContext, string $field, ?object $entity): mixed
    {
        // Si la date est fournie dans le contexte (même si null), l'utiliser
        if ($validationContext->has($field)) {
            return $validationContext->get($field);
        }

        // Sinon, pour l'UPDATE, récupérer depuis l'entité existante
        if ($validationContext->getOperation() === OperationType::UPDATE && $entity !== null) {
            return match ($field) {
                'validity_start' => $entity->validity_start ?? null,
                'validity_end' => $entity->validity_end ?? null,
                default => null
            };
        }

        // Pour CREATE ou sans entité, retourner null
        return null;
    }
}
