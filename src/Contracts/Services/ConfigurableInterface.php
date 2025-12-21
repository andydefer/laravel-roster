<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use InvalidArgumentException;

/**
 * Interface for configuration management in services.
 * Handles configuration rules, validation, and entity metadata.
 */
interface ConfigurableInterface
{
    /**
     * Apply configuration rules to input data based on operation type.
     *
     * @param array<string, mixed> $data The input data to process
     * @param string $operation The operation type: 'create' or 'update'
     * @return array<string, mixed> The processed data with configuration rules applied
     */
    public function applyConfigurationRules(array $data, string $operation): array;

    /**
     * Validate configuration rules for a specific operation.
     *
     * @param string $operation The operation type: 'create' or 'update'
     * @throws InvalidArgumentException If configuration validation fails
     */
    public function validateConfiguration(string $operation): void;

    /**
     * Get the entity type identifier.
     *
     * @return string The entity type in lowercase
     */
    public function getEntityType(): string;

    /**
     * Get the human-readable display name for the entity.
     *
     * @return string The entity display name in title case
     */
    public function getEntityDisplayName(): string;

    /**
     * Get date and time fields for a specific entity type.
     *
     * @param string $entityType The entity type identifier
     * @return array<string> List of date/time field names
     */
    public function getDateTimeFields(string $entityType): array;
}
