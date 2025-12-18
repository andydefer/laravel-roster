<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Schedule;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;

class ScheduleRepository implements ScheduleRepositoryInterface
{
    /**
     * Create a new schedule.
     */
    public function create(array $data): Schedule
    {
        return Schedule::create($data);
    }

    /**
     * Update an existing schedule.
     */
    public function update(int $id, array $data): bool
    {
        $schedule = $this->findById($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        return $schedule->update($data);
    }

    /**
     * Delete a schedule.
     */
    public function delete(int $id): bool
    {
        $schedule = $this->findById($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        return $schedule->delete();
    }

    /**
     * Find schedule by ID.
     */
    public function findById(int $id): ?Schedule
    {
        return Schedule::find($id);
    }

    /**
     * Find schedules for a time slot.
     */
    public function findForTimeSlot(
        int $availabilityId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return Schedule::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Check if a time slot has overlapping schedules.
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

        return $query->exists();
    }

    /**
     * Find overlapping schedules with time range.
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
     * Get all schedules for a schedulable.
     *
     * @return Collection<int, Schedule>
     */
    public function getAllForSchedulable(
        int $schedulableId,
        string $schedulableType,
        array $filters = []
    ): Collection {
        $builder = $this->buildSchedulableQuery($schedulableId, $schedulableType);
        $this->applyCommonFilters($builder, $filters);

        return $builder->orderBy('start_datetime')->get();
    }

    /**
     * Get schedules between dates.
     */
    public function getBetweenDates(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection {
        $builder = $this->buildSchedulableQuery($schedulableId, $schedulableType)
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end);

        $this->applyCommonFilters($builder, $filters);

        return $builder->orderBy('start_datetime')->get();
    }

    /**
     * Apply filters to query.
     *
     * @return Builder
     */
    public function applyFilters(
        int $schedulableId,
        string $schedulableType,
        array $filters = []
    ) {
        $builder = $this->buildSchedulableQuery($schedulableId, $schedulableType);
        $this->applyCommonFilters($builder, $filters);

        return $builder;
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

    /**
     * Apply common filters to query.
     * @param array<string, mixed> $filters
     */
    private function applyCommonFilters(Builder $builder, array $filters): void
    {
        if (isset($filters['type'])) {
            $builder->whereHas('availability', function ($q) use ($filters): void {
                $q->where('type', $filters['type']);
            });
        }

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['start_date'])) {
            $builder->where('start_datetime', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $builder->where('end_datetime', '<=', $filters['end_date']);
        }
    }
}
