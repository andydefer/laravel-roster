<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
use Roster\Services\Core\AbstractSchedulableService;
use Roster\Traits\FilterableTrait;

/**
 * Service for managing availability records within the scheduling system.
 *
 * Handles creation, validation, and management of time slots when resources
 * (users, rooms, equipment) are available for scheduling appointments or events.
 * Integrates with validation, merging, and slot finding services to ensure
 * data consistency and prevent scheduling conflicts.
 */
class AvailabilityService extends AbstractSchedulableService
{
    use FilterableTrait;

    private AvailabilityValidatorInterface $availabilityValidator;
    private ValidationServiceInterface $validationService;
    private AvailabilityRepositoryInterface $availabilityRepository;
    private AvailabilityMergerInterface $availabilityMerger;
    private SlotFinderInterface $slotFinder;
    private AvailabilityCheckerInterface $availabilityChecker;
    private ?Availability $currentAvailability = null;

    public function __construct(
        AvailabilityValidatorInterface $availabilityValidator,
        ValidationServiceInterface $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        AvailabilityMergerInterface $availabilityMerger,
        SlotFinderInterface $slotFinder,
        AvailabilityCheckerInterface $availabilityChecker
    ) {
        $this->availabilityValidator = $availabilityValidator;
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->availabilityMerger = $availabilityMerger;
        $this->slotFinder = $slotFinder;
        $this->availabilityChecker = $availabilityChecker;
    }

    /**
     * Validate minimum duration for availability time range.
     *
     * @param string $operation Operation type ('create' or 'update')
     * @param int $minImpedimentMinutes Minimum impediment duration in minutes
     * @param int $minScheduleMinutes Minimum schedule duration in minutes
     * @param int $defaultDurationMinutes Default duration in minutes
     * @throws ValidationException When duration is below minimum threshold
     */
    protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void {
        if (!isset($this->data['start_time'], $this->data['end_time'])) {
            return;
        }

        $startTime = Carbon::parse($this->data['start_time']);
        $endTime = Carbon::parse($this->data['end_time']);

        $minimumAvailabilityMinutes = config('roster.durations.minimum_availability_minutes', 15);

        if ($startTime->diffInMinutes($endTime) < $minimumAvailabilityMinutes) {
            $this->throwMinimumDurationException($minimumAvailabilityMinutes);
        }
    }

    /**
     * Validate maximum allowed days for availability period.
     *
     * @param string $operation Operation type ('create' or 'update')
     * @param int $maxDays Maximum allowed days for availability period
     * @throws ValidationException When period exceeds maximum days
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        if (!isset($this->data['start_date'], $this->data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($this->data['start_date']);
        $endDate = Carbon::parse($this->data['end_date']);

        if ($startDate->diffInDays($endDate) > $maxDays) {
            throw ValidationException::withMessage(
                sprintf('Availability period cannot exceed %d days', $maxDays)
            );
        }
    }

    /**
     * Get the validation service instance.
     */
    protected function getValidationService(): ValidationServiceInterface
    {
        return $this->validationService;
    }

    /**
     * Validate data before creating a new availability.
     *
     * @throws ValidationException When validation fails or overlapping exists
     */
    protected function validateBeforeCreate(): void
    {
        $this->availabilityValidator->validateBasicData($this->data);
        $this->validationService->parseAndValidateTimeRange($this->data);

        if ($this->availabilityChecker->hasOverlapping($this->schedulable, $this->data)) {
            $this->throwOverlapException();
        }
    }

