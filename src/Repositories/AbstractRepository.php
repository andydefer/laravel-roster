<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;
use Roster\Contracts\Repository\RepositoryInterface;
use Roster\Domain\Helpers\TimeWindowHelper;
use Roster\Exceptions\InvalidOwnerException;
use Roster\Exceptions\MissingOwnerException;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Support\RosterMutationContext;
use ReflectionClass;

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

    /* ========= CRUD Operations ========= */

    /**
     * Create a new entity associated with a schedulable resource.
     *
     * @param array<string, mixed> $data Entity attributes
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @return Model The created entity instance
     * @throws MissingSchedulableException If schedulable not provided
     * @throws InvalidOwnerException If owner provided for Availability model
     * @throws MissingOwnerException If owner required but not provided
     */
    final public function create(array $data, Model $schedulable, ?Model $owner = null): Model
    {
        return RosterMutationContext::allow(function () use ($data, $schedulable, $owner): Model {
            $this->validateSchedulableAndOwner($schedulable, $owner);

            $data['schedulable_id'] = $schedulable->id;
            $data['schedulable_type'] = get_class($schedulable);
            $data = $this->injectOwnerIntoData($data, $owner);

            $model = $this->getModel();

            return $model::create($data);
        });
    }

    /**
     * Update an existing entity.
     *
     * @param int $id Entity identifier
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $data Updated entity attributes
     * @return bool True if update was successful, false otherwise
     * @throws MissingSchedulableException If schedulable not provided
     * @throws InvalidOwnerException If owner provided for Availability model
     * @throws MissingOwnerException If owner required but not provided
     */
    final public function update(int $id, Model $schedulable, ?Model $owner = null, array $data = []): bool
    {
        return RosterMutationContext::allow(function () use ($id, $schedulable, $owner, $data): bool {
            $this->validateSchedulableAndOwner($schedulable, $owner);

            $query = $this->buildSchedulableScopedQuery($schedulable)
                ->whereKey($id);

            $query = $this->applyOwnerScope($query, $owner);

            $model = $query->first();

            if ($model === null) {
                return false;
            }

            return $model->update($data);
        });
    }

    /**
     * Delete an entity.
     *
     * @param int $id Entity identifier
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @return bool True if deletion was successful, false otherwise
     * @throws MissingSchedulableException If schedulable not provided
     * @throws InvalidOwnerException If owner provided for Availability model
     * @throws MissingOwnerException If owner required but not provided
     */
    final public function delete(int $id, Model $schedulable, ?Model $owner = null): bool
    {
        return RosterMutationContext::allow(function () use ($id, $schedulable, $owner): bool {
            $this->validateSchedulableAndOwner($schedulable, $owner);

            $query = $this->buildSchedulableScopedQuery($schedulable)
                ->whereKey($id);

            $query = $this->applyOwnerScope($query, $owner);

            $model = $query->first();

            return $model instanceof Model && (bool) $model->delete();
        });
    }

    /**
     * Find an entity by its ID with optional schedulable and ownership constraints.
     *
     * @param int $id Entity identifier
     * @param Model|null $schedulable Optional schedulable resource for scoping
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $filters Additional query filters
     * @return Model|null The found entity or null if not found
     * @throws MissingSchedulableException If schedulable not provided
     * @throws InvalidOwnerException If owner provided for Availability model
     * @throws MissingOwnerException If owner required but not provided
     */
    final public function find(int $id, ?Model $schedulable = null, ?Model $owner = null, array $filters = []): ?Model
    {
        $this->validateSchedulableAndOwner($schedulable, $owner);

        $query = $this->buildSchedulableScopedQuery($schedulable)
            ->whereKey($id);

        $query = $this->applyOwnerScope($query, $owner);

        if ($filters !== []) {
            $query = $this->applyFilters($query, $filters);
        }

        return $query->first();
    }

    /**
     * Retrieve the first entity matching schedulable, owner, and filters.
     *
     * @param Model $schedulable Schedulable entity
     * @param Model|null $owner Optional owner entity
     * @param array<string, mixed> $filters Optional filters
     * @return Model|null First matching entity or null if none found
     */
    final public function first(Model $schedulable, ?Model $owner = null, array $filters = []): ?Model
    {
        $this->validateSchedulableAndOwner($schedulable, $owner);

        $query = $this->buildSchedulableScopedQuery($schedulable);
        $query = $this->applyOwnerScope($query, $owner);

        if ($filters !== []) {
            $query = $this->applyFilters($query, $filters);
        }

        return $query->first();
    }

    /**
     * Get all entities for a schedulable resource with optional filtering.
     *
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $filters Query filters to apply
     * @return Collection<int, Model> Collection of entities
     * @throws MissingSchedulableException If schedulable not provided
     * @throws InvalidOwnerException If owner provided for Availability model
     * @throws MissingOwnerException If owner required but not provided
     */
    final public function all(Model $schedulable, ?Model $owner = null, array $filters = []): Collection
    {
        $this->validateSchedulableAndOwner($schedulable, $owner);

        $query = $this->buildSchedulableScopedQuery($schedulable);
        $query = $this->applyOwnerScope($query, $owner);

        if ($filters !== []) {
            $query = $this->applyFilters($query, $filters);
        }

        return $query->get();
    }

    /**
     * Get paginated entities for a schedulable resource.
     *
     * @param Model $schedulable The schedulable resource model
     * @param Model|null $owner Optional owner model for access control
     * @param array<string, mixed> $filters Query filters to apply
     * @param int $perPage Number of items per page
     * @param array<int, string> $columns Columns to select
     * @param string $pageName Query parameter name for pagination
     * @param int|null $page Current page number
     * @return LengthAwarePaginator Paginated collection of entities
     * @throws MissingSchedulableException If schedulable not provided
     * @throws InvalidOwnerException If owner provided for Availability model
     * @throws MissingOwnerException If owner required but not provided
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
        $query = $this->applyOwnerScope($query, $owner);

        return $query->paginate(
            perPage: $perPage,
            columns: $columns,
            pageName: $pageName,
            page: $page
        );
    }

    /* ========= Specialized Queries ========= */

    /**
     * Finds impediments overlapping with a specific time slot.
     *
     * @param int $availabilityId Availability identifier
     * @param Carbon $start Start time
     * @param Carbon $end End time
     * @return Collection<int, Impediment> Overlapping impediments
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

    /* ========= Filter Management ========= */

    /**
     * Sets a filter value.
     *
     * @param string $key Filter key
     * @param mixed $value Filter value
     * @return static
     */
    public function setFilter(string $key, mixed $value): static
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * Clears all active filters.
     *
     * @return static
     */
    public function clearFilters(): static
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Gets all active filters.
     *
     * @return array<string, mixed> Current filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Checks if a filter is set.
     *
     * @param string $key Filter key
     * @return bool True if filter is set
     */
    public function hasFilter(string $key): bool
    {
        return array_key_exists($key, $this->filters);
    }

    /* ========= Protected Helper Methods ========= */

    /**
     * Builds a query with schedulable scope and applied filters.
     *
     * @param Model $schedulable Schedulable entity
     * @param array<string, mixed> $filters Query filters
     * @return Builder Query builder
     * @throws MissingSchedulableException If schedulable not provided
     */
    public function buildQueryWithFilters(Model $schedulable, array $filters): Builder
    {
        $this->validateSchedulable($schedulable);

        $builder = $this->buildSchedulableScopedQuery($schedulable);
        return $this->applyFilters($builder, $filters);
    }

    /**
     * Applies filters to a query builder.
     *
     * Supports:
     * - Array values: WHERE IN clause
     * - Fields containing 'start': WHERE >= value
     * - Fields containing 'end': WHERE <= value
     * - String values: WHERE LIKE pattern
     * - Other values: WHERE = value
     *
     * @param Builder $builder Query builder
     * @param array<string, mixed> $filters Filters to apply
     * @return Builder Modified query builder
     */
    protected function applyFilters(Builder $builder, array $filters = []): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value === null) {
                continue;
            }

            $lowerField = strtolower($field);

            match (true) {
                is_array($value) => $builder->whereIn($field, $value),

                str_contains($lowerField, 'start') => $builder->where($field, '>=', $value),

                str_contains($lowerField, 'end') => $builder->where($field, '<=', $value),

                is_string($value) => $builder->where($field, 'LIKE', '%' . $value . '%'),

                default => $builder->where($field, $value),
            };
        }

        return $builder;
    }

    /**
     * Gets an instance of the managed model.
     *
     * @return TModel Model instance
     * @throws LogicException When model class cannot be resolved or instantiated
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

    /* ========= Private Helper Methods ========= */

    /**
     * Determines if the managed model is an Availability or its subclass.
     *
     * @return bool True if model is Availability
     */
    private function isAvailabilityModel(): bool
    {
        $modelClass = $this->getModelClass();

        return $modelClass === Availability::class || is_subclass_of($modelClass, Availability::class);
    }

    /**
     * Gets the fully qualified model class name.
     *
     * @return string Model class name
     */
    private function getModelClass(): string
    {
        return $this->modelClass ?? $this->resolveModelClass();
    }

    /**
     * Resolves the model class name based on repository class name convention.
     *
     * @return string Fully qualified model class name
     */
    private function resolveModelClass(): string
    {
        $repositoryClass = static::class;
        $shortName = (new ReflectionClass($repositoryClass))->getShortName();

        $modelName = str_replace('Repository', '', $shortName);

        return 'Roster\Models\\' . $modelName;
    }

    /**
     * Validates that a schedulable entity is provided.
     *
     * @param Model|null $schedulable Schedulable entity
     * @throws MissingSchedulableException When no schedulable is provided
     */
    private function validateSchedulable(?Model $schedulable): void
    {
        if (!$schedulable instanceof Model) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Validates that no owner is provided for Availability models.
     *
     * @param Model|null $owner Owning entity
     * @throws InvalidOwnerException When owner is provided for Availability model
     */
    private function validateOwnerForAvailability(?Model $owner): void
    {
        if ($owner instanceof Model && $this->isAvailabilityModel()) {
            throw InvalidOwnerException::forAvailability();
        }
    }

    /**
     * Validates that an owner is provided for non-Availability models.
     *
     * @param Model|null $owner Owning entity
     * @throws MissingOwnerException When owner is not provided for non-Availability model
     */
    private function validateOwnerForNonAvailability(?Model $owner): void
    {
        if (!$this->isAvailabilityModel() && !$owner instanceof Model) {
            throw MissingOwnerException::create($this->getModelClass());
        }
    }

    /**
     * Validates both schedulable and owner according to model-specific rules.
     *
     * @param Model|null $schedulable Schedulable entity
     * @param Model|null $owner Owning entity
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
     * Builds a base query scoped to a specific schedulable entity.
     *
     * @param Model $schedulable Schedulable entity
     * @return Builder Query builder
     */
    private function buildSchedulableScopedQuery(Model $schedulable): Builder
    {
        $model = $this->getModel();

        return $model::query()
            ->where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable));
    }

    /**
     * Applies owner constraint to a query builder for non-Availability models.
     *
     * @param Builder $builder Query builder
     * @param Model|null $owner Owning entity
     * @return Builder Modified query builder
     */
    private function applyOwnerScope(Builder $builder, ?Model $owner): Builder
    {
        if ($owner instanceof Model && !$this->isAvailabilityModel()) {
            $builder->where('availability_id', $owner->id);
        }

        return $builder;
    }

    /**
     * Injects owner relationship into data array for non-Availability models.
     *
     * @param array<string, mixed> $data Entity data
     * @param Model|null $owner Owning entity
     * @return array<string, mixed> Modified data
     * @throws MissingOwnerException When owner is required but not provided
     */
    private function injectOwnerIntoData(array $data, ?Model $owner): array
    {
        $this->validateOwnerForNonAvailability($owner);

        if ($owner instanceof Model && !$this->isAvailabilityModel()) {
            $data['availability_id'] = $owner->id;
        }

        return $data;
    }
}
