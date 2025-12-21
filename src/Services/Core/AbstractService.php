<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Roster\Services\Core\Components\ExceptionHandler;
use Roster\Traits\FilterableTrait;

/**
 * Abstract service providing a CRUD template with lifecycle hooks.
 *
 * This abstract class implements the Template Method pattern for CRUD operations,
 * allowing concrete services to define specific execution logic while maintaining
 * consistent lifecycle hooks (before/after operations).
 */
abstract class AbstractService
{
    use ExceptionHandler;
    use FilterableTrait;

    /**
     * The schedulable model instance.
     */
    protected ?Model $schedulable = null;

    /**
     * Current filters for data operations.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Current data payload for operations.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Execute the creation operation.
     *
     * Concrete implementations must define the specific creation logic.
     *
     * @return mixed The created entity or result
     */
    abstract protected function executeCreate(): mixed;

    /**
     * Execute the update operation.
     *
     * Concrete implementations must define the specific update logic.
     *
     * @param int $id The ID of the entity to update
     * @return bool True if update was successful
     */
    abstract protected function executeUpdate(int $id): bool;

    /**
     * Execute the deletion operation.
     *
     * Concrete implementations must define the specific deletion logic.
     *
     * @param int $id The ID of the entity to delete
     * @return bool True if deletion was successful
     */
    abstract protected function executeDelete(int $id): bool;

    /**
     * Hook executed before creation.
     *
     * Override in concrete classes to add pre-creation logic.
     *
     * @param mixed ...$args Variable arguments for the hook
     */
    protected function beforeCreate(mixed ...$args): void {}

    /**
     * Hook executed after creation.
     *
     * Override in concrete classes to add post-creation logic.
     *
     * @param mixed $result The result from executeCreate()
     */
    protected function afterCreate(mixed $result): void {}

    /**
     * Hook executed before update.
     *
     * Override in concrete classes to add pre-update logic.
     *
     * @param int $id The ID of the entity being updated
     */
    protected function beforeUpdate(int $id): void {}

    /**
     * Hook executed after update.
     *
     * Override in concrete classes to add post-update logic.
     *
     * @param int $id The ID of the entity that was updated
     * @param bool $result The result from executeUpdate()
     */
    protected function afterUpdate(int $id, bool $result): void {}

    /**
     * Hook executed before deletion.
     *
     * Override in concrete classes to add pre-deletion logic.
     *
     * @param int $id The ID of the entity being deleted
     */
    protected function beforeDelete(int $id): void {}

    /**
     * Hook executed after deletion.
     *
     * Override in concrete classes to add post-deletion logic.
     *
     * @param int $id The ID of the entity that was deleted
     * @param bool $result The result from executeDelete()
     */
    protected function afterDelete(int $id, bool $result): void {}

    /**
     * Retrieve entities.
     *
     * Default implementation returns an empty collection.
     * Override in concrete classes to implement specific retrieval logic.
     *
     * @return Collection<int, mixed> The collection of entities
     */
    public function get(): Collection
    {
        return collect();
    }

    /**
     * Create a new entity with lifecycle hooks.
     *
     * @param array<string, mixed> $data The data for creation
     * @return mixed The created entity or result
     */
    public function create(array $data): mixed
    {
        $this->data = $data;

        $this->beforeCreate();
        $result = $this->executeCreate();
        $this->afterCreate($result);

        return $result;
    }

    /**
     * Update an existing entity with lifecycle hooks.
     *
     * @param int $id The ID of the entity to update
     * @param array<string, mixed> $data The data for update
     * @return bool True if update was successful
     */
    public function update(int $id, array $data): bool
    {
        $this->data = $data;

        $this->beforeUpdate($id);
        $result = $this->executeUpdate($id);
        $this->afterUpdate($id, $result);

        return $result;
    }

    /**
     * Delete an entity with lifecycle hooks.
     *
     * @param int $id The ID of the entity to delete
     * @return bool True if deletion was successful
     */
    public function delete(int $id): bool
    {
        $this->beforeDelete($id);
        $result = $this->executeDelete($id);
        $this->afterDelete($id, $result);

        return $result;
    }

    /**
     * Get the current data payload.
     *
     * @return array<string, mixed> The current data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data payload.
     *
     * @param array<string, mixed> $data The data to set
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the current filters.
     *
     * @return array<string, mixed> The current filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Set the filters.
     *
     * @param array<string, mixed> $filters The filters to set
     * @return $this
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Get the schedulable model instance.
     *
     * @return Model|null The schedulable model or null if not set
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Set the schedulable model instance.
     *
     * @param Model $model The schedulable model
     * @return $this
     */
    public function setSchedulable(Model $model): self
    {
        $this->schedulable = $model;
        return $this;
    }

    /**
     * Clear the data and filters.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->data = [];
        $this->filters = [];
        return $this;
    }
}
