<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Models\Schedule;
use Roster\Support\RosterMutationContext;

/**
 * Repository for Schedule entity data access operations.
 *
 * Provides methods for querying schedules based on availability,
 * date ranges, and schedulable entities with support for filtering.
 */
class ScheduleRepository extends AbstractRepository implements ScheduleRepositoryInterface
{
    /**
     * Finds schedules by availability with optional time range constraints.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon|null $start Optional start time for range filtering
     * @param Carbon|null $end Optional end time for range filtering
     * @return Builder Query builder for further refinement
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder {
        $query = Schedule::where('availability_id', $availabilityId);

        if ($start instanceof Carbon && $end instanceof Carbon) {
            $query->whereBetween('start_datetime', [$start, $end]);
        } elseif ($start instanceof Carbon) {
            $query->where('start_datetime', '>=', $start);
        } elseif ($end instanceof Carbon) {
            $query->where('end_datetime', '<=', $end);
        }

        return $query;
    }

    /**
     * Retrieves future schedules for an availability starting from a given date.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $from Starting date for filtering future schedules
     * @return Collection Future schedules ordered by start time
     */
    public function getFutureSchedules(int $availabilityId, Carbon $from): Collection
    {
        return Schedule::where('availability_id', $availabilityId)
            ->where('end_datetime', '>=', $from)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Retrieves schedules within a date range for a schedulable entity.
     *
     * @param int $schedulableId Schedulable entity identifier
     * @param string $schedulableType Schedulable entity class name
     * @param Carbon $start Start of date range
     * @param Carbon $end End of date range
     * @param array $filters Additional query filters
     * @return Collection Schedules within the specified range
     */
    public function getForDateRange(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection {
        $builder = $this->buildSchedulableQuery($schedulableId, $schedulableType)
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end);

        $this->applyFilters($builder, $filters);

        return $builder->orderBy('start_datetime')->get();
    }

    /**
     * Attach a model to a schedule.
     */
    public function attach(int $scheduleId, Model $model, ?array $metadata = null): void
    {
        RosterMutationContext::allow(function () use ($scheduleId, $model, $metadata) {
            DB::table('roster_schedule_links')->insertOrIgnore([
                'schedule_id' => $scheduleId,
                'linkable_id' => $model->getKey(),
                'linkable_type' => get_class($model),
                'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Attach multiple models to a schedule.
     */
    public function attachMany(int $scheduleId, array $models, ?array $metadata = null): void
    {
        RosterMutationContext::allow(function () use ($scheduleId, $models, $metadata) {
            $links = [];
            $now = now();

            foreach ($models as $model) {
                $links[] = [
                    'schedule_id' => $scheduleId,
                    'linkable_id' => $model->getKey(),
                    'linkable_type' => get_class($model),
                    'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($links)) {
                DB::table('roster_schedule_links')->insertOrIgnore($links);
            }
        });
    }

    /**
     * Detach a model from a schedule.
     */
    public function detach(int $scheduleId, Model $model): void
    {
        RosterMutationContext::allow(function () use ($scheduleId, $model) {
            DB::table('roster_schedule_links')
                ->where('schedule_id', $scheduleId)
                ->where('linkable_id', $model->getKey())
                ->where('linkable_type', get_class($model))
                ->delete();
        });
    }

    /**
     * Detach multiple models from a schedule.
     */
    public function detachMany(int $scheduleId, array $models): void
    {
        RosterMutationContext::allow(function () use ($scheduleId, $models) {
            foreach ($models as $model) {
                $this->detach($scheduleId, $model);
            }
        });
    }

    /**
     * Detach all models from a schedule.
     */
    public function detachAll(int $scheduleId): void
    {
        RosterMutationContext::allow(function () use ($scheduleId) {
            DB::table('roster_schedule_links')
                ->where('schedule_id', $scheduleId)
                ->delete();
        });
    }

    /**
     * Check if a model is attached to a schedule.
     */
    public function hasAttached(int $scheduleId, Model $model): bool
    {
        return DB::table('roster_schedule_links')
            ->where('schedule_id', $scheduleId)
            ->where('linkable_id', $model->getKey())
            ->where('linkable_type', get_class($model))
            ->exists();
    }

    /**
     * Get all models attached to a schedule.
     */
    public function getAttached(int $scheduleId): Collection
    {
        $links = DB::table('roster_schedule_links')
            ->where('schedule_id', $scheduleId)
            ->get();

        $collection = new Collection();

        foreach ($links as $link) {
            $modelClass = $link->linkable_type;
            if (class_exists($modelClass)) {
                $model = $modelClass::find($link->linkable_id);
                if ($model) {
                    $model->schedule_link_metadata = $link->metadata ? json_decode($link->metadata, true) : [];
                    $model->schedule_link_created_at = Carbon::parse($link->created_at);
                    $model->schedule_link_updated_at = Carbon::parse($link->updated_at);
                    $collection->push($model);
                }
            }
        }

        return $collection;
    }

    /**
     * Get models of a specific type attached to a schedule.
     */
    public function getAttachedByType(int $scheduleId, string $modelClass): Collection
    {
        $links = DB::table('roster_schedule_links')
            ->where('schedule_id', $scheduleId)
            ->where('linkable_type', $modelClass)
            ->get();

        $collection = new Collection();

        foreach ($links as $link) {
            $model = $modelClass::find($link->linkable_id);
            if ($model) {
                $model->schedule_link_metadata = $link->metadata ? json_decode($link->metadata, true) : [];
                $model->schedule_link_created_at = Carbon::parse($link->created_at);
                $model->schedule_link_updated_at = Carbon::parse($link->updated_at);
                $collection->push($model);
            }
        }

        return $collection;
    }

    /**
     * Synchronize attached models for a schedule.
     */
    public function sync(int $scheduleId, array $models, ?array $metadata = null): void
    {
        RosterMutationContext::allow(function () use ($scheduleId, $models, $metadata) {
            $this->detachAll($scheduleId);
            $this->attachMany($scheduleId, $models, $metadata);
        });
    }

    /**
     * Builds a base query for schedules associated with a schedulable entity.
     *
     * @param int $schedulableId Schedulable entity identifier
     * @param string $schedulableType Schedulable entity class name
     * @return Builder Query builder for schedules
     */
    private function buildSchedulableQuery(int $schedulableId, string $schedulableType): Builder
    {
        return Schedule::whereHas('availability', function ($query) use ($schedulableId, $schedulableType): void {
            $query->where('schedulable_id', $schedulableId)
                ->where('schedulable_type', $schedulableType);
        });
    }
}
