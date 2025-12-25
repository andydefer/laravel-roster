<?php

declare(strict_types=1);

namespace Roster\Support;

/**
 * Controls whether model mutations are allowed.
 *
 * By default, mutations are forbidden.
 * Only domain services / repositories may explicitly allow them.
 */
final class RosterMutationContext
{
    private static int $depth = 0;

    public static function allow(callable $callback): mixed
    {
        try {
            ++self::$depth;
            return $callback();
        } finally {
            --self::$depth;
        }
    }

    public static function isAllowed(): bool
    {
        return self::$depth > 0;
    }
}
