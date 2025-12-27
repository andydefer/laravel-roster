<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;
use Roster\Contracts\Repository\RepositoryInterface;
use Roster\Exceptions\InvalidOwnerException;
use Roster\Exceptions\MissingOwnerException;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Support\RosterMutationContext;
use ReflectionClass;
use Roster\Domain\Helpers\TimeWindowHelper;

/**
 * Abstract repository providing CRUD operations with filter support and mutation context protection.
 *
 * Implements the Repository pattern with built-in validation for schedulable entities and owner relationships.
 * Handles Availability and Impediment models with appropriate business rule enforcement.
 *
 * @template TModel of Model
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The Eloquent model class managed by this repository.
     *
     * @var class-string<TModel>|null
     */
    protected ?string $modelClass = null;

    /**
     * Currently active filters for query operations.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Get an instance of the managed model.
     *
     * @return TModel
     *
     * @throws LogicException When the model class cannot be resolved or instantiated
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
     * Get the fully qualified model class name.
     */
    private function getModelClass(): string
    {
        return $this->modelClass ?? $this->resolveModelClass();
    }

    /**
     * Resolve the model class name based on repository class name convention.
     */
    private function resolveModelClass(): string
    {
        $repositoryClass = static::class;
        $shortName = (new ReflectionClass($repositoryClass))->getShortName();

        $modelName = str_replace('Repository', '', $shortName);

        return 'Roster\Models\\' . $modelName;
    }

    /**
     * Determine if the managed model is an Availability or its subclass.
     */
    private function isAvailabilityModel(): bool
    {
        $modelClass = $this->getModelClass();
        return $modelClass === Availability::class || is_subclass_of($modelClass, Availability::class);
    }

    /**
     * Validate that a schedulable entity is provided.
     *
     * @throws MissingSchedulableException When no schedulable is provided
     */
    private function validateSchedulable(?Model $schedulable): void
    {
        if (!$schedulable instanceof Model) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Validate that no owner is provided for Availability models.
     *
     * @throws InvalidOwnerException When owner is provided for Availability model
     */
    private function validateOwnerForAvailability(?Model $owner): void
    {
        if ($owner instanceof Model && $this->isAvailabilityModel()) {
            throw InvalidOwnerException::forAvailability();
        }
    }

    /**
     * Validate that an owner is provided for non-Availability models.
     *
     * @throws MissingOwnerException When owner is not provided for non-Availability model
     */
    private function validateOwnerForNonAvailability(?Model $owner): void
    {
        if (!$this->isAvailabilityModel() && !$owner instanceof Model) {
            throw MissingOwnerException::create($this->getModelClass());
        }
    }

    /**
     * Validate both schedulable and owner according to model-specific rules.
     *
     * @throws MissingSchedulableException|InvalidOwnerException|MissingOwnerException
     */
    private function validateSchedulableAndOwner(?Model $schedulable, ?Model $owner): void
    {
        $this->validateSchedulable($schedulable);

        if ($this->isAvailabilityModel()) {
            $this->validateOwnerForAvailability($owner);
        } else {
            $this->validateOwnerForNonAvailability($owner);
        }
    }

    /**
     * Build a base query scoped to a specific schedulable entity.
     */
    private function buildBaseQuery(Model $schedulable): Builder
    {
        $model = $this->getModel();

        return $model::query()
            ->where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable));
    }

    /**
     * Apply owner constraint to a query builder for non-Availability models.
     */
    private function applyOwnerConstraint(Builder $builder, ?Model $owner): Builder
    {
        if ($owner instanceof Model && !$this->isAvailabilityModel()) {
            $builder->where('availability_id', $owner->id);
        }

        return $builder;
    }

    /**
     * Inject owner relationship into data array for non-Availability models.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     *
     * @throws MissingOwnerException When owner is required but not provided
     */
    private function applyOwnerConstraintToData(array $data, ?Model $owner): array
    {
        $this->validateOwnerForNonAvailability($owner);

        if ($owner instanceof Model && !$this->isAvailabilityModel()) {
            $data['availability_id'] = $owner->id;
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    final public function create(array $data, Model $schedulable, ?Model $owner = null): Model
    {
        return RosterMutationContext::allow(function () use ($data, $schedulable, $owner): Model {
            $this->validateSchedulableAndOwner($schedulable, $owner);

            $data['schedulable_id'] = $schedulable->id;
            $data['schedulable_type'] = get_class($schedulable);
            $data = $this->applyOwnerConstraintToData($data, $owner);

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
     *
     * @param array<string, mixed> $filters
     */
    public function buildQueryWithFilters(Model $schedulable, array $filters): Builder
    {
        $this->validateSchedulable($schedulable);

        $builder = $this->buildBaseQuery($schedulable);
        return $this->applyFilters($builder, $filters);
    }

    /**
     * {@inheritDoc}
     */
    final public function all(Model $schedulable, ?Model $owner = null, array $filters = []): Collection
    {
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
     * Find impediments overlapping with a specific time slot.
     *
     * @return Collection<int, Impediment>
     *
     * @throws InvalidArgumentException When the time window is invalid
     */
    public function findForTimeSlot(int $availabilityId, Carbon $start, Carbon $end): Collection
    {
        TimeWindowHelper::assertDailyWindow($start, $end);

        $model = $this->getModel();

        return $model::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Apply filters to a query builder.
     *
     * Supports:
     * - Array values: WHERE IN clause
     * - Fields containing 'start': WHERE >= value
     * - Fields containing 'end': WHERE <= value
     * - String values: WHERE LIKE pattern
     * - Other values: WHERE = value
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
