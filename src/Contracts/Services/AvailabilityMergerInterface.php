<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Roster\Models\Availability;

interface AvailabilityMergerInterface
{
    /**
     * Merge new availability data with adjacent existing availabilities.
     *
     * This method identifies adjacent availabilities and merges them with the new data
     * to create larger continuous availability blocks when possible.
     *
     * @param array<string, mixed> $data New availability data to merge
     * @param Model $model Schedulable entity model
     * @return array<string, mixed> Merged availability data
     */
    public function mergeWithAdjacent(array $data, Model $model): array;

    /**
     * Find availabilities adjacent to the specified time range.
     *
     * Adjacent availabilities are those that touch the new availability's time range
     * without overlapping, allowing them to be merged into a single continuous block.
     *
     * @param array<string, mixed> $data Availability data containing time range
     * @param Model $model Schedulable entity model
     * @return Collection<int, Availability> Collection of adjacent availabilities
     */
    public function findAdjacentAvailabilities(array $data, Model $model): Collection;
}
