<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Support\Collection;
use Roster\Models\Availability;

interface AvailabilityMergerInterface
{
    /**
     * Merge new availability data with adjacent existing ones.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function mergeWithAdjacent(array $data, object $schedulable): array;

    /**
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findAdjacentAvailabilities(array $data, object $schedulable): Collection;
}
