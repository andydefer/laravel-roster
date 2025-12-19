<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Repository contract for managing impediment records.
 *
 * Provides methods for querying impediments that block or restrict availability time slots.
 */
interface ImpedimentRepositoryInterface
{
    /**
     * Find impediments for a specific time slot within an availability.
     *
     * @param int $availabilityId Parent availability ID
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @return Collection<int, object> Collection of impediments in the time slot
     */
    public function findForTimeSlot(
        int $availabilityId,
        Carbon $start,
        Carbon $end
    ): Collection;

    /**
     * Check if a time slot has overlapping impediments.
     *
     * @param int $availabilityId Parent availability ID
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param int|null $excludeId Optional impediment ID to exclude from check
     * @return bool True if overlapping impediments exist
     */
    public function hasOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool;
}
