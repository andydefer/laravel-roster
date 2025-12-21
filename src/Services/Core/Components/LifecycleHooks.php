<?php

declare(strict_types=1);

namespace Roster\Services\Core\Components;

trait LifecycleHooks
{
    /**
     * After create hook.
     *
     * @param mixed $result The created entity
     */
    protected function afterCreate(mixed $result): void
    {
        if (method_exists(object_or_class: $result, method: 'getId') || property_exists(object_or_class: $result, property: 'id')) {
            $id = $result->id ?? $result->getId();
            $this->clearEntityCache(entityId: (int) $id);
        }
    }

    /**
     * After update hook.
     *
     * @param int $id The updated entity ID
     * @param bool $result Whether the update was successful
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        if ($result) {
            $this->clearEntityCache(entityId: $id);
        }
    }
}
