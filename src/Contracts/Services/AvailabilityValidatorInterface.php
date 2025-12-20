<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;

interface AvailabilityValidatorInterface
{
    /**
     * Validate basic availability data structure and required fields.
     *
     * @param  array<string, mixed>  $data  Availability data to validate
     *
     * @throws ValidationException When validation fails
     */
    public function validateBasicData(array $data): void;

    /**
     * Check if new availability overlaps with existing ones.
     *
     * @param  Model  $model  Schedulable model instance
     * @param  array<string, mixed>  $data  New availability data
     * @param  int|null  $exceptId  Availability ID to exclude from overlap check
     * @return bool True if overlapping availability exists
     */
    public function hasOverlapping(Model $model, array $data, ?int $exceptId = null): bool;

    /**
     * Determine if two availability periods overlap in time and date ranges.
     *
     * @param  Availability  $availability  Existing availability instance
     * @param  Carbon  $newStartTime  New availability start time
     * @param  Carbon  $newEndTime  New availability end time
     * @param  Carbon|null  $newStartDate  New availability start date (null for indefinite)
     * @param  Carbon|null  $newEndDate  New availability end date (null for indefinite)
     * @return bool True if the periods overlap
     */
    public function overlaps(
        Availability $availability,
        Carbon $newStartTime,
        Carbon $newEndTime,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool;

    /**
     * Check if two time ranges overlap within a single day.
     *
     * @param  Carbon  $existingStart  Existing time range start
     * @param  Carbon  $existingEnd  Existing time range end
     * @param  Carbon  $newStart  New time range start
     * @param  Carbon  $newEnd  New time range end
     * @return bool True if time ranges overlap
     */
    public function timeOverlaps(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool;

    /**
     * Determine if two availabilities are adjacent (touching without overlap).
     *
     * Adjacent availabilities share a boundary in time and can be merged.
     *
     * @param  Availability  $first  First availability instance
     * @param  Availability  $second  Second availability instance
     * @return bool True if availabilities are adjacent
     */
    public function areAdjacent(Availability $first, Availability $second): bool;

    /**
     * Merge two adjacent availabilities into a single availability data structure.
     *
     * @param  Availability  $first  First availability to merge
     * @param  Availability  $second  Second availability to merge
     * @return array{
     *     type: string,
     *     start_time: string,
     *     end_time: string,
     *     days: array<string>,
     *     start_date: string|null,
     *     end_date: string|null
     * } Merged availability data
     */
    public function mergeAdjacent(Availability $first, Availability $second): array;

    /**
     * Check if two date ranges overlap.
     *
     * Handles open-ended ranges (null values) correctly.
     *
     * @param  Carbon|null  $existingStartDate  Existing start date
     * @param  Carbon|null  $existingEndDate  Existing end date
     * @param  Carbon|null  $newStartDate  New start date
     * @param  Carbon|null  $newEndDate  New end date
     * @return bool True if date ranges overlap
     */
    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool;
}
