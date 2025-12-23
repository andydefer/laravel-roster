<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Models\Availability;

class AvailabilityChecker
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository
    ) {}

    /**
     * Check if the schedulable resource is available at a specific datetime.
     *
     * @param Model $model The schedulable resource (user, equipment, room, etc.)
     * @param Carbon $datetime The datetime to check availability for
     * @return bool True if the resource is available at the given datetime
     */
    public function isAvailableAt(Model $model, Carbon $datetime): bool
    {
        return $this->availabilityRepository->isAvailableAt($model, $datetime);
    }

    /**
     * Check if the schedulable resource is available for a continuous time period.
     *
     * @param Model $model The schedulable resource
     * @param Carbon $start Start of the time period
     * @param Carbon $end End of the time period
     * @param string|null $type Optional availability type filter
     * @return bool True if the resource is available for the entire period
     * @throws InvalidArgumentException If the time range is invalid
     */
    public function isAvailableForPeriod(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {

        $availability = $this->availabilityRepository->findForTimeSlot($model, $start, $end, $type);

        return $availability instanceof Availability;
    }
}
