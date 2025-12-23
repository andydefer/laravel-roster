<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Roster\Traits\FilterableTrait;

/**
 * Abstract service providing a CRUD template.
 *
 * This abstract class implements basic CRUD operations.
 */
abstract class AbstractService
{
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
     * Retrieve entities.
     *
     * @return Collection<int, mixed> The collection of entities
     */
    abstract public function get(): Collection;

    /**
     * Find entity by ID.
     *
     * @param int $id Entity ID
     * @return mixed Entity or null if not found
     */
    abstract public function find(int $id): mixed;

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

    public function update(int $id, array $data): bool
    {

        // Supprime les clés spécifiées si elles existent
        $data = array_diff_key($data, array_flip(['schedulable_id', 'schedulable_type', 'availability_id']));
        return true;
    }


    /**
     * Scope the service to a specific schedulable model.
     *
     * @param Model $model The parent model to scope operations to
     * @return $this
     */
    public function for(Model $model): static
    {
        $this->schedulable = $model;
        return $this;
    }

    /**
     * Set a single filter.
     *
     * @param string $key Filter key
     * @param mixed $value Filter value
     * @return $this
     */
    public function setFilter(string $key, mixed $value): self
    {
        $this->filters[$key] = $value;
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
