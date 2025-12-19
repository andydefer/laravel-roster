<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface SlotFinderInterface
{

    /**
     * Find the next available time slot.
     *
     * @param Model $model The schedulable model
     * @param int $durationMinutes Duration of the slot in minutes
     * @param string|null $type Optional availability type filter
     * @return array|null Array with slot details or null if none found
     */
    public function findNextAvailableSlot(
        Model $model,
        int $durationMinutes,
        ?string $type = null
    ): ?array;

    /**
     * Find all available slots in a period.
     *
     * @param Model $model The schedulable model
     * @param Carbon $startDate Start date of the period
     * @param Carbon $endDate End date of the period
     * @param int $durationMinutes Duration of each slot in minutes
     * @param string|null $type Optional availability type filter
     * @return array Array of available slots
     */
    public function findAvailableSlots(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array;

    /**
     * Get the first available period of a specific duration.
     *
     * @param Model $model The schedulable model
     * @param Carbon $startDate Start date to search from
     * @param Carbon $endDate End date to search to
     * @param int $durationMinutes Duration of the period in minutes
     * @param string|null $type Optional availability type filter
     * @return array|null Array with period details or null if none found
     */
    public function findFirstAvailablePeriod(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array;

    /**
     * Check if a time period is completely available.
     *
     * @param Model $model The schedulable model
     * @param Carbon $start Start of the period
     * @param Carbon $end End of the period
     * @param string|null $type Optional availability type filter
     * @return bool True if the entire period is available
     */
    public function isPeriodAvailable(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;

    // ===== Méthodes provenant de SlotFinderService =====

    /**
     * Find all available slots between two dates.
     *
     * @param object $schedulable The schedulable object
     * @param Carbon $startDate Start date of the period
     * @param Carbon $endDate End date of the period
     * @param int $durationMinutes Duration of each slot in minutes (default: 60)
     * @param int $intervalMinutes Interval between slot starts in minutes (default: 30)
     * @param string|null $type Optional availability type filter
     * @return array Array of available slots
     */
    public function findAvailableSlotsBetween(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array;

    /**
     * Find the next available slot.
     *
     * @param object $schedulable The schedulable object
     * @param Carbon $fromDate Date to start searching from
     * @param int $durationMinutes Duration of the slot in minutes (default: 60)
     * @return Carbon|null The start time of the next available slot or null
     */
    public function nextAvailableSlot(
        object $schedulable,
        Carbon $fromDate,
        int $durationMinutes = 60
    ): ?Carbon;

    /**
     * Get all available slots in a period.
     *
     * @param object $schedulable The schedulable object
     * @param Carbon $startDate Start date of the period
     * @param Carbon $endDate End date of the period
     * @param int $durationMinutes Duration of each slot in minutes (default: 60)
     * @param int $intervalMinutes Interval between slot starts in minutes (default: 30)
     * @return array Array of available slots with details
     */
    public function availableSlots(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30
    ): array;

    /**
     * Check if a time period has any availability.
     *
     * @param object $schedulable The schedulable object
     * @param Carbon $start Start of the period
     * @param Carbon $end End of the period
     * @param string|null $type Optional availability type filter
     * @return bool True if there is any availability in the period
     */
    public function hasAvailabilityBetween(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;

    /**
     * Get available slots from impediments.
     *
     * @param Carbon $start Start time of the period
     * @param Carbon $end End time of the period
     * @param Collection $impediments Collection of impediments to consider
     * @return Collection Collection of available slots between impediments
     */
    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection;
}
