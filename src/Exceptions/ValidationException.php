<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Exceptions\Enums\ValidationType;
use Throwable;

/**
 * Exception thrown when data validation fails.
 *
 * This is a generic validation exception that can be used for various
 * validation failures with specific types defined in ValidationType enum.
 */
class ValidationException extends RosterException
{
    /**
     * Create a new ValidationException instance.
     *
     * @param  ValidationType  $type  Type of validation failure
     * @param  array  $context  Additional context about the validation failure
     * @param  string  $message  Custom error message
     * @param  int  $code  Error code
     * @param  Throwable|null  $previous  Previous exception
     */
    public function __construct(
        ValidationType $type,
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
     * Create a ValidationException with a custom message.
     *
     * This factory method is useful when you need to provide a specific
     * error message that doesn't fit the predefined validation types.
     *
     * @param  string  $message  Custom validation error message
     */
    public static function withMessage(string $message): self
    {
        return new self(
            type: ValidationType::CUSTOM,
            context: [],
            message: $message
        );
    }
}
