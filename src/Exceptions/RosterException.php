<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use InvalidArgumentException;

/**
 * Base exception for the Roster package.
 */
abstract class RosterException extends InvalidArgumentException
{
    public function __construct(
        protected string $type,
        string $message = '',
        protected array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
