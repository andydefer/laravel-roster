<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Traits\DateRangeOverlapTrait;

class AvailabilityRepository extends AbstractRepository implements AvailabilityRepositoryInterface
{
    use DateRangeOverlapTrait;

    protected ValidationServiceInterface $validationService;

    public function __construct(ValidationServiceInterface $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Create a new availability.
     */
    public function create(array $data): Availability
    {
        return Availability::create($data);
    }

    /**
     * Update an existing availability.
     */
    public function update(int $id, array $data): bool
    {
        $availability = $this->findById($id);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $availability->update($data);
    }

    public function getForDateRange(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->where(function ($query) use ($end): void {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $end);
            })
            ->where(function ($query) use ($start): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            });

        if ($type) {
            $builder->where('type', $type);
        }

        return $builder->get();
    }

    public function findForTimeSlotWithOverlaps(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        $this->validationService->validateTimeRange($start, $end);

        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->when($type, function ($query) use ($type): void {
                $query->where('type', $type);
            })
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'))
            ->where(function ($q) use ($start): void {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $start->toDateString());
            })
            ->where(function ($q) use ($end): void {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $end->toDateString());
            })
            // Sous-requêtes pour les overlaps
            ->withExists(['schedules as has_overlapping_schedules' => function ($query) use ($start, $end): void {
                $query->where('start_datetime', '<', $end)
                    ->where('end_datetime', '>', $start);
            }])
            ->withExists(['impediments as has_overlapping_impediments' => function ($query) use ($start, $end): void {
                $query->where('start_datetime', '<', $end)
                    ->where('end_datetime', '>', $start);
            }])
            ->first();
    }

    /**
     * Delete an availability.
     */
    public function delete(int $id): bool
    {
        $availability = $this->findById($id);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $availability->delete();
    }

    /**
     * Delete multiple availabilities by IDs.
     */
    public function deleteMultiple(array $ids): bool
    {
        return Availability::whereIn('id', $ids)->delete() > 0;
    }

    /**
     * Find availability by ID.
     */
    public function findById(int $id): ?Availability
    {
        return Availability::find($id);
    }



    /**
     * Find availability for a time slot.
     */
    public function findForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        $this->validationService->validateTimeRange($start, $end);

        $builder = $this->buildBaseQuery($model);

        if ($type) {
            $builder->where('type', $type);
        }

        $this->applyTimeSlotFilters($builder, $start, $end);

        /** @var Availability|null $availability */
        $availability = $builder->first();

        return $availability;
    }

    /**
     * Get availabilities for a specific date.
     *
     * @return Collection<int, Availability>
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type) {
            $builder->where('type', $type);
        }

        $this->applyDateFilters($builder, $date);

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $builder->orderBy('start_time')->get();

        return $availabilities;
    }

    /**
     * Get all availabilities.
     *
     * @return Collection<int, Availability>
     */
    public function getAll(): Collection
    {
        return Availability::query()
            ->orderBy('start_time')
            ->get();
    }



    /**
     * Get all availabilities for a schedulable.
     *
     * @return Collection<int, Availability>
     */
    public function getAllForSchedulable(
        Model $model,
        ?string $type = null,
        ?string $day = null
    ): Collection {
        $builder = $this->buildBaseQuery($model)
            ->with(['schedules', 'impediments']);; // précharge la relation principale pour éviter N+1

        if ($type) {
            $builder->where('type', $type);
        }

        if ($day) {
            $builder->whereJsonContains('days', strtolower($day));
        }

        return $builder->orderBy('start_time')->get();
    }

    /**
     * Check if schedulable is available at specific datetime.
     */
    public function isAvailableAt(
        Model $model,
        Carbon $datetime
    ): bool {
        $dayOfWeek = strtolower($datetime->englishDayOfWeek);
        $time = $datetime->format('H:i:s');

        $builder = $this->buildBaseQuery($model)
            ->whereJsonContains('days', $dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time);

        $this->applyDateFilters($builder, $datetime);

        return $builder->exists();
    }

    /**
     * Find overlapping availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): Collection {
        ['start' => $startTime, 'end' => $endTime] = $this->validationService
            ->parseAndValidateTimeRange($data);

        $days = $data['days'] ?? [];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

        $builder = $this->buildBaseQuery($model);

        if ($exceptId !== null) {
            $builder->where('id', '!=', $exceptId);
        }

        // Appliquer les filtres directement dans la requête SQL
        if (!empty($days)) {
            $builder->where(function ($query) use ($days): void {
                foreach ($days as $day) {
                    $query->orWhereJsonContains('days', $day);
                }
            });
        }

        // Filtrer par chevauchement horaire
        $builder->where(function ($query) use ($startTime, $endTime): void {
            $query->where(function ($q) use ($startTime, $endTime): void {
                $q->where('start_time', '<', $endTime->format('H:i:s'))
                    ->where('end_time', '>', $startTime->format('H:i:s'));
            });
        });

        // Filtrer par chevauchement des dates
        $builder->where(function ($query) use ($startDate, $endDate): void {
            $query->where(function ($q) use ($startDate, $endDate): void {
                // Aucune date de fin pour l'existant ou la nouvelle
                $q->whereNull('start_date')
                    ->orWhereNull('end_date')
                    ->orWhere(function ($subQuery) use ($startDate, $endDate): void {
                        if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
                            // Les deux ont des dates, vérifier le chevauchement
                            $subQuery->where('start_date', '<=', $endDate)
                                ->where('end_date', '>=', $startDate);
                        } elseif ($startDate instanceof Carbon) {
                            // Seule la nouvelle a une date de début
                            $subQuery->where('end_date', '>=', $startDate)
                                ->orWhereNull('end_date');
                        } elseif ($endDate instanceof Carbon) {
                            // Seule la nouvelle a une date de fin
                            $subQuery->where('start_date', '<=', $endDate)
                                ->orWhereNull('start_date');
                        }
                    });
            });
        });

        // OPTIMISATION: Eager loading des relations pour éviter les N+1 ultérieurs
        $builder->with([
            'schedules' => function ($query) use ($startDate, $endDate): void {
                // Filtrer uniquement les schedules potentiellement pertinents
                if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
                    $query->where(function ($q) use ($startDate, $endDate): void {
                        $q->whereBetween('start_datetime', [$startDate, $endDate])
                            ->orWhereBetween('end_datetime', [$startDate, $endDate])
                            ->orWhere(function ($subQ) use ($startDate, $endDate): void {
                                $subQ->where('start_datetime', '<', $startDate)
                                    ->where('end_datetime', '>', $endDate);
                            });
                    });
                }

                $query->orderBy('start_datetime');
            },
            'impediments' => function ($query) use ($startDate, $endDate): void {
                // Filtrer uniquement les impediments potentiellement pertinents
                if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
                    $query->where(function ($q) use ($startDate, $endDate): void {
                        $q->whereBetween('start_datetime', [$startDate, $endDate])
                            ->orWhereBetween('end_datetime', [$startDate, $endDate])
                            ->orWhere(function ($subQ) use ($startDate, $endDate): void {
                                $subQ->where('start_datetime', '<', $startDate)
                                    ->where('end_datetime', '>', $endDate);
                            });
                    });
                }

                $query->orderBy('start_datetime');
            }
        ]);

        // OPTIMISATION: Ajouter des sous-requêtes pour les informations fréquemment utilisées
        $builder->withExists([
            'schedules as has_schedules',
            'impediments as has_impediments'
        ]);

        /** @var Collection<int, Availability> $overlappingAvailabilities */
        $overlappingAvailabilities = $builder->get();

        return $overlappingAvailabilities;
    }


    /**
     * Check if time ranges overlap.
     */
    public function timeRangesOverlap(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool {
        return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
    }

    /**
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findAdjacentAvailabilities(
        Model $model,
        array $data
    ): Collection {
        $type = $data['type'] ?? null;

        $builder = $this->buildBaseQuery($model);

        if ($type !== null) {
            $builder->where('type', $type);
        }

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $builder->get();

        return $availabilities;
    }

    /**
     * Apply filters to query.
     *
     * @return Builder
     */
    public function applyFilters(
        Model $model,
        array $filters = []
    ) {
        $builder = $this->buildBaseQuery($model);

        if (isset($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        if (isset($filters['day'])) {
            $builder->whereJsonContains('days', strtolower($filters['day']));
        }

        return $builder;
    }

    /**
     * Build base query for availabilities.
     */
    private function buildBaseQuery(Model $model): Builder
    {
        return Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model));
    }

    /**
     * Apply time slot filters to query.
     */
    private function applyTimeSlotFilters(Builder $builder, Carbon $start, Carbon $end): void
    {
        $builder->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'));

        $this->applyDateRangeFilters($builder, $start, $end);
    }

    /**
     * Apply date filters to query.
     */
    private function applyDateFilters(Builder $builder, Carbon $date): void
    {
        $this->applyDateRangeFilters($builder, $date, $date);
    }

    /**
     * Apply date range filters to query.
     */
    private function applyDateRangeFilters(Builder $builder, Carbon $startDate, Carbon $endDate): void
    {
        $builder->where(function ($q) use ($startDate): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $startDate->toDateString());
        })->where(function ($q) use ($endDate): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $endDate->toDateString());
        });
    }

    /**
     * Check if an availability applies to a specific date.
     *
     * @param Availability $availability The availability to check
     * @param Carbon $date The date to check
     * @return bool True if the availability applies to the date
     */


    public function availabilityAppliesToDate(Availability $availability, Carbon $date): bool
    {
        $dayOfWeek = strtolower($date->englishDayOfWeek);
        if (!in_array($dayOfWeek, $availability->days)) {
            return false;
        }

        if ($availability->start_date !== null && $date->lt($availability->start_date)) {
            return false;
        }

        return !($availability->end_date !== null && $date->gt($availability->end_date));
    }

    /**
     * Load availabilities with pre-loaded schedule and impediment conflicts.
     *
     * @param object $schedulable The schedulable entity
     * @param Carbon $start Start of the date range
     * @param Carbon $end End of the date range
     * @param string|null $type Optional availability type filter
     * @return Collection<Availability> Availabilities with conflicts loaded
     */
    public function loadAvailabilitiesWithConflicts(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection {
        $availabilities = $this->getForDateRange($schedulable, $start, $end, $type);
        return $availabilities->load(['schedules', 'impediments']);
    }

    /**
     * Filter availabilities for a specific date.
     *
     * @param Collection<Availability> $availabilities Collection of availabilities
     * @param Carbon $date Date to filter for
     * @return Collection<Availability> Filtered availabilities
     */
    public function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection
    {
        return $availabilities->filter(
            fn(Availability $availability): bool => $this->availabilityAppliesToDate($availability, $date)
        );
    }
}
