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
     * @param array $data Resource attributes
     * @return mixed The created resource
     */
    public function create(array $data): mixed;

    /**
     * Update an existing resource.
     *
     * @param int $id Resource identifier
     * @param array $data Updated attributes
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
     * @return mixed The found resource or null
     */
    public function find(int $id): mixed;

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
     * @param array $columns Columns to select
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
     * Get current data for operations.
     *
     * @return array Operation data
     */
    public function getData(): array;

    /**
     * Set data for operations.
     *
     * @param array $data Operation data
     */
    public function setData(array $data): self;

    /**
     * Get current filters.
     *
     * @return array<string, mixed> Active filters
     */
    public function getFilters(): array;

    /**
     * Replace all filters.
     *
     * @param array $filters New filters
     */
    public function setFilters(array $filters): self;

    /**
     * Clear all filters.
     */
    public function resetFilters(): self;

    /**
     * Set a specific filter.
     *
     * @param string $key Filter key
     * @param mixed $value Filter value
     */
    public function setFilter(string $key, mixed $value): self;

    /* ========= Context Management ========= */

    /**
     * Get the current schedulable entity context.
     *
     * @return Model|null Current schedulable entity
     */
    public function getSchedulable(): ?Model;

    /**
     * Set the schedulable entity context.
     *
     * @param Model $model Schedulable entity
     */
    public function setSchedulable(Model $model): self;

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
     */
    public function clear(): self;
}
