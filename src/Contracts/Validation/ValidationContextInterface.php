<?php

declare(strict_types=1);

namespace Roster\Contracts\Validation;

use Illuminate\Database\Eloquent\Model;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

interface ValidationContextInterface
{
    /* -----------------------------------------------------------------
     | Context metadata
     | -----------------------------------------------------------------
     */

    public function getOperation(): OperationType;

    public function getEntityType(): EntityType;

    public function getSchedulable(): ?Model;

    public function getCurrentEntity(): mixed;

    /* -----------------------------------------------------------------
     | Data access (safe: null = absent)
     | -----------------------------------------------------------------
     */

    /**
     * Get a value from context data.
     * Null values are treated as absent.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Check if a key exists and is not null.
     */
    public function has(string $key): bool;

    /**
     * Return only defined and non-null values.
     *
     * @return array<string, mixed>
     */
    public function safeData(): array;

    /* -----------------------------------------------------------------
     | Raw data access (includes nulls)
     | -----------------------------------------------------------------
     */

    /**
     * Get a raw value, including null.
     */
    public function rawGet(string $key, mixed $default = null): mixed;

    /**
     * Check if a key exists, even if its value is null.
     */
    public function rawHas(string $key): bool;

    /**
     * Return raw context data (including nulls).
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Alias of getData() for clarity.
     */
    public function rawData(): array;

    /* -----------------------------------------------------------------
     | Mutation
     | -----------------------------------------------------------------
     */

    public function set(string $key, mixed $value): void;

    /**
     * Return all raw data (BC helper).
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /* -----------------------------------------------------------------
     | Violations
     | -----------------------------------------------------------------
     */

    public function setViolation(string $field, string $message): void;

    public function addViolation(string $field, string $message): void;

    public function getViolations(): array;

    public function hasViolations(): bool;

    /* -----------------------------------------------------------------
     | Flags
     | -----------------------------------------------------------------
     */

    /**
     * Set a flag in the context.
     */
    public function setFlag(string $flag, mixed $value = true): void;

    /**
     * Check if a flag exists and is truthy.
     */
    public function hasFlag(string $flag): bool;

    /**
     * Get a flag value.
     */
    public function getFlag(string $flag, mixed $default = false): mixed;
}
