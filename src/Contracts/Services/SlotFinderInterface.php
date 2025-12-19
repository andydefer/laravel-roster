<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface SlotFinderInterface
{
    /**
     * Find all available slots between two dates.
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
     */
    public function nextAvailableSlot(
        object $schedulable,
        Carbon $fromDate,
        int $durationMinutes = 60
    ): ?Carbon;

    /**
     * Get all available slots in a period.
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
     */
    public function hasAvailabilityBetween(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool;


    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection;
}
