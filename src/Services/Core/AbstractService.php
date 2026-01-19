<?php

declare(strict_types=1);

namespace Roster\Services\Core;

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
use Roster\DTOs\AvailabilityDto;
use Roster\DTOs\ImpedimentDto;
use Roster\DTOs\ScheduleDto;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Exceptions\DirectServiceUsageException;
use Roster\Exceptions\InvalidServiceContextException;
use Roster\Support\RosterServiceContext;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\DTOs\ViolationData;
use Roster\Validation\Exceptions\ValidationFailedException;
use ReflectionClass;

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
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Current operation data.
     *
     * @var array<string, mixed>
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
        $this->guardAgainstDirectUsage();
    }

    /* ========= CRUD Operations ========= */

    /**
     * Creates a new entity.
     *
     * @param array<string, mixed> $data Entity creation data
     * @return Model The created entity
     * @throws ValidationFailedException If validation fails
     * @throws InvalidServiceContextException If service context is incomplete
     */
    public function create(array $data): Model
    {
        $this->requireContext();

        $this->prepareCreateData($data);

        $operationData = $this->createDTOFromArray($this->data, OperationType::CREATE);
        $this->validate($operationData->toArray(), OperationType::CREATE);
        $this->data = $operationData->toArray();

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
     * @param array<string, mixed> $data Update data
     * @return bool True if update successful
     * @throws ValidationFailedException If validation fails or entity not found
     * @throws InvalidServiceContextException If service context is incomplete
     */
    public function update(int $id, array $data): bool
    {
        $this->requireContext();
        $existingEntity = $this->find($id);

        if ($existingEntity === null) {
            throw $this->createEntityNotFoundValidationException(OperationType::UPDATE);
        }

        $preparedData = $this->prepareUpdateData($data, $existingEntity);
        $entityData = $this->createDTOFromArray($preparedData, OperationType::UPDATE);
        $this->validate($entityData->toArray(), OperationType::UPDATE, $id, $existingEntity);

        $result = $this->getCurrentRepository()->update(
            id: $id,
            schedulable: $this->schedulable,
            owner: $this->owner,
            data: $entityData->toArray()
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

        if ($entity === null) {
            throw $this->createEntityNotFoundValidationException(OperationType::DELETE);
        }

        $deleteData = $this->prepareDeleteData($id, $entity);
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
     * @return Model|null The found entity or null
     */
    public function find(int $id): ?Model
    {
        return $this->getCurrentRepository()->find(
            id: $id,
            schedulable: $this->schedulable,
            owner: $this->owner,
            filters: $this->filters
        );
    }

    /**
     * Retrieve the first resource matching current filters and context.
     *
     * @return Model|null The first matching resource or null if none found
     * @throws InvalidServiceContextException If service context is incomplete
     */
    public function first(): ?Model
    {
        $this->requireContext();

        return $this->getCurrentRepository()->first(
            $this->schedulable,
            $this->owner,
            $this->filters
        );
    }

    /**
     * Retrieves all entities.
     *
     * @return Collection<int, Model> All entities matching current context and filters
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
     * @param array<int, string> $columns Columns to select
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

    /* ========= Data & Filter Management ========= */

    /**
     * Replace all filters.
     *
     * @param array<string, mixed> $filters New filters
     * @return static
     */
    public function setFilters(array $filters): static
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * Clear all filters.
     *
     * @return static
     */
    public function resetFilters(): static
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Set a specific filter.
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
     * Set the context for a specific schedulable entity (fluent alias).
     *
     * @param Model $model Schedulable entity
     * @return static New instance with context set
     */
    final public function for(Model $model): static
    {
        $clone = clone $this;
        $clone->schedulable = $model;
        return $clone;
    }

    /**
     * Set the owner context (fluent alias).
     *
     * @param Model $model Owner entity
     * @return static New instance with context set
     */
    final public function owner(Model $model): static
    {
        $clone = clone $this;
        $clone->owner = $model;
        return $clone;
    }

    /**
     * Clear all contextual data (filters, data, schedulable).
     *
     * @return static
     */
    public function clear(): static
    {
        $this->data = [];
        $this->filters = [];
        return $this;
    }

    /* ========= Abstract Methods ========= */

    /**
     * Returns the entity type for this service.
     *
     * @return EntityType The entity type enum
     */
    abstract protected function getEntityTypeEnum(): EntityType;

    /* ========= Protected Helper Methods ========= */

    /**
     * Creates a DTO from array data based on entity type.
     *
     * @param array<string, mixed> $data Raw data
     * @param OperationType $operationType Operation being performed
     * @return AvailabilityDto|ScheduleDto|ImpedimentDto Appropriate DTO instance
     * @throws LogicException If entity type not supported
     */
    final protected function createDTOFromArray(array $data, OperationType $operationType): AvailabilityDto|ScheduleDto|ImpedimentDto
    {
        return match ($this->getEntityTypeEnum()) {
            EntityType::AVAILABILITY => AvailabilityDto::fromArray($data),
            EntityType::SCHEDULE => ScheduleDto::fromArray($data),
            EntityType::IMPEDIMENT => ImpedimentDto::fromArray($data),
            default => throw new LogicException('Unsupported entity type for DTO creation')
        };
    }

    /**
     * Validates data for an operation.
     *
     * @param array<string, mixed> $data Data to validate
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
     * Gets the current repository based on service type.
     *
     * @return AvailabilityRepositoryInterface|ScheduleRepositoryInterface|ImpedimentRepositoryInterface
     * @throws LogicException If service type not recognized
     */
    protected function getCurrentRepository(): AvailabilityRepositoryInterface|ScheduleRepositoryInterface|ImpedimentRepositoryInterface
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
     * Checks if this service handles Availability entities.
     *
     * @return bool True if this is an AvailabilityService
     */
    protected function isAvailabilityService(): bool
    {
        return str_contains(static::class, 'AvailabilityService');
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

    /* ========= Private Helper Methods ========= */

    /**
     * Prepares data for entity creation.
     *
     * @param array<string, mixed> $data
     */
    private function prepareCreateData(array $data): void
    {
        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

        if ($this->owner instanceof Model && !$this->isAvailabilityService()) {
            $this->data['availability_id'] = $this->owner->id;
        }
    }

    /**
     * Prepares data for entity update.
     *
     * @param array<string, mixed> $data
     * @param Model $existingEntity
     * @return array<string, mixed>
     */
    private function prepareUpdateData(array $data, Model $existingEntity): array
    {
        if (!$this->isAvailabilityService() && !isset($data['availability_id']) && isset($existingEntity->availability_id)) {
            $data['availability_id'] = $existingEntity->availability_id;
        }

        return $data;
    }

    /**
     * Prepares data for entity deletion.
     *
     * @param int $id
     * @param Model $entity
     * @return array<string, mixed>
     */
    private function prepareDeleteData(int $id, Model $entity): array
    {
        $deleteData = [
            'id' => $id,
            'schedulable_id' => $entity->schedulable_id ?? $this->schedulable->id,
            'schedulable_type' => $entity->schedulable_type ?? get_class($this->schedulable),
        ];

        if (!$this->isAvailabilityService() && isset($entity->availability_id)) {
            $deleteData['availability_id'] = $entity->availability_id;
        }

        return $deleteData;
    }

    /**
     * Creates a validation exception for non-existent entity.
     *
     * @param OperationType $operationType
     * @return ValidationFailedException
     */
    private function createEntityNotFoundValidationException(OperationType $operationType): ValidationFailedException
    {
        return ValidationFailedException::fromViolations(
            [
                new ViolationData(
                    field: 'id',
                    message: sprintf(
                        '%s with given ID does not exist for owner or schedulable',
                        $this->getEntityTypeEnum()->displayName()
                    )
                )
            ],
            $operationType,
            $this->getEntityTypeEnum()
        );
    }

    /**
     * Guards against direct service usage.
     *
     * @throws DirectServiceUsageException If service used without context
     */
    private function guardAgainstDirectUsage(): void
    {
        if (RosterServiceContext::isDirectUsage()) {
            throw DirectServiceUsageException::create(static::class);
        }
    }

    /**
     * Resolves the model class name from service class.
     *
     * @return string Fully qualified model class name
     */
    private function resolveModelClass(): string
    {
        $serviceClass = static::class;
        $shortName = (new ReflectionClass($serviceClass))->getShortName();
        $modelName = str_replace('Service', '', $shortName);

        return 'Roster\Models\\' . $modelName;
    }

    /* ========= Unused/Deprecated Methods (Cleanup) ========= */

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
     * Checks for entity-specific conflicts before persistence.
     *
     * @param mixed $dto Data transfer object
     * @param int|null $excludeId Entity ID to exclude from conflict checks
     */
    protected function checkEntityConflicts(mixed $dto, ?int $excludeId = null): void
    {
        // Default implementation - can be overridden by child classes
    }

    /* ========= Legacy Methods (For Backward Compatibility) ========= */

    /**
     * Gets current operation data.
     *
     * @return array<string, mixed> Current data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Sets operation data.
     *
     * @param array<string, mixed> $data Data to set
     * @return static
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets active filters.
     *
     * @return array<string, mixed> Current filters
     */
    public function getFilters(): array
    {
        return $this->filters;
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
     * @return static
     */
    public function setSchedulable(Model $model): static
    {
        $this->schedulable = $model;
        return $this;
    }

    /* ========= Magic Methods ========= */

    /**
     * Intercepts dynamic whereXyz method calls.
     *
     * @param string $method Method name
     * @param array<int, mixed> $arguments Method arguments
     * @return static
     * @throws BadMethodCallException If method not supported
     */
    public function __call(string $method, array $arguments): static
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
