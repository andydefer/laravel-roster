<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Database\Eloquent\Model;
use Roster\Models\Availability;
use Roster\Exceptions\ValidationException;

interface AvailabilityValidatorInterface
{
    /**
     * Validate the basic data for creating or updating an availability.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    public function validateBasicData(array $data): void;

    /**
     * Check if the given data overlaps with existing availabilities.
     *
     * @param object $schedulable
     * @param array<string, mixed> $data
     *
     */
    public function hasOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): bool;

    /**
     * Determine if two Availability instances are adjacent.
     *
     *
     */
    public function areAdjacent(Availability $availability1, Availability $availability2): bool;

    /**
     * Merge two adjacent Availability instances into a single array of data.
     *
     *
     * @return array<string, mixed>
     */
    public function mergeAdjacent(Availability $availability1, Availability $availability2): array;
}
