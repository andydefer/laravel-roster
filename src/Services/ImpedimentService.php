<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Domain\Helpers\TimeSlotHelper;
use Roster\Domain\Helpers\TimeWindowHelper;
use Roster\DTOs\ImpedimentData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing Impediment entities and their interactions with schedules.
 *
 * Handles creation, validation, conflict detection, and time slot availability
 * calculations involving impediments within availability periods.
 */
class ImpedimentService extends AbstractService
{

    /**
     * Returns the entity type for this service.
     *
     * @return EntityType Impediment entity type
     */
    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::IMPEDIMENT;
    }

    /**
     * Checks for scheduling conflicts before creating or updating an impediment.
     *
     * @param mixed $dto Impediment data transfer object
     * @param int|null $excludeId Impediment ID to exclude from conflict checks
     * @throws ValidationFailedException When conflicts are detected
     */
    protected function checkEntityConflicts(mixed $dto, ?int $excludeId = null): void
    {
        if (!isset($dto->availabilityId, $dto->startDatetime, $dto->endDatetime)) {
            return;
        }

        $availabilityId = $dto->availabilityId;
        $start = Carbon::parse($dto->startDatetime);
        $end = Carbon::parse($dto->endDatetime);

        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeImpedimentId: $excludeId
        );

        if (!$conflictResult->hasConflicts) {
            return;
        }

        $this->handleConflictingEntity(
            conflictResult: $conflictResult,
            excludeId: $excludeId,
            operationType: OperationType::CREATE
        );
    }

    /**
     * Determines if a specific time slot is blocked by an impediment.
     *
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     * @return bool True if the time slot is blocked
     */
    public function isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->availabilityRepository->getAvailabilityForTimeSlot(
            model: $this->schedulable,
            start: $start,
            end: $end,
            type: $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        $conflictResult = $this->conflictService->checkImpedimentConflicts(
            availabilityId: $availability->id,
            start: $start,
            end: $end
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Retrieves available time slots considering existing impediments.
     *
     * @param Carbon $start Start of the time range to check
     * @param Carbon $end End of the time range to check
     * @param string|null $type Optional availability type filter
     * @return Collection<array> Available time slots
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $availability = $this->availabilityRepository->getAvailabilityForTimeSlot(
            model: $this->schedulable,
            start: $start,
            end: $end,
            type: $type
        );

        if (!$availability instanceof Availability) {
            return collect();
        }

        $availableSlots = $this->conflictService->findAvailableSlots(
            availabilityId: $availability->id,
            rangeStart: $start,
            rangeEnd: $end
        );

        return collect($availableSlots);
    }

    /**
     * Checks if an impediment would overlap with any existing schedule.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time of proposed impediment
     * @param Carbon $end End time of proposed impediment
     * @param int|null $exceptImpedimentId Impediment ID to exclude from check
     * @return bool True if overlapping with a schedule
     */
    public function wouldOverlapWithSchedule(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        $conflictResult = $this->conflictService->checkScheduleConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Checks if an impediment would overlap with any other impediment.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time of proposed impediment
     * @param Carbon $end End time of proposed impediment
     * @param int|null $exceptImpedimentId Impediment ID to exclude from check
     * @return bool True if overlapping with another impediment
     */
    public function wouldOverlapWithOtherImpediment(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        $conflictResult = $this->conflictService->checkImpedimentConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeImpedimentId: $exceptImpedimentId
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Finds impediments within a specific time slot.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @return Collection Impediments within the specified time slot
     */
    public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection
    {
        TimeWindowHelper::assertDailyWindow($start, $end);

        return $this->impedimentRepository
            ->findByAvailability($availabilityId)
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->get();
    }

    /**
     * Finds impediments that overlap with a given time range.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time of the range
     * @param Carbon $end End time of the range
     * @param int|null $excludeId Impediment ID to exclude from results
     * @return Collection Overlapping impediments
     */
    public function findOverlappingImpediments(int $availabilityId, Carbon $start, Carbon $end, ?int $excludeId = null): Collection
    {
        return $this->impedimentRepository
            ->findByAvailability($availabilityId)
            ->get()
            ->filter(function ($impediment) use ($start, $end, $excludeId): bool {
                if ($excludeId && $impediment->id === $excludeId) {
                    return false;
                }

                return TimeSlotHelper::overlaps(
                    firstSlotStart: $start,
                    firstSlotEnd: $end,
                    secondSlotStart: $impediment->start_datetime,
                    secondSlotEnd: $impediment->end_datetime
                );
            });
    }

    /**
     * Retrieves future impediments for an availability starting from a given date.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $from Start date for filtering future impediments
     * @return Collection Future impediments
     */
    public function getFutureImpediments(int $availabilityId, Carbon $from): Collection
    {
        return $this->impedimentRepository->getFutureImpediments(
            availabilityId: $availabilityId,
            from: $from
        );
    }

    /**
     * Checks if a time slot has any scheduling conflict.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param int|null $excludeImpedimentId Impediment ID to exclude from check
     * @return bool True if any conflict exists
     */
    public function hasAnyConflict(int $availabilityId, Carbon $start, Carbon $end, ?int $excludeImpedimentId = null): bool
    {
        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeImpedimentId: $excludeImpedimentId
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Retrieves all blocked periods for an availability within a time range.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time of the range
     * @param Carbon $end End time of the range
     * @return Collection<array> Blocked periods with metadata
     */
    public function getBlockedPeriods(int $availabilityId, Carbon $start, Carbon $end): Collection
    {
        TimeWindowHelper::assertDailyWindow($start, $end);

        return $this->impedimentRepository
            ->findByAvailability($availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->get()
            ->map(function ($impediment): array {
                return [
                    'start' => $impediment->start_datetime,
                    'end' => $impediment->end_datetime,
                    'reason' => $impediment->reason,
                    'type' => 'impediment',
                    'id' => $impediment->id
                ];
            });
    }

    /**
     * Handles conflicts by throwing appropriate validation exceptions.
     *
     * @param object $conflictResult Conflict checking result
     * @param int|null $excludeId ID to exclude from conflict checks
     * @param OperationType $operationType Operation being performed
     * @throws ValidationFailedException With appropriate conflict message
     */
    private function handleConflictingEntity(object $conflictResult, ?int $excludeId, OperationType $operationType): void
    {
        if ($conflictResult->hasScheduleConflicts()) {
            $this->throwScheduleConflictException(
                conflictingSchedule: $conflictResult->conflictingSchedules[0],
                operationType: $operationType
            );
        }

        if ($conflictResult->hasImpedimentConflicts()) {
            $this->throwImpedimentConflictException(
                conflictingImpediment: $conflictResult->conflictingImpediments[0],
                operationType: $operationType
            );
        }
    }

    /**
     * Throws a validation exception for schedule conflicts.
     *
     * @param object $conflictingSchedule Conflicting schedule
     * @param OperationType $operationType Operation being performed
     * @throws ValidationFailedException With schedule conflict message
     */
    private function throwScheduleConflictException(object $conflictingSchedule, OperationType $operationType): void
    {
        throw ValidationFailedException::fromViolations(
            violations: [
                'overlap' => sprintf(
                    'Impediment would overlap with existing schedule from %s to %s',
                    $conflictingSchedule->start_datetime->format('Y-m-d H:i'),
                    $conflictingSchedule->end_datetime->format('Y-m-d H:i')
                )
            ],
            operationType: $operationType,
            entityType: EntityType::IMPEDIMENT
        );
    }

    /**
     * Throws a validation exception for impediment conflicts.
     *
     * @param object $conflictingImpediment Conflicting impediment
     * @param OperationType $operationType Operation being performed
     * @throws ValidationFailedException With impediment conflict message
     */
    private function throwImpedimentConflictException(object $conflictingImpediment, OperationType $operationType): void
    {
        throw ValidationFailedException::fromViolations(
            violations: [
                'overlap' => sprintf(
                    'Impediment would overlap with existing impediment from %s to %s',
                    $conflictingImpediment->start_datetime->format('Y-m-d H:i'),
                    $conflictingImpediment->end_datetime->format('Y-m-d H:i')
                )
            ],
            operationType: $operationType,
            entityType: EntityType::IMPEDIMENT
        );
    }
}
