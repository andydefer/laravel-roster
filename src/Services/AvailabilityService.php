<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\AvailabilityCheckerInterface;
use Roster\Contracts\Services\AvailabilityMergerInterface;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Traits\FilterableTrait;

class AvailabilityService extends AbstractSchedulableService
{
    use FilterableTrait;

    private AvailabilityValidatorInterface $validator;
    private ValidationServiceInterface $validationService;
    private AvailabilityRepositoryInterface $repository;
    private AvailabilityMergerInterface $merger;
    private SlotFinderInterface $slotFinder;
    private AvailabilityCheckerInterface $checker;

    public function __construct(
        AvailabilityValidatorInterface $availabilityValidator,
        ValidationServiceInterface $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        AvailabilityMergerInterface $availabilityMerger,
        SlotFinderInterface $slotFinder,
        AvailabilityCheckerInterface $availabilityChecker
    ) {
        $this->validator = $availabilityValidator;
        $this->validationService = $validationService;
        $this->repository = $availabilityRepository;
        $this->merger = $availabilityMerger;
        $this->slotFinder = $slotFinder;
        $this->checker = $availabilityChecker;
    }

    /**
     * Create a new availability with overlap validation.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Availability
    {
        $this->validateSchedulable();

        // Validate basic data including time range
        $this->validator->validateBasicData($data);
        $this->validationService->parseAndValidateTimeRange($data);

        // Check for overlaps (always forbidden)
        if ($this->checker->hasOverlapping($this->schedulable, $data)) {
            throw ValidationException::withMessage('This availability overlaps with an existing one.');
        }

        // Automatic merging of adjacent availabilities (always enabled)
        $data = $this->merger->mergeWithAdjacent($data, $this->schedulable);

        // Prepare data for creation
        $availabilityData = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
        ]);

        // Delegate to repository
        return $this->repository->create($availabilityData);
    }

    /**
     * Update an existing availability.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $availability = $this->find($id);
        if (!$availability instanceof Availability) {
            return false;
        }

        if ($data !== []) {
            // Validate basic data
            $this->validator->validateBasicData($data);

            // Validate time range if time fields are being updated
            if (isset($data['start_time']) || isset($data['end_time'])) {
                $validationData = array_merge(
                    [
                        'start_time' => $availability->start_time?->format('H:i:s'),
                        'end_time' => $availability->end_time?->format('H:i:s'),
                    ],
                    $data
                );
                $this->validationService->parseAndValidateTimeRange($validationData);
            }

            // Prepare data for overlap check
            $checkData = $this->prepareCheckData($availability, $data);

            // Check for overlaps with other availabilities (always forbidden)
            if ($this->checker->hasOverlapping($this->schedulable, $checkData, $id)) {
                throw ValidationException::withMessage('This availability overlaps with an existing one.');
            }
        }

        // Delegate to repository
        return $this->repository->update($id, $data);
    }

    /**
     * Delete an availability.
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $availability = $this->find($id);
        if (!$availability instanceof Availability) {
            return false;
        }

        return $this->repository->delete($id);
    }

    /**
     * Find an availability by its ID.
     */
    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();
        return $this->repository->findById($id);
    }

    /**
     * Check if there are overlaps.
     *
     * @param array<string, mixed> $data
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();
        return $this->checker->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find all overlapping availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->parseAndValidateTimeRange($data);
        return $this->repository->findOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findAdjacentAvailabilities(array $data): Collection
    {
        $this->validateSchedulable();
        return $this->merger->findAdjacentAvailabilities($data, $this->schedulable);
    }

    /**
     * Filter by specific day.
     */
    public function whereDay(string $day): self
    {
        $this->filters['day'] = strtolower($day);
        return $this;
    }

    /**
     * Check if the schedulable is available at a given time.
     */
    public function isAvailableAt(Carbon $datetime): bool
    {
        $this->validateSchedulable();
        return $this->checker->isAvailableAt($this->schedulable, $datetime);
    }

    /**
     * Check availability for a time period.
     */
    public function isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        return $this->checker->isAvailableForPeriod($this->schedulable, $start, $end, $type);
    }

    /**
     * Find all available slots between two dates.
     */
    public function findAvailableSlotsBetween(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array {
        $this->validateSchedulable();
        return $this->slotFinder->findAvailableSlotsBetween(
            $this->schedulable,
            $startDate,
            $endDate,
            $durationMinutes,
            $intervalMinutes,
            $type
        );
    }

    /**
     * Check if a time period has any availability.
     */
    public function hasAvailabilityBetween(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        return $this->slotFinder->hasAvailabilityBetween($this->schedulable, $start, $end, $type);
    }

    /**
     * Find the next available slot.
     */
    public function nextAvailableSlot(Carbon $fromDate, int $durationMinutes = 60): ?Carbon
    {
        $this->validateSchedulable();
        return $this->slotFinder->nextAvailableSlot($this->schedulable, $fromDate, $durationMinutes);
    }

    /**
     * Get all available slots in a period.
     *
     * @return array<array{
     *     start: Carbon,
     *     end: Carbon,
     *     type: string,
     *     availability_id: int
     * }>
     */
    public function availableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30
    ): array {
        $this->validateSchedulable();
        return $this->slotFinder->availableSlots(
            $this->schedulable,
            $startDate,
            $endDate,
            $durationMinutes,
            $intervalMinutes
        );
    }

    /**
     * Prepare data for overlap check.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareCheckData(Availability $availability, array $data): array
    {
        $checkData = array_merge([
            'type' => $availability->type,
            'days' => $availability->days,
            'start_date' => $availability->start_date?->format('Y-m-d'),
            'end_date' => $availability->end_date?->format('Y-m-d'),
        ], $data);

        // Ensure time fields are present
        if (!isset($checkData['start_time']) && $availability->start_time) {
            $checkData['start_time'] = $availability->start_time->format('H:i:s');
        }

        if (!isset($checkData['end_time']) && $availability->end_time) {
            $checkData['end_time'] = $availability->end_time->format('H:i:s');
        }

        // Validate time range for check data
        $this->validationService->parseAndValidateTimeRange($checkData);

        return $checkData;
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters(): Builder
    {
        return $this->repository->applyFilters($this->schedulable, $this->filters);
    }
}
