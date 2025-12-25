<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Roster\Models\Impediment;
use ReflectionClass;
use LogicException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Roster\Contracts\RepositoryInterface;
use Roster\Support\RosterMutationContext;

/**
 * Abstract base repository providing common CRUD operations for Eloquent models.
 *
 * @template TModel of Model
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The model class managed by the repository.
     *
     * @var class-string<Model>
     */
    protected string $modelClass;

    private function getModel(): Model
    {
        if (isset($this->modelClass)) {
            return app()->make($this->modelClass);
        }

        // Déduire le modèle à partir du nom du repository
        $repositoryClass = static::class; // ex: Roster\Repositories\ImpedimentRepository
        $shortName = (new ReflectionClass($repositoryClass))->getShortName(); // ex: ImpedimentRepository
        $modelName = str_replace('Repository', '', $shortName); // ex: Impediment
        $modelClass = 'Roster\Models\\' . $modelName;

        if (!class_exists($modelClass)) {
            throw new LogicException(sprintf('Model class %s not found for repository %s', $modelClass, $repositoryClass));
        }

        $this->modelClass = $modelClass;

        return app()->make($this->modelClass);
    }

    final public function create(array $data): Model
    {
        return RosterMutationContext::allow(function () use ($data) {
            $model = $this->getModel();

            return $model::create($data);
        });
    }

    final public function update(int $id, array $data): bool
    {
        return RosterMutationContext::allow(function () use ($id, $data): bool {
            $model = $this->find($id);
            return $model instanceof Model && $model->update($data);
        });
    }

    final public function delete(int $id): bool
    {
        return RosterMutationContext::allow(function () use ($id) {
            $model = $this->find($id);
            return $model instanceof Model ? $model->delete() : false;
        });
    }

    /**
     * Find a record by its ID.
     *
     * @return TModel|null
     */
    final public function find(int $id): ?Model
    {
        $model = $this->getModel();

        return $model::find($id);
    }

    public function buildQueryWithFilters($schedulable, array $filters): Builder
    {
        $model = $this->getModel();

        $builder = $model::query()
            ->where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable));

        return $this->applyFilters($builder, $filters);
    }


    /**
     * Get all records.
     *
     * @return Collection<int, TModel>
     */
    final public function all(Model $schedulable, ?Model $owner = null, array $filters = []): Collection
    {
        $model = $this->getModel();

        $result = $model::query()
            ->where('schedulable_id', $schedulable->id)
            ->where('schedulable_type', get_class($schedulable))
            // Si owner est défini, on filtre par availability_id
            ->when($owner !== null, fn($query) => $query->where('availability_id', $owner->id))
            // Appliquer les filtres dynamiques
            ->when(!empty($filters), function ($query) use ($filters, $model) {
                foreach ($filters as $field => $value) {
                    $lowerField = strtolower($field);
                    if (str_contains($lowerField, 'start')) {
                        $query->where($field, '>=', $value);
                    } elseif (str_contains($lowerField, 'end')) {
                        $query->where($field, '<=', $value);
                    } else {
                        $query->where($field, 'LIKE', '%' . $value . '%');
                    }
                }
            })
            ->get();


        return $result;
    }


    /**
     * Get paginated records.
     *
     * @param array<int, string> $columns
     */
    final public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        $model = $this->getModel();
        return $model::paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Get available slots between impediments.
     */
    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection {
        $availableSlots = collect();
        $currentTime = $start->copy();

        if ($impediments->isEmpty()) {
            $availableSlots->push([
                'start' => $start->copy(),
                'end' => $end->copy(),
            ]);
            return $availableSlots;
        }

        // Trier les impediments par start_datetime
        /** @var Collection<int, Impediment> $sortedImpediments */
        $sortedImpediments = $impediments->sortBy('start_datetime');


        foreach ($sortedImpediments as $sortedImpediment) {
            $impStart = $sortedImpediment->start_datetime;
            $impEnd = $sortedImpediment->end_datetime;

            // S'il y a un espace avant l'impediment
            if ($impStart->gt($currentTime)) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            // Avancer le temps courant à la fin de l'impediment
            $currentTime = max($currentTime, $impEnd);
        }

        // S'il reste du temps après le dernier impediment
        if ($currentTime->lt($end)) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }

    /**
     * Find impediments for a time slot.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @return Collection<int, Impediment>
     */
    public function findForTimeSlot(
        int $availabilityId,
        Carbon $start,
        Carbon $end
    ): Collection {

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
     * @param Builder $query
     * @param array<string, mixed> $filters
     */
    protected function applyFilters(Builder $builder, array $filters = []): Builder
    {
        foreach ($filters as $field => $value) {
            if (is_null($value)) {
                continue;
            }

            if (is_array($value)) {
                $builder->whereIn($field, $value);
            } else {
                $builder->where($field, $value);
            }
        }

        return $builder;
    }

    /**
     * Active filters.
     *
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * Add or override a filter.
     */
    public function setFilter(string $key, mixed $value): self
    {
        $this->filters[$key] = $value;

        return $this;
    }

    /**
     * Clear all active filters.
     */
    public function clearFilters(): self
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Get all active filters.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Check if a specific filter exists.
     */
    public function hasFilter(string $key): bool
    {
        return array_key_exists($key, $this->filters);
    }
}
