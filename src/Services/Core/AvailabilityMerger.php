<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\AvailabilityMergerInterface;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;

class AvailabilityMerger implements AvailabilityMergerInterface
{
    public function __construct(
        private AvailabilityValidatorInterface $availabilityValidator,
        private AvailabilityRepositoryInterface $availabilityRepository,
        private ValidationServiceInterface $validationService
    ) {}

    /**
     * Merge new availability data with adjacent existing ones.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function mergeWithAdjacent(array $data, object $schedulable): array
    {
        $adjacentAvailabilities = $this->findAdjacentAvailabilities($data, $schedulable);

        if ($adjacentAvailabilities->isEmpty()) {
            return $data;
        }

        $mergedData = $data;
        $idsToDelete = [];

        foreach ($adjacentAvailabilities as $adjacentAvailability) {
            try {
                $tempAvailability = $this->createAvailabilityFromData($mergedData, $schedulable);

                if ($this->availabilityValidator->areAdjacent($tempAvailability, $adjacentAvailability)) {
                    $mergedData = $this->availabilityValidator->mergeAdjacent($tempAvailability, $adjacentAvailability);
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
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findAdjacentAvailabilities(array $data, object $schedulable): Collection
    {
        $availabilities = $this->availabilityRepository->findAdjacentAvailabilities($schedulable, $data);
        $tempAvailability = $this->createAvailabilityFromData($data, $schedulable);

        return $availabilities->filter(function (Availability $availability) use ($tempAvailability): bool {
            return $this->availabilityValidator->areAdjacent($tempAvailability, $availability);
        });
    }

    /**
     * Create temporary Availability object from data.
     *
     * @param array<string, mixed> $data
     */
    private function createAvailabilityFromData(array $data, object $schedulable): Availability
    {
        ['start' => $startTime, 'end' => $endTime] = $this->validationService
            ->parseAndValidateTimeRange($data);

        $availability = new Availability;
        $availability->schedulable_id = $schedulable->id;
        $availability->schedulable_type = get_class($schedulable);
        $availability->start_time = $startTime;
        $availability->end_time = $endTime;
        $availability->days = $data['days'] ?? [];
        $availability->type = $data['type'] ?? null;

        if (isset($data['start_date'])) {
            $availability->start_date = Carbon::parse($data['start_date']);
        }

        if (isset($data['end_date'])) {
            $availability->end_date = Carbon::parse($data['end_date']);
        }

        return $availability;
    }
}
