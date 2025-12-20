<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Provides a fluent interface for filtering Eloquent queries with common date and field filters.
 *
 * @method self whereStartDate(Carbon $date)
 * @method self whereEndDate(Carbon $date)
 * @method self whereStatus(string $status)
 * @method self whereReason(string $reason)
 * @method self whereAvailabilityId(int $availabilityId)
 */
trait FilterableTrait
{
    /**
     * Active filters for queries.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Applies date range filters to a query builder.
     *
     * @param Builder $builder Query builder instance
     * @param string $startField Field name for start date
     * @param string $endField Field name for end date
     *
     * @return Builder The modified query builder
     */
    protected function applyDateFilters(
        Builder $builder,
        string $startField = 'start_datetime',
        string $endField = 'end_datetime'
    ): Builder {
        if (isset($this->filters['start_date'])) {
            $builder->where($startField, '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $builder->where($endField, '<=', $this->filters['end_date']);
        }

        return $builder;
    }

    /**
     * Applies type filter to a query builder.
     *
     * Supports filtering directly on the model or through a relation.
     *
     * @param Builder $builder Query builder instance
     * @param string $relation Optional relation name for nested filtering
     *
     * @return Builder The modified query builder
     */
    protected function applyTypeFilter(Builder $builder, string $relation = ''): Builder
    {
        if (! isset($this->filters['type'])) {
            return $builder;
        }

        if ($relation !== '' && $relation !== '0') {
            $builder->whereHas($relation, function ($query): void {
                $query->where('type', $this->filters['type']);
            });
        } else {
            $builder->where('type', $this->filters['type']);
        }

        return $builder;
    }

    /**
     * Filters query by a specific day in a JSON days array.
     *
     * @param Builder $builder Query builder instance
     *
     * @return Builder The modified query builder
     */
    protected function applyDayFilter(Builder $builder): Builder
    {
        if (isset($this->filters['day'])) {
            $builder->whereJsonContains('days', $this->filters['day']);
        }

        return $builder;
    }

    /**
     * Applies status filter to a query builder.
     *
     * @param Builder $builder Query builder instance
     *
     * @return Builder The modified query builder
     */
    protected function applyStatusFilter(Builder $builder): Builder
    {
        if (isset($this->filters['status'])) {
            $builder->where('status', $this->filters['status']);
        }

        return $builder;
    }

    /**
     * Applies reason filter with partial matching to a query builder.
     *
     * @param Builder $builder Query builder instance
     *
     * @return Builder The modified query builder
     */
    protected function applyReasonFilter(Builder $builder): Builder
    {
        if (isset($this->filters['reason'])) {
            $builder->where('reason', 'like', '%' . $this->filters['reason'] . '%');
        }

        return $builder;
    }

    /**
     * Filters query by availability ID.
     *
     * @param Builder $builder Query builder instance
     *
     * @return Builder The modified query builder
     */
    protected function applyAvailabilityIdFilter(Builder $builder): Builder
    {
        if (isset($this->filters['availability_id'])) {
            $builder->where('availability_id', $this->filters['availability_id']);
        }

        return $builder;
    }

    /**
     * Filters query by a specific schedulable model.
     *
     * @param Builder $builder Query builder instance
     * @param Model|null $model The model to filter by
     *
     * @return Builder The modified query builder
     */
    protected function applySchedulableFilter(Builder $builder, ?Model $model = null): Builder
    {
        if ($model instanceof Model) {
            $builder->where('schedulable_id', $model->id)
                ->where('schedulable_type', get_class($model));
        }

        return $builder;
    }

    /**
     * Adds a start date filter.
     *
     * @param Carbon $date Start date threshold
     *
     * @return self
     */
    public function whereStartDate(Carbon $date): self
    {
        $this->filters['start_date'] = $date;

        return $this;
    }

    /**
     * Adds an end date filter.
     *
     * @param Carbon $date End date threshold
     *
     * @return self
     */
    public function whereEndDate(Carbon $date): self
    {
        $this->filters['end_date'] = $date;

        return $this;
    }

    /**
     * Adds a status filter.
     *
     * @param string $status Status value to filter by
     *
     * @return self
     */
    public function whereStatus(string $status): self
    {
        $this->filters['status'] = $status;

        return $this;
    }

    /**
     * Adds a reason filter for impediments.
     *
     * @param string $reason Reason text to filter by (partial match)
     *
     * @return self
     */
    public function whereReason(string $reason): self
    {
        $this->filters['reason'] = $reason;

        return $this;
    }

    /**
     * Adds an availability ID filter.
     *
     * @param int $availabilityId Availability ID to filter by
     *
     * @return self
     */
    public function whereAvailabilityId(int $availabilityId): self
    {
        $this->filters['availability_id'] = $availabilityId;

        return $this;
    }

    /**
     * Clears all active filters.
     *
     * @return self
     */
    public function clearFilters(): self
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Retrieves all currently active filters.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Checks if a specific filter is currently set.
     *
     * @param string $key Filter key to check
     *
     * @return bool True if the filter is set, false otherwise
     */
    public function hasFilter(string $key): bool
    {
        return isset($this->filters[$key]);
    }
}