    /**
     * Process data before creating a new availability.
     */
    protected function processBeforeCreate(): void
    {
        $this->data = $this->availabilityMerger->mergeAdjacentAvailabilities($this->data, $this->schedulable);
        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);
    }

    /**
     * Execute the creation of a new availability record.
     */
    protected function executeCreate(): Availability
    {
        return $this->availabilityRepository->create($this->data);
    }

    /**
     * Validate data before updating an existing availability.
     *
     * @param int $id Availability ID to update
     * @throws ValidationException When validation fails or overlapping exists
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentAvailability = $this->find($id);

        if (!$this->currentAvailability instanceof Availability) {
            $this->throwNotFoundException();
        }

        if (empty($this->data)) {
            return;
        }

        $this->availabilityValidator->validateBasicData($this->data);

        if (isset($this->data['start_time']) || isset($this->data['end_time'])) {
            $validationData = array_merge(
                [
                    'start_time' => $this->currentAvailability->start_time?->format('H:i:s'),
                    'end_time' => $this->currentAvailability->end_time?->format('H:i:s'),
                ],
                $this->data
            );
            $this->validationService->parseAndValidateTimeRange($validationData);
        }

        $checkData = $this->prepareCheckData($this->currentAvailability, $this->data);

        if ($this->availabilityChecker->hasOverlapping($this->schedulable, $checkData, $id)) {
            $this->throwOverlapException();
        }
    }

    /**
     * Process data before updating an existing availability.
     */
    protected function processBeforeUpdate(int $id): void
    {
        // Additional processing logic can be implemented here
    }

    /**
     * Execute the update of an existing availability record.
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->availabilityRepository->update($id, $this->data);
    }

    /**
     * Create a new availability record.
     *
     * @param array<string, mixed> $data Availability data including time range, type, and days
     * @return Availability Created availability instance
     * @throws ValidationException When validation fails or overlapping exists
     */
    public function create(array $data): Availability
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->data = $this->applyConfigurationRules($this->data, 'create');
        $this->validateConfigurationRules('create');
        $this->validateBeforeCreate();
        $this->processBeforeCreate();

        $availability = $this->executeCreate();
        $this->afterCreate($availability);

        return $availability;
    }

    /**
     * Find an availability record by ID.
     *
     * @param int $id Availability ID
     * @return Availability|null Availability instance or null if not found
     */
    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();
        return $this->availabilityRepository->find($id);
    }

    /**
     * Delete an availability record.
     *
     * @param int $id Availability ID to delete
     * @return bool True if deleted successfully, false if not found
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $availability = $this->find($id);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $this->availabilityRepository->delete($id);
    }

    /**
     * Check if the given availability data overlaps with existing records.
     *
     * @param array<string, mixed> $data Availability data to check
     * @param int|null $exceptId Availability ID to exclude from overlap check
     * @return bool True if overlapping exists, false otherwise
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find all availability records that overlap with the given data.
     *
     * @param array<string, mixed> $data Availability data to check against
     * @param int|null $exceptId Availability ID to exclude from results
     * @return Collection<int, Availability> Collection of overlapping availability records
     */
    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->parseAndValidateTimeRange($data);

        return $this->availabilityRepository->findOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find availability records by type.
     *
     * @param array<string, mixed> $data Filter criteria including type
     * @return Collection<int, Availability> Collection of availability records matching the type
     */
    public function findByType(array $data): Collection
    {
        $this->validateSchedulable();
        return $this->availabilityMerger->getAdjacentAvailabilities($data, $this->schedulable);
    }

    /**
     * Filter availability records by day of week.
     *
     * @param string $day Day name (e.g., 'monday', 'tuesday')
     * @return self Current service instance for method chaining
     */
    public function filterByDay(string $day): self
    {
        $this->filters['day'] = strtolower($day);
        return $this;
    }

    /**
     * Check if the schedulable is available at a specific datetime.
     *
     * @param Carbon $datetime Datetime to check availability
     * @return bool True if available, false otherwise
     */
    public function isAvailableAt(Carbon $datetime): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableAt($this->schedulable, $datetime);
    }

    /**
     * Check if the schedulable is available for a continuous period.
     *
     * @param Carbon $start Period start datetime
     * @param Carbon $end Period end datetime
     * @param string|null $type Optional availability type filter
     * @return bool True if available for the entire period, false otherwise
     */
    public function isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableForPeriod($this->schedulable, $start, $end, $type);
    }

    /**
     * Find available time slots within a specified period.
     *
     * @param Carbon $startDate Start date of the search period
     * @param Carbon $endDate End date of the search period
     * @param int $durationMinutes Required slot duration in minutes
     * @param int $intervalMinutes Interval between slot checks in minutes
     * @param string|null $type Optional availability type filter
     * @return array<array<string, mixed>> Array of available time slots
     */
    public function findSlotsInPeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array {
        $this->validateSchedulable();

        return $this->slotFinder->findSlotsInPeriod(
            model: $this->schedulable,
            startDate: $startDate,
            endDate: $endDate,
            durationMinutes: $durationMinutes,
            intervalMinutes: $intervalMinutes,
            type: $type
        );
    }

    /**
     * Prepare data for overlap checking by merging current availability with update data.
     *
     * @param Availability $availability Current availability instance
     * @param array<string, mixed> $updateData New data to merge
     * @return array<string, mixed> Complete data for overlap checking
     */
    private function prepareCheckData(Availability $availability, array $updateData): array
    {
        $checkData = array_merge([
            'type' => $availability->type,
            'days' => $availability->days,
            'start_date' => $availability->start_date?->format('Y-m-d'),
            'end_date' => $availability->end_date?->format('Y-m-d'),
        ], $updateData);

        if (!isset($checkData['start_time']) && $availability->start_time) {
            $checkData['start_time'] = $availability->start_time->format('H:i:s');
        }

        if (!isset($checkData['end_time']) && $availability->end_time) {
            $checkData['end_time'] = $availability->end_time->format('H:i:s');
        }

        $this->validationService->parseAndValidateTimeRange($checkData);

        return $checkData;
    }

    /**
     * Build a query with applied filters.
     */
    protected function buildQueryWithFilters(): Builder
    {
        return $this->availabilityRepository->buildQueryWithFilters($this->schedulable, $this->filters);
    }
}
