<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Models\Schedule;

class ScheduleRepository extends AbstractRepository implements ScheduleRepositoryInterface
{
    /**
     * Find schedules by availability with optional time range.
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder {
        $query = Schedule::where('availability_id', $availabilityId);

        if ($start instanceof Carbon && $end instanceof Carbon) {
            $query->whereBetween('start_datetime', [$start, $end]);
        } elseif ($start instanceof Carbon) {
            $query->where('start_datetime', '>=', $start);
        } elseif ($end instanceof Carbon) {
            $query->where('end_datetime', '<=', $end);
        }

        return $query;
    }

    /**
     * Get future schedules for an availability.
     */
    public function getFutureSchedules(int $availabilityId, Carbon $from): Collection
    {
        return Schedule::where('availability_id', $availabilityId)
            ->where('end_datetime', '>=', $from)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Get schedules between dates for a schedulable.
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
