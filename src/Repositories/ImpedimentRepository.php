<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Models\Impediment;

class ImpedimentRepository extends AbstractRepository implements ImpedimentRepositoryInterface
{
    /**
     * Create a new impediment.
     */
    public function create(array $data): Impediment
    {
        return Impediment::create($data);
    }

    /**
     * Update an existing impediment.
     */
    public function update(int $id, array $data): bool
    {
        $impediment = $this->findById($id);

        if (! $impediment instanceof Impediment) {
            return false;
        }

        return $impediment->update($data);
    }

    /**
     * Delete an impediment.
     */
    public function delete(int $id): bool
    {
        $impediment = $this->findById($id);

        if (! $impediment instanceof Impediment) {
            return false;
        }

        return $impediment->delete();
    }

    /**
     * Find impediment by ID.
     */
    public function findById(int $id): ?Impediment
    {
        return Impediment::find($id);
    }

    /**
     * Get all impediments.
     */
    public function getAll(): Collection
    {
        return Impediment::query()
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Find impediments for a time slot.
     */
    public function findForTimeSlot(
        int $availabilityId,
        Carbon $start,
        Carbon $end
    ): Collection {
        return Impediment::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Check if a time slot has overlapping impediments.
     */
    public function hasOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): bool {
        $query = Impediment::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Find overlapping impediments with time range.
     */
    public function findOverlappingImpediments(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        ?int $excludeId = null
    ): Collection {
        $query = Impediment::where('availability_id', $availabilityId)
            ->where(function (Builder $builder) use ($start, $end): void {
                $builder->where('start_datetime', '<', $end)
                    ->where('end_datetime', '>', $start);
            });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }
}
