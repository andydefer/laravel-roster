<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Exception thrown when an owner is required but not provided.
 * Used for non-Availability models that must be associated with an Availability.
 */
final class MissingOwnerException extends LogicException
{
    /**
     * Creates a new MissingOwnerException instance.
     */
    public static function create(string $modelClass): self
    {
        $modelName = class_basename($modelClass);
        return new self(
            sprintf('Owner is required for %s model. %s must be associated with an Availability.', $modelName, $modelName)
        );
    }
}
