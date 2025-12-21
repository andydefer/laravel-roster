<?php

declare(strict_types=1);

namespace Roster\Services\Core\Components;

use Roster\Exceptions\Messages\ErrorMessageFactory;
use Roster\Exceptions\ValidationException;

trait ExceptionHandler
{
    /**
     * Throw a not found exception.
     *
     * @throws ValidationException
     */
    protected function throwNotFoundException(): void
    {
        throw ValidationException::withMessage(
            message: ErrorMessageFactory::notFound(entity: $this->getEntityType())
        );
    }

    /**
     * Throw an overlap exception.
     *
     * @throws ValidationException
     */
    protected function throwOverlapException(): void
    {
        throw ValidationException::withMessage(
            message: ErrorMessageFactory::overlap(entity: $this->getEntityType())
        );
    }

    /**
     * Throw a minimum duration exception.
     *
     * @param int $minutes The minimum required minutes
     * @throws ValidationException
     */
    protected function throwMinimumDurationException(int $minutes): void
    {
        throw ValidationException::withMessage(
            message: ErrorMessageFactory::minimumDuration(
                entity: $this->getEntityType(),
                minutes: $minutes
            )
        );
    }
}
