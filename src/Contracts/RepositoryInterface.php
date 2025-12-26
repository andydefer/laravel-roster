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
    public function create(array $data, Model $schedulable, ?Model $owner = null): Model;

    /**
     * Find an entity by ID.
     */
    public function find(
        int $id,
        ?Model $schedulable = null,
        ?Model $owner = null,
        array $filters = []
    ): ?Model;



    /**
     * Get all entities with applied filters.
     */
    public function all(Model $schedulable, ?Model $model = null, array $filters = []): Collection;

    /**
     * Get paginated entities with applied filters.
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
     */
    public function update(
        int $id,
        Model $schedulable,
        ?Model $owner = null,
        array $data = []
    ): bool;


    /**
     * Delete an entity.
     */
    public function delete(
        int $id,
        Model $schedulable,
        ?Model $owner = null,
    ): bool;
}


/*

  $this->requireSchedulable();
  */
