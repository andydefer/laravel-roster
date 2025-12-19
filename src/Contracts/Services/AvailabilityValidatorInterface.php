<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Models\Availability;

interface AvailabilityValidatorInterface
{
    /**
     * Validate basic availability data.
     *
     * @param array<string, mixed> $data
     */
    public function validateBasicData(array $data): void;

    /**
     * Check if there is an overlap with existing availabilities.
     *
     * @param Model $model
     * @param array<string, mixed> $data
     * @param int|null $exceptId
     */
    public function hasOverlapping(Model $model, array $data, ?int $exceptId = null): bool;

    /**
     * Check if two availabilities overlap in time and date ranges.
     */
    public function overlaps(
        Availability $availability,
        Carbon $newStartTime,
        Carbon $newEndTime,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool;

    /**
     * Check if two time ranges overlap.
     */
    public function timeOverlaps(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool;

    /**
     * Determine if two availabilities are adjacent (touching).
     */
    public function areAdjacent(Availability $first, Availability $second): bool;

    /**
     * Merge two adjacent availabilities.
     *
     * @return array{
     *     type: string,
     *     start_time: string,
     *     end_time: string,
     *     days: array<string>,
     *     start_date: string|null,
     *     end_date: string|null
     * }
     */
    public function mergeAdjacent(Availability $first, Availability $second): array;

    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool;
}
