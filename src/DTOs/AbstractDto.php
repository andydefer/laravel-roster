<?php

declare(strict_types=1);

namespace Roster\DTOs;

use InvalidArgumentException;
use Illuminate\Support\Carbon;

/**
 * Abstract base class for all Data Transfer Objects.
 * Provides common functionality and enforces consistent DTO patterns.
 */
abstract class AbstractDto implements DataInterface
{
    /**
     * Convert the DTO to an array representation suitable for database storage.
     *
     * @return array<string, mixed> Array with database-ready formats
     */
    public function toArray(): array
    {
        $data = $this->getArrayData();
        return self::filterNullValues($data);
    }

    /**
     * Create a new instance with updated schedulable entity information.
     *
     * @param int|null $schedulableId New schedulable entity ID
     * @param string|null $schedulableType New schedulable entity type
     * @return self New instance with updated schedulable information
     */
    public function withSchedulable(?int $schedulableId, ?string $schedulableType): self
    {
        $data = $this->toArray();
        $data['schedulable_id'] = $schedulableId;
        $data['schedulable_type'] = $schedulableType;

        return static::fromArray($data);
    }

    /**
     * Parse a datetime input into a Carbon instance.
     *
     * @param string|Carbon|null $datetime Datetime input to parse
     * @return Carbon|null Parsed Carbon instance or null
     *
     * @throws InvalidArgumentException If the input is not null, string, or Carbon
     */
    final protected static function parseDateTime(string|Carbon|null $datetime): ?Carbon
    {
        return match (true) {
            $datetime === null => null,
            $datetime instanceof Carbon => $datetime,
            is_string($datetime) => Carbon::parse($datetime),
            default => throw new InvalidArgumentException(
                'Datetime must be null, string or instance of Carbon'
            ),
        };
    }

    /**
     * Parse a time string into a Carbon instance.
     *
     * @param string|null $timeString Time string or null
     * @return Carbon|null Carbon instance or null
     */
    final protected static function parseTime(?string $timeString): ?Carbon
    {
        return $timeString !== null ? Carbon::parse($timeString) : null;
    }

    /**
     * Remove null values from an array.
     *
     * @param array<string, mixed> $array Array to filter
     * @return array<string, mixed> Filtered array
     */
    final protected static function filterNullValues(array $array): array
    {
        return array_filter($array, static fn($value): bool => $value !== null);
    }

    /**
     * Get the array data for this DTO.
     * Each concrete DTO must implement this method.
     *
     * @return array<string, mixed> Raw array data
     */
    abstract protected function getArrayData(): array;
}
