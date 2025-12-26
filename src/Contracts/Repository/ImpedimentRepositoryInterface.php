<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\RepositoryInterface;

interface ImpedimentRepositoryInterface extends RepositoryInterface
{
    /**
     * Build a query for impediments related to a specific availability.
     *
     * @param int $availabilityId The ID of the availability
     * @param Carbon|null $start Optional start date filter
     * @param Carbon|null $end Optional end date filter
     * @return Builder Query builder for impediments
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder;

    /**
     * Get all future impediments for a specific availability.
     *
     * @param int $availabilityId The ID of the availability
     * @param Carbon $from Starting date for future impediments
     * @return Collection<int, \Roster\Models\Impediment> Collection of future impediments
     */
    public function getFutureImpediments(
        int $availabilityId,
        Carbon $from
    ): Collection;
}
