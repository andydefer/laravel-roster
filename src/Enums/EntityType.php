<?php

declare(strict_types=1);

namespace Roster\Enums;

use InvalidArgumentException;

enum EntityType: string
{
    case AVAILABILITY = 'availability';
    case SCHEDULE = 'schedule';
    case IMPEDIMENT = 'impediment';

    public function displayName(): string
    {
        return match ($this) {
            self::AVAILABILITY => 'Availability',
            self::SCHEDULE => 'Schedule',
            self::IMPEDIMENT => 'Impediment',
        };
    }

    public function dateFields(): array
    {
        return match ($this) {
            self::AVAILABILITY => ['validity_start', 'validity_end', 'daily_start', 'daily_end'],
            self::SCHEDULE, self::IMPEDIMENT => ['start_datetime', 'end_datetime'],
        };
    }

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
