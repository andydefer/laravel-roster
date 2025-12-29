<?php

declare(strict_types=1);

namespace Roster\Contracts\Validation;

use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Services\ServiceInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Services\AvailabilityService;

/**
 * Context container for validation operations.
 *
 * Provides access to validation context data, operation metadata,
 * and violation tracking during roster validation processes.
 */
interface ValidationContextInterface
{
    /* -----------------------------------------------------------------
     | Context metadata
     | -----------------------------------------------------------------
     */

    /**
     * Get the operation type being performed.
     */
    public function getOperation(): OperationType;

    /**
     * Get the entity type being validated.
     */
    public function getEntityType(): EntityType;

    /**
     * Get the schedulable model instance.
     */
    public function getSchedulable(): ?Model;

    /**
     * Get the current entity being validated.
     *
     * @return mixed The entity instance or data being validated
     */
    public function getCurrentEntity(): mixed;

    /**
     * Get the current entity id being validated.
     *
     * @return mixed The entity id instance or data being validated
     */
    public function getEntityId(): int;

    /**
     * Get the availability service instance.
     */
    public function getAvailabilityService(): AvailabilityService;

    /* -----------------------------------------------------------------
     | Safe data access (null = absent)
     | -----------------------------------------------------------------
     */

    /**
     * Get a value from context data, treating null values as absent.
     *
     * @param string $key The data key to retrieve
     * @param mixed $default Default value if key is absent
     * @return mixed The value if present and not null, otherwise default
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if a key exists and has a non-null value.
     *
     * @param string $key The key to check
     * @return bool True if key exists and value is not null
     */
    public function has(string $key): bool;

    /**
     * Determines whether at least one validation violation exists for the given field.
     *
     * @param string $field The field name to check for validation violations.
     * @return bool True if at least one violation exists for the given field, false otherwise.
     */
    public function hasViolationFor(string $field): bool;

    /**
     * Return only defined and non-null values from context data.
     *
     * @return array<string, mixed> Filtered context data
     */
    public function safeData(): array;

    /* -----------------------------------------------------------------
     | Raw data access (includes nulls)
     | -----------------------------------------------------------------
     */

    /**
     * Get a raw value from context data, including null values.
     *
     * @param string $key The data key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The raw value, even if null
     */
    public function rawGet(string $key, mixed $default = null): mixed;

    /**
     * Check if a key exists in context data, even if its value is null.
     *
     * @param string $key The key to check
     * @return bool True if key exists in context data
     */
    public function rawHas(string $key): bool;

    /**
     * Return all raw context data including null values.
     *
     * @return array<string, mixed> Complete context data
     */
    public function getData(): array;

    /**
     * Alias of getData() for clarity when working with raw data.
     *
     * @return array<string, mixed> Complete context data
     */
    public function rawData(): array;

    /* -----------------------------------------------------------------
     | Data mutation
     | -----------------------------------------------------------------
     */

    /**
     * Set a value in the context data.
     *
     * @param string $key The data key
     * @param mixed $value The value to set
     */
    public function set(string $key, mixed $value): void;

    /* -----------------------------------------------------------------
     | Violation management
     | -----------------------------------------------------------------
     */

    /**
     * Set a violation for a field, replacing any existing violation.
     *
     * @param string $field The field name
     * @param string $message The violation message
     */
    public function setViolation(string $field, string $message, ?string $rule = null): void;

    /**
     * Add a validation violation with automatic rule information.
     *
     * @param RuleInterface $rule The rule that triggered the violation
     * @param string $field The field name with the violation
     * @param string $message The violation message
     */
    public function setViolationFromRule(
        RuleInterface $rule,
        string $field,
        string $message
    ): void;

    /**
     * Get all recorded violations.
     *
     * @return array<string, string[]> Field-keyed array of violation messages
     */
    public function getViolations(): array;

    /**
     * Check if any violations have been recorded.
     *
     * @return bool True if violations exist
     */
    public function hasViolations(): bool;

    /**
     * Get the current service instance.
     */
    public function getCurrentService(): ServiceInterface;

    /* -----------------------------------------------------------------
     | Flag management
     | -----------------------------------------------------------------
     */

    /**
     * Set a flag in the context.
     *
     * @param string $flag The flag name
     * @param mixed $value The flag value (defaults to true)
     */
    public function setFlag(string $flag, mixed $value = true): void;

    /**
     * Check if a flag exists and has a truthy value.
     *
     * @param string $flag The flag name
     * @return bool True if flag exists and is truthy
     */
    public function hasFlag(string $flag): bool;

    /**
     * Get a flag value.
     *
     * @param string $flag The flag name
     * @param mixed $default Default value if flag doesn't exist
     * @return mixed The flag value
     */
    public function getFlag(string $flag, mixed $default = false): mixed;
}
