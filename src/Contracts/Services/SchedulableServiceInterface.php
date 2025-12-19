<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Service interface for working with schedulable entities.
 *
 * Provides a fluent interface for filtering and retrieving schedulable data.
 */
interface SchedulableServiceInterface
{
    /**
     * Scope the service to work with a specific schedulable model.
     *
     * @param Model $model Schedulable model instance
     */
    public function for(Model $model): self;

    /**
     * Clear all applied filters and reset the service state.
     */
    public function resetFilters(): self;

    /**
     * Filter results by availability type.
     *
     * @param string $type Availability type to filter by
     */
    public function whereType(string $type): self;

    /**
     * Get all results without applying any filters.
     *
     * @return Collection All matching results
     */
    public function all(): Collection;

    /**
     * Execute the query with current filters and get results.
     *
     * @return Collection Filtered results
     */
    public function get(): Collection;
}
