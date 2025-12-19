<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Support\Carbon;

interface AvailabilityCheckerInterface
{
    /**
     * Check if the schedulable is available at a given time.
     */
    public function isAvailableAt(object $schedulable, Carbon $datetime): bool;

    /**
     * Check availability for a time period.
     */
    public function isAvailableForPeriod(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;

    /**
     * Check if there are overlaps.
     *
     * @param array<string, mixed> $data
     */
    public function hasOverlapping(
        object $schedulable,
        array $data,
        ?int $exceptId = null
    ): bool;
}
