<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

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
