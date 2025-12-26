<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\RepositoryInterface;

interface ScheduleRepositoryInterface extends RepositoryInterface
{
    /**
     * Build a query for schedules related to a specific availability.
     *
     * @param int $availabilityId The ID of the availability
     * @param Carbon|null $start Optional start date filter
     * @param Carbon|null $end Optional end date filter
     * @return Builder Query builder for schedules
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder;

    /**
     * Get all future schedules for a specific availability.
     *
     * @param int $availabilityId The ID of the availability
     * @param Carbon $from Starting date for future schedules
     * @return Collection<int, \Roster\Models\Schedule> Collection of future schedules
     */
    public function getFutureSchedules(
        int $availabilityId,
        Carbon $from
    ): Collection;

    /**
     * Get schedules for a schedulable resource within a date range.
     *
     * @param int $schedulableId The ID of the schedulable resource
     * @param string $schedulableType The type/class of the schedulable resource
     * @param Carbon $start Start date of the range
     * @param Carbon $end End date of the range
     * @param array<string, mixed> $filters Additional filters to apply
     * @return Collection<int, \Roster\Models\Schedule> Collection of schedules
     */
    public function getForDateRange(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection;
}
