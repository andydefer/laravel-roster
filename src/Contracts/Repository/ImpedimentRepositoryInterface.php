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
     * Find impediments for a given availability.
     *
     * This method only builds a query.
     * No business logic must be implemented here.
     *
     *
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder;

    /**
     * Get future impediments for a given availability.
     *
     *
     */
    public function getFutureImpediments(
        int $availabilityId,
        Carbon $from
    ): Collection;
}
