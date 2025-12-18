<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

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
    protected function applyTypeFilter(Builder $builder, string $relation = 'availability'): Builder
    {
        if (isset($this->filters['type'])) {
            if ($relation !== '' && $relation !== '0') {
                $builder->whereHas($relation, function ($q): void {
                    $q->where('type', $this->filters['type']);
                });
            } else {
                $builder->where('type', $this->filters['type']);
            }
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
    final public function whereStatus(string $status): self
    {
        $this->filters['status'] = $status;
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
}
