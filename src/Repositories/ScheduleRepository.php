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
     * {@inheritdoc}
     */
    public function create(array $data): Schedule
    {
        return Schedule::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): bool
    {
        $schedule = $this->findById($id);

        return $schedule instanceof Schedule
            ? $schedule->update($data)
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $schedule = $this->findById($id);

        return $schedule instanceof Schedule
            ? $schedule->delete()
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?Schedule
    {
        return Schedule::with([
            'availability.schedules' => function ($query): void {
                $query->where('status', '!=', 'cancelled')
                    ->orderBy('start_datetime');
            },
            'availability.impediments' => function ($query): void {
                $query->orderBy('start_datetime');
            },
        ])->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): Collection
    {
        return Schedule::with(['availability', 'availability.schedules', 'availability.impediments'])
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Find schedules for a time slot.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @return Collection<int, Schedule>
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
     * Get all schedules for a schedulable.
     *
     * @param int $schedulableId The schedulable ID
     * @param string $schedulableType The schedulable class type
     * @param array<string, mixed> $filters Additional filters
     * @return Collection<int, Schedule>
     */
    public function getAllForSchedulable(
        int $schedulableId,
        string $schedulableType,
        array $filters = []
    ): Collection {
        $builder = $this->buildSchedulableQuery($schedulableId, $schedulableType)
            ->with(['availability', 'availability.schedules', 'availability.impediments']);

        $this->applyCommonFilters($builder, $filters);

        return $builder->orderBy('start_datetime')->get();
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

        $this->applyCommonFilters($builder, $filters);

        return $builder->orderBy('start_datetime')->get();
    }

    /**
     * Apply filters to query.
     *
     * @param int $schedulableId The schedulable ID
     * @param string $schedulableType The schedulable class type
     * @param array<string, mixed> $filters Filters to apply
     * @return Builder
     */
    public function buildQueryWithFilters(
        int $schedulableId,
        string $schedulableType,
        array $filters = []
    ): Builder {
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
     *
     * @param Builder $builder The query builder
     * @param array<string, mixed> $filters Filters to apply
     */
    private function applyCommonFilters(Builder $builder, array $filters): void
    {
        if (isset($filters['type'])) {
            $builder->whereHas('availability', function ($query) use ($filters): void {
                $query->where('type', $filters['type']);
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
