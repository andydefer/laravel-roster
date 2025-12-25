<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\CrudInterface;
use Roster\Contracts\RepositoryInterface;
use Roster\Models\Schedule;

/**
 * Repository contract for managing schedule records.
 *
 * Provides methods for creating, updating, deleting, and querying schedule data
 * associated with availability slots.
 */
interface ScheduleRepositoryInterface extends RepositoryInterface
{


    /**
     * Check if a time slot has overlapping schedules.
     *
     * @param  int  $availabilityId  Parent availability ID
     * @param  Carbon  $start  Start time of the slot
     * @param  Carbon  $end  End time of the slot
     * @param  int|null  $excludeId  Optional schedule ID to exclude from check
     * @return bool True if overlapping schedules exist
     */
    public function hasOverlappingSchedule(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool;

    /**
     * Find schedules that overlap with the given time range.
     *
     * @param  int  $availabilityId  Parent availability ID
     * @param  Carbon  $start  Start time of the range
     * @param  Carbon  $end  End time of the range
     * @param  int|null  $excludeId  Optional schedule ID to exclude from search
     * @return Collection<int, Schedule> Collection of overlapping schedules
     */
    public function findOverlappingSchedules(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): Collection;


    /**
     * Get schedules between specific dates for a schedulable resource.
     *
     * @param  int  $schedulableId  ID of the schedulable resource
     * @param  string  $schedulableType  Type/class of the schedulable resource
     * @param  Carbon  $start  Start date of the range
     * @param  Carbon  $end  End date of the range
     * @param  array<string, mixed>  $filters  Optional query filters
     * @return Collection<int, Schedule> Collection of schedules in the date range
     */
    public function getForDateRange(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection;

    /**
     * Apply filters to schedule query builder.
     *
     * @param  int  $schedulableId  ID of the schedulable resource
     * @param  string  $schedulableType  Type/class of the schedulable resource
     * @param  array<string, mixed>  $filters  Query filters
     * @return Builder Eloquent query builder with filters applied
     */

    public function buildQueryWithFilters($schedulable, array $filters): Builder;
}
