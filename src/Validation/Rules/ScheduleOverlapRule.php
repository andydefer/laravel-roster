<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Domain\Services\TemporalConflictService;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Validation\Attributes\ValidationRule;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

#[ValidationRule(
    priority: 80,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class ScheduleOverlapRule extends AbstractRule
{
    public function __construct(
        private TemporalConflictService $conflictService
    ) {}

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
            $excludeScheduleId = null;
            $excludeImpedimentId = null;

            if ($currentEntity) {
                if ($validationContext->getEntityType() === EntityType::SCHEDULE) {
                    $excludeScheduleId = $currentEntity->id ?? null;
                } elseif ($validationContext->getEntityType() === EntityType::IMPEDIMENT) {
                    $excludeImpedimentId = $currentEntity->id ?? null;
                }
            }

            $conflictResult = $this->conflictService->checkAllConflicts(
                availabilityId: $availabilityId,
                start: $start,
                end: $end,
                excludeScheduleId: $excludeScheduleId,
                excludeImpedimentId: $excludeImpedimentId
            );

            if ($conflictResult->hasConflicts) {
                $validationContext->setViolation(
                    'overlap',
                    $conflictResult->message
                );
            }
        } catch (\Exception $exception) {
            report($exception);
        }
    }
}
