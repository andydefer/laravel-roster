<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\Domain\Helpers\TimeSlotHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\DTOs\ImpedimentData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;
use Roster\Validation\Exceptions\ValidationFailedException;

class ImpedimentService extends AbstractService
{


    protected function createDTOFromArray(array $data, OperationType $operationType): ImpedimentData
    {
        return ImpedimentData::fromArray($data);
    }

    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::IMPEDIMENT;
    }

    protected function checkEntityConflicts(mixed $dto, ?int $excludeId = null): void
    {
        if (!isset($dto->availabilityId, $dto->startDatetime, $dto->endDatetime)) {
            return;
        }

        $availabilityId = $dto->availabilityId;
        $start = Carbon::parse($dto->startDatetime);
        $end = Carbon::parse($dto->endDatetime);

        // Vérifier les conflits avec tous les types d'entités
        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeImpedimentId: $excludeId
        );

        if ($conflictResult->hasConflicts) {
            if ($conflictResult->hasScheduleConflicts()) {
                throw ValidationFailedException::fromViolations(
                    [
                        'overlap' => sprintf(
                            'Impediment would overlap with existing schedule from %s to %s',
                            $conflictResult->conflictingSchedules[0]->start_datetime->format('Y-m-d H:i'),
                            $conflictResult->conflictingSchedules[0]->end_datetime->format('Y-m-d H:i')
                        )
                    ],
                    OperationType::CREATE,
                    EntityType::IMPEDIMENT
                );
            }

            if ($conflictResult->hasImpedimentConflicts()) {
                throw ValidationFailedException::fromViolations(
                    [
                        'overlap' => sprintf(
                            'Impediment would overlap with existing impediment from %s to %s',
                            $conflictResult->conflictingImpediments[0]->start_datetime->format('Y-m-d H:i'),
                            $conflictResult->conflictingImpediments[0]->end_datetime->format('Y-m-d H:i')
                        )
                    ],
                    OperationType::CREATE,
                    EntityType::IMPEDIMENT
                );
            }
        }
    }

    // Impediment-specific methods

    /**
     * Check if a time slot is blocked by an impediment.
     */
    public function isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        // Trouver la disponibilité pour ce créneau
        $availability = $this->availabilityRepository->getAvailabilityForTimeSlot(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        // Vérifier les conflits d'impediments
        $conflictResult = $this->conflictService->checkImpedimentConflicts(
            $availability->id,
            $start,
            $end
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Get available time slots considering impediments.
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        // Trouver la disponibilité pour ce créneau
        $availability = $this->availabilityRepository->getAvailabilityForTimeSlot(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        if (!$availability instanceof Availability) {
            return collect();
        }

        // Trouver les créneaux disponibles en tenant compte des schedules et impediments
        $availableSlots = $this->conflictService->findAvailableSlots(
            $availability->id,
            $start,
            $end
        );

        return collect($availableSlots);
    }

    /**
     * Check if creating an impediment would overlap with any schedule.
     */
    public function wouldOverlapWithSchedule(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        $conflictResult = $this->conflictService->checkScheduleConflicts(
            $availabilityId,
            $start,
            $end
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Check if creating an impediment would overlap with any other impediment.
     */
    public function wouldOverlapWithOtherImpediment(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        $conflictResult = $this->conflictService->checkImpedimentConflicts(
            $availabilityId,
            $start,
            $end,
            $exceptImpedimentId
        );

        return $conflictResult->hasConflicts;
    }

    /**
     * Find impediments for a specific time slot.
     */
    public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection
    {
        return $this->impedimentRepository
            ->findByAvailability($availabilityId)
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->get();
    }

    /**
     * Calculate available slots by removing impediments from a time range.
     */
    public function getAvailableSlotsFromImpediments(Carbon $start, Carbon $end, Collection $impediments): Collection
    {
        $blockedPeriods = $impediments->map(function ($impediment): array {
            return [
                'start' => $impediment->start_datetime,
                'end' => $impediment->end_datetime,
                'type' => 'impediment'
            ];
        })->all();

        $availableSlots = TimeSlotHelper::calculateAvailableSlots(
            $start,
            $end,
            $blockedPeriods
        );

        return collect($availableSlots);
    }

    /**
     * Find impediments that overlap with a time range.
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
                    $start,
                    $end,
                    $impediment->start_datetime,
                    $impediment->end_datetime
                );
            });
    }

    /**
     * Get future impediments for an availability.
     */
    public function getFutureImpediments(int $availabilityId, Carbon $from): Collection
    {
        return $this->impedimentRepository->getFutureImpediments($availabilityId, $from);
    }

    /**
     * Check if a time slot has any conflict (schedule or impediment).
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
     * Get all blocked periods (impediments) for an availability.
     */
    public function getBlockedPeriods(int $availabilityId, Carbon $start, Carbon $end): Collection
    {
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
}
