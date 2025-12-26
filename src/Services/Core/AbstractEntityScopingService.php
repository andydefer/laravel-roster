<?php

declare(strict_types=1);

namespace Roster\Services\Core;

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


    abstract protected function clearEntityCache(int $entityId): void;
}
