<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Abstract base repository providing common CRUD operations for Eloquent models.
 *
 * @template TModel of Model
 */
abstract class AbstractRepository
{
    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data
     * @return TModel
     */
    abstract public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param array<string, mixed> $data
     * @return bool True if update was successful
     */
    abstract public function update(int $id, array $data): bool;

    /**
     * Delete a record.
     *
     * @return bool True if deletion was successful
     */
    abstract public function delete(int $id): bool;

    /**
     * Find a record by its ID.
     *
     * @return TModel|null The found model or null
     */
    abstract public function find(int $id): ?Model;

    /**
     * Get all records.
     *
     * @return Collection<int, TModel>
     */
    abstract public function getAll(): Collection;
}
