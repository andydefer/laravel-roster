<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

interface AvailabilityCheckerInterface
{
    /**
     * Check if a schedulable entity is available at a specific datetime.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $datetime Datetime to check availability
     * @return bool True if available at the specified time
     */
    public function isAvailableAt(Model $model, Carbon $datetime): bool;

    /**
     * Check availability for an entire time period.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $start Period start datetime
     * @param Carbon $end Period end datetime
     * @param string|null $type Optional availability type filter
     * @return bool True if available for the entire period
     */
    public function isAvailableForPeriod(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;

    /**
     * Check if new availability data would overlap with existing ones.
     *
     * @param Model $model Schedulable model instance
     * @param array<string, mixed> $data New availability data
     * @param int|null $exceptId Availability ID to exclude from overlap check
     * @return bool True if overlapping availability exists
     */
    public function hasOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): bool;
}
