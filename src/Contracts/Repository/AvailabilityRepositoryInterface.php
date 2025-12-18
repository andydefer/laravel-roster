<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;

interface AvailabilityRepositoryInterface
{
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
}
