<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

final class InvalidServiceContextException extends LogicException
{
    public static function forService(string $serviceClass): self
    {
        return new self(
            sprintf(
                "%s requires a valid context (schedulable and/or owner).\n\n" .
                    "This usually happens because you are calling the service after changing the context " .
                    "using ->for() or ->owner() without creating a new service instance.\n\n" .
                    "How to fix:\n" .
                    "- Always call ->for(\$schedulable) and ->owner(\$owner) on the Facade before performing any action.\n" .
                    "- Do not reuse a service instance with a previous context; instead, use the new instance returned by ->for() / ->owner().\n\n" .
                    "Example:\n" .
                    "%s::for(\$schedulable)->owner(\$owner)->create(\$data);",
                $serviceClass,
                $serviceClass
            )
        );
    }
}
