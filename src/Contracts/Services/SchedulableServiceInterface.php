<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Contract for schedulable services.
 *
 * Defines the interface for services that work with schedulable entities,
 * providing methods for scoping, filtering, and retrieving data.
 */
interface SchedulableServiceInterface
{
    /**
     * Scope the service to a specific schedulable model.
     *
     * @param Model $model The schedulable model to scope to
     */
    public function for(Model $model): self;

    /**
     * Clear all applied filters.
     */
    public function resetFilters(): self;

    /**
     * Filter results by type.
     *
     * @param string $type The type to filter by
     */
    public function whereType(string $type): self;

    /**
     * Get all matching results.
     */
    public function all(): Collection;

    /**
     * Execute the query with current filters and get results.
     */
    public function get(): Collection;
}
