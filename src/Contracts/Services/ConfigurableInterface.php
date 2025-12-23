<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

/**
 * Interface for configuration management in services.
 * Handles configuration rules, validation, and entity metadata.
 */
interface ConfigurableInterface
{

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
