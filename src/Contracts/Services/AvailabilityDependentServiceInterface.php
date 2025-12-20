<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Roster\Models\Availability;

interface AvailabilityDependentServiceInterface
{
    /**
     * Create a new entity with explicit availability.
     *
     * @param  Availability  $availability  The availability to link to
     * @param  array<string, mixed>  $data  Entity data
     * @return mixed Created entity
     */
    public function create(Availability $availability, array $data): mixed;
}
