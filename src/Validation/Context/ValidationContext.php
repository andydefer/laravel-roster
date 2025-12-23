<?php

declare(strict_types=1);

namespace Roster\Validation\Context;

use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;

class ValidationContext implements ValidationContextInterface
{
    private OperationType $operationType;

    private EntityType $entityType;

    /**
     * Raw data (may contain null values)
     *
     * @var array<string, mixed>
     */
    private array $data;

    private ?Model $model;

    private mixed $currentEntity;

    /**
     * @var array<string, string|array<int, string>>
     */
    private array $violations = [];

    /**
     * @var array<string, mixed>
     */
    private array $flags = [];

    public function __construct(
        OperationType $operationType,
        EntityType $entityType,
        array $data,
        ?Model $model = null,
        mixed $currentEntity = null
    ) {
        $this->operationType  = $operationType;
        $this->entityType     = $entityType;
        $this->data           = $data;
        $this->model          = $model;
        $this->currentEntity  = $currentEntity;
    }

    /* -----------------------------------------------------------------
     | Context metadata
     | -----------------------------------------------------------------
     */

    public function getOperation(): OperationType
    {
        return $this->operationType;
    }

    public function getEntityType(): EntityType
    {
        return $this->entityType;
    }

    public function getSchedulable(): ?Model
    {
        return $this->model;
    }

    public function getCurrentEntity(): mixed
    {
        return $this->currentEntity;
    }

    /* -----------------------------------------------------------------
     | Safe data access (null = absent)
     | -----------------------------------------------------------------
     */

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->data[$key] ?? null;

        return $value !== null ? $value : $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data)
            && $this->data[$key] !== null;
    }

    public function safeData(): array
    {
        return array_filter(
            $this->data,
            static fn($value): bool => $value !== null
        );
    }

    /* -----------------------------------------------------------------
     | Raw data access (includes nulls)
     | -----------------------------------------------------------------
     */

    public function rawGet(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function rawHas(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function rawData(): array
    {
        return $this->data;
    }

    /* -----------------------------------------------------------------
     | Mutation
     | -----------------------------------------------------------------
     */

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /* -----------------------------------------------------------------
     | Violations
     | -----------------------------------------------------------------
     */

    public function setViolation(string $field, string $message): void
    {
        $this->violations[$field] = $message;
    }

    public function addViolation(string $field, string $message): void
    {
        if (!isset($this->violations[$field])) {
            $this->violations[$field] = [];
        }

        if (is_string($this->violations[$field])) {
            $this->violations[$field] = [$this->violations[$field]];
        }

        $this->violations[$field][] = $message;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    /* -----------------------------------------------------------------
     | Flags
     | -----------------------------------------------------------------
     */

    public function setFlag(string $flag, mixed $value = true): void
    {
        $this->flags[$flag] = $value;
    }

    public function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]) && $this->flags[$flag];
    }

    public function getFlag(string $flag, mixed $default = false): mixed
    {
        return $this->flags[$flag] ?? $default;
    }
}
