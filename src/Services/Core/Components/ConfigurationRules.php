<?php

declare(strict_types=1);

namespace Roster\Services\Core\Components;

use Illuminate\Support\Facades\Config;

trait ConfigurationRules
{
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
}
