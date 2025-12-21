<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Roster\Exceptions\MissingSchedulableException;

interface ValidatableInterface
{
    /**
     * Ensure a schedulable model has been provided.
     *
     * @throws MissingSchedulableException
     */
    public function validateSchedulable(): void;

    /**
     * Validate common required fields.
     *
     * @param array<string> $requiredFields Additional required fields
     */
    public function validateRequiredFields(array $requiredFields = []): void;

    /**
     * Validate future dates based on configuration.
     *
     * @param string $operation The operation being performed
     * @param string $entityType The type of entity
     * @param array<string, mixed> $entityConfig The entity-specific configuration
     */
    public function validateFutureDates(
        string $operation,
        string $entityType,
        array $entityConfig
    ): void;

    /**
     * Validate durations based on configuration.
     *
     * @param string $operation The operation being performed
     */
    public function validateDurations(string $operation): void;

    /**
     * Validate timezone for the entity.
     *
     * @param string $timezone The timezone to validate
     */
    public function validateTimezoneHook(string $timezone): void;

    /**
     * Validate other global configuration rules.
     *
     * @param string $operation The operation being performed
     */
    public function validateGlobalConfigurationRules(string $operation): void;
}
