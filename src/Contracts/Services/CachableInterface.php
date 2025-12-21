<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

/**
 * Interface for services that support cache management.
 * Provides a method to clear cached data for specific entities.
 */
interface CachableInterface
{
    /**
     * Clear cached data for a specific entity.
     *
     * @param int $entityId The unique identifier of the entity
     */
    public function clearEntityCache(int $entityId): void;
}
