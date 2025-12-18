<?php

declare(strict_types=1);

namespace Roster\Exceptions\Enums;

enum MissingResourceType: string
{
    case MISSING_AVAILABILITY = 'MISSING_AVAILABILITY';
    case MISSING_SCHEDULABLE = 'MISSING_SCHEDULABLE';

    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::MISSING_AVAILABILITY => 'Must belong to an Availability',
            self::MISSING_SCHEDULABLE => 'No schedulable specified. Use the for() method before executing the query.',
        };
    }
}
