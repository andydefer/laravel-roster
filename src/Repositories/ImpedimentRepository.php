<?php

declare(strict_types=1);

namespace Roster\Repositories;

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
     * Calculate available time slots by removing impediments from a time range.
     *
     * @param Carbon $start Start of the time range
     * @param Carbon $end End of the time range
     * @param Collection $impediments Collection of impediments
     * @return Collection<int, array<string, mixed>> Available time slots
     */
    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection {
        if ($impediments->isEmpty()) {
            return collect([['start' => $start, 'end' => $end]]);
        }

        $availableSlots = collect();
        $currentTime = $start->copy();

        /** @var Impediment $impediment */
        foreach ($impediments as $impediment) {
            $impStart = $impediment->start_datetime;
            $impEnd = $impediment->end_datetime;

            if ($impStart->gt($currentTime)) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            $currentTime = $currentTime->gt($impEnd) ? $currentTime : $impEnd;
        }

        if ($currentTime->lt($end)) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }
}
