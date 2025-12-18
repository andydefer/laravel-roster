<?php

declare(strict_types=1);

namespace Roster\Enums;

use Stringable;

/**
 * Trait to provide helper methods for Enums.
 */
trait EnumValues
{
    /**
     * Get all possible values of the enum as an array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
