<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;
use Roster\Contracts\RepositoryInterface;
use Roster\Exceptions\InvalidOwnerException;
use Roster\Exceptions\MissingOwnerException;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Support\RosterMutationContext;
use ReflectionClass;

/**
 * Abstract base repository providing common CRUD operations for Eloquent models.
 * Implements the Repository pattern with filter support and mutation context protection.
 *
 * @template TModel of Model
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The model class managed by the repository.
     *
     * @var class-string<TModel>|null
     */
    protected ?string $modelClass = null;

    /**
     * Active filters for query operations.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Get the model instance managed by this repository.
     *
     * @throws LogicException When model class cannot be determined
     */
    final protected function getModel(): Model
    {
        if ($this->modelClass !== null) {
            return app()->make($this->modelClass);
        }

        $this->modelClass = $this->resolveModelClass();

        if (!class_exists($this->modelClass)) {
            throw new LogicException(sprintf(
                'Model class %s not found for repository %s',
                $this->modelClass,
                static::class
            ));
        }

        return app()->make($this->modelClass);
    }

    /**
     * Get the model class name.
     */
    private function getModelClass(): string
    {
        return $this->modelClass ?? $this->resolveModelClass();
    }

    /**
     * Resolve the model class name from repository class name.
     */
    private function resolveModelClass(): string
    {
        $repositoryClass = static::class;
        $shortName = (new ReflectionClass($repositoryClass))->getShortName();

        // Remove 'Repository' suffix and prepend model namespace
        $modelName = str_replace('Repository', '', $shortName);

        return 'Roster\Models\\' . $modelName;
    }

    /**
     * Check if the managed model is an Availability instance.
     */
    private function isAvailabilityModel(): bool
    {
        $modelClass = $this->getModelClass();
        return $modelClass === Availability::class || is_subclass_of($modelClass, Availability::class);
    }

    /**
     * Validate that schedulable is provided for all operations.
     *
     * @throws MissingSchedulableException When schedulable is not provided
     */
    private function validateSchedulable(?Model $model): void
    {
        if (!$model instanceof Model) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Validate that an owner is not provided for Availability models.
     *
     * @throws InvalidOwnerException When owner is provided for Availability model
     */
    private function validateOwnerForAvailability(?Model $model): void
    {
        if ($model instanceof Model && $this->isAvailabilityModel()) {
            throw InvalidOwnerException::forAvailability();
        }
    }

    /**
     * Validate that owner is provided for non-Availability models.
     *
     * @throws MissingOwnerException When owner is not provided for non-Availability model
     */
    private function validateOwnerForNonAvailability(?Model $model): void
    {
        if (!$this->isAvailabilityModel() && !$model instanceof Model) {
            throw MissingOwnerException::create($this->getModelClass());
        }
    }

    /**
     * Validate both schedulable and owner based on model type.
     */
    private function validateSchedulableAndOwner(?Model $schedulable, ?Model $owner): void
    {
        // Validate schedulable (required for all models)
        $this->validateSchedulable($schedulable);

        // Validate owner based on model type
        if ($this->isAvailabilityModel()) {
            $this->validateOwnerForAvailability($owner);
        } else {
            $this->validateOwnerForNonAvailability($owner);
        }
    }

    /**
     * Build base query with schedulable scope.
     */
    private function buildBaseQuery(Model $schedulable): Builder
    {
        $model = $this->getModel();

        return $model::query()
            ->where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable));
    }

    /**
     * Apply owner constraint to query if applicable.
     */
    private function applyOwnerConstraint(Builder $builder, ?Model $model): Builder
    {
        if ($model instanceof Model && !$this->isAvailabilityModel()) {
            $builder->where('availability_id', $model->id);
        }

        return $builder;
    }

    /**
     * Apply owner constraint to data array if applicable.
     * @param array<string, mixed> $data
     */
    private function applyOwnerConstraintToData(array $data, ?Model $model): array
    {
        $this->validateOwnerForNonAvailability($model);

        if ($model instanceof Model && !$this->isAvailabilityModel()) {
            $data['availability_id'] = $model->id;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    final public function create(array $data, Model $schedulable, ?Model $owner = null): Model
    {
        return RosterMutationContext::allow(function () use ($data, $schedulable, $owner): Model {
            // Validate schedulable and owner
            $this->validateSchedulableAndOwner($schedulable, $owner);

            // Inject schedulable into data
            $data['schedulable_id'] = $schedulable->id;
            $data['schedulable_type'] = get_class($schedulable);

            // Inject owner if required
            $data = $this->applyOwnerConstraintToData($data, $owner);

            // Create the model
            $model = $this->getModel();
            return $model::create($data);
        });
    }

    /**
     * {@inheritDoc}
     */
    final public function update(
        int $id,
        Model $schedulable,
        ?Model $owner = null,
        array $data = []
    ): bool {
        return RosterMutationContext::allow(function () use ($id, $schedulable, $owner, $data): bool {
            // Validate schedulable and owner
            $this->validateSchedulableAndOwner($schedulable, $owner);

            $query = $this->buildBaseQuery($schedulable)
                ->whereKey($id);

            $query = $this->applyOwnerConstraint($query, $owner);

            /** @var Model|null $model */
            $model = $query->first();

            if (!$model) {
                return false;
            }

            return $model->update($data);
        });
    }

    /**
     * {@inheritDoc}
     */
    final public function delete(
        int $id,
        Model $schedulable,
        ?Model $owner = null
    ): bool {
        return RosterMutationContext::allow(function () use ($id, $schedulable, $owner): bool {
            // Validate schedulable and owner
            $this->validateSchedulableAndOwner($schedulable, $owner);

            $query = $this->buildBaseQuery($schedulable)
                ->whereKey($id);

            $query = $this->applyOwnerConstraint($query, $owner);

            $model = $query->first();

            return $model instanceof Model && (bool) $model->delete();
        });
    }

    /**
     * {@inheritDoc}
     */
    final public function find(
        int $id,
        ?Model $schedulable = null,
        ?Model $owner = null,
        array $filters = []
    ): ?Model {
        // Validate schedulable and owner
        $this->validateSchedulableAndOwner($schedulable, $owner);

        $query = $this->buildBaseQuery($schedulable)
            ->whereKey($id);

        $query = $this->applyOwnerConstraint($query, $owner);

        if ($filters !== []) {
            $query = $this->applyFilters($query, $filters);
        }

        return $query->first();
    }

    /**
     * Build a query with schedulable scope and applied filters.
     */
    public function buildQueryWithFilters(Model $model, array $filters): Builder
    {
        // Validate schedulable (owner validation happens at query execution)
        $this->validateSchedulable($model);

        $builder = $this->buildBaseQuery($model);
        return $this->applyFilters($builder, $filters);
    }

    /**
     * {@inheritDoc}
     */
    final public function all(Model $schedulable, ?Model $owner = null, array $filters = []): Collection
    {
        // Validate schedulable and owner
        $this->validateSchedulableAndOwner($schedulable, $owner);

        $query = $this->buildBaseQuery($schedulable);
        $query = $this->applyOwnerConstraint($query, $owner);

        if ($filters !== []) {
            $query = $this->applyFilters($query, $filters);
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    final public function paginate(
        Model $schedulable,
        ?Model $owner = null,
        array $filters = [],
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        // Validate schedulable and owner
        $this->validateSchedulableAndOwner($schedulable, $owner);

        $query = $this->buildQueryWithFilters($schedulable, $filters);
        $query = $this->applyOwnerConstraint($query, $owner);

        return $query->paginate(
            perPage: $perPage,
            columns: $columns,
            pageName: $pageName,
            page: $page
        );
    }

    /**
     * Calculate available time slots between impediments.
     */
    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection {
        if ($impediments->isEmpty()) {
            return collect([['start' => $start->copy(), 'end' => $end->copy()]]);
        }

        $availableSlots = collect();
        $currentTime = $start->copy();

        /** @var Collection<int, Impediment> $sortedImpediments */
        $sortedImpediments = $impediments->sortBy('start_datetime');

        foreach ($sortedImpediments as $sortedImpediment) {
            $impStart = $sortedImpediment->start_datetime;
            $impEnd = $sortedImpediment->end_datetime;

            // Check for gap before impediment
            if ($impStart->gt($currentTime)) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            // Move current time to after impediment
            $currentTime = max($currentTime, $impEnd);
        }

        // Check for remaining time after last impediment
        if ($currentTime->lt($end)) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }

    /**
     * Find impediments overlapping with a specific time slot.
     *
     * @return Collection<int, Impediment>
     */
    public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection
    {
        $model = $this->getModel();

        return $model::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Apply filters to query builder.
     */
    protected function applyFilters(Builder $builder, array $filters = []): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value === null) {
                continue;
            }

            $lowerField = strtolower($field);

            match (true) {
                is_array($value) =>
                $builder->whereIn($field, $value),

                str_contains($lowerField, 'start') =>
                $builder->where($field, '>=', $value),

                str_contains($lowerField, 'end') =>
                $builder->where($field, '<=', $value),

                is_string($value) =>
                $builder->where($field, 'LIKE', '%' . $value . '%'),

                default =>
                $builder->where($field, $value),
            };
        }

        return $builder;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilter(string $key, mixed $value): self
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearFilters(): self
    {
        $this->filters = [];
        return $this;
    }

    /**
     * {@inheritDoc}
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * {@inheritDoc}
     */
    public function hasFilter(string $key): bool
    {
        return array_key_exists($key, $this->filters);
    }
}
