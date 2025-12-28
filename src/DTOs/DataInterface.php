<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface for all Data Transfer Objects.
 * Defines the common contract that all DTOs must implement.
 */
interface DataInterface
{
    /**
     * Create a DTO instance from raw array data.
     *
     * @param array<string, mixed> $data Raw data
     * @return self New immutable DTO instance
     */
    public static function fromArray(array $data): self;

    /**
     * Create a DTO instance from an Eloquent model.
     *
     * @param Model $model Eloquent model instance
     * @return self New immutable DTO instance
     */
    public static function fromModel(Model $model): self;

    /**
     * Convert the DTO to an array representation suitable for database storage.
     *
     * @return array<string, mixed> Array with database-ready formats
     */
    public function toArray(): array;

    /**
     * Create a new instance with updated schedulable entity information.
     *
     * @param int|null $schedulableId New schedulable entity ID
     * @param string|null $schedulableType New schedulable entity type
     * @return self New instance with updated schedulable information
     */
    public function withSchedulable(?int $schedulableId, ?string $schedulableType): self;
}
