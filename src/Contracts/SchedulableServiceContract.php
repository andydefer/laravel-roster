<?php

declare(strict_types=1);

namespace Roster\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Contract for schedulable services.
 *
 * Defines the interface for services that work with schedulable entities,
 * providing methods for scoping, filtering, and retrieving data.
 */
interface SchedulableServiceContract
{
    /**
     * Scope the service to a specific schedulable model.
     *
     * @param Model $model The schedulable model to scope to
     * @return self
     */
    public function for(Model $model): self;

    /**
     * Clear all applied filters.
     *
     * @return self
     */
    public function resetFilters(): self;

    /**
     * Filter results by type.
     *
     * @param string $type The type to filter by
     * @return self
     */
    public function whereType(string $type): self;

    /**
     * Get all matching results.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Execute the query with current filters and get results.
     *
     * @return Collection
     */
    public function get(): Collection;
}
