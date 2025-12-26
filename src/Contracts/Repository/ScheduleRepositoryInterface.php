<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\RepositoryInterface;

interface ScheduleRepositoryInterface extends RepositoryInterface
{
    /**
     * Find schedules for a given availability.
     *
     * This method MUST NOT contain business logic.
     * It only builds a query.
     *
     *
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder;

    /**
     * Get future schedules for a given availability.
     *
     *
     */
    public function getFutureSchedules(
        int $availabilityId,
        Carbon $from
    ): Collection;

    /**
     * Get schedules for a schedulable within a date range.
     *
     * @param array<string, mixed> $filters
     *
     */
    public function getForDateRange(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection;
}
