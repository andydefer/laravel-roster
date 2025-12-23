<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 80,
    entities: [EntityType::SCHEDULE],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class ScheduleOverlapRule extends AbstractRule
{
    public function validate(ValidationContextInterface $validationContext): void
    {

        if (!$validationContext->has('start_datetime') || !$validationContext->has('end_datetime')) {
            return;
        }

        try {
            $start = Carbon::parse($validationContext->get('start_datetime'));
            $end = Carbon::parse($validationContext->get('end_datetime'));
            $availabilityId = $validationContext->get('availability_id');


            if (!$availabilityId) {
                return;
            }

            $currentEntity = $validationContext->getCurrentEntity();


            if ($currentEntity) {
            }

            $excludeId = $currentEntity ? ($currentEntity->id ?? null) : null;


            // 1. Vérifie chevauchement avec autres schedules
            $scheduleRepository = app(ScheduleRepositoryInterface::class);


            // Vérifiez d'abord SANS exclusion pour voir ce qui existe
            $allOverlapping = $scheduleRepository->findOverlappingSchedules($availabilityId, $start, $end);
            if ($allOverlapping->count() > 0) {
                foreach ($allOverlapping as $schedule) {
                }
            }

            // Puis vérifiez AVEC exclusion
            if ($excludeId) {
                $overlappingExcludingSelf = $scheduleRepository->findOverlappingSchedules($availabilityId, $start, $end, $excludeId);
                if ($overlappingExcludingSelf->count() > 0) {
                    foreach ($overlappingExcludingSelf as $schedule) {
                    }
                }
            }

            $hasScheduleOverlap = $scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end, $excludeId);



            if ($hasScheduleOverlap) {
                $validationContext->setViolation(
                    'overlap',
                    'Schedule overlaps with an existing schedule'
                );
                return;
            }

            // 2. Vérifie chevauchement avec impediments
            $impedimentRepository = app(ImpedimentRepositoryInterface::class);
            $hasImpedimentOverlap = $impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end);


            if ($hasImpedimentOverlap) {
                $validationContext->setViolation(
                    'overlap',
                    'Schedule overlaps with an existing impediment'
                );
                return;
            }
        } catch (Exception $exception) {
        }
    }
}
