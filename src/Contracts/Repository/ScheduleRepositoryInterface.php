<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Roster\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface ScheduleRepositoryInterface extends RepositoryInterface
{
    /**
     * Build a query for schedules related to a specific availability.
     *
     * @param int $availabilityId The ID of the availability
     * @param Carbon|null $start Optional start date filter
     * @param Carbon|null $end Optional end date filter
     * @return Builder Query builder for schedules
     */
    public function findByAvailability(
        int $availabilityId,
        ?Carbon $start = null,
        ?Carbon $end = null
    ): Builder;

    /**
     * Get all future schedules for a specific availability.
     *
     * @param int $availabilityId The ID of the availability
     * @param Carbon $from Starting date for future schedules
     * @return Collection<int, Schedule> Collection of future schedules
     */
    public function getFutureSchedules(
        int $availabilityId,
        Carbon $from
    ): Collection;

    /**
     * Get schedules for a schedulable resource within a date range.
     *
     * @param int $schedulableId The ID of the schedulable resource
     * @param string $schedulableType The type/class of the schedulable resource
     * @param Carbon $start Start date of the range
     * @param Carbon $end End date of the range
     * @param array<string, mixed> $filters Additional filters to apply
     * @return Collection<int, Schedule> Collection of schedules
     */
    public function getForDateRange(
        int $schedulableId,
        string $schedulableType,
        Carbon $start,
        Carbon $end,
        array $filters = []
    ): Collection;

    /**
     * Attach a model to a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @param Model $model The model to attach
     * @param array|null $metadata Optional metadata for the relationship
     * @return void
     */
    public function attach(int $scheduleId, Model $model, ?array $metadata = null): void;

    /**
     * Attach multiple models to a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @param array<Model> $models The models to attach
     * @param array|null $metadata Optional metadata for the relationships
     * @return void
     */
    public function attachMany(int $scheduleId, array $models, ?array $metadata = null): void;

    /**
     * Detach a model from a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @param Model $model The model to detach
     * @return void
     */
    public function detach(int $scheduleId, Model $model): void;

    /**
     * Detach multiple models from a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @param array<Model> $models The models to detach
     * @return void
     */
    public function detachMany(int $scheduleId, array $models): void;

    /**
     * Detach all models from a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @return void
     */
    public function detachAll(int $scheduleId): void;

    /**
     * Check if a model is attached to a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @param Model $model The model to check
     * @return bool True if the model is attached
     */
    public function hasAttached(int $scheduleId, Model $model): bool;

    /**
     * Get all models attached to a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @return Collection<int, Model> Collection of attached models
     */
    public function getAttached(int $scheduleId): Collection;

    /**
     * Get models of a specific type attached to a schedule.
     *
     * @param int $scheduleId The ID of the schedule
     * @param string $modelClass The class name of the model type
     * @return Collection<int, Model> Collection of attached models of the specified type
     */
    public function getAttachedByType(int $scheduleId, string $modelClass): Collection;

    /**
     * Synchronize attached models for a schedule.
     * Detaches all current models and attaches the new ones.
     *
     * @param int $scheduleId The ID of the schedule
     * @param array<Model> $models The models to attach
     * @param array|null $metadata Optional metadata for the relationships
     * @return void
     */
    public function sync(int $scheduleId, array $models, ?array $metadata = null): void;
}
