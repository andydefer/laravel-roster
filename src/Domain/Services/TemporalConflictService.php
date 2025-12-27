<?php

declare(strict_types=1);

namespace Roster\Domain\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Domain\Helpers\TimeSlotHelper;
use Roster\Domain\DTOs\ConflictResult;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Models\Availability;

/**
 * Service for detecting and managing temporal conflicts in schedules, availabilities, and impediments.
 */
class TemporalConflictService
{
    /**
     * @param AvailabilityRepositoryInterface $availabilityRepository Repository for availability data access
     * @param ScheduleRepositoryInterface $scheduleRepository Repository for schedule data access
     * @param ImpedimentRepositoryInterface $impedimentRepository Repository for impediment data access
     */
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository,
        private ScheduleRepositoryInterface $scheduleRepository,
        private ImpedimentRepositoryInterface $impedimentRepository
    ) {}

    /**
     * Check for overlapping availability conflicts.
     *
     * @param Model $model The schedulable model (e.g., User, Resource)
     * @param array<string, mixed> $availabilityData New availability data to validate
     * @param int|null $excludeId ID of existing availability to exclude (for updates)
     * @return ConflictResult Result containing conflict status and details
     */
    public function checkAvailabilityConflicts(
        Model $model,
        array $availabilityData,
        ?int $excludeId = null
    ): ConflictResult {
        $availabilityPeriod = $this->extractAvailabilityPeriod($availabilityData);

        if (!$this->isValidAvailabilityPeriod($availabilityPeriod)) {
            return ConflictResult::noConflict();
        }

        $conflictingAvailabilities = $this->findConflictingAvailabilities(
            model: $model,
            period: $availabilityPeriod,
            excludeId: $excludeId
        );

        if ($conflictingAvailabilities->isEmpty()) {
            return ConflictResult::noConflict();
        }

        $firstConflict = $conflictingAvailabilities->first();
        return new ConflictResult(
            hasConflicts: true,
            conflictingSchedules: [],
            conflictingImpediments: [],
            message: $this->generateAvailabilityConflictMessage($firstConflict)
        );
    }

    /**
     * Check if two date ranges overlap.
     *
     * @param Carbon|null $startA Start of first period (null for open start)
     * @param Carbon|null $endA End of first period (null for open end)
     * @param Carbon|null $startB Start of second period (null for open start)
     * @param Carbon|null $endB End of second period (null for open end)
     * @return bool True if periods overlap
     */
    public function isDateRangeOverlapping(
        ?Carbon $startA,
        ?Carbon $endA,
        ?Carbon $startB,
        ?Carbon $endB
    ): bool {
        $effectiveStartA = $startA ?? Carbon::create(1, 1, 1);
        $effectiveEndA = $endA ?? Carbon::create(9999, 12, 31);
        $effectiveStartB = $startB ?? Carbon::create(1, 1, 1);
        $effectiveEndB = $endB ?? Carbon::create(9999, 12, 31);

        return $effectiveStartB->lte($effectiveEndA) && $effectiveEndB->gte($effectiveStartA);
    }

    /**
     * Check all possible conflicts for a time slot.
     *
     * @param int $availabilityId The availability to check within
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @param int|null $excludeScheduleId Schedule ID to exclude from conflict check
     * @param int|null $excludeImpedimentId Impediment ID to exclude from conflict check
     * @return ConflictResult Result containing all detected conflicts
     */
    public function checkAllConflicts(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeScheduleId = null,
        ?int $excludeImpedimentId = null
    ): ConflictResult {
        $scheduleConflict = $this->checkScheduleConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeScheduleId: $excludeScheduleId
        );

        if ($scheduleConflict->hasConflicts) {
            return $scheduleConflict;
        }

        $impedimentConflict = $this->checkImpedimentConflicts(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeImpedimentId: $excludeImpedimentId
        );

        if ($impedimentConflict->hasConflicts) {
            return $impedimentConflict;
        }

        return ConflictResult::noConflict();
    }

    /**
     * Check conflicts with existing schedules.
     *
     * @param int $availabilityId The availability to check within
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @param int|null $excludeScheduleId Schedule ID to exclude from conflict check
     * @return ConflictResult Result containing schedule conflicts
     */
    public function checkScheduleConflicts(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeScheduleId = null
    ): ConflictResult {
        $conflictingSchedules = $this->findOverlappingSchedules(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeId: $excludeScheduleId
        );

        if ($conflictingSchedules->isEmpty()) {
            return ConflictResult::noConflict();
        }

        return ConflictResult::scheduleConflict(
            $conflictingSchedules->all(),
            $this->generateScheduleConflictMessage($conflictingSchedules->first())
        );
    }

    /**
     * Check conflicts with existing impediments.
     *
     * @param int $availabilityId The availability to check within
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @param int|null $excludeImpedimentId Impediment ID to exclude from conflict check
     * @return ConflictResult Result containing impediment conflicts
     */
    public function checkImpedimentConflicts(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeImpedimentId = null
    ): ConflictResult {
        $conflictingImpediments = $this->findOverlappingImpediments(
            availabilityId: $availabilityId,
            start: $start,
            end: $end,
            excludeId: $excludeImpedimentId
        );

        if ($conflictingImpediments->isEmpty()) {
            return ConflictResult::noConflict();
        }

        return ConflictResult::impedimentConflict(
            $conflictingImpediments->all(),
            $this->generateImpedimentConflictMessage($conflictingImpediments->first())
        );
    }

    /**
     * Find available time slots within a given range.
     *
     * @param int $availabilityId The availability to search within
     * @param Carbon $rangeStart Start of the search range
     * @param Carbon $rangeEnd End of the search range
     * @return array<array{start: Carbon, end: Carbon}> Array of available time slots
     */
    public function findAvailableSlots(
        int $availabilityId,
        Carbon $rangeStart,
        Carbon $rangeEnd
    ): array {
        $blockedPeriods = $this->getBlockedPeriodsInRange(
            availabilityId: $availabilityId,
            rangeStart: $rangeStart,
            rangeEnd: $rangeEnd
        );

        return TimeSlotHelper::calculateAvailableSlots($rangeStart, $rangeEnd, $blockedPeriods);
    }

    /**
     * Calculate available slots by removing impediments from a time range.
     *
     * @param Carbon $start Start of the time range
     * @param Carbon $end End of the time range
     * @param Collection<int, object> $impediments Collection of impediments to consider
     * @return Collection<int, array{start: Carbon, end: Carbon}> Collection of available slots
     */
    public function calculateAvailableSlotsExcludingImpediments(Carbon $start, Carbon $end, Collection $impediments): Collection
    {
        $blockedPeriods = $impediments->map(function ($impediment): array {
            return [
                'start' => $impediment->start_datetime,
                'end' => $impediment->end_datetime,
                'type' => 'impediment'
            ];
        })->all();

        $availableSlots = TimeSlotHelper::calculateAvailableSlots($start, $end, $blockedPeriods);

        return collect($availableSlots);
    }

    /**
     * Check if a time slot has overlapping schedules (compatibility method).
     *
     * @param int $availabilityId The availability to check within
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @param int|null $excludeId Schedule ID to exclude from conflict check
     * @return bool True if overlapping schedules exist
     */
    public function hasOverlappingSchedule(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool {
        return $this->checkScheduleConflicts($availabilityId, $start, $end, $excludeId)->hasConflicts;
    }

    /**
     * Find overlapping schedules (compatibility method).
     *
     * @param int $availabilityId The availability to check within
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @param int|null $excludeId Schedule ID to exclude from conflict check
     * @return Collection<int, object> Collection of overlapping schedules
     */
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


    /**
     * Apply date overlap filter to query builder.
     *
     * @param Builder $builder Query builder to apply filter to
     * @param Carbon|null $startDate Start date for overlap check (null for open start)
     * @param Carbon|null $endDate End date for overlap check (null for open end)
     */
    private function applyDateOverlapFilter(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
    {
        $builder->where(function ($query) use ($startDate, $endDate): void {
            match (true) {
                $startDate instanceof Carbon && $endDate instanceof Carbon =>
                $query->where('validity_start', '<=', $endDate)
                    ->where('validity_end', '>=', $startDate),

                $startDate instanceof Carbon =>
                $query->where(function ($subQuery) use ($startDate): void {
                    $subQuery->where('validity_end', '>=', $startDate)
                        ->orWhereNull('validity_end');
                }),

                $endDate instanceof Carbon =>
                $query->where(function ($subQuery) use ($endDate): void {
                    $subQuery->where('validity_start', '<=', $endDate)
                        ->orWhereNull('validity_start');
                }),

                default =>
                $query->where(function ($subQuery): void {
                    $subQuery->whereNull('validity_start')
                        ->orWhereNull('validity_end');
                }),
            };
        });
    }

    /**
     * Generate user-friendly availability conflict message.
     *
     * @param Availability $availability The conflicting availability
     * @return string Formatted conflict message
     */
    private function generateAvailabilityConflictMessage(Availability $availability): string
    {
        return sprintf(
            'Availability overlaps with an existing availability {#%s} -> type : %s %s - %s for %s - %s',
            $availability->id,
            $availability->type,
            $availability->validity_start?->format('Y-m-d') ?? '∞',
            $availability->validity_end?->format('Y-m-d') ?? '∞',
            $availability->daily_start?->format('H:i') ?? 'N/A',
            $availability->daily_end?->format('H:i') ?? 'N/A'
        );
    }

    /**
     * Generate user-friendly schedule conflict message.
     *
     * @param object $schedule The conflicting schedule
     * @return string Formatted conflict message
     */
    private function generateScheduleConflictMessage(object $schedule): string
    {
        return sprintf(
            'Schedule overlaps with existing schedule from %s to %s',
            $schedule->start_datetime->format('Y-m-d H:i'),
            $schedule->end_datetime->format('Y-m-d H:i')
        );
    }

    /**
     * Generate user-friendly impediment conflict message.
     *
     * @param object $impediment The conflicting impediment
     * @return string Formatted conflict message
     */
    private function generateImpedimentConflictMessage(object $impediment): string
    {
        return sprintf(
            'Schedule overlaps with existing impediment from %s to %s',
            $impediment->start_datetime->format('Y-m-d H:i'),
            $impediment->end_datetime->format('Y-m-d H:i')
        );
    }

    /**
     * Extract availability period from raw data.
     *
     * @param array<string, mixed> $availabilityData
     * @return array{
     *     dailyStart: Carbon|null,
     *     dailyEnd: Carbon|null,
     *     days: array,
     *     validityStart: Carbon|null,
     *     validityEnd: Carbon|null,
     *     type: string|null
     * }
     */
    private function extractAvailabilityPeriod(array $availabilityData): array
    {
        return [
            'dailyStart' => isset($availabilityData['daily_start'])
                ? Carbon::parse($availabilityData['daily_start'])
                : null,
            'dailyEnd' => isset($availabilityData['daily_end'])
                ? Carbon::parse($availabilityData['daily_end'])
                : null,
            'days' => $availabilityData['days'] ?? [],
            'validityStart' => isset($availabilityData['validity_start'])
                ? Carbon::parse($availabilityData['validity_start'])
                : null,
            'validityEnd' => isset($availabilityData['validity_end'])
                ? Carbon::parse($availabilityData['validity_end'])
                : null,
            'type' => $availabilityData['type'] ?? null,
        ];
    }

    /**
     * Validate if an availability period has required data.
     *
     * @param array{
     *     dailyStart: Carbon|null,
     *     dailyEnd: Carbon|null,
     *     days: array,
     *     validityStart: Carbon|null,
     *     validityEnd: Carbon|null,
     *     type: string|null
     * } $period
     * @return bool
     */
    private function isValidAvailabilityPeriod(array $period): bool
    {
        return $period['dailyStart'] instanceof Carbon
            && $period['dailyEnd'] instanceof Carbon
            && !empty($period['days']);
    }

    /**
     * Find availabilities that conflict with the given period.
     *
     * @param Model $model The schedulable model
     * @param array{
     *     dailyStart: Carbon|null,
     *     dailyEnd: Carbon|null,
     *     days: array,
     *     validityStart: Carbon|null,
     *     validityEnd: Carbon|null,
     *     type: string|null
     * } $period Availability period to check
     * @param int|null $excludeId ID to exclude from search
     * @return Collection<int, Availability>
     */
    private function findConflictingAvailabilities(
        Model $model,
        array $period,
        ?int $excludeId
    ): Collection {
        $builder = $this->availabilityRepository->findForSchedulable($model, $period['type']);

        if ($excludeId !== null) {
            $builder->where('id', '!=', $excludeId);
        }

        if (!empty($period['days'])) {
            $builder->where(function ($query) use ($period): void {
                foreach ($period['days'] as $day) {
                    $query->orWhereJsonContains('days', $day);
                }
            });
        }

        $builder->where(function ($query) use ($period): void {
            $query->where('daily_start', '<', $period['dailyEnd']->format('H:i:s'))
                ->where('daily_end', '>', $period['dailyStart']->format('H:i:s'));
        });

        $this->applyDateOverlapFilter(
            builder: $builder,
            startDate: $period['validityStart'],
            endDate: $period['validityEnd']
        );

        return $builder->get();
    }

    /**
     * Find overlapping impediments within a time range.
     *
     * @param int $availabilityId The availability to check within
     * @param Carbon $start Start of the time slot
     * @param Carbon $end End of the time slot
     * @param int|null $excludeId Impediment ID to exclude
     * @return Collection<int, object>
     */
    private function findOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId
    ): Collection {
        $builder = $this->impedimentRepository->findByAvailability($availabilityId);

        return $builder->get()->filter(function ($impediment) use ($start, $end, $excludeId): bool {
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
     * Get all blocked periods (schedules and impediments) within a range.
     *
     * @param int $availabilityId The availability to search within
     * @param Carbon $rangeStart Start of the search range
     * @param Carbon $rangeEnd End of the search range
     * @return array<array{start: Carbon, end: Carbon, type: string}>
     */
    private function getBlockedPeriodsInRange(
        int $availabilityId,
        Carbon $rangeStart,
        Carbon $rangeEnd
    ): array {
        $schedules = $this->scheduleRepository
            ->findByAvailability($availabilityId)
            ->whereBetween('start_datetime', [$rangeStart, $rangeEnd])
            ->get()
            ->map(fn($schedule): array => [
                'start' => $schedule->start_datetime,
                'end' => $schedule->end_datetime,
                'type' => 'schedule'
            ])
            ->all();

        $impediments = $this->impedimentRepository
            ->findByAvailability($availabilityId)
            ->whereBetween('start_datetime', [$rangeStart, $rangeEnd])
            ->get()
            ->map(fn($impediment): array => [
                'start' => $impediment->start_datetime,
                'end' => $impediment->end_datetime,
                'type' => 'impediment'
            ])
            ->all();

        return array_merge($schedules, $impediments);
    }
}
