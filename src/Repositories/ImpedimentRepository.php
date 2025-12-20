<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Models\Impediment;

/**
 * Repository for managing Impediment entities.
 */
class ImpedimentRepository extends AbstractRepository implements ImpedimentRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Impediment
    {
        return Impediment::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int $id, array $data): bool
    {
        $impediment = $this->findById($id);

        return $impediment instanceof Impediment
            ? $impediment->update($data)
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id): bool
    {
        $impediment = $this->findById($id);

        return $impediment instanceof Impediment
            ? $impediment->delete()
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function findById(int $id): ?Impediment
    {
        return Impediment::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): Collection
    {
        return Impediment::query()
            ->orderBy('start_datetime')
            ->get();
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
        return Impediment::where('availability_id', $availabilityId)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Check if a time slot has overlapping impediments.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param int|null $excludeId Impediment ID to exclude
     * @return bool True if overlapping impediments exist
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
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time range
     * @param Carbon $end End of time range
     * @param int|null $excludeId Impediment ID to exclude
     * @return Collection<int, Impediment>
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
