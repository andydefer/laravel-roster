<?php

declare(strict_types=1);

namespace Roster\Support;

/**
 * Contrôle l'utilisation autorisée des services.
 */
final class RosterServiceContext
{
    private static int $allowedDepth = 0;

    /**
     * Autoriser l'utilisation des services via un helper.
     */
    public static function allowViaHelper(callable $callback): mixed
    {
        try {
            ++self::$allowedDepth;
            return $callback();
        } finally {
            --self::$allowedDepth;
        }
    }

    /**
     * Vérifie si l'utilisation du service est autorisée.
     */
    public static function isAllowed(): bool
    {
        return self::$allowedDepth > 0;
    }

    /**
     * Vérifie si l'utilisation est directe (sans helper).
     */
    public static function isDirectUsage(): bool
    {
        return self::$allowedDepth === 0;
    }
}
