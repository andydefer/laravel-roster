<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Models\Availability;
use Roster\Traits\DateRangeOverlapTrait;

/**
 * Validates availability data and checks for overlaps between time ranges.
 *
 * This service ensures that availability records do not conflict with each other
 * and provides validation for basic availability data integrity.
 */
class AvailabilityValidator implements AvailabilityValidatorInterface
{
    use DateRangeOverlapTrait;

    /**
     * Validate basic availability data.
     *
     * @param  array<string, mixed>  $data  Availability data containing days, times, and dates
     * @throws InvalidArgumentException When data validation fails
     */
    public function validateBasicData(array $data): void
    {
        $this->validateDays($data);
        $this->validateTimeRange($data);
        $this->validateDateRange($data);
    }

    /**
     * Check for overlapping availabilities.
     *
     * @param  Model  $schedulable  The schedulable model (e.g., User, Team)
     * @param  array<string, mixed>  $data  New availability data
     * @param  int|null  $exceptId  Availability ID to exclude from check (for updates)
     * @return bool True if overlapping availability exists
     */
    public function hasOverlapping(
        Model $schedulable,
        array $data,
        ?int $exceptId = null
    ): bool {
        $query = $this->buildOverlapQuery($schedulable, $data, $exceptId);

        return $query->exists();
    }

    /**
     * Check if a time range overlaps with an existing availability.
     *
     * @param  Availability  $availability  Existing availability to check against
     * @param  Carbon  $newStartTime  New availability start time
     * @param  Carbon  $newEndTime  New availability end time
     * @param  Carbon|null  $newStartDate  New availability start date
     * @param  Carbon|null  $newEndDate  New availability end date
     * @return bool True if time ranges overlap
     */
    public function overlaps(
        Availability $availability,
        Carbon $newStartTime,
        Carbon $newEndTime,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool {
        if (! $this->timeOverlaps(
            $availability->start_time,
            $availability->end_time,
            $newStartTime,
            $newEndTime
        )) {
            return false;
        }

        return $this->dateRangesOverlap(
            $availability->start_date,
            $availability->end_date,
            $newStartDate,
            $newEndDate
        );
    }

    /**
     * Check if two time ranges overlap.
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
    ): bool {
        return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
    }

    /**
     * Check if two availabilities are adjacent (touch each other).
     *
     * Two availabilities are adjacent if they share common properties
     * and their time ranges touch exactly.
     *
     * @param  Availability  $first  First availability
     * @param  Availability  $second  Second availability
     * @return bool True if availabilities are adjacent
     */
    public function areAdjacent(
        Availability $first,
        Availability $second
    ): bool {
        return match (true) {
            ! $this->areSameSchedulable($first, $second) => false,
            ! $this->haveCommonDays($first, $second) => false,
            $first->type !== $second->type => false,
            ! $this->dateRangesOverlap(
                $first->start_date,
                $first->end_date,
                $second->start_date,
                $second->end_date
            ) => false,
            default => $this->timeRangesTouch($first, $second),
        };
    }


    /**
     * Merge two adjacent availabilities into a single availability data array.
     *
     * @param  Availability  $first  First availability
     * @param  Availability  $second  Second availability
     * @return array{
     *     type: string,
     *     start_time: string,
     *     end_time: string,
     *     days: array<string>,
     *     start_date: string|null,
     *     end_date: string|null
     * } Merged availability data
     * @throws InvalidArgumentException When availabilities are not adjacent
     */
    public function mergeAdjacent(
        Availability $first,
        Availability $second
    ): array {
        if (! $this->areAdjacent($first, $second)) {
            throw new InvalidArgumentException('Cannot merge non-adjacent availabilities');
        }

        return [
            'type' => $first->type,
            'start_time' => $this->calculateMergedStartTime($first, $second)->format('H:i:s'),
            'end_time' => $this->calculateMergedEndTime($first, $second)->format('H:i:s'),
            'days' => $this->mergeDays($first, $second),
            'start_date' => $this->calculateMergedStartDate($first, $second)?->format('Y-m-d'),
            'end_date' => $this->calculateMergedEndDate($first, $second)?->format('Y-m-d'),
        ];
    }

    /**
     * Validate that at least one day is specified.
     */
    private function validateDays(array $data): void
    {
        if (isset($data['days']) && empty($data['days'])) {
            throw new InvalidArgumentException('At least one day must be specified');
        }
    }

    /**
     * Validate time range (end time must be after start time).
     */
    private function validateTimeRange(array $data): void
    {
        if (! isset($data['start_time']) || ! isset($data['end_time'])) {
            return;
        }

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        if ($endTime->lte($startTime)) {
            throw new InvalidArgumentException('End time must be after start time');
        }
    }

    /**
     * Validate date range (end date must be on or after start date).
     */
    private function validateDateRange(array $data): void
    {
        if (! isset($data['start_date']) || ! isset($data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw new InvalidArgumentException('End date must be after or equal to start date');
        }
    }

    /**
     * Build query to check for overlapping availabilities.
     */
    private function buildOverlapQuery(Model $schedulable, array $data, ?int $exceptId = null)
    {
        $query = Availability::where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable))
            ->where(function ($query) use ($data): void {
                $this->addDaysFilter($query, $data);
            })
            ->where(function ($query) use ($data): void {
                $this->addTimeRangeFilter($query, $data);
            });

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query;
    }

