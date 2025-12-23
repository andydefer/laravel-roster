<?php

declare(strict_types=1);

namespace Roster\Repositories;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Models\Availability;
use Roster\Traits\DateRangeOverlapTrait;

/**
 * Repository for managing Availability entities.
 *
 * Provides methods to create, update, delete, and query Availability records
 * with support for date ranges, time slots, and conflict detection.
 */
class AvailabilityRepository extends AbstractRepository implements AvailabilityRepositoryInterface
{
    use DateRangeOverlapTrait;

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Availability
    {
        return Availability::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): bool
    {
        $availability = $this->find($id);

        return match (true) {
            $availability instanceof Availability => $availability->update($data),
            default => false,
        };
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $availability = $this->find($id);

        return match (true) {
            $availability instanceof Availability => $availability->delete(),
            default => false,
        };
    }

    /**
     * Delete multiple availabilities by their IDs.
     *
     * @param array<int> $ids Array of availability IDs to delete
     * @return bool True if any records were deleted
     */
    public function deleteMultiple(array $ids): bool
    {
        return Availability::whereIn('id', $ids)->delete() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): ?Availability
    {
        return Availability::find($id);
    }

    /**
     * Find availabilities for a specific schedulable entity.
     *
     * @param Model $model The schedulable entity
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the schedulable
     */
    public function findForSchedulable(Model $model, ?string $type = null): Collection
    {
        $builder = $this->buildBaseQuery($model);

        if ($type !== null) {
            $builder->where('type', $type);
        }

        return $builder->orderBy('daily_start')->get();
    }

