<?php

declare(strict_types=1);

namespace Roster\Exceptions;

/**
 * Exception lancée lorsqu'un service est utilisé directement sans passer par un helper.
 */
final class DirectServiceUsageException extends \RuntimeException
{
    public static function create(string $serviceClass): self
    {
        return new self(
            sprintf(
                'Service %s must be used through a helper function (availability_for, impediment_for, or schedule_for).',
                $serviceClass
            )
        );
    }
}
