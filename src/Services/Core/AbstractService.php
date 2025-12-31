<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Roster\DTOs\AvailabilityData;
use Roster\DTOs\ScheduleData;
use Roster\DTOs\ImpedimentData;
use BadMethodCallException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use LogicException;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\ServiceInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Exceptions\DirectServiceUsageException;
use Roster\Exceptions\InvalidServiceContextException;
use Roster\Support\RosterServiceContext;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Exceptions\ValidationFailedException;
use ReflectionClass;
use Roster\Validation\DTOs\ViolationData;

/**
 * Abstract service providing a complete CRUD template with dynamic repository resolution.
 *
 * Serves as the foundation for all entity-specific services, handling common operations
 * such as creation, validation, conflict detection, and data persistence while enforcing
 * context-aware execution patterns.
 */
abstract class AbstractService implements ServiceInterface
{
    /**
     * The schedulable entity this service is operating on.
     */
    protected ?Model $schedulable = null;

    /**
     * The owning entity for nested resources (e.g., Availability for Schedule/Impediment).
     */
    protected ?Model $owner = null;

    /**
     * Active filters for query operations.
     * @var mixed[]
     */
    protected array $filters = [];

    /**
     * Current operation data.
     * @var mixed[]
     */
    protected array $data = [];

    /**
     * Initializes the service with required dependencies.
     *
     * @param ValidatorInterface $validator Validation service
     * @param AvailabilityRepositoryInterface $availabilityRepository Availability data access
     * @param ImpedimentRepositoryInterface $impedimentRepository Impediment data access
     * @param ScheduleRepositoryInterface $scheduleRepository Schedule data access
     * @param TemporalConflictService $conflictService Temporal conflict detection
     */
    public function __construct(
        protected ValidatorInterface $validator,
        protected AvailabilityRepositoryInterface $availabilityRepository,
        protected ImpedimentRepositoryInterface $impedimentRepository,
        protected ScheduleRepositoryInterface $scheduleRepository,
        protected TemporalConflictService $conflictService
    ) {
        $this->guardDirectUsage();
    }

    /**
     * Creates a new entity.
     *
     * @param array $data Entity creation data
     * @return mixed The created entity
     * @throws ValidationFailedException If validation fails
     * @throws InvalidServiceContextException If service context is incomplete
     */
    public function create(array $data): mixed
    {
        $this->requireContext();

        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

        if ($this->owner instanceof Model && !$this->isAvailabilityService()) {
            $this->data['availability_id'] = $this->owner->id;
        }

        $dto = $this->createDTOFromArray($this->data, OperationType::CREATE);
        $this->validate($dto->toArray(), OperationType::CREATE);
        $this->data = $dto->toArray();

        $model = $this->getCurrentRepository()->create(
            data: $this->data,
            schedulable: $this->schedulable,
            owner: $this->owner
        );

        $this->clearEntityCache($model->id);
        return $model;
    }

