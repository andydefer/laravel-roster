<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;

/**
 * Repository contract for managing availability records.
 *
 * Provides methods for creating, updating, deleting, and querying availability data
 * for schedulable resources (e.g., employees, rooms, equipment).
 */
interface AvailabilityRepositoryInterface
{
    /**
     * Create a new availability record.
     *
     * @param array<string, mixed> $data Availability data
     * @return Availability Created availability instance
     */
    public function create(array $data): Availability;

    /**
     * Update an existing availability record.
     *
     * @param int $id Availability ID
     * @param array<string, mixed> $data Updated availability data
     * @return bool True if update successful, false otherwise
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete an availability record.
     *
     * @param int $id Availability ID
     * @return bool True if deletion successful, false otherwise
     */
    public function delete(int $id): bool;

    /**
     * Find an availability record by its ID.
     *
     * @param int $id Availability ID
     * @return Availability|null Availability instance or null if not found
     */
    public function findById(int $id): ?Availability;

    /**
     * Find availability for a specific time slot.
     *
     * @param Model $model The schedulable resource
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null Matching availability or null
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
     * @param Model $model The schedulable resource
     * @param Carbon $date Target date
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the date
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection;

    /**
     * Get all availabilities for a schedulable resource.
     *
     * @param Model $model The schedulable resource
     * @param string|null $type Optional availability type filter
     * @param string|null $day Optional day of week filter
     * @return Collection<int, Availability> Collection of all availabilities
     */
    public function getAllForSchedulable(
        Model $model,
        ?string $type = null,
        ?string $day = null
    ): Collection;

    /**
     * Check if a schedulable resource is available at a specific datetime.
     *
     * @param Model $model The schedulable resource
     * @param Carbon $datetime Datetime to check
     * @return bool True if available at the given datetime
     */
    public function isAvailableAt(
        Model $model,
        Carbon $datetime
    ): bool;

    /**
     * Find availabilities that overlap with the given time range.
     *
     * @param Model $model The schedulable resource
     * @param array<string, mixed> $availabilityData New availability data
     * @param int|null $exceptId Optional availability ID to exclude from search
     * @return Collection<int, Availability> Collection of overlapping availabilities
     */
    public function findOverlapping(
        Model $model,
        array $availabilityData,
        ?int $exceptId = null
    ): Collection;

    /**
     * Check if two time ranges overlap.
     *
     * @param Carbon $existingStart Existing time range start
     * @param Carbon $existingEnd Existing time range end
     * @param Carbon $newStart New time range start
     * @param Carbon $newEnd New time range end
     * @return bool True if time ranges overlap
     */
    public function timeRangesOverlap(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool;

    /**
     * Check if two date ranges overlap.
     *
     * @param Carbon|null $existingStartDate Existing date range start (nullable for open-ended)
     * @param Carbon|null $existingEndDate Existing date range end (nullable for open-ended)
     * @param Carbon|null $newStartDate New date range start (nullable for open-ended)
     * @param Carbon|null $newEndDate New date range end (nullable for open-ended)
     * @return bool True if date ranges overlap
     */
    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool;

    /**
     * Get availabilities for a date range.
     *
     * @param Model $model The schedulable resource
     * @param Carbon $start Start date of the range
     * @param Carbon $end End date of the range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities in the date range
     */
    public function getForDateRange(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection;

    /**
     * Delete multiple availability records by IDs.
     *
     * @param array<int> $ids Array of availability IDs to delete
     * @return bool True if all deletions successful, false otherwise
     */
    public function deleteMultiple(array $ids): bool;

    /**
     * Find availabilities adjacent to the given time range.
     *
     * @param Model $model The schedulable resource
     * @param array<string, mixed> $availabilityData New availability data
     * @return Collection<int, Availability> Collection of adjacent availabilities
     */
    public function findAdjacentAvailabilities(
        Model $model,
        array $availabilityData
    ): Collection;

    /**
     * Find availability for a time slot, including those with partial overlaps.
     *
     * @param Model $model The schedulable resource
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null Matching availability or null
     */
    public function findForTimeSlotWithPartialOverlaps(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability;

    /**
     * Apply filters to availability query builder.
     *
     * @param Model $model The schedulable resource
     * @param array<string, mixed> $filters Query filters
     * @return Builder Eloquent query builder with filters applied
     */
    public function applyFilters(
        Model $model,
        array $filters = []
    ): Builder;

    /**
     * Check if an availability applies to a specific date.
     *
     * @param Availability $availability Availability to check
     * @param Carbon $date Date to verify
     * @return bool True if availability applies to the date
     */
    public function isAvailabilityValidForDate(Availability $availability, Carbon $date): bool;

    /**
     * Load availabilities with schedule conflicts for a time range.
     *
     * @param Model $model The schedulable resource
     * @param Carbon $start Start of time range
     * @param Carbon $end End of time range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities with conflict information
     */
    public function getAvailabilitiesWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection;

    /**
     * Filter availabilities collection for a specific date.
     *
     * @param Collection<int, Availability> $availabilities Collection to filter
     * @param Carbon $date Target date
     * @return Collection<int, Availability> Filtered collection for the date
     */
    public function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection;
}
