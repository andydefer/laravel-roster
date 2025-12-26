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

class TemporalConflictService
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository,
        private ScheduleRepositoryInterface $scheduleRepository,
        private ImpedimentRepositoryInterface $impedimentRepository
    ) {}

    /* -----------------------------------------------------------------
     | Chevauchments de disponibilités (Availability vs Availability)
     | -----------------------------------------------------------------
     */

    /**
     * Check availability conflicts (overlapping availabilities).
     */
    public function checkAvailabilityConflicts(
        Model $schedulable,
        array $availabilityData,
        ?int $excludeId = null
    ): ConflictResult {
        // Récupérer les champs de la disponibilité
        $dailyStart = isset($availabilityData['daily_start']) ? Carbon::parse($availabilityData['daily_start']) : null;
        $dailyEnd = isset($availabilityData['daily_end']) ? Carbon::parse($availabilityData['daily_end']) : null;
        $days = $availabilityData['days'] ?? [];
        $validityStart = isset($availabilityData['validity_start']) ? Carbon::parse($availabilityData['validity_start']) : null;
        $validityEnd = isset($availabilityData['validity_end']) ? Carbon::parse($availabilityData['validity_end']) : null;
        $type = $availabilityData['type'] ?? null;

        // Vérifier les conditions minimales
        if (!$dailyStart || !$dailyEnd || empty($days)) {
            return ConflictResult::noConflict();
        }

        // Récupérer les disponibilités potentielles en conflit
        $builder = $this->availabilityRepository->findForSchedulable($schedulable, $type);

        if ($excludeId !== null) {
            $builder->where('id', '!=', $excludeId);
        }

        // Filtrer par jours
        if (!empty($days)) {
            $builder->where(function ($query) use ($days): void {
                foreach ($days as $day) {
                    $query->orWhereJsonContains('days', $day);
                }
            });
        }

        // Filtrer par chevauchement temporel quotidien
        $builder->where(function ($query) use ($dailyStart, $dailyEnd): void {
            $query->where('daily_start', '<', $dailyEnd->format('H:i:s'))
                ->where('daily_end', '>', $dailyStart->format('H:i:s'));
        });

        // Filtrer par chevauchement de période de validité
        $this->applyDateOverlapFilter($builder, $validityStart, $validityEnd);

        $overlappingAvailabilities = $builder->get();

        if ($overlappingAvailabilities->isEmpty()) {
            return ConflictResult::noConflict();
        }

        $firstOverlap = $overlappingAvailabilities->first();
        return new ConflictResult(
            hasConflicts: true,
            conflictingSchedules: [],
            conflictingImpediments: [],
            message: $this->generateAvailabilityConflictMessage($firstOverlap)
        );
    }

    /**
     * Check if two date ranges overlap.
     */
    public function dateRangesOverlap(
        ?Carbon $startA,
        ?Carbon $endA,
        ?Carbon $startB,
        ?Carbon $endB
    ): bool {
        // Convert nulls to open-ended ranges
        $effectiveStartA = $startA ?? Carbon::create(1, 1, 1);
        $effectiveEndA = $endA ?? Carbon::create(9999, 12, 31);
        $effectiveStartB = $startB ?? Carbon::create(1, 1, 1);
        $effectiveEndB = $endB ?? Carbon::create(9999, 12, 31);

        return $effectiveStartB->lte($effectiveEndA) && $effectiveEndB->gte($effectiveStartA);
    }

    /* -----------------------------------------------------------------
     | Chevauchments de créneaux (Schedule/Impediment vs Schedule/Impediment)
     | -----------------------------------------------------------------
     */

    /**
     * Check all possible conflicts for a time slot.
     */
    public function checkAllConflicts(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeScheduleId = null,
        ?int $excludeImpedimentId = null
    ): ConflictResult {
        // Check schedule conflicts
        $conflictResult = $this->checkScheduleConflicts(
            $availabilityId,
            $start,
            $end,
            $excludeScheduleId
        );

        if ($conflictResult->hasConflicts) {
            return $conflictResult;
        }

        // Check impediment conflicts
        $impedimentConflicts = $this->checkImpedimentConflicts(
            $availabilityId,
            $start,
            $end,
            $excludeImpedimentId
        );

        if ($impedimentConflicts->hasConflicts) {
            return $impedimentConflicts;
        }

        return ConflictResult::noConflict();
    }

    /**
     * Check conflicts with existing schedules.
     */
    public function checkScheduleConflicts(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeScheduleId = null
    ): ConflictResult {
        $builder = $this->scheduleRepository->findByAvailability($availabilityId);

        $conflictingSchedules = $builder->get()->filter(function ($schedule) use ($start, $end, $excludeScheduleId): bool {
            if ($excludeScheduleId && $schedule->id === $excludeScheduleId) {
                return false;
            }

            return TimeSlotHelper::overlaps(
                $start,
                $end,
                $schedule->start_datetime,
                $schedule->end_datetime
            );
        });

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
     */
    public function checkImpedimentConflicts(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeImpedimentId = null
    ): ConflictResult {
        $builder = $this->impedimentRepository->findByAvailability($availabilityId);

        $conflictingImpediments = $builder->get()->filter(function ($impediment) use ($start, $end, $excludeImpedimentId): bool {
            if ($excludeImpedimentId && $impediment->id === $excludeImpedimentId) {
                return false;
            }

            return TimeSlotHelper::overlaps(
                $start,
                $end,
                $impediment->start_datetime,
                $impediment->end_datetime
            );
        });

        if ($conflictingImpediments->isEmpty()) {
            return ConflictResult::noConflict();
        }

        return ConflictResult::impedimentConflict(
            $conflictingImpediments->all(),
            $this->generateImpedimentConflictMessage($conflictingImpediments->first())
        );
    }

    /* -----------------------------------------------------------------
     | Utilitaires pour slots disponibles
     | -----------------------------------------------------------------
     */

    /**
     * Find available slots within a time range.
     */
    public function findAvailableSlots(
        int $availabilityId,
        Carbon $rangeStart,
        Carbon $rangeEnd
    ): array {
        // Get all scheduled items in range
        $schedules = $this->scheduleRepository
            ->findByAvailability($availabilityId)
            ->whereBetween('start_datetime', [$rangeStart, $rangeEnd])
            ->get()
            ->map(fn($s): array => [
                'start' => $s->start_datetime,
                'end' => $s->end_datetime,
                'type' => 'schedule'
            ])
            ->all();

        // Get all impediments in range
        $impediments = $this->impedimentRepository
            ->findByAvailability($availabilityId)
            ->whereBetween('start_datetime', [$rangeStart, $rangeEnd])
            ->get()
            ->map(fn($i): array => [
                'start' => $i->start_datetime,
                'end' => $i->end_datetime,
                'type' => 'impediment'
            ])
            ->all();

        // Combine all blocked periods
        $blockedPeriods = array_merge($schedules, $impediments);

        return TimeSlotHelper::calculateAvailableSlots($rangeStart, $rangeEnd, $blockedPeriods);
    }

    /**
     * Calculate available slots by removing impediments.
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

        $availableSlots = TimeSlotHelper::calculateAvailableSlots($start, $end, $blockedPeriods);

        return collect($availableSlots);
    }

    /* -----------------------------------------------------------------
     | Méthodes helper pour compatibilité
     | -----------------------------------------------------------------
     */

    /**
     * Check if a time slot has overlapping schedules (compatibility method).
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
     * Check if a time slot has overlapping impediments (compatibility method).
     */
    public function hasOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool {
        return $this->checkImpedimentConflicts($availabilityId, $start, $end, $excludeId)->hasConflicts;
    }

    /* -----------------------------------------------------------------
     | Méthodes privées
     | -----------------------------------------------------------------
     */

    /**
     * Apply date overlap filter to query builder.
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
     */
    private function generateImpedimentConflictMessage(object $impediment): string
    {
        return sprintf(
            'Schedule overlaps with existing impediment from %s to %s',
            $impediment->start_datetime->format('Y-m-d H:i'),
            $impediment->end_datetime->format('Y-m-d H:i')
        );
    }
}
