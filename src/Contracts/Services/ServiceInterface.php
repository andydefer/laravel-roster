<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Interface for service layer operations in the Roster system.
 *
 * Provides a standardized contract for CRUD operations, data filtering,
 * and contextual operations on schedulable entities.
 */
interface ServiceInterface
{
    /* ========= CRUD Operations ========= */

    /**
     * Create a new resource.
     *
     * @param array<string, mixed> $data Resource attributes
     * @return Model The created resource
     */
    public function create(array $data): Model;

    /**
     * Update an existing resource.
     *
     * @param int $id Resource identifier
     * @param array<string, mixed> $data Updated attributes
     * @return bool True if update succeeded, false otherwise
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a resource.
     *
     * @param int $id Resource identifier
     * @return bool True if deletion succeeded, false otherwise
     */
    public function delete(int $id): bool;

    /**
     * Find a resource by its identifier.
     *
     * @param int $id Resource identifier
     * @return Model|null The found resource or null
     */
    public function find(int $id): ?Model;

    /**
     * Retrieve the first resource matching the current filters.
     *
     * @return Model|null The first matching resource or null if none found
     */
    public function first(): ?Model;

    /**
     * Retrieve all resources.
     *
     * @return Collection<int, Model> All resources
     */
    public function all(): Collection;

    /**
     * Retrieve resources with pagination.
     *
     * @param int $perPage Number of items per page
     * @param array<int, string> $columns Columns to select
     * @param string $pageName Pagination query parameter name
     * @param int|null $page Specific page number
     * @return LengthAwarePaginator Paginated results
     */
    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator;

    /* ========= Data & Filter Management ========= */

    /**
     * Replace all filters.
     *
     * @param array<string, mixed> $filters New filters
     * @return static
     */
    public function setFilters(array $filters): static;

    /**
     * Clear all filters.
     *
     * @return static
     */
    public function resetFilters(): static;

    /**
     * Set a specific filter.
     *
     * @param string $key Filter key
     * @param mixed $value Filter value
     * @return static
     */
    public function setFilter(string $key, mixed $value): static;

    /**
     * Set the context for a specific schedulable entity (fluent alias).
     *
     * @param Model $model Schedulable entity
     * @return static New instance with context set
     */
    public function for(Model $model): static;

    /**
     * Set the owner context (fluent alias).
     *
     * @param Model $model Owner entity
     * @return static New instance with context set
     */
    public function owner(Model $model): static;

    /**
     * Clear all contextual data (filters, data, schedulable).
     *
     * @return static
     */
    public function clear(): static;
}
