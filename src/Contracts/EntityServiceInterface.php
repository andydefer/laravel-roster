<?php

declare(strict_types=1);

namespace Roster\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EntityServiceInterface
{
    /**
     * Create a new entity.
     *
     * @param mixed $data
     */
    public function create(array $data);

    /**
     * Find an entity by ID.
     */
    public function find(int $id): mixed;

    /**
     * Get all entities with applied filters.
     */
    public function all(): Collection;

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