    /**
     * Updates an existing entity.
     *
     * @param int $id Entity identifier
     * @param array $data Update data
     * @return bool True if update successful
     * @throws ValidationFailedException If validation fails or entity not found
     * @throws InvalidServiceContextException If service context is incomplete
     */
    public function update(int $id, array $data): bool
    {
        $this->requireContext();
        $existingEntity = $this->find($id);

        if (!$existingEntity) {
            throw ValidationFailedException::fromViolations(
                [
                    new ViolationData(
                        field: 'id',
                        message: sprintf(
                            '%s with given ID does not exist for owner or schedulable',
                            $this->getEntityTypeEnum()->displayName()
                        )
                    )
                ],
                OperationType::UPDATE,
                $this->getEntityTypeEnum()
            );
        }

        if (!$this->isAvailabilityService() && !isset($data['availability_id']) && isset($existingEntity->availability_id)) {
            $data['availability_id'] = $existingEntity->availability_id;
        }

        $entityData = $this->createDTOFromArray($data, OperationType::UPDATE);
        $this->validate($entityData->toArray(), OperationType::UPDATE, $id, $existingEntity);

        $result = $this->getCurrentRepository()->update(
            id: $id,
            data: $entityData->toArray(),
            owner: $this->owner,
            schedulable: $this->schedulable,
        );

        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * Deletes an entity.
     *
     * @param int $id Entity identifier
     * @return bool True if deletion successful
     * @throws ValidationFailedException If validation fails or entity not found
     * @throws InvalidServiceContextException If service context is incomplete
     */
    public function delete(int $id): bool
    {
        $this->requireContext();
        $entity = $this->find($id);

        if (!$entity) {
            throw ValidationFailedException::fromViolations(
                [
                    new ViolationData(
                        field: 'id',
                        message: sprintf(
                            '%s with given ID does not exist',
                            $this->getEntityTypeEnum()->displayName()
                        )
                    )
                ],
                OperationType::DELETE,
                $this->getEntityTypeEnum()
            );
        }

        $deleteData = [
            'id' => $id,
            'schedulable_id' => $entity->schedulable_id ?? $this->schedulable->id,
            'schedulable_type' => $entity->schedulable_type ?? get_class($this->schedulable),
        ];

        if (!$this->isAvailabilityService() && isset($entity->availability_id)) {
            $deleteData['availability_id'] = $entity->availability_id;
        }

        $this->validate($deleteData, OperationType::DELETE, $id);

        $result = $this->getCurrentRepository()->delete(
            id: $id,
            schedulable: $this->schedulable,
            owner: $this->owner
        );

        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * Finds an entity by its identifier.
     *
     * @param int $id Entity identifier
     * @return mixed The found entity or null
     */
    public function find(int $id): mixed
    {
        $repository = $this->getCurrentRepository();

        if (method_exists($repository, 'find')) {
            return $repository->find(
                id: $id,
                schedulable: $this->schedulable,
                owner: $this->owner,
                filters: $this->filters
            );
        }

        $modelClass = $this->resolveModelClass();
        $query = $modelClass::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        if ($this->owner instanceof Model && !$this->isAvailabilityService()) {
            $query->where('availability_id', $this->owner->id);
        }

        return $query->find($id);
    }

    /**
     * Retrieves all entities.
     *
     * @return Collection All entities matching current context and filters
     */
    public function all(): Collection
    {
        return $this->getCurrentRepository()->all(
            schedulable: $this->schedulable,
            owner: $this->owner,
            filters: $this->filters
        );
    }

    /**
     * Paginates entities.
     *
     * @param int $perPage Number of items per page
     * @param array $columns Columns to select
     * @param string $pageName Page parameter name
     * @param int|null $page Current page number
     * @return LengthAwarePaginator Paginated results
     */
    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->getCurrentRepository()->paginate(
            schedulable: $this->schedulable,
            owner: $this->owner,
            filters: $this->filters,
            perPage: $perPage,
            columns: $columns,
            pageName: $pageName,
            page: $page
        );
    }

    /**
     * Creates a DTO from array data based on entity type.
     *
     * @param array $data Raw data
     * @param OperationType $operationType Operation being performed
     * @return mixed Appropriate DTO instance
     * @throws LogicException If entity type not supported
     */
    final protected function createDTOFromArray(array $data, OperationType $operationType): mixed
    {
        return match ($this->getEntityTypeEnum()) {
            EntityType::AVAILABILITY => AvailabilityData::fromArray($data),
            EntityType::SCHEDULE => ScheduleData::fromArray($data),
            EntityType::IMPEDIMENT => ImpedimentData::fromArray($data),
            default => throw new LogicException('Unsupported entity type for DTO creation')
        };
    }

    /**
     * Returns the entity type for this service.
     *
     * @return EntityType The entity type enum
     */
    abstract protected function getEntityTypeEnum(): EntityType;

    /**
     * Checks for entity-specific conflicts before persistence.
     *
     * @param mixed $dto Data transfer object
     * @param int|null $excludeId Entity ID to exclude from conflict checks
     */
    protected function checkEntityConflicts(mixed $dto, ?int $excludeId = null): void
    {
        // Default implementation - can be overridden by child classes
    }

    /**
     * Validates data for an operation.
     *
     * @param array $data Data to validate
     * @param OperationType $operationType Operation type
     * @param int|null $entityId Entity identifier (for updates/deletes)
     * @param object|null $currentEntity Current entity instance
     * @throws ValidationFailedException If validation fails
     */
    protected function validate(array $data, OperationType $operationType, ?int $entityId = null, ?object $currentEntity = null): void
    {
        $entityType = $this->getEntityTypeEnum();

        if ($currentEntity === null && $entityId !== null) {
            $currentEntity = $this->find($entityId);
        }

        $validationContext = new ValidationContext(
            operationType: $operationType,
            entityType: $entityType,
            data: $data,
            model: $this->schedulable,
            currentEntity: $currentEntity
        );

        $validationResult = $this->validator->validate($validationContext);

        if (!$validationResult->isValid()) {
            throw ValidationFailedException::fromViolations(
                $validationResult->getViolations(),
                $operationType,
                $entityType
            );
        }
    }

    /**
     * Ensures service context is complete before operations.
     *
     * @throws InvalidServiceContextException If schedulable is not set
     */
    protected function requireContext(): void
    {
        if (!$this->schedulable instanceof Model) {
            throw InvalidServiceContextException::forService(static::class);
        }
    }

    /**
     * Gets the current repository based on service type.
     *
     * @return mixed The appropriate repository instance
     * @throws LogicException If service type not recognized
     */
    protected function getCurrentRepository(): mixed
    {
        $serviceClass = static::class;

        return match (true) {
            str_contains($serviceClass, 'AvailabilityService') => $this->availabilityRepository,
            str_contains($serviceClass, 'ScheduleService') => $this->scheduleRepository,
            str_contains($serviceClass, 'ImpedimentService') => $this->impedimentRepository,
            default => throw new LogicException('Repository not defined for service: ' . $serviceClass)
        };
    }

    /**
     * Resolves the model class name from service class.
     *
     * @return string Fully qualified model class name
     */
    protected function resolveModelClass(): string
    {
        $serviceClass = static::class;
        $shortName = (new ReflectionClass($serviceClass))->getShortName();
        $modelName = str_replace('Service', '', $shortName);

        return 'Roster\Models\\' . $modelName;
    }

    /**
     * Checks if this service handles Availability entities.
     *
     * @return bool True if this is an AvailabilityService
     */
    protected function isAvailabilityService(): bool
    {
        return str_contains(static::class, 'AvailabilityService');
    }

    /**
     * Clears entity cache.
     *
     * @param int $entityId Entity identifier
     */
    protected function clearEntityCache(int $entityId): void
    {
        // Implementation if caching is needed
    }

    /**
     * Guards against direct service usage.
     *
     * @throws DirectServiceUsageException If service used without context
     */
    private function guardDirectUsage(): void
    {
        if (RosterServiceContext::isDirectUsage()) {
            throw DirectServiceUsageException::create(static::class);
        }
    }

    /**
     * Gets current operation data.
     *
     * @return mixed[] Current data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets operation data.
     *
     * @param array $data Data to set
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets active filters.
     *
     * @return mixed[] Current filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Sets filters.
     *
     * @param array $filters Filters to apply
     * @return $this
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Clears all filters.
     *
     * @return $this
     */
    public function resetFilters(): self
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Sets a specific filter.
     *
     * @param string $key Filter key
     * @param mixed $value Filter value
     * @return $this
     */
    public function setFilter(string $key, $value): self
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * Gets the current schedulable entity.
     *
     * @return Model|null The schedulable entity
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Sets the schedulable entity.
     *
     * @param Model $model Schedulable entity
     * @return $this
     */
    public function setSchedulable(Model $model): self
    {
        $this->schedulable = $model;
        return $this;
    }

    /**
     * Sets the service context to operate on a specific schedulable entity.
     *
     * @param Model $model Schedulable entity
     * @return static New service instance with context
     */
    final public function for(Model $model): static
    {
        $clone = clone $this;
        $clone->schedulable = $model;
        return $clone;
    }

    /**
     * Sets the owning entity for nested operations.
     *
     * @param Model $model Owning entity
     * @return static New service instance with owner
     */
    final public function owner(Model $model): static
    {
        $clone = clone $this;
        $clone->owner = $model;
        return $clone;
    }

    /**
     * Clears all data and filters.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->data = [];
        $this->filters = [];
        return $this;
    }

    /**
     * Intercepts dynamic whereXyz method calls.
     *
     * @param string $method Method name
     * @param array<int, mixed> $arguments Method arguments
     * @return $this
     * @throws BadMethodCallException If method not supported
     */
    public function __call(string $method, array $arguments): self
    {
        if (str_starts_with($method, 'where') && $arguments !== []) {
            $field = lcfirst(substr($method, 5));
            $this->setFilter($field, $arguments[0]);
            return $this;
        }

        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            static::class,
            $method
        ));
    }
}
