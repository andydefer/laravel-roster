<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Roster\Enums\OperationType;
use Roster\Validation\Exceptions\ValidationFailedException;
use Illuminate\Support\Collection;
use Roster\Enums\EntityType;
use Roster\Validation\Context\ValidationContext;
use BadMethodCallException;
use LogicException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Exceptions\InvalidServiceContextException;
use ReflectionClass;
use Roster\Contracts\Services\ServiceInterface;
use Roster\Exceptions\DirectServiceUsageException;
use Roster\Support\RosterServiceContext;

/**
 * Abstract service providing a complete CRUD template with dynamic repository resolution.
 */
abstract class AbstractService implements ServiceInterface
{
    protected ?Model $schedulable = null;

    protected ?Model $owner = null;

    protected array $filters = [];

    protected array $data = [];

    public function __construct(
        protected ValidatorInterface $validator,
        protected AvailabilityRepositoryInterface $availabilityRepository,
        protected ImpedimentRepositoryInterface $impedimentRepository,
        protected ScheduleRepositoryInterface $scheduleRepository,
        protected TemporalConflictService $conflictService
    ) {
        // Empêche l'instanciation directe du service
        $this->guardDirectUsage();
    }

    /**
     * Protège contre l'utilisation directe du service.
     */
    private function guardDirectUsage(): void
    {
        if (RosterServiceContext::isDirectUsage()) {
            throw DirectServiceUsageException::create(static::class);
        }
    }


    /**
     * Create a new entity.
     */
    public function create(array $data): mixed
    {
        $this->requireContext();

        // Add schedulable info
        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

        // Add owner info if applicable
        if ($this->owner instanceof Model && !$this->isAvailabilityService()) {
            $this->data['availability_id'] = $this->owner->id;
        }

        // Create DTO
        $dto = $this->createDTOFromArray($this->data, OperationType::CREATE);

        // Add schedulable info to DTO
        $dto = $this->addSchedulableInfoToDto($dto);

        // Validate
        $this->validate($dto->toArray(), OperationType::CREATE);

        // Check conflicts if applicable
        $this->checkEntityConflicts($dto);

        // Update data with complete DTO
        $this->data = $dto->toArray();

        // Create entity using repository
        $model = $this->getCurrentRepository()->create(
            data: $this->data,
            schedulable: $this->schedulable,
            owner: $this->owner
        );

        // Clear cache
        $this->clearEntityCache($model->id);

        return $model;
    }

