<?php

declare(strict_types=1);

namespace Roster\Enums;

use InvalidArgumentException;

/**
 * Enumeration of entity types supported by the scheduling system.
 *
 * Defines the different types of entities that can be managed,
 * along with their metadata and utility methods for type resolution.
 */
enum EntityType: string
{
    case AVAILABILITY = 'availability';
    case SCHEDULE = 'schedule';
    case IMPEDIMENT = 'impediment';

    /**
     * Gets the human-readable display name for the entity type.
     *
     * @return string Formatted display name
     */
    public function displayName(): string
    {
        return match ($this) {
            self::AVAILABILITY => 'Availability',
            self::SCHEDULE => 'Schedule',
            self::IMPEDIMENT => 'Impediment',
        };
    }

    /**
     * Gets the date/time fields associated with this entity type.
     *
     * @return array<string> Array of date/time field names
     */
    public function dateFields(): array
    {
        return match ($this) {
            self::AVAILABILITY => ['validity_start', 'validity_end', 'daily_start', 'daily_end'],
            self::SCHEDULE, self::IMPEDIMENT => ['start_datetime', 'end_datetime'],
        };
    }

    /**
     * Resolves entity type from a service class name.
     *
     * @param string $className Fully qualified service class name
     * @return self Corresponding entity type
     * @throws InvalidArgumentException If service class type is unknown
     */
    public static function fromServiceClass(string $className): self
    {
        $baseName = class_basename($className);
        $type = strtolower(str_replace('Service', '', $baseName));

        return match ($type) {
            'availability' => self::AVAILABILITY,
            'schedule' => self::SCHEDULE,
            'impediment' => self::IMPEDIMENT,
            default => throw new InvalidArgumentException('Unknown entity type: ' . $type)
        };
    }
}
