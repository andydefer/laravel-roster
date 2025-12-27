<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Exception thrown when an invalid owner is assigned to a model.
 *
 * Prevents assignment of ownership relationships that violate business rules,
 * particularly for Availability models which cannot have owners.
 */
final class InvalidOwnerException extends LogicException
{
    /**
     * Creates exception for invalid Availability model ownership.
     *
     * @return self Exception for Availability owner assignment attempts
     */
    public static function forAvailability(): self
    {
        return new self(
            'Cannot assign an owner to an Availability model. Availability models cannot have availability_id.'
        );
    }

    /**
     * Creates exception with custom error message.
     *
     * @param string $message Custom error description
     * @return self Exception with provided message
     */
    public static function withMessage(string $message): self
    {
        return new self($message);
    }
}
