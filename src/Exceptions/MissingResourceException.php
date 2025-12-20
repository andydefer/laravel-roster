<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\MissingResourceType;
use Throwable;

/**
 * Exception thrown when a required parent resource is missing.
 */
class MissingResourceException extends RosterException
{
    /**
     * Create a new MissingResourceException instance.
     *
     * @param  MissingResourceType  $type  Type of missing resource
     * @param  array  $context  Additional context about the missing resource
     * @param  string  $message  Custom error message
     * @param  int  $code  Error code
     * @param  Throwable|null  $previous  Previous exception
     */
    public function __construct(
        MissingResourceType $type,
        array $context = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: $type->getDefaultMessage();

        parent::__construct(
            type: $type->value,
            message: $message,
            context: $context,
            code: $code,
            previous: $previous
        );
    }

    /**
     * Get the missing resource type enum.
     *
     * @return MissingResourceType The type of missing resource
     */
    public function getMissingResourceType(): MissingResourceType
    {
        return MissingResourceType::from($this->getType());
    }
}
