<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;

interface AvailabilityRepositoryInterface
{
    /**
     * Create a new availability.
     */
    public function create(array $data): Availability;

    /**
     * Update an existing availability.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete an availability.
     */
    public function delete(int $id): bool;

    /**
     * Find availability by ID.
     */
    public function findById(int $id): ?Availability;

    /**
     * Find availability for a time slot.
     */
    public function findForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability;

    /**
     * Get availabilities for a specific date.
     *
     * @return Collection<int, Availability>
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection;

    /**
     * Get all availabilities for a schedulable.
     *
     * @return Collection<int, Availability>
     */
    public function getAllForSchedulable(
        Model $model,
        ?string $type = null,
        ?string $day = null
    ): Collection;

    /**
     * Check if schedulable is available at specific datetime.
     */
    public function isAvailableAt(
        Model $model,
        Carbon $datetime
    ): bool;

    /**
     * Find overlapping availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): Collection;

    /**
     * Check if time ranges overlap.
     */
    public function timeRangesOverlap(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool;

    /**
     * Check if date ranges overlap.
     */
    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool;

    public function getForDateRange(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection;

    /**
     * Delete multiple availabilities by IDs.
     */
    public function deleteMultiple(array $ids): bool;

    /**
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findAdjacentAvailabilities(
        Model $model,
        array $data
    ): Collection;

    public function findForTimeSlotWithOverlaps(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability;

    /**
     * Apply filters to query.
     *
     * @return Builder
     */
    public function applyFilters(
        Model $model,
        array $filters = []
    );
}
