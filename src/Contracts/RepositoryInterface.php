<?php

declare(strict_types=1);

namespace Roster\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Create a new entity.
     *
     * @param mixed $data
     */
    public function create(array $data): Model;

    /**
     * Find an entity by ID.
     */
    public function find(int $id): mixed;

    /**
     * Get all entities with applied filters.
     */
    public function all(Model $schedulable, ?Model $model = null, array $filters = []): Collection;

    /**
     * Get paginated entities with applied filters.
     */
    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator;

    /**
     * Update an existing entity.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete an entity.
     */
    public function delete(int $id): bool;
}


/*

  $this->requireSchedulable();
  */
