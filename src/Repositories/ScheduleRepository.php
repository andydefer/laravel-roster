<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Models\Schedule;

/**
 * Repository for managing Schedule entities.
 */
class ScheduleRepository extends AbstractRepository implements ScheduleRepositoryInterface
{

    /**
     * Check if a time slot has overlapping schedules.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param int|null $excludeId Schedule ID to exclude
     * @return bool True if overlapping schedules exist
     */
    public function hasOverlappingSchedule(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool {
        $query = Schedule::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Log pour déboguer
        $query->toSql();
        $query->getBindings();



        return $query->exists();
    }

    /**
     * Find overlapping schedules with time range.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time range
     * @param Carbon $end End of time range
     * @param int|null $excludeId Schedule ID to exclude
     * @return Collection<int, Schedule>
     */
    public function findOverlappingSchedules(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): Collection {
        $query = Schedule::where('availability_id', $availabilityId)
            ->where(function (Builder $builder) use ($start, $end): void {
                $builder->where('start_datetime', '<', $end)
                    ->where('end_datetime', '>', $start);
            });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }


    /**
     * Get schedules between dates.
     *
     * @param int $schedulableId The schedulable ID
     * @param string $schedulableType The schedulable class type
     * @param Carbon $start Start of date range
     * @param Carbon $end End of date range
     * @param array<string, mixed> $filters Additional filters
     * @return Collection<int, Schedule>
     */
    public function getForDateRange(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection {
        $builder = $this->buildSchedulableQuery($schedulableId, $schedulableType)
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end);

        $this->applyFilters($builder, $filters);

        return $builder->orderBy('start_datetime')->get();
    }


    /**
     * Build base query for schedulable.
     */
    private function buildSchedulableQuery(int $schedulableId, string $schedulableType): Builder
    {
        return Schedule::whereHas('availability', function ($query) use ($schedulableId, $schedulableType): void {
            $query->where('schedulable_id', $schedulableId)
                ->where('schedulable_type', $schedulableType);
        });
    }
}
