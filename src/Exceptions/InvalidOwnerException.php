<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Exception thrown when an invalid owner is provided for a model.
 * Specifically used when trying to assign an owner to an Availability model.
 */
final class InvalidOwnerException extends LogicException
{
    /**
     * Creates a new InvalidOwnerException instance for Availability model.
     */
    public static function forAvailability(): self
    {
        return new self(
            'Cannot assign an owner to an Availability model. Availability models cannot have availability_id.'
        );
    }

    /**
     * Creates a new InvalidOwnerException with a custom message.
     */
    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
