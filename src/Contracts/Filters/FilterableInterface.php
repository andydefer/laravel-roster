<?php

declare(strict_types=1);

namespace Roster\Contracts\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterableInterface
{
    /**
     * Add or override a filter.
     */
    public function setFilter(string $key, mixed $value): self;

    /**
     * Clear all active filters.
     */
    public function clearFilters(): self;

    /**
     * Get all active filters.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array;

    /**
     * Check if a specific filter exists.
     */
    public function hasFilter(string $key): bool;


    public function applyFilters(
        Builder $builder,
        array $filters = []
    ): Builder;
}
