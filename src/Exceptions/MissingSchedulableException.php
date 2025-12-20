<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;
use Roster\Exceptions\Enums\MissingResourceType;

/**
 * Exception thrown when a schedulable service is used without a parent model.
 *
 * This exception is thrown when trying to execute operations on a schedulable service
 * before specifying which schedulable entity to scope the operations to.
 */
final class MissingSchedulableException extends LogicException
{
    /**
     * Creates a new MissingSchedulableException instance.
     */
    public static function create(): self
    {
        return new self(
            MissingResourceType::MISSING_SCHEDULABLE->getDefaultMessage()
        );
    }
}