    /**
     * Get availabilities for a specific date range.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of date range
     * @param Carbon $end End of date range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities within the date range
     */
    public function getForDateRange(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->where(function ($query) use ($end): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $end);
            })
            ->where(function ($query) use ($start): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $start);
            });

        if ($type) {
            $builder->where('type', $type);
        }

        return $builder->get();
    }

    /**
     * Find availability for a time slot with conflict information.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null The matching availability with conflict info or null
     * @throws InvalidArgumentException If the time range is invalid
     */
    public function findForTimeSlotWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {

        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->when($type, function ($query) use ($type): void {
                $query->where('type', $type);
            })
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('daily_start', '<=', $start->format('H:i:s'))
            ->where('daily_end', '>=', $end->format('H:i:s'))
            ->where(function ($query) use ($start): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $start->toDateString());
            })
            ->where(function ($query) use ($end): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $end->toDateString());
            })
            ->withExists([
                'schedules as has_overlapping_schedules' => function ($query) use ($start, $end): void {
                    $query->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                },
                'impediments as has_overlapping_impediments' => function ($query) use ($start, $end): void {
                    $query->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                }
            ])
            ->first();
    }

    /**
     * Find availability for a specific time slot.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null The matching availability or null
     * @throws InvalidArgumentException If the time range is invalid
     */
    public function findForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {

        $builder = $this->buildBaseQuery($model);

        if ($type) {
            $builder->where('type', $type);
        }

        $this->applyTimeSlotFilters($builder, $start, $end);

        /** @var Availability|null $availability */
        $availability = $builder->first();

        return $availability;
    }

    /**
     * Get availabilities for a specific date.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $date The date to check
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the date
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type) {
            $builder->where('type', $type);
        }

        $this->applyDateFilters($builder, $date);

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $builder->orderBy('daily_start')->get();

        return $availabilities;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): Collection
    {
        return Availability::query()
            ->orderBy('daily_start')
            ->get();
    }

    /**
     * Get all availabilities for a schedulable entity.
     *
     * @param Model $model The schedulable entity
     * @param string|null $type Optional availability type filter
     * @param string|null $day Optional day filter
     * @return Collection<int, Availability> Collection of availabilities
     */
    public function getAllForSchedulable(
        Model $model,
        ?string $type = null,
        ?string $day = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->with(['schedules', 'impediments']);

        if ($type) {
            $builder->where('type', $type);
        }

        if ($day) {
            $builder->whereJsonContains('days', strtolower($day));
        }

        return $builder->orderBy('daily_start')->get();
    }

    /**
     * Check if schedulable is available at specific datetime.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $datetime The datetime to check
     * @return bool True if available at the given datetime
     */
    public function isAvailableAt(Model $model, Carbon $datetime): bool
    {
        $dayOfWeek = strtolower($datetime->englishDayOfWeek);
        $time = $datetime->format('H:i:s');

        $builder = $this->buildBaseQuery($model)
            ->whereJsonContains('days', $dayOfWeek)
            ->where('daily_start', '<=', $time)
            ->where('daily_end', '>=', $time);

        $this->applyDateFilters($builder, $datetime);

        return $builder->exists();
    }

    /**
     * Find overlapping availabilities.
     *
     * @param Model $model The schedulable entity
     * @param array<string, mixed> $data The availability data to check
     * @param int|null $exceptId ID to exclude from search
     * @return Collection<int, Availability> Collection of overlapping availabilities
     * @throws InvalidArgumentException If time range is invalid
     */
    public function findOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): Collection {
        $dailyStart = isset($data['daily_start']) ? Carbon::parse($data['daily_start']) : null;
        $dailyEnd = isset($data['daily_end']) ? Carbon::parse($data['daily_end']) : null;
        $days = $data['days'] ?? [];
        $validityStart = isset($data['validity_start']) ? Carbon::parse($data['validity_start']) : null;
        $validityEnd = isset($data['validity_end']) ? Carbon::parse($data['validity_end']) : null;

        $builder = $this->buildBaseQuery($model);

        if ($exceptId !== null) {
            $builder->where('id', '!=', $exceptId);
        }

        $this->applyDayFilters($builder, $days);
        $this->applyTimeOverlapFilters($builder, $dailyStart, $dailyEnd);
        $this->applyDateOverlapFilters($builder, $validityStart, $validityEnd);
        $this->eagerLoadRelations($builder, $validityStart, $validityEnd);

        /** @var Collection<int, Availability> $overlappingAvailabilities */
        $overlappingAvailabilities = $builder->get();

        return $overlappingAvailabilities;
    }

    /**
     * Check if time ranges overlap.
     *
     * @param Carbon $existingStart Existing start time
     * @param Carbon $existingEnd Existing end time
     * @param Carbon $newStart New start time
     * @param Carbon $newEnd New end time
     * @return bool True if time ranges overlap
     */
    public function doTimeRangesOverlap(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool {
        return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
    }

    /**
     * Find related availabilities based on search criteria.
     *
     * @param Model $model The schedulable entity
     * @param array<string, mixed> $data Search criteria
     * @return Collection<int, Availability> Collection of related availabilities
     */
    public function findByType(Model $model, array $data): Collection
    {
        $type = $data['type'] ?? null;

        $builder = $this->buildBaseQuery($model);

        if ($type !== null) {
            $builder->where('type', $type);
        }

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $builder->get();

        return $availabilities;
    }

    /**
     * Build filtered query for availabilities.
     *
     * @param Model $model The schedulable entity
     * @param array<string, mixed> $filters Filters to apply
     * @return Builder Eloquent query builder
     */
    public function buildQueryWithFilters(Model $model, array $filters = []): Builder
    {
        $builder = $this->buildBaseQuery($model);

        match (true) {
            isset($filters['type']) && isset($filters['day']) =>
            $builder->where('type', $filters['type'])
                ->whereJsonContains('days', strtolower($filters['day'])),

            isset($filters['type']) =>
            $builder->where('type', $filters['type']),

            isset($filters['day']) =>
            $builder->whereJsonContains('days', strtolower($filters['day'])),

            default => null,
        };

        return $builder;
    }

    /**
     * Check if an availability applies to a specific date.
     *
     * @param Availability $availability The availability to check
     * @param Carbon $date The date to check
     * @return bool True if the availability applies to the date
     */
    public function isAvailableOnDate(Availability $availability, Carbon $date): bool
    {
        $dayOfWeek = strtolower($date->englishDayOfWeek);

        if (!in_array($dayOfWeek, $availability->days)) {
            return false;
        }

        $isBeforeValidityStart = $availability->validity_start !== null && $date->lt($availability->validity_start);
        $isAfterValidityEnd = $availability->validity_end !== null && $date->gt($availability->validity_end);

        return !$isBeforeValidityStart && !$isAfterValidityEnd;
    }

    /**
     * Load availabilities with pre-loaded schedule and impediment conflicts.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of the date range
     * @param Carbon $end End of the date range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities with conflict info
     */
    public function getAvailabilitiesWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection {
        $availabilities = $this->getForDateRange($model, $start, $end, $type);

        return $availabilities->load(['schedules', 'impediments']);
    }

    /**
     * Filter availabilities for a specific date.
     *
     * @param Collection<int, Availability> $availabilities Collection of availabilities
     * @param Carbon $date Date to filter for
     * @return Collection<int, Availability> Filtered availabilities
     */
    public function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection
    {
        return $availabilities->filter(
            fn(Availability $availability): bool => $this->isAvailableOnDate($availability, $date)
        );
    }

    /**
     * Build base query for availabilities of a schedulable entity.
     *
     * @param Model $model The schedulable entity
     * @return Builder Base query builder
     */
    private function buildBaseQuery(Model $model): Builder
    {
        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model));
    }

    /**
     * Apply time slot filters to query.
     *
     * @param Builder $builder Query builder
     * @param Carbon $start Start time
     * @param Carbon $end End time
     */
    private function applyTimeSlotFilters(Builder $builder, Carbon $start, Carbon $end): void
    {
        $builder->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('daily_start', '<=', $start->format('H:i:s'))
            ->where('daily_end', '>=', $end->format('H:i:s'));

        $this->applyDateRangeFilters($builder, $start, $end);
    }

    /**
     * Apply date filters to query.
     *
     * @param Builder $builder Query builder
     * @param Carbon $date Date to filter for
     */
    private function applyDateFilters(Builder $builder, Carbon $date): void
    {
        $this->applyDateRangeFilters($builder, $date, $date);
    }

    /**
     * Apply date range filters to query.
     *
     * @param Builder $builder Query builder
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     */
    private function applyDateRangeFilters(Builder $builder, Carbon $startDate, Carbon $endDate): void
    {
        $builder->where(function ($query) use ($startDate): void {
            $query->whereNull('validity_start')
                ->orWhere('validity_start', '<=', $startDate->toDateString());
        })->where(function ($query) use ($endDate): void {
            $query->whereNull('validity_end')
                ->orWhere('validity_end', '>=', $endDate->toDateString());
        });
    }

    /**
     * Apply day filters to query.
     *
     * @param Builder $builder Query builder
     * @param array<string> $days Days to filter for
     */
    private function applyDayFilters(Builder $builder, array $days): void
    {
        if ($days === []) {
            return;
        }

        $builder->where(function ($query) use ($days): void {
            foreach ($days as $day) {
                $query->orWhereJsonContains('days', $day);
            }
        });
    }

    /**
     * Apply time overlap filters to query.
     *
     * @param Builder $builder Query builder
     * @param Carbon $startTime Start time
     * @param Carbon $endTime End time
     */
    private function applyTimeOverlapFilters(Builder $builder, Carbon $startTime, Carbon $endTime): void
    {
        $builder->where(function ($query) use ($startTime, $endTime): void {
            $query->where('daily_start', '<', $endTime->format('H:i:s'))
                ->where('daily_end', '>', $startTime->format('H:i:s'));
        });
    }

    /**
     * Apply date overlap filters to query using strategy pattern.
     *
     * @param Builder $builder Query builder
     * @param Carbon|null $startDate Optional start date
     * @param Carbon|null $endDate Optional end date
     */
    private function applyDateOverlapFilters(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
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
     * Eager load relations with date filtering.
     *
     * @param Builder $builder Query builder
     * @param Carbon|null $startDate Optional start date for filtering
     * @param Carbon|null $endDate Optional end date for filtering
     */
    private function eagerLoadRelations(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
    {
        $builder->with([
            'schedules' => function ($relation) use ($startDate, $endDate): void {
                // $relation est une instance de HasMany, nous obtenons son query builder
                $query = $relation->getQuery();
                $this->applyRelationDateFilter($query, $startDate, $endDate);
                $relation->orderBy('start_datetime');
            },
            'impediments' => function ($relation) use ($startDate, $endDate): void {
                // $relation est une instance de HasMany, nous obtenons son query builder
                $query = $relation->getQuery();
                $this->applyRelationDateFilter($query, $startDate, $endDate);
                $relation->orderBy('start_datetime');
            },
        ]);

        $builder->withExists([
            'schedules as has_schedules',
            'impediments as has_impediments',
        ]);
    }

    /**
     * Apply date filter to relation query using strategy pattern.
     *
     * @param Builder $builder Relation query builder
     * @param Carbon|null $startDate Optional start date
     * @param Carbon|null $endDate Optional end date
     */
    private function applyRelationDateFilter(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
    {
        match (true) {
            $startDate instanceof Carbon && $endDate instanceof Carbon =>
            $builder->where(function ($q) use ($startDate, $endDate): void {
                $q->whereBetween('start_datetime', [$startDate, $endDate])
                    ->orWhereBetween('end_datetime', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate): void {
                        $subQuery->where('start_datetime', '<', $startDate)
                            ->where('end_datetime', '>', $endDate);
                    });
            }),

            default => null,
        };
    }
}
