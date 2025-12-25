<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use LogicException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\CrudInterface;
use Roster\Contracts\EntityServiceInterface;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Exceptions\InvalidServiceContextException;

/**
 * Abstract service providing a CRUD template.
 *
 * This abstract class implements basic CRUD operations.
 */
abstract class AbstractService implements EntityServiceInterface
{

    /**
     * The schedulable model instance.
     */
    protected ?Model $schedulable = null;

    protected ?Model $owner = null;

    /**
     * Current filters for data operations.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Current data payload for operations.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Common dependencies for all services.
     */
    protected ValidatorInterface $validator;

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    protected ScheduleRepositoryInterface $scheduleRepository;

    public function __construct(
        ValidatorInterface $validator,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository,
        ScheduleRepositoryInterface $scheduleRepository,
    ) {
        $this->validator = $validator;
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
        $this->scheduleRepository = $scheduleRepository;
    }


    /**
     * Vérifie que le contexte du service est complet avant toute action.
     *
     * Lance une exception pédagogique si le schedulable ou l'owner est manquant.
     */
    protected function requireContext(): void
    {
        if (!$this->schedulable instanceof Model) {
            throw InvalidServiceContextException::forService(static::class);
        }
    }

    abstract public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator;

    /**
     * Find entity by ID.
     *
     * @param int $id Entity ID
     * @return mixed Entity or null if not found
     */
    abstract public function find(int $id): mixed;

    /**
     * Get the current data payload.
     *
     * @return array<string, mixed> The current data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data payload.
     *
     * @param array<string, mixed> $data The data to set
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the current filters.
     *
     * @return array<string, mixed> The current filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Intercepte les appels dynamiques pour les méthodes "whereXyz".
     *
     * Exemple :
     *   $service->whereType('consultation');
     *   $service->whereReason('holiday');
     *
     * @param string $method Nom de la méthode appelée
     * @param array $arguments Arguments passés à la méthode
     * @return $this
     *
     * @throws \BadMethodCallException Si la méthode ne correspond pas au pattern whereXyz
     */
    public function __call(string $method, array $arguments): self
    {
        // Vérifie si la méthode commence par "where" (insensible à la casse)
        if (str_starts_with($method, 'where') && !empty($arguments)) {
            // Extrait le nom du champ à partir du nom de la méthode
            // whereType => type, whereReason => reason
            $field = lcfirst(substr($method, 5)); // enlève 'where' et passe la première lettre en minuscule

            // Définit le filtre avec la valeur fournie
            $this->setFilter($field, $arguments[0]);

            return $this;
        }

        throw new \BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            static::class,
            $method
        ));
    }

    /**
     * Set the filters.
     *
     * @param array<string, mixed> $filters The filters to set
     * @return $this
     */
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

    public function setFilter($key, $value): self
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * Get the schedulable model instance.
     *
     * @return Model|null The schedulable model or null if not set
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Set the schedulable model instance.
     *
     * @param Model $model The schedulable model
     * @return $this
     */
    public function setSchedulable(Model $model): self
    {
        $this->schedulable = $model;
        return $this;
    }

    /**
     * Scope the service to a specific schedulable model.
     *
     * @param Model $model The parent model to scope operations to
     * @return $this
     */
    final public function for(Model $model): static
    {
        $clone = clone $this;
        $clone->schedulable = $model;

        return $clone;
    }

    /**
     * Définit l'entité "parent" ou "owner" pour ce service.
     *
     * @param Model $model La disponibilité ou autre modèle parent
     * @return static Retourne une nouvelle instance du service avec l'owner défini
     */
    final public function owner(Model $model): static
    {
        // Créer un clone pour rester immuable
        $clone = clone $this;

        // Définir l'owner sur le clone
        $clone->owner = $model;

        return $clone;
    }


    /**
     * Clear the data and filters.
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
     * {@inheritDoc}
     */
    protected function getAvailabilityRepository(): AvailabilityRepositoryInterface
    {
        return $this->availabilityRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function getScheduleRepository(): ScheduleRepositoryInterface
    {
        return $this->scheduleRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function getImpedimentRepository(): ImpedimentRepositoryInterface
    {
        return $this->impedimentRepository;
    }

    /**
     * Get the "current" repository for this service.
     *
     * The child class must define the protected property $repositoryType
     * with one of: 'availability', 'schedule', 'impediment'.
     *
     * @return AvailabilityRepositoryInterface|ScheduleRepositoryInterface|ImpedimentRepositoryInterface
     */
    public function getCurrentRepository(): mixed
    {
        $childClass = static::class; // classe de l'enfant qui appelle

        return match (true) {
            str_contains($childClass, 'Availability') => $this->availabilityRepository,
            str_contains($childClass, 'Schedule') => $this->scheduleRepository,
            str_contains($childClass, 'Impediment') => $this->impedimentRepository,
            default => throw new LogicException('Repository not defined for child class ' . $childClass)
        };
    }


    /**
     * Get the validator.
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
