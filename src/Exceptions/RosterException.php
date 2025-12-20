<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Base exception class for all Roster package exceptions.
 *
 * Provides consistent structure for exception handling, error reporting,
 * and debugging across the entire package ecosystem.
 */
abstract class RosterException extends InvalidArgumentException
{
    /**
     * Create a new RosterException instance.
     *
     * @param string $type Unique identifier for the exception type
     * @param string $message Human-readable error message
     * @param array $context Additional context data for debugging
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception in the chain
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
     * Get the unique identifier for this exception type.
     *
     * @return string Exception type identifier
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get additional context data associated with the exception.
     *
     * @return array Contextual information for debugging
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
