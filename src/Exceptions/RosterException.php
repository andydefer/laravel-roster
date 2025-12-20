<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Base exception for the Roster package.
 *
 * All custom exceptions in the Roster package should extend this class
 * to ensure consistent exception handling and error reporting.
 */
abstract class RosterException extends InvalidArgumentException
{
    /**
     * Create a new RosterException instance.
     *
     * @param  string  $type  Unique identifier for the exception type
     * @param  string  $message  Human-readable error message
     * @param  array  $context  Additional context data for debugging
     * @param  int  $code  Error code
     * @param  Throwable|null  $previous  Previous exception in the chain
     */
    public function __construct(
        protected string $type,
        string $message = '',
        protected array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the exception type identifier.
     *
     * @return string Unique identifier for this exception type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the context data associated with the exception.
     *
     * @return array Additional context for debugging
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
