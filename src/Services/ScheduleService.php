<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\Domain\Helpers\TimeSlotHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\DTOs\ScheduleData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\Core\AbstractService;
use Roster\Validation\Exceptions\ValidationFailedException;

class ScheduleService extends AbstractService
{
    protected function createDTOFromArray(array $data, OperationType $operationType): ScheduleData
    {
        return ScheduleData::fromArray($data);
    }

    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::SCHEDULE;
    }

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
            excludeScheduleId: $excludeId
        );

        if ($conflictResult->hasConflicts) {
            if ($conflictResult->hasScheduleConflicts()) {
                throw ValidationFailedException::fromViolations(
                    [
                        'overlap' => sprintf(
                            'Schedule would overlap with existing schedule from %s to %s',
                            $conflictResult->conflictingSchedules[0]->start_datetime->format('Y-m-d H:i'),
                            $conflictResult->conflictingSchedules[0]->end_datetime->format('Y-m-d H:i')
                        )
                    ],
                    OperationType::CREATE,
                    EntityType::SCHEDULE
                );
            }

            if ($conflictResult->hasImpedimentConflicts()) {
                throw ValidationFailedException::fromViolations(
                    [
                        'overlap' => sprintf(
                            'Schedule would overlap with existing impediment from %s to %s',
                            $conflictResult->conflictingImpediments[0]->start_datetime->format('Y-m-d H:i'),
                            $conflictResult->conflictingImpediments[0]->end_datetime->format('Y-m-d H:i')
                        )
                    ],
                    OperationType::CREATE,
                    EntityType::SCHEDULE
                );
            }
        }
    }

    // Additional Schedule-specific methods

    /**
     * Find the next available time slot from now.
     */
    public function findNextSlot(
        int $durationMinutes,
        ?string $type = null,
        bool $returnStartOnly = false,
        ?Carbon $startFrom = null,
        ?Carbon $endBefore = null
    ): array|Carbon|null {
        $startFrom = $startFrom ?? Carbon::now();
        $endBefore = $endBefore ?? $startFrom->copy()->addDays(config('roster.durations.max_search_period_days', 30));

        $searchDate = $startFrom->copy()->startOfDay();

        while ($searchDate->lt($endBefore)) {
            $result = $this->findAvailableSlotInDay(
                $searchDate,
                $durationMinutes,
                $type,
                $searchDate->isSameDay($startFrom) ? $startFrom : null
            );

            if ($result !== null) {
                return $returnStartOnly ? $result['start'] : $result;
            }

            $searchDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Check if time slot is available.
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->getAvailabilityRepository()->getAvailabilityForTimeSlot(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availability->id,
            start: $start,
            end: $end
        );

        return !$conflictResult->hasConflicts;
    }

    /**
     * Get all available time slots in a date range.
     */
    public function findAvailableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): Collection {
        $availableSlots = collect();
        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate)) {
            $slot = $this->findAvailableSlotInDay($currentDate, $durationMinutes, $type);
            if ($slot !== null) {
                $availableSlots->push($slot);
            }
            $currentDate->addDay();
        }

        return $availableSlots;
    }

    /**
     * Check if a time period is completely available.
     */
    public function isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->getAvailabilityRepository()->getAvailabilityForTimeSlot(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        if (!$availability->isAvailableForSchedule($start, $end)) {
            return false;
        }

        $conflictResult = $this->conflictService->checkAllConflicts(
            availabilityId: $availability->id,
            start: $start,
            end: $end
        );

        return !$conflictResult->hasConflicts;
    }

    // Helper methods

    private function findAvailableSlotInDay(
        Carbon $day,
        int $durationMinutes,
        ?string $type = null,
        ?Carbon $searchStart = null
    ): ?array {
        /** @var \Illuminate\Support\Collection<\Roster\Models\Availability> $availabilities */
        $availabilities = $this->getAvailabilityRepository()->getForDate(
            $this->schedulable,
            $day,
            $type
        );

        if ($availabilities->isEmpty()) {
            return null;
        }

        foreach ($availabilities as $availability) {
            $slot = $this->findSlotInAvailability($availability, $day, $durationMinutes, $searchStart);
            if ($slot !== null) {
                return $slot;
            }
        }

        return null;
    }

    private function findSlotInAvailability(
        Availability $availability,
        Carbon $day,
        int $durationMinutes,
        ?Carbon $searchStart = null
    ): ?array {
        if (!$availability->daily_start || !$availability->daily_end) {
            return null;
        }

        if (!$availability->isActiveOnDate($day)) {
            return null;
        }

        $availabilityStart = $day->copy()->setTimeFromTimeString($availability->daily_start->format('H:i:s'));
        $availabilityEnd = $day->copy()->setTimeFromTimeString($availability->daily_end->format('H:i:s'));

        $slotStart = $availabilityStart->copy();

        if ($searchStart instanceof Carbon && $searchStart->isSameDay($day)) {
            if ($searchStart->lt($availabilityStart)) {
                $slotStart = $availabilityStart->copy();
            } elseif ($searchStart->lt($availabilityEnd)) {
                $slotStart = $searchStart->copy();
            } else {
                return null;
            }
        }

        $slotInterval = config('roster.durations.default_slot_interval_minutes', 15);
        if ($slotStart->minute > 0 || $slotStart->second > 0) {
            $minutes = $slotStart->minute;
            $roundedMinutes = ceil($minutes / $slotInterval) * $slotInterval;
            $slotStart->setMinute((int)$roundedMinutes)->setSecond(0);
        }

        if ($slotStart->copy()->addMinutes($durationMinutes)->gt($availabilityEnd)) {
            return null;
        }

        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($availabilityEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

            if ($this->isTimeSlotAvailable($slotStart, $slotEnd, $availability->type)) {
                return [
                    'start' => $slotStart->copy(),
                    'end' => $slotEnd->copy(),
                    'availability' => $availability,
                    'duration_minutes' => $durationMinutes,
                ];
            }

            $slotStart->addMinutes($slotInterval);
        }

        return null;
    }

    // Compatibility methods

    public function hasOverlappingSchedule(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool {
        $conflictResult = $this->conflictService->checkScheduleConflicts(
            $availabilityId,
            $start,
            $end,
            $excludeId
        );

        return $conflictResult->hasConflicts;
    }

    public function findOverlappingSchedules(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): Collection {
        $builder = $this->scheduleRepository->findByAvailability($availabilityId);

        return $builder->get()->filter(function ($schedule) use ($start, $end, $excludeId): bool {
            if ($excludeId && $schedule->id === $excludeId) {
                return false;
            }

            return TimeSlotHelper::overlaps(
                $start,
                $end,
                $schedule->start_datetime,
                $schedule->end_datetime
            );
        });
    }

    public function hasOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool {
        $conflictResult = $this->conflictService->checkImpedimentConflicts(
            $availabilityId,
            $start,
            $end,
            $excludeId
        );

        return $conflictResult->hasConflicts;
    }

    // Repository getters

    protected function getAvailabilityRepository(): AvailabilityRepositoryInterface
    {
        return $this->availabilityRepository;
    }
}
