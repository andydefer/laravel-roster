<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Exception thrown when an owner is required but not provided.
 *
 * This exception occurs when attempting to create or manage entities
 * that must be associated with an Availability (e.g., Schedule, Impediment)
 * but the required ownership relationship has not been established.
 */
final class MissingOwnerException extends LogicException
{
    /**
     * Creates a new MissingOwnerException instance.
     *
     * @param string $modelClass The class name of the model requiring an owner
     * @return self Configured exception instance
     */
    public static function create(string $modelClass): self
    {
        $modelName = class_basename($modelClass);

        return new self(
            message: sprintf(
                'Owner is required for %s model. %s must be associated with an Availability.',
                $modelName,
                $modelName
            )
        );
    }
}