    /**
     * Add days filter to query.
     */
    private function addDaysFilter($query, array $data): void
    {
        if (empty($data['days'])) {
            return;
        }

        $query->where(function ($query) use ($data): void {
            foreach ($data['days'] as $day) {
                $query->orWhereJsonContains('days', $day);
            }
        });
    }

    /**
     * Add time range filter to query.
     */
    private function addTimeRangeFilter($query, array $data): void
    {
        if (! isset($data['start_time']) || ! isset($data['end_time'])) {
            return;
        }

        $startTime = Carbon::parse($data['start_time'])->format('H:i:s');
        $endTime = Carbon::parse($data['end_time'])->format('H:i:s');

        $query->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    /**
     * Check if two availabilities belong to the same schedulable.
     */
    private function areSameSchedulable(Availability $first, Availability $second): bool
    {
        return $first->schedulable_id === $second->schedulable_id &&
            $first->schedulable_type === $second->schedulable_type;
    }

    /**
     * Check if two availabilities have at least one common day.
     */
    private function haveCommonDays(Availability $first, Availability $second): bool
    {
        return ! empty(array_intersect($first->days, $second->days));
    }

    /**
     * Check if two time ranges touch exactly.
     */
    private function timeRangesTouch(Availability $first, Availability $second): bool
    {
        return $first->end_time->eq($second->start_time) ||
            $second->end_time->eq($first->start_time);
    }

    /**
     * Calculate merged start time from two availabilities.
     */
    private function calculateMergedStartTime(Availability $first, Availability $second): Carbon
    {
        $startTimestamp = min($first->start_time->timestamp, $second->start_time->timestamp);
        return Carbon::createFromTimestamp($startTimestamp);
    }

    /**
     * Calculate merged end time from two availabilities.
     */
    private function calculateMergedEndTime(Availability $first, Availability $second): Carbon
    {
        $endTimestamp = max($first->end_time->timestamp, $second->end_time->timestamp);
        return Carbon::createFromTimestamp($endTimestamp);
    }

    /**
     * Merge days from two availabilities.
     *
     * @return array<string>
     */
    private function mergeDays(Availability $first, Availability $second): array
    {
        return array_values(array_unique(array_merge($first->days, $second->days)));
    }

    /**
     * Calculate merged start date from two availabilities.
     */
    private function calculateMergedStartDate(Availability $first, Availability $second): ?Carbon
    {
        if ($first->start_date === null && $second->start_date === null) {
            return null;
        }

        $firstStart = $first->start_date ? $first->start_date->timestamp : PHP_INT_MAX;
        $secondStart = $second->start_date ? $second->start_date->timestamp : PHP_INT_MAX;

        return Carbon::createFromTimestamp(min($firstStart, $secondStart));
    }

    /**
     * Calculate merged end date from two availabilities.
     */
    private function calculateMergedEndDate(Availability $first, Availability $second): ?Carbon
    {
        if ($first->end_date === null && $second->end_date === null) {
            return null;
        }

        $firstEnd = $first->end_date ? $first->end_date->timestamp : PHP_INT_MIN;
        $secondEnd = $second->end_date ? $second->end_date->timestamp : PHP_INT_MIN;

        return Carbon::createFromTimestamp(max($firstEnd, $secondEnd));
    }
}
