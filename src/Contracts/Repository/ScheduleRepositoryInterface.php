<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Schedule;

interface ScheduleRepositoryInterface
{
    /**
     * Create a new schedule.
     */
    public function create(array $data): Schedule;

    /**
     * Update an existing schedule.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a schedule.
     */
    public function delete(int $id): bool;

    /**
     * Find schedule by ID.
     */
    public function findById(int $id): ?Schedule;

    /**
     * Find schedules for a time slot.
     */
    public function findForTimeSlot(
        int $availabilityId,
        Carbon $start,
        Carbon $end
    ): Collection;

    /**
     * Check if a time slot has overlapping schedules.
     */
    public function hasOverlappingSchedule(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool;

    /**
     * Find overlapping schedules with time range.
     */
    public function findOverlappingSchedules(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): Collection;

    /**
     * Get all schedules for a schedulable.
     *
     * @return Collection<int, Schedule>
     */
    public function getAllForSchedulable(
        int $schedulableId,
        string $schedulableType,
        array $filters = []
    ): Collection;

    /**
     * Get schedules between dates.
     */
    public function getBetweenDates(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection;

    /**
     * Apply filters to query.
     *
     * @return Builder
     */
    public function applyFilters(
        int $schedulableId,
        string $schedulableType,
        array $filters = []
    );
}
