<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Models\Impediment;

/**
 * Repository for Impediment entity data access operations.
 *
 * Provides methods for querying and retrieving impediment data
 * with support for availability-based filtering and temporal constraints.
 */
class ImpedimentRepository extends AbstractRepository implements ImpedimentRepositoryInterface
{
    /**
     * Finds impediments by availability with optional time range constraints.
     *
     * @param int $availabilityId Availability entity identifier
     * @param Carbon|null $start Start time filter (inclusive)
     * @param Carbon|null $end End time filter (inclusive)
     * @return Builder Eloquent query builder for further refinement
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder {
        $query = Impediment::where('availability_id', $availabilityId);

        if ($start instanceof Carbon && $end instanceof Carbon) {
            $query->whereBetween('start_datetime', [$start, $end]);
        } elseif ($start instanceof Carbon) {
            $query->where('start_datetime', '>=', $start);
        } elseif ($end instanceof Carbon) {
            $query->where('end_datetime', '<=', $end);
        }

        return $query;
    }

    /**
     * Retrieves future impediments for an availability starting from a specific date.
     *
     * @param int $availabilityId Availability entity identifier
     * @param Carbon $from Starting date for future impediments (inclusive)
     * @return Collection<Impediment> Future impediments ordered by start time
     */
    public function getFutureImpediments(int $availabilityId, Carbon $from): Collection
    {
        return Impediment::where('availability_id', $availabilityId)
            ->where('end_datetime', '>=', $from)
            ->orderBy('start_datetime')
            ->get();
    }
}
