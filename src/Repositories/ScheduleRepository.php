<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Models\Schedule;

/**
 * Repository for Schedule entity data access operations.
 *
 * Provides methods for querying schedules based on availability,
 * date ranges, and schedulable entities with support for filtering.
 */
class ScheduleRepository extends AbstractRepository implements ScheduleRepositoryInterface
{
    /**
     * Finds schedules by availability with optional time range constraints.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon|null $start Optional start time for range filtering
     * @param Carbon|null $end Optional end time for range filtering
     * @return Builder Query builder for further refinement
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
     * Retrieves future schedules for an availability starting from a given date.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $from Starting date for filtering future schedules
     * @return Collection Future schedules ordered by start time
     */
    public function getFutureSchedules(int $availabilityId, Carbon $from): Collection
    {
        return Schedule::where('availability_id', $availabilityId)
            ->where('end_datetime', '>=', $from)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Retrieves schedules within a date range for a schedulable entity.
     *
     * @param int $schedulableId Schedulable entity identifier
     * @param string $schedulableType Schedulable entity class name
     * @param Carbon $start Start of date range
     * @param Carbon $end End of date range
     * @param array $filters Additional query filters
     * @return Collection Schedules within the specified range
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
     * Builds a base query for schedules associated with a schedulable entity.
     *
     * @param int $schedulableId Schedulable entity identifier
     * @param string $schedulableType Schedulable entity class name
     * @return Builder Query builder for schedules
     */
    private function buildSchedulableQuery(int $schedulableId, string $schedulableType): Builder
    {
        return Schedule::whereHas('availability', function ($query) use ($schedulableId, $schedulableType): void {
            $query->where('schedulable_id', $schedulableId)
                ->where('schedulable_type', $schedulableType);
        });
    }
}
