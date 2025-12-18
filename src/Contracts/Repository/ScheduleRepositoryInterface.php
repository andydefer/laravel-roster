<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ScheduleRepositoryInterface
{
    /**
     * Find schedules for a time slot.
     */
    public function findForTimeSlot(
        int $availabilityId,
        Carbon $start,
        Carbon $end
    ): Collection;

    /**
     * Check if a time slot has overlapping schedules.
     */
    public function hasOverlappingSchedule(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool;
}
