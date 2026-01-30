<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Domain\Helpers\TimeWindowHelper;
use Roster\Models\Availability;

/**
 * Repository for managing Availability model operations.
 *
 * Provides methods to query availabilities for schedulable entities with support for
 * time-based filtering, validity periods, and conflict detection.
 */
class AvailabilityRepository extends AbstractRepository implements AvailabilityRepositoryInterface
{
    /**
     * Retrieves a query builder for availabilities of a specific schedulable entity.
     *
     * @param Model $schedulable The schedulable entity (e.g., User, Team)
     * @param string|null $type Optional availability type filter
     *
     * @return Builder Query builder for availabilities
     */
    public function findForSchedulable(Model $schedulable, ?string $type = null): Builder
    {
        $query = $this->buildBaseQuery($schedulable);

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query;
    }

    /**
     * Retrieves availabilities valid within a specific date range.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $startDate Start date of the range
     * @param Carbon $endDate End date of the range
     * @param string|null $type Optional availability type filter
     *
     * @return Collection<int, Availability> Collection of matching availabilities
     */
    public function getForDateRange(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        ?string $type = null
    ): Collection {
        $query = $this->buildBaseQuery($model)
            ->where(function (Builder $query) use ($endDate): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $endDate);
            })
            ->where(function (Builder $query) use ($startDate): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $startDate);
            });

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    /**
     * Finds an availability that covers a specific time slot.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $slotStart Start time of the slot
     * @param Carbon $slotEnd End time of the slot
     * @param string|null $type Optional availability type filter
     *
     * @return Availability|null Matching availability or null if none found
     */
    public function getAvailabilityForTimeSlot(
        Model $model,
        Carbon $slotStart,
        Carbon $slotEnd,
        ?string $type = null
    ): ?Availability {
        TimeWindowHelper::assertDailyWindow($slotStart, $slotEnd);

        $query = $this->buildBaseQuery($model)
            ->whereJsonContains('days', strtolower($slotStart->englishDayOfWeek))
            ->where('daily_start', '<=', $slotStart->format('H:i:s'))
            ->where('daily_end', '>=', $slotEnd->format('H:i:s'));

        if ($type !== null) {
            $query->where('type', $type);
        }

        $this->applyDateRangeFilters($query, $slotStart, $slotEnd);

        /** @var Availability|null $availability */
        $availability = $query->first();

        return $availability;
    }

    /**
     * Retrieves availabilities applicable to a specific date.
     *
     * @param Model $schedulable The schedulable entity
     * @param Carbon $date Target date
     * @param string|null $type Optional availability type filter
     *
     * @return Collection<int, Availability> Collection of availabilities for the date
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection {
        $query = $this->buildBaseQuery($model)
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type !== null) {
            $query->where('type', $type);
        }

        $this->applyDateFilters($query, $date);

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $query
            ->orderBy('daily_start')
            ->get()
            ->unique('id');

        return $availabilities;
    }

    /**
     * Checks if an availability applies to a specific date.
     *
     * @param Availability $availability The availability to check
     * @param Carbon $date Date to validate against
     *
     * @return bool True if the availability applies to the date
     */
    public function isAvailableOnDate(Availability $availability, Carbon $date): bool
    {
        $dayOfWeek = strtolower($date->englishDayOfWeek);

        if (!in_array($dayOfWeek, $availability->days)) {
            return false;
        }

        $isBeforeValidityStart = $availability->validity_start !== null
            && $date->lt($availability->validity_start);

        $isAfterValidityEnd = $availability->validity_end !== null
            && $date->gt($availability->validity_end);

        return !$isBeforeValidityStart && !$isAfterValidityEnd;
    }

    /**
     * Finds an availability for a time slot with conflict detection information.
     *
     * @param Model $schedulable The schedulable entity
     * @param Carbon $slotStart Start time of the slot
     * @param Carbon $slotEnd End time of the slot
     * @param string|null $type Optional availability type filter
     *
     * @return Availability|null Matching availability with conflict flags or null
     */
    public function findForTimeSlotWithConflictInfo(
        Model $schedulable,
        Carbon $slotStart,
        Carbon $slotEnd,
        ?string $type = null
    ): ?Availability {
        return Availability::where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable))
            ->when($type !== null, function (Builder $query) use ($type): void {
                $query->where('type', $type);
            })
            ->whereJsonContains('days', strtolower($slotStart->englishDayOfWeek))
            ->where('daily_start', '<=', $slotStart->format('H:i:s'))
            ->where('daily_end', '>=', $slotEnd->format('H:i:s'))
            ->where(function (Builder $query) use ($slotStart): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $slotStart->toDateString());
            })
            ->where(function (Builder $query) use ($slotEnd): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $slotEnd->toDateString());
            })
            ->withExists([
                'schedules as has_overlapping_schedules' => function (Builder $query) use ($slotStart, $slotEnd): void {
                    $query->where('start_datetime', '<', $slotEnd)
                        ->where('end_datetime', '>', $slotStart);
                },
                'impediments as has_overlapping_impediments' => function (Builder $query) use ($slotStart, $slotEnd): void {
                    $query->where('start_datetime', '<', $slotEnd)
                        ->where('end_datetime', '>', $slotStart);
                }
            ])
            ->first();
    }

    /**
     * Builds a base query for availabilities of a schedulable entity.
     *
     * @param Model $schedulable The schedulable entity
     *
     * @return Builder Base query builder
     */
    private function buildBaseQuery(Model $schedulable): Builder
    {
        return Availability::where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable));
    }

    /**
     * Applies time slot filters to a query builder.
     *
     * @param Builder $query Query builder to modify
     * @param Carbon $slotStart Start time of the slot
     * @param Carbon $slotEnd End time of the slot
     */
    private function applyTimeSlotFilters(Builder $query, Carbon $slotStart, Carbon $slotEnd): void
    {
        TimeWindowHelper::assertDailyWindow($slotStart, $slotEnd);

        $query->whereJsonContains('days', strtolower($slotStart->englishDayOfWeek))
            ->where('daily_start', '<=', $slotStart->format('H:i:s'))
            ->where('daily_end', '>=', $slotEnd->format('H:i:s'));

        $this->applyDateRangeFilters($query, $slotStart, $slotEnd);
    }

    /**
     * Applies date-specific filters to a query builder.
     *
     * @param Builder $query Query builder to modify
     * @param Carbon $date Target date
     */
    private function applyDateFilters(Builder $query, Carbon $date): void
    {
        $this->applyDateRangeFilters($query, $date, $date);
    }

    /**
     * Applies date range validity filters to a query builder.
     *
     * @param Builder $query Query builder to modify
     * @param Carbon $startDate Start date of validity period
     * @param Carbon $endDate End date of validity period
     */
    private function applyDateRangeFilters(Builder $query, Carbon $startDate, Carbon $endDate): void
    {
        $query->where(function (Builder $query) use ($startDate): void {
            $query->whereNull('validity_start')
                ->orWhere('validity_start', '<=', $startDate->toDateString());
        })->where(function (Builder $query) use ($endDate): void {
            $query->whereNull('validity_end')
                ->orWhere('validity_end', '>=', $endDate->toDateString());
        });
    }
}
