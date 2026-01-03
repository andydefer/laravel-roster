<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Repository interface for managing schedulable entities.
 *
 * Provides a consistent API for CRUD operations on entities that belong
 * to schedulable resources with optional ownership constraints.
 */
interface RepositoryInterface
{
    /**
     * Create a new entity associated with a schedulable resource.
     *
     * @param array<string, mixed> $data Entity attributes
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @return Model The created entity instance
     */
    public function create(array $data, Model $schedulable, ?Model $owner = null): Model;

    /**
     * Find an entity by its ID with optional schedulable and ownership constraints.
     *
     * @param int $id Entity identifier
     * @param Model|null $schedulable Optional schedulable resource for scoping
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $filters Additional query filters
     * @return Model|null The found entity or null if not found
     */
    public function find(
        int $id,
        ?Model $schedulable = null,
        ?Model $owner = null,
        array $filters = []
    ): ?Model;

    /**
     * Retrieve the first entity matching schedulable, owner, and filters.
     *
     * @param Model $schedulable Schedulable entity
     * @param Model|null $owner Optional owner entity
     * @param array<string, mixed> $filters Optional filters
     * @return Model|null First matching entity or null if none found
     */
    public function first(Model $schedulable, ?Model $owner = null, array $filters = []): ?Model;

    /**
     * Get all entities for a schedulable resource with optional filtering.
     *
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $filters Query filters to apply
     * @return Collection<int, Model> Collection of entities
     */
    public function all(Model $schedulable, ?Model $owner = null, array $filters = []): Collection;

    /**
     * Get paginated entities for a schedulable resource.
     *
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $filters Query filters to apply
     * @param int $perPage Number of items per page
     * @param array<int, string> $columns Columns to select
     * @param string $pageName Query parameter name for pagination
     * @param int|null $page Current page number
     * @return LengthAwarePaginator Paginated collection of entities
     */
    public function paginate(
        Model $schedulable,
        ?Model $owner = null,
        array $filters = [],
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator;

    /**
     * Update an existing entity.
     *
     * @param int $id Entity identifier
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $data Updated entity attributes
     * @return bool True if update was successful, false otherwise
     */
    public function update(
        int $id,
        Model $schedulable,
        ?Model $owner = null,
        array $data = []
    ): bool;

    /**
     * Delete an entity.
     *
     * @param int $id Entity identifier
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete(
        int $id,
        Model $schedulable,
        ?Model $owner = null
    ): bool;
}
