<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Roster\Models\Availability;
use Illuminate\Support\Carbon;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\AvailabilityCheckerInterface;
use Roster\Contracts\Services\ValidationServiceInterface;

class AvailabilityChecker implements AvailabilityCheckerInterface
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository,
        private ValidationServiceInterface $validationService
    ) {}

    /**
     * Check if the schedulable is available at a given time.
     */
    public function isAvailableAt(object $schedulable, Carbon $datetime): bool
    {
        return $this->availabilityRepository->isAvailableAt($schedulable, $datetime);
    }

    /**
     * Check availability for a time period.
     */
    public function isAvailableForPeriod(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($schedulable, $start, $end, $type);
        return $availability instanceof Availability;
    }

    /**
     * Check if there are overlaps.
     *
     * @param array<string, mixed> $data
     */
    public function hasOverlapping(
        object $schedulable,
        array $data,
        ?int $exceptId = null
    ): bool {
        $this->validationService->parseAndValidateTimeRange($data);

        // Note: This method should be implemented in AvailabilityValidator
        // For now, we'll delegate to repository's findOverlapping
        return $this->availabilityRepository->findOverlapping($schedulable, $data, $exceptId)->isNotEmpty();
    }
}
