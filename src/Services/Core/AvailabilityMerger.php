<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\AvailabilityMergerInterface;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;

/**
 * Service responsible for merging availability data with adjacent existing availabilities.
 *
 * This service handles the logic for detecting and merging overlapping or adjacent
 * availability periods to maintain data consistency and avoid conflicts.
 */
class AvailabilityMerger implements AvailabilityMergerInterface
{
    public function __construct(
        private readonly AvailabilityValidatorInterface $availabilityValidator,
        private readonly AvailabilityRepositoryInterface $availabilityRepository,
        private readonly ValidationServiceInterface $validationService
    ) {}

    /**
     * Merge new availability data with adjacent existing ones.
     *
     * This method identifies availabilities that are adjacent to the new data,
     * merges them when possible, and removes the merged entities to avoid duplicates.
     *
     * @param array<string, mixed> $data The new availability data to merge
     * @param Model $schedulable The schedulable entity (e.g., User, Team)
     *
     * @return array<string, mixed> The merged availability data
     */
    public function mergeAdjacentAvailabilities(array $data, Model $schedulable): array
    {
        $adjacentAvailabilities = $this->getAdjacentAvailabilities($data, $schedulable);

        if ($adjacentAvailabilities->isEmpty()) {
            return $data;
        }

        $mergedData = $data;
        $idsToDelete = [];

        foreach ($adjacentAvailabilities as $adjacentAvailability) {
            try {
                $temporaryAvailability = $this->createAvailabilityFromData($mergedData, $schedulable);

                if ($this->availabilityValidator->areAdjacent($temporaryAvailability, $adjacentAvailability)) {
                    $mergedData = $this->availabilityValidator->mergeAdjacent(
                        $temporaryAvailability,
                        $adjacentAvailability
                    );
                    $idsToDelete[] = $adjacentAvailability->id;
                }
            } catch (ValidationException) {
                continue;
            }
        }

        if ($idsToDelete !== []) {
            $this->availabilityRepository->deleteMultiple($idsToDelete);
        }

        return $mergedData;
    }

    /**
     * Find availabilities adjacent to the provided data.
     *
     * @param array<string, mixed> $data The availability data to check against
     * @param Model $schedulable The schedulable entity
     *
     * @return Collection<int, Availability> Collection of adjacent availabilities
     */
    public function getAdjacentAvailabilities(array $data, Model $schedulable): Collection
    {
        $existingAvailabilities = $this->availabilityRepository->findByType($schedulable, $data);
        $temporaryAvailability = $this->createAvailabilityFromData($data, $schedulable);

        return $existingAvailabilities->filter(
            fn(Availability $availability): bool => $this->availabilityValidator->areAdjacent(
                $temporaryAvailability,
                $availability
            )
        );
    }

    /**
     * Create a temporary Availability instance from raw data.
     *
     * @param array<string, mixed> $data The availability data
     * @param Model $schedulable The schedulable entity
     *
     * @return Availability A temporary Availability instance
     */
    private function createAvailabilityFromData(array $data, Model $schedulable): Availability
    {
        ['start' => $startTime, 'end' => $endTime] = $this->validationService
            ->parseAndValidateTimeRange($data);

        return new Availability([
            'schedulable_id' => $schedulable->id,
            'schedulable_type' => get_class($schedulable),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'days' => $data['days'] ?? [],
            'type' => $data['type'] ?? null,
            'start_date' => isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            'end_date' => isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
        ]);
    }
}
