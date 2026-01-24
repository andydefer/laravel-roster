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
     * @param Model $model The schedulable entity (e.g., User, Team)
     * @param string|null $type Optional availability type filter
     *
     * @return Builder Query builder for availabilities
     */
    public function findForSchedulable(Model $model, ?string $type = null): Builder
    {
        $builder = $this->buildBaseQuery($model);

        if ($type !== null) {
            $builder->where('type', $type);
        }

        return $builder;
    }

    /**
     * Retrieves availabilities valid within a specific date range.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start date of the range
     * @param Carbon $end End date of the range
     * @param string|null $type Optional availability type filter
     *
     * @return Collection<int, Availability> Collection of matching availabilities
     */
    public function getForDateRange(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->where(function ($query) use ($end): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $end);
            })
            ->where(function ($query) use ($start): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $start);
            });

        if ($type !== null) {
            $builder->where('type', $type);
        }

        return $builder->get();
    }

    // Dans AvailabilityRepository.php
    /**
     * Finds an availability that covers a specific time slot.
     *
     * @param Model $schedulable The schedulable entity
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     *
     * @return Availability|null Matching availability or null if none found
     */
    public function getAvailabilityForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        TimeWindowHelper::assertDailyWindow($start, $end);

        $dayOfWeek = strtolower($start->englishDayOfWeek);
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');
        $date = $start->toDateString();

        $query = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->whereJsonContains('days', $dayOfWeek)
            ->where('daily_start', '<=', $startTime)
            ->where('daily_end', '>=', $endTime);

        if ($type !== null) {
            $query->where('type', $type);
        }

        // Vérifier la période de validité
        $query->where(function ($q) use ($date) {
            $q->whereNull('validity_start')
                ->orWhere('validity_start', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('validity_end')
                ->orWhere('validity_end', '>=', $date);
        });

        return $query->first();
    }

    /**
     * Retrieves availabilities applicable to a specific date.
     *
     * @param Model $model The schedulable entity
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
        $builder = $this->buildBaseQuery($model)
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type !== null) {
            $builder->where('type', $type);
        }

        $this->applyDateFilters($builder, $date);

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $builder->orderBy('daily_start')->get();

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
     * @param Model $model The schedulable entity
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     *
     * @return Availability|null Matching availability with conflict flags or null
     */
    public function findForTimeSlotWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->when($type !== null, function ($query) use ($type): void {
                $query->where('type', $type);
            })
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('daily_start', '<=', $start->format('H:i:s'))
            ->where('daily_end', '>=', $end->format('H:i:s'))
            ->where(function ($query) use ($start): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $start->toDateString());
            })
            ->where(function ($query) use ($end): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $end->toDateString());
            })
            ->withExists([
                'schedules as has_overlapping_schedules' => function ($query) use ($start, $end): void {
                    $query->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                },
                'impediments as has_overlapping_impediments' => function ($query) use ($start, $end): void {
                    $query->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                }
            ])
            ->first();
    }

    /**
     * Builds a base query for availabilities of a schedulable entity.
     *
     * @param Model $model The schedulable entity
     *
     * @return Builder Base query builder
     */
    private function buildBaseQuery(Model $model): Builder
    {
        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model));
    }

    /**
     * Applies time slot filters to a query builder.
     *
     * @param Builder $builder Query builder to modify
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     */
    private function applyTimeSlotFilters(Builder $builder, Carbon $start, Carbon $end): void
    {
        TimeWindowHelper::assertDailyWindow($start, $end);

        $builder->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('daily_start', '<=', $start->format('H:i:s'))
            ->where('daily_end', '>=', $end->format('H:i:s'));

        $this->applyDateRangeFilters($builder, $start, $end);
    }

    /**
     * Applies date-specific filters to a query builder.
     *
     * @param Builder $builder Query builder to modify
     * @param Carbon $date Target date
     */
    private function applyDateFilters(Builder $builder, Carbon $date): void
    {
        $this->applyDateRangeFilters($builder, $date, $date);
    }

    /**
     * Applies date range validity filters to a query builder.
     *
     * @param Builder $builder Query builder to modify
     * @param Carbon $startDate Start date of validity period
     * @param Carbon $endDate End date of validity period
     */
    private function applyDateRangeFilters(Builder $builder, Carbon $startDate, Carbon $endDate): void
    {
        $builder->where(function ($query) use ($startDate): void {
            $query->whereNull('validity_start')
                ->orWhere('validity_start', '<=', $startDate->toDateString());
        })->where(function ($query) use ($endDate): void {
            $query->whereNull('validity_end')
                ->orWhere('validity_end', '>=', $endDate->toDateString());
        });
    }
}
