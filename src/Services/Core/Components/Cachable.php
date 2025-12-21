<?php

declare(strict_types=1);

namespace Roster\Services\Core\Components;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait Cachable
{
    /**
     * Clear entity cache.
     *
     * @param int $entityId The entity ID to clear cache for
     */
    protected function clearEntityCache(int $entityId): void
    {
        if (! Config::get(key: 'roster.cache.enabled', default: true)) {
            return;
        }

        $prefix = Config::get(key: 'roster.cache.prefix', default: 'roster_');
        $entityType = $this->getEntityType();
        $cacheKey = sprintf('%s%s_%d', $prefix, $entityType, $entityId);

        Cache::forget(key: $cacheKey);

        if (Config::get(key: 'roster.cache.use_tags', default: true)) {
            Cache::tags(names: [sprintf('%s_%d', $entityType, $entityId)])->flush();
        }
    }
}
