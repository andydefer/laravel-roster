<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

trait FilterableTrait
{
    /**
     * @var array<string, mixed> Active filters for queries
     */
    protected array $filters = [];

    /**
     * Apply date filters to query.
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
     * Apply type filter to query.
     */
    protected function applyTypeFilter(Builder $builder, string $relation = ''): Builder
    {
        if (! isset($this->filters['type'])) {
            return $builder;
        }

        if ($relation !== '' && $relation !== '0') {
            $builder->whereHas($relation, function ($q): void {
                $q->where('type', $this->filters['type']);
            });
        } else {
            $builder->where('type', $this->filters['type']);
        }

        return $builder;
    }

    /**
     * Apply day filter to query.
     */
    protected function applyDayFilter(Builder $builder): Builder
    {
        if (isset($this->filters['day'])) {
            $builder->whereJsonContains('days', $this->filters['day']);
        }

        return $builder;
    }

    /**
     * Apply status filter to query.
     */
    protected function applyStatusFilter(Builder $builder): Builder
    {
        if (isset($this->filters['status'])) {
            $builder->where('status', $this->filters['status']);
        }

        return $builder;
    }

    /**
     * Apply reason filter to query.
     */
    protected function applyReasonFilter(Builder $builder): Builder
    {
        if (isset($this->filters['reason'])) {
            $builder->where('reason', 'like', '%'.$this->filters['reason'].'%');
        }

        return $builder;
    }

    /**
     * Apply availability ID filter to query.
     */
    protected function applyAvailabilityIdFilter(Builder $builder): Builder
    {
        if (isset($this->filters['availability_id'])) {
            $builder->where('availability_id', $this->filters['availability_id']);
        }

        return $builder;
    }

    /**
     * Apply schedulable filter to query.
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
     * Filter by start date.
     */
    public function whereStartDate(Carbon $date): self
    {
        $this->filters['start_date'] = $date;

        return $this;
    }

    /**
     * Filter by end date.
     */
    public function whereEndDate(Carbon $date): self
    {
        $this->filters['end_date'] = $date;

        return $this;
    }

    /**
     * Filter by status.
     */
    public function whereStatus(string $status): self
    {
        $this->filters['status'] = $status;

        return $this;
    }

    /**
     * Filter by reason (for impediments).
     */
    public function whereReason(string $reason): self
    {
        $this->filters['reason'] = $reason;

        return $this;
    }

    /**
     * Filter by availability ID.
     */
    public function whereAvailabilityId(int $availabilityId): self
    {
        $this->filters['availability_id'] = $availabilityId;

        return $this;
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): self
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Get current filters.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Check if a filter is set.
     */
    public function hasFilter(string $key): bool
    {
        return isset($this->filters[$key]);
    }
}
