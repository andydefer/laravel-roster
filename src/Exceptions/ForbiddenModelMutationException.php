<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Exception thrown when direct model mutation is attempted outside authorized contexts.
 *
 * Ensures that all model modifications go through domain services or facades
 * to maintain data integrity and enforce business rules.
 */
final class ForbiddenModelMutationException extends LogicException
{
    /**
     * Creates exception for unauthorized model mutation attempts.
     *
     * @param string $model Fully qualified model class name
     * @return self Exception instance with descriptive message
     */
    public static function create(string $model): self
    {
        return new self(
            sprintf(
                'Direct mutation of %s is forbidden. Use domain services or facades.',
                class_basename($model)
            )
        );
    }
}
