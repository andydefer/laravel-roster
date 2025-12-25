<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Roster\Models\Impediment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\CrudInterface;
use Roster\Contracts\RepositoryInterface;

/**
 * Interface for Impediment repository implementations.
 */
interface ImpedimentRepositoryInterface extends RepositoryInterface
{
    /**
     * Check if a time slot has overlapping impediments.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param int|null $excludeId Impediment ID to exclude
     * @return bool True if overlapping impediments exist
     */
    public function hasOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool;

    /**
     * Find impediments for a time slot.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @return Collection<int, Impediment>
     */
    public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection;

    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection;
}
