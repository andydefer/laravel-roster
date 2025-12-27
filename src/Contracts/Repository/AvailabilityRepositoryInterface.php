<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;

interface AvailabilityRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a query builder for availabilities of a schedulable resource.
     *
     * @param Model $model The schedulable resource model
     * @param string|null $type Optional availability type filter
     * @return Builder<Availability> Query builder for availabilities
     */
    public function findForSchedulable(Model $model, ?string $type = null): Builder;

    /**
     * Get all availabilities for a schedulable resource within a date range.
     *
     * @param Model $model The schedulable resource model
     * @param Carbon $start Start date of the range
     * @param Carbon $end End date of the range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities
     */
    public function getForDateRange(Model $model, Carbon $start, Carbon $end, ?string $type = null): Collection;

    /**
     * Find a specific availability that covers a time slot.
     *
     * @param Model $model The schedulable resource model
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null The availability covering the slot, or null if not found
     */
    public function getAvailabilityForTimeSlot(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;

    /**
     * Get all availabilities for a specific date.
     *
     * @param Model $schedulable The schedulable resource model
     * @param Carbon $date The date to check
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the date
     */
    public function getForDate(Model $schedulable, Carbon $date, ?string $type = null): Collection;

    /**
     * Check if an availability is valid for a specific date.
     *
     * @param Availability $availability The availability to check
     * @param Carbon $date The date to validate against
     * @return bool True if the availability is valid for the date
     */
    public function isAvailableOnDate(Availability $availability, Carbon $date): bool;

    /**
     * Find an availability for a time slot with conflict detection information.
     *
     * @param Model $model The schedulable resource model
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null The availability with conflict information, or null if not found
     */
    public function findForTimeSlotWithConflictInfo(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
}
