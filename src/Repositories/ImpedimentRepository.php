<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Models\Impediment;

class ImpedimentRepository extends AbstractRepository implements ImpedimentRepositoryInterface
{
    /**
     * Find impediments by availability with optional time range.
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
     * Get future impediments for an availability.
     */
    public function getFutureImpediments(int $availabilityId, Carbon $from): Collection
    {
        return Impediment::where('availability_id', $availabilityId)
            ->where('end_datetime', '>=', $from)
            ->orderBy('start_datetime')
            ->get();
    }
}
