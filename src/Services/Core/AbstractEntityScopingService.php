<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

/**
 * Base service for resources scoped to a schedulable model.
 *
 * Provides common functionality for entity management including filtering,
 * configuration rule application, and CRUD operations.
 *
 * @template TEntity of mixed
 */
abstract class AbstractEntityScopingService extends AbstractService
{
    /**
     * Current entity being processed.
     */
    protected mixed $currentEntity = null;

    /**
     * Set multiple filters at once.
     *
     * @param array<string, mixed> $filters Associative array of filter key-value pairs
     * @return $this
     */
    final public function setFilters(array $filters): static
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Filter by entity type.
     *
     * @param string $type Entity type to filter by
     * @return $this
     */
    final public function whereType(string $type): static
    {
        $this->filters['type'] = $type;
        return $this;
    }

    /**
     * Clear all filters.
     *
     * @return $this
     */
    final public function resetFilters(): static
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Get all matching results.
     *
     * @return Collection<int, TEntity>
     */
    final public function getAll(): Collection
    {
        return $this->get();
    }



    /**
     * Apply configuration rules specific to create operation.
     *
     * @param array<string, mixed> $data The input data
     * @return array<string, mixed> The processed data
     */
    final protected function applyCreateConfigurationRules(array $data): array
    {
        return $this->applyEntitySpecificDefaults(data: $data);
    }

    /**
     * Apply configuration rules specific to update operation.
     *
     * @param array<string, mixed> $data The input data
     * @return array<string, mixed> The processed data
     */
    final protected function applyUpdateConfigurationRules(array $data): array
    {
        return $data;
    }

    /**
     * Apply entity-specific default values.
     *
     * @param array<string, mixed> $data The input data
     * @return array<string, mixed> The processed data
     */
    final protected function applyEntitySpecificDefaults(array $data): array
    {
        $entityType = $this->getEntityType();

        if ($entityType === 'schedule' && ! isset($data['status'])) {
            $data['status'] = Config::get(
                key: 'roster.schedule.default_status',
                default: 'available'
            );
        }

        return $data;
    }

    /**
     * Get the entity type based on service class name.
     *
     * @return string Entity type (e.g., 'availability', 'schedule', 'impediment')
     */
    final public function getEntityType(): string
    {
        $className = class_basename(static::class);
        return strtolower(str_replace('Service', '', $className));
    }

    /**
     * Get the display name for the entity type.
     *
     * @return string Human-readable entity name
     */
    final public function getEntityDisplayName(): string
    {
        return ucfirst($this->getEntityType());
    }



    // Abstract methods that must be implemented by child classes
    abstract protected function buildQueryWithFilters(): Builder;

    abstract protected function clearEntityCache(int $entityId): void;
}
