<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Models\Availability;

class AvailabilityRepository extends AbstractRepository implements AvailabilityRepositoryInterface
{
    /**
     * Find availabilities for a specific schedulable entity.
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
     * Get availabilities for a specific date range.
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

        if ($type) {
            $builder->where('type', $type);
        }

        return $builder->get();
    }

    /**
     * Find availability for a specific time slot.
     */
    public function getAvailabilityForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        $builder = $this->buildBaseQuery($model);

        if ($type) {
            $builder->where('type', $type);
        }

        $this->applyTimeSlotFilters($builder, $start, $end);

        /** @var Availability|null $availability */
        $availability = $builder->first();

        return $availability;
    }

    /**
     * Get availabilities for a specific date.
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type) {
            $builder->where('type', $type);
        }

        $this->applyDateFilters($builder, $date);

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $builder->orderBy('daily_start')->get();

        return $availabilities;
    }

    /**
     * Check if an availability applies to a specific date.
     */
    public function isAvailableOnDate(Availability $availability, Carbon $date): bool
    {
        $dayOfWeek = strtolower($date->englishDayOfWeek);

        if (!in_array($dayOfWeek, $availability->days)) {
            return false;
        }

        $isBeforeValidityStart = $availability->validity_start !== null && $date->lt($availability->validity_start);
        $isAfterValidityEnd = $availability->validity_end !== null && $date->gt($availability->validity_end);

        return !$isBeforeValidityStart && !$isAfterValidityEnd;
    }

    /**
     * Find availability for a time slot with conflict information.
     */
    public function findForTimeSlotWithConflictInfo(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->when($type, function ($query) use ($type): void {
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
     * Build base query for availabilities of a schedulable entity.
     */
    private function buildBaseQuery(Model $model): Builder
    {
        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model));
    }

    /**
     * Apply time slot filters to query.
     */
    private function applyTimeSlotFilters(Builder $builder, Carbon $start, Carbon $end): void
    {
        $builder->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('daily_start', '<=', $start->format('H:i:s'))
            ->where('daily_end', '>=', $end->format('H:i:s'));

        $this->applyDateRangeFilters($builder, $start, $end);
    }

    /**
     * Apply date filters to query.
     */
    private function applyDateFilters(Builder $builder, Carbon $date): void
    {
        $this->applyDateRangeFilters($builder, $date, $date);
    }

    /**
     * Apply date range filters to query.
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
