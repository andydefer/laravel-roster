<?php

declare(strict_types=1);

namespace Roster\Traits;

/**
 * Provides helper methods for working with enums.
 */
trait EnumValues
{
    /**
     * Retrieves all possible values of the enum as an array.
     *
     * @return array<int, string> Array of enum values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
