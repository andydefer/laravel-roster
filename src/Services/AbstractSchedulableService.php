<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Roster\Contracts\Services\SchedulableServiceInterface;
use Roster\Exceptions\MissingSchedulableException;

/**
 * Base service for resources scoped to a schedulable model.
 *
 * Defines the execution workflow and enforces a consistent
 * usage contract for all schedulable services.
 */
abstract class AbstractSchedulableService implements SchedulableServiceInterface
{
    protected ?Model $schedulable = null;

    /**
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Scope the service to a specific parent model.
     */
    final public function for(Model $model): static
    {
        $this->schedulable = $model;

        return $this;
    }

    /**
     * Get the current schedulable model.
     */
    final public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Clear all applied filters.
     */
    final public function resetFilters(): static
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Ensure a schedulable model has been provided.
     *
     * @throws MissingSchedulableException
     */
    final protected function validateSchedulable(): void
    {
        if (! $this->schedulable instanceof Model) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Return all matching results.
     */
    final public function all(): Collection
    {
        return $this->get();
    }

    /**
     * Execute the query with the current filters.
     */
    final public function get(): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()->get();
    }

    /**
     * Filter results by type.
     */
    final public function whereType(string $type): static
    {
        $this->filters['type'] = $type;

        return $this;
    }

    /**
     * Build the base query and apply all active filters.
     *
     * @return Builder
     */
    abstract protected function applyFilters();
}
