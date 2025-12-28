<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use LogicException;

final class InvalidServiceContextException extends LogicException
{
    /**
     * Create exception for invalid service context.
     *
     * @param string $serviceClass The service class name
     * @return self Exception with helpful message
     */
    public static function forService(string $serviceClass): self
    {
        $serviceName = class_basename($serviceClass);

        $message = match (true) {
            str_contains($serviceClass, 'AvailabilityService') =>
            $serviceName . ' requires a valid schedulable context.

' .
                "This usually happens because you are calling the service without providing a schedulable model.\n\n" .
                "How to fix:\n" .
                "- Always use the availability_for() helper with a schedulable model.\n" .
                "- Do not instantiate the service directly; use the helper function.\n\n" .
                "Example:\n" .
                "availability_for(\$schedulable)->create([...]);",

            str_contains($serviceClass, 'ScheduleService') || str_contains($serviceClass, 'ImpedimentService') =>
            $serviceName . ' requires both schedulable and owner context.

' .
                "This usually happens because:\n" .
                "1. You are calling the service without providing an availability as owner.\n" .
                "2. You are using the wrong helper function.\n\n" .
                "How to fix:\n" .
                "- For schedules: use schedule_for(\$availability) where \$availability is an Availability model.\n" .
                "- For impediments: use impediment_for(\$availability) where \$availability is an Availability model.\n" .
                "- Do not use the schedulable directly; pass the availability instance instead.\n\n" .
                "Examples:\n" .
                "schedule_for(\$availability)->create([...]);\n" .
                "impediment_for(\$availability)->create([...]);",

            default =>
            $serviceName . ' requires a valid context.

' .
                "This usually happens because you are using the service incorrectly.\n\n" .
                "How to fix:\n" .
                "- Use the appropriate helper function instead of instantiating the service directly.\n" .
                "- Check that you're passing the correct model type to the helper.\n\n" .
                "Available helpers:\n" .
                "- availability_for(\$schedulable) -> AvailabilityService\n" .
                "- schedule_for(\$availability) -> ScheduleService\n" .
                "- impediment_for(\$availability) -> ImpedimentService"
        };

        return new self($message);
    }
}
