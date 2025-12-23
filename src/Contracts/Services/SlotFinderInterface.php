<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface SlotFinderInterface
{

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
