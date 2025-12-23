<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Impediment;

/**
 * Interface for Impediment repository implementations.
 */
interface ImpedimentRepositoryInterface
{
    /**
     * Create a new impediment.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Impediment;

    /**
     * Update an existing impediment.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete an impediment.
     */
    public function delete(int $id): bool;

    /**
     * Find an impediment by ID.
     */
    public function find(int $id): ?Impediment;

    /**
     * Get all impediments.
     *
     * @return Collection<int, Impediment>
     */
    public function getAll(): Collection;

    /**
     * Find impediments for a specific time slot.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @return Collection<int, Impediment>
     */
    public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection;

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
     * Find overlapping impediments with a time range.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time range
     * @param Carbon $end End of time range
     * @param int|null $excludeId Impediment ID to exclude
     * @return Collection<int, Impediment>
     */
    public function findOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): Collection;
}
