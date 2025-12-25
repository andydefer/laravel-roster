<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\CrudInterface;
use Roster\Contracts\RepositoryInterface;
use Roster\Models\Availability;

/**
 * Repository contract for managing availability records.
 *
 * Provides methods for creating, updating, deleting, and querying availability data
 * for schedulable resources (e.g., employees, rooms, equipment).
 */
interface AvailabilityRepositoryInterface extends RepositoryInterface
{

    /**
     * Find availabilities for a specific schedulable entity.
     *
     * @param Model $model The schedulable entity
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the schedulable
     */
    public function findForSchedulable(Model $model, ?string $type = null): Collection;

    /**
     * Find availability for a specific time slot.
     *
     * @param  Model  $model  The schedulable resource
     * @param  Carbon  $start  Start time of the slot
     * @param  Carbon  $end  End time of the slot
     * @param  string|null  $type  Optional availability type filter
     * @return Availability|null Matching availability or null
     */
    public function getAvailabilityForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability;

    /**
     * Get availabilities for a specific date.
     *
     * @param  Model  $model  The schedulable resource
     * @param  Carbon  $date  Target date
     * @param  string|null  $type  Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the date
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection;




    /**
     * Find availabilities that overlap with the given time range.
     *
     * @param  Model  $model  The schedulable resource
     * @param  array<string, mixed>  $availabilityData  New availability data
     * @param  int|null  $exceptId  Optional availability ID to exclude from search
     * @return Collection<int, Availability> Collection of overlapping availabilities
     */
    public function findOverlapping(
        Model $model,
        array $availabilityData,
        ?int $exceptId = null
    ): Collection;

    /**
     * Check if two date ranges overlap.
     *
     * @param  Carbon|null  $existingStartDate  Existing date range start (nullable for open-ended)
     * @param  Carbon|null  $existingEndDate  Existing date range end (nullable for open-ended)
     * @param  Carbon|null  $newStartDate  New date range start (nullable for open-ended)
     * @param  Carbon|null  $newEndDate  New date range end (nullable for open-ended)
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
     * @param  Model  $model  The schedulable resource
     * @param  Carbon  $start  Start date of the range
     * @param  Carbon  $end  End date of the range
     * @param  string|null  $type  Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities in the date range
     */
    public function getForDateRange(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection;

    /**
     * Find availability for a time slot, including those with partial overlaps.
     *
     * @param  Model  $model  The schedulable resource
     * @param  Carbon  $start  Start time of the slot
     * @param  Carbon  $end  End time of the slot
     * @param  string|null  $type  Optional availability type filter
     * @return Availability|null Matching availability or null
     */
    public function findForTimeSlotWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability;


    /**
     * Check if an availability applies to a specific date.
     *
     * @param  Availability  $availability  Availability to check
     * @param  Carbon  $date  Date to verify
     * @return bool True if availability applies to the date
     */
    public function isAvailableOnDate(Availability $availability, Carbon $date): bool;

    /**
     * Load availabilities with schedule conflicts for a time range.
     *
     * @param  Model  $model  The schedulable resource
     * @param  Carbon  $start  Start of time range
     * @param  Carbon  $end  End of time range
     * @param  string|null  $type  Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities with conflict information
     */
    public function getAvailabilitiesWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection;
}
