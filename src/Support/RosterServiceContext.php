<?php

declare(strict_types=1);

namespace Roster\Support;

/**
 * Controls authorized service usage within the Roster package.
 *
 * Provides context-aware access control to prevent direct instantiation
 * of services and ensure proper usage through designated helpers.
 */
final class RosterServiceContext
{
    /**
     * Tracks the current nesting depth of authorized contexts.
     */
    private static int $allowedDepth = 0;

    /**
     * Executes a callback within an authorized service context via helper.
     *
     * This method should only be called by package helper functions to
     * establish a valid context for service operations.
     *
     * @param callable $callback Operation to execute within authorized context
     * @return mixed Result of the callback execution
     */
    public static function allow(callable $callback): mixed
    {
        try {
            ++self::$allowedDepth;
            return $callback();
        } finally {
            --self::$allowedDepth;
        }
    }

    /**
     * Determines if service usage is currently authorized.
     *
     * @return bool True if service operations are permitted
     */
    public static function isAllowed(): bool
    {
        return self::$allowedDepth > 0;
    }

    /**
     * Checks if service is being used directly without proper context.
     *
     * @return bool True if service is being instantiated directly
     */
    public static function isDirectUsage(): bool
    {
        return self::$allowedDepth === 0;
    }
}
