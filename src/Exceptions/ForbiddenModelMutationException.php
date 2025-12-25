<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Thrown when a model mutation is attempted outside
 * of an authorized mutation context.
 */
final class ForbiddenModelMutationException extends LogicException
{
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
