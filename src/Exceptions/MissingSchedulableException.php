<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

/**
 * Thrown when a schedulable service is used without a parent model.
 */
final class MissingSchedulableException extends LogicException
{
    public static function create(): self
    {
        return new self(
            'No schedulable specified. Use the for() method before executing the query.'
        );
    }
}