    /**
     * Update an existing entity.
     */
    public function update(int $id, array $data): bool
    {
        $this->requireContext();
        $this->data = $data;

        // Find existing entity
        $existingEntity = $this->find($id);
        if (!$existingEntity) {
            throw ValidationFailedException::fromViolations(
                ['id' => sprintf('%s with given ID does not exist for owner or schedulable', $this->getEntityTypeEnum()->displayName())],
                OperationType::UPDATE,
                $this->getEntityTypeEnum()
            );
        }

        // Add ID for validation
        $data['id'] = $id;

        // Ensure availability_id is present for non-availability entities
        if (!$this->isAvailabilityService() && !isset($data['availability_id']) && isset($existingEntity->availability_id)) {
            $data['availability_id'] = $existingEntity->availability_id;
        }

        // Create DTO
        $entityData = $this->createDTOFromArray($data, OperationType::UPDATE);

        // Validate
        $this->validate($entityData->toArray(), OperationType::UPDATE, $id, $existingEntity);

        // Check conflicts with exclusion
        $this->checkEntityConflicts($entityData, $id);

        // Update data with complete DTO
        $this->data = $entityData->toArray();

        // Update entity
        $result = $this->getCurrentRepository()->update(
            id: $id,
            data: $this->data,
            owner: $this->owner,
            schedulable: $this->schedulable,
        );

        // Clear cache if needed
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * Delete an entity.
     */
    final public function delete(int $id): bool
    {
        $this->requireContext();
        $entity = $this->find($id);

        if (!$entity) {
            throw ValidationFailedException::fromViolations(
                ['id' => sprintf('%s with given ID does not exist', $this->getEntityTypeEnum()->displayName())],
                OperationType::DELETE,
                $this->getEntityTypeEnum()
            );
        }

        // Prepare validation data
        $deleteData = [
            'id' => $id,
            'schedulable_id' => $entity->schedulable_id ?? $this->schedulable->id,
            'schedulable_type' => $entity->schedulable_type ?? get_class($this->schedulable),
        ];

        // Add availability_id for non-availability entities
        if (!$this->isAvailabilityService() && isset($entity->availability_id)) {
            $deleteData['availability_id'] = $entity->availability_id;
        }

        // Validate deletion
        $this->validate($deleteData, OperationType::DELETE, $id);

        // Delete entity
        $result = $this->getCurrentRepository()->delete(
            id: $id,
            schedulable: $this->schedulable,
            owner: $this->owner
        );

        // Clear cache if needed
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * Find entity by ID.
     */
    final public function find(int $id): mixed
    {
        $repository = $this->getCurrentRepository();

        // Use repository's find method if it exists
        if (method_exists($repository, 'find')) {
            return $repository->find($id, $this->schedulable, $this->owner, $this->filters);
        }

        // Fallback to direct Eloquent query
        $modelClass = $this->resolveModelClass();
        $query = $modelClass::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        if ($this->owner instanceof Model && !$this->isAvailabilityService()) {
            $query->where('availability_id', $this->owner->id);
        }

        return $query->find($id);
    }

    /**
     * Get all entities.
     */
    final public function all(): Collection
    {
        return $this->getCurrentRepository()->all($this->schedulable, $this->owner, $this->filters);
    }

    /**
     * Paginate entities.
     */
    final public function paginate(
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
     * Vérifie que le contexte du service est complet avant toute action.
     */
    protected function requireContext(): void
    {
        if (!$this->schedulable instanceof Model) {
            throw InvalidServiceContextException::forService(static::class);
        }
    }

    /**
     * Template method for DTO creation from array.
     */
    abstract protected function createDTOFromArray(array $data, OperationType $operationType): mixed;

    /**
     * Get the entity type as an enum.
     */
    abstract protected function getEntityTypeEnum(): EntityType;

    /**
     * Add schedulable info to DTO.
     */
    protected function addSchedulableInfoToDto(mixed $dto): mixed
    {
        if (method_exists($dto, 'withSchedulableInfo')) {
            return $dto->withSchedulableInfo(
                $this->schedulable->id,
                get_class($this->schedulable)
            );
        }

        return $dto;
    }

    /**
     * Check entity conflicts.
     */
    protected function checkEntityConflicts(mixed $dto, ?int $excludeId = null): void
    {
        // This method can be overridden by child classes
        // Default implementation does nothing
    }

    /**
     * Validate data.
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
            schedulable: $this->schedulable,
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
     * Get current repository dynamically.
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
     * Resolve model class dynamically.
     */
    protected function resolveModelClass(): string
    {
        $serviceClass = static::class;
        $shortName = (new ReflectionClass($serviceClass))->getShortName();
        $modelName = str_replace('Service', '', $shortName);

        return 'Roster\Models\\' . $modelName;
    }

    /**
     * Check if this is an Availability service.
     */
    protected function isAvailabilityService(): bool
    {
        return str_contains(static::class, 'AvailabilityService');
    }

    /**
     * Clear entity cache (to be implemented by child classes if needed).
     */
    protected function clearEntityCache(int $entityId): void
    {
        // Implementation if caching is needed
    }

    // Common getters and setters

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function resetFilters(): self
    {
        $this->filters = [];
        return $this;
    }

    public function setFilter(string $key, $value): self
    {
        $this->filters[$key] = $value;
        return $this;
    }

    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    public function setSchedulable(Model $model): self
    {
        $this->schedulable = $model;
        return $this;
    }

    final public function for(Model $model): static
    {
        $clone = clone $this;
        $clone->schedulable = $model;
        return $clone;
    }

    final public function owner(Model $model): static
    {
        $clone = clone $this;
        $clone->owner = $model;
        return $clone;
    }

    public function clear(): self
    {
        $this->data = [];
        $this->filters = [];
        return $this;
    }

    /**
     * Intercept dynamic method calls for "whereXyz" methods.
     * @param array<int, mixed> $arguments
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
