<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a service is used directly without going through a helper function.
 *
 * This exception ensures that service instantiation and usage follow the package's
 * intended patterns for context management and dependency injection.
 */
final class DirectServiceUsageException extends RuntimeException
{
    /**
     * Creates a new exception instance for unauthorized direct service usage.
     *
     * @param string $serviceClass Fully qualified class name of the misused service
     * @return self Configured exception instance
     */
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
