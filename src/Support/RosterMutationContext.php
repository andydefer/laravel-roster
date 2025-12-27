<?php

declare(strict_types=1);

namespace Roster\Support;

/**
 * Controls whether model mutations are allowed within the Roster package.
 *
 * This context manager enforces a security pattern where direct model
 * manipulation is prohibited by default. Mutations can only be performed
 * within explicitly authorized contexts, typically by domain services
 * and repositories that implement proper business logic and validation.
 *
 * @package Roster\Support
 */
final class RosterMutationContext
{
    /**
     * Current nesting depth of mutation contexts.
     */
    private static int $depth = 0;

    /**
     * Executes code within an authorized mutation context.
     *
     * This method creates a safe execution environment where model
     * mutations are permitted. The context is automatically cleaned up
     * after execution, ensuring mutations cannot occur outside of
     * explicitly authorized scopes.
     *
     * @param callable $callback Code to execute within mutation context
     * @return mixed The result of the callback execution
     *
     * @example
     * $result = RosterMutationContext::allow(function () {
     *     return Model::create(['field' => 'value']);
     * });
     */
    public static function allow(callable $callback): mixed
    {
        try {
            ++self::$depth;
            return $callback();
        } finally {
            --self::$depth;
        }
    }

    /**
     * Checks if mutations are currently allowed.
     *
     * This method verifies whether the current execution context
     * is within an authorized mutation scope. It's used internally
     * by models and repositories to enforce mutation restrictions.
     *
     * @return bool True if mutations are permitted, false otherwise
     */
    public static function isAllowed(): bool
    {
        return self::$depth > 0;
    }
}
