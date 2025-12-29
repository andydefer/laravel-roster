<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates that schedules and impediments do not overlap with existing entities.
 *
 * Ensures temporal integrity by checking for conflicts with existing schedules
 * and impediments within the same availability period.
 */
#[ValidationRule(
    priority: 80,
    entities: [EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class ScheduleOverlapRule extends AbstractRule
{
    /**
     * Initializes the rule with required dependencies.
     *
     * @param TemporalConflictService $temporalConflictService Service for detecting temporal conflicts
     */
    public function __construct(
        private TemporalConflictService $temporalConflictService
    ) {}

    /**
     * Validates that the proposed time slot does not overlap with existing entities.
     *
     * @param ValidationContextInterface $validationContext Validation context with entity data
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$this->hasRequiredFields($validationContext)) {
            return;
        }

        try {
            $start = Carbon::parse($validationContext->get('start_datetime'));
            $end = Carbon::parse($validationContext->get('end_datetime'));
            $availabilityId = $validationContext->get('availability_id');

            if (!$availabilityId) {
                return;
            }

            $exclusionIds = $this->determineExclusionIds($validationContext);
            $this->checkForConflicts($validationContext, $availabilityId, $start, $end, $exclusionIds);
        } catch (Exception $exception) {
            report($exception);
        }
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "Validates that no temporal overlap exists with other time slots.\n" .
            "This rule prevents:\n" .
            "- Double bookings on the same time slot\n" .
            "- Impediments overlapping with existing schedules\n" .
            "- Availability conflicts between different entity types\n" .
            "\n" .
            "The system checks within the same availability if there are existing " .
            "entities (schedules or impediments) whose periods overlap with the proposed one.";
    }

    /**
     * Checks if the validation context has required fields.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return bool True if required fields are present
     */
    private function hasRequiredFields(ValidationContextInterface $validationContext): bool
    {
        return $validationContext->has('start_datetime')
            && $validationContext->has('end_datetime');
    }

    /**
     * Determines which entity IDs to exclude from conflict checking.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @return array<string, int|null> Exclusion IDs keyed by entity type
     */
    private function determineExclusionIds(ValidationContextInterface $validationContext): array
    {
        $currentEntity = $validationContext->getCurrentEntity();
        $entityType = $validationContext->getEntityType();

        $excludeScheduleId = null;
        $excludeImpedimentId = null;

        if ($currentEntity) {
            if ($entityType === EntityType::SCHEDULE) {
                $excludeScheduleId = $currentEntity->id ?? null;
            } elseif ($entityType === EntityType::IMPEDIMENT) {
                $excludeImpedimentId = $currentEntity->id ?? null;
            }
        }

        return [
            'excludeScheduleId' => $excludeScheduleId,
            'excludeImpedimentId' => $excludeImpedimentId,
        ];
    }

    /**
     * Checks for temporal conflicts and reports violations if found.
     *
     * @param ValidationContextInterface $validationContext Validation context
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Proposed start time
     * @param Carbon $end Proposed end time
     * @param array<string, int|null> $exclusionIds IDs to exclude from conflict checking
     */
    private function checkForConflicts(
        ValidationContextInterface $validationContext,
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        array $exclusionIds
    ): void {
        $conflictResult = $this->temporalConflictService->checkAllConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeScheduleId: $exclusionIds['excludeScheduleId'],
            excludeImpedimentId: $exclusionIds['excludeImpedimentId']
        );

        if ($conflictResult->hasConflicts) {
            $validationContext->setViolationFromRule(
                $this,
                'overlap',
                $conflictResult->message
            );
        }
    }
}
