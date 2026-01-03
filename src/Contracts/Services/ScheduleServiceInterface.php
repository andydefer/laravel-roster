<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ScheduleServiceInterface extends ServiceInterface
{
    /**
     * Set the current schedule for link operations.
     *
     * @param Model $schedule The schedule model
     * @return static
     */
    public function schedule(Model $schedule): static;

    /**
     * Attach a model to the current schedule.
     *
     * @param Model $model The model to attach
     * @param array|null $metadata Optional metadata for the relationship
     * @return static
     */
    public function attach(Model $model, ?array $metadata = null): static;

    /**
     * Attach multiple models to the current schedule.
     *
     * @param array<Model> $models The models to attach
     * @param array|null $metadata Optional metadata for the relationships
     * @return static
     */
    public function attachMany(array $models, ?array $metadata = null): static;

    /**
     * Detach a model from the current schedule.
     *
     * @param Model $model The model to detach
     * @return static
     */
    public function detach(Model $model): static;

    /**
     * Detach multiple models from the current schedule.
     *
     * @param array<Model> $models The models to detach
     * @return static
     */
    public function detachMany(array $models): static;

    /**
     * Detach all models from the current schedule.
     *
     * @return static
     */
    public function detachAll(): static;

    /**
     * Check if a model is attached to the current schedule.
     *
     * @param Model $model The model to check
     * @return bool True if the model is attached
     */
    public function hasAttached(Model $model): bool;

    /**
     * Get all models attached to the current schedule.
     *
     * @return Collection<int, Model> Collection of attached models
     */
    public function getAttached(): Collection;

    /**
     * Get models of a specific type attached to the current schedule.
     *
     * @param string $modelClass The class name of the model type
     * @return Collection<int, Model> Collection of attached models of the specified type
     */
    public function getAttachedByType(string $modelClass): Collection;

    /**
     * Synchronize attached models for the current schedule.
     * Detaches all current models and attaches the new ones.
     *
     * @param array<Model> $models The models to attach
     * @param array|null $metadata Optional metadata for the relationships
     * @return static
     */
    public function sync(array $models, ?array $metadata = null): static;
}
