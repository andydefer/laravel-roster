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
            $excludeId = $currentEntity ? $currentEntity->id : null;
            // 1. Vérifie chevauchement avec autres schedules
            $scheduleRepository = app(ScheduleRepositoryInterface::class);
            if ($scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end, $excludeId)) {
                $validationContext->setViolation(
                    'overlap',
                    'Schedule overlaps with an existing schedule'
                );
            }

            // 2. Vérifie chevauchement avec impediments
            $impedimentRepository = app(ImpedimentRepositoryInterface::class);
            if ($impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end)) {
                $validationContext->setViolation(
                    'overlap',
                    'Schedule overlaps with an existing impediment'
                );
            }
        } catch (Exception $exception) {
            // Format validation handled by other rules
        }
    }
}
