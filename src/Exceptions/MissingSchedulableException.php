<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Exception thrown when a schedulable service is used without a parent model.
 *
 * This exception is thrown when trying to execute operations on a schedulable service
 * before specifying which schedulable entity to scope the operations to.
 */
final class MissingSchedulableException extends LogicException
{
    /**
     * Create a new MissingSchedulableException instance.
     *
     * @return self
     */
    public static function create(): self
    {
        return new self(
            'No schedulable specified. Use the for() method before executing the query.'
        );
    }
}
