<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Throwable;

/**
 * Exception thrown when data validation fails.
 */
class ValidationException extends RosterException
{
    public function __construct(
        ValidationType $type,
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: $type->getDefaultMessage();
        parent::__construct($type->value, $message, $context, $code, $previous);
    }

    public static function withMessage(string $message): self
    {
        return new self(
            ValidationType::CUSTOM,
            [],
            $message
        );
    }
}
