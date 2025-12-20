<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface SlotFinderInterface
{
    /**
     * Find the next available slot for a schedulable entity.
     *
     * @param  Model  $model  Schedulable model instance
     * @param  int  $durationMinutes  Required slot duration in minutes
     * @param  string|null  $type  Optional availability type filter
     * @param  bool  $returnStartOnly  Return only the start time if true
     * @return array|Carbon|null Slot details array, start time, or null if none
     */
    public function findNextSlot(
        Model $model,
        int $durationMinutes,
        ?string $type = null,
        bool $returnStartOnly = false
    ): array|Carbon|null;

    /**
     * Find available slots in a given period.
     *
     * @param  Model  $model  Schedulable model instance
     * @param  Carbon  $startDate  Period start date
     * @param  Carbon  $endDate  Period end date
     * @param  int  $durationMinutes  Slot duration in minutes
     * @param  int  $intervalMinutes  Interval between slot starts in minutes
     * @param  string|null  $type  Optional availability type filter
     * @return array<array<string, mixed>> Array of available slots
     */
    public function findSlotsInPeriod(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array;

    /**
     * Find the first available continuous period of specified duration.
     *
     * @param  Model  $model  Schedulable model instance
     * @param  Carbon  $startDate  Search start date
     * @param  Carbon  $endDate  Search end date
     * @param  int  $durationMinutes  Required period duration in minutes
     * @param  string|null  $type  Optional availability type filter
     * @return array|null Period details or null if none found
     */
    public function findFirstAvailablePeriod(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array;

    /**
     * Check if an entire time period is available without interruptions.
     *
     * @param  Model  $model  Schedulable model instance
     * @param  Carbon  $start  Period start datetime
     * @param  Carbon  $end  Period end datetime
     * @param  string|null  $type  Optional availability type filter
     * @return bool True if the entire period is available
     */
    public function isPeriodAvailable(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;

    /**
     * Check if any availability exists within a time period.
     *
     * @param  Model  $model  Schedulable model instance
     * @param  Carbon  $start  Period start datetime
     * @param  Carbon  $end  Period end datetime
     * @param  string|null  $type  Optional availability type filter
     * @return bool True if any availability exists in the period
     */
    public function hasAnyAvailabilityInPeriod(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;

    /**
     * Calculate available time slots between impediments.
     *
     * Takes a collection of impediments and returns the free time slots
     * between them within the specified period.
     *
     * @param  Carbon  $start  Period start time
     * @param  Carbon  $end  Period end time
     * @param  Collection  $impediments  Collection of impediments to consider
     * @return Collection<int, array<string, mixed>> Available time slots
     */
    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection;
}
