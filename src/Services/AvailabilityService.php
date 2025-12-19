<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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
        parent::__construct();
        $this->availabilityValidator = $availabilityValidator;
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->availabilityMerger = $availabilityMerger;
        $this->slotFinder = $slotFinder;
        $this->availabilityChecker = $availabilityChecker;
    }

    // ========== HOOK METHODS IMPLEMENTATION ==========

    /**
     * Validate future dates hook
     */
    protected function validateFutureDatesHook(string $operation): void
    {
        // For availabilities, validate start_date if provided
        if (isset($this->data['start_date'])) {
            $startDate = Carbon::parse($this->data['start_date']);

            if ($startDate->isPast() && $operation === 'create') {
                throw ValidationException::withMessage(
                    'Availability start date cannot be in the past'
                );
            }
        }
    }

    /**
     * Validate duration hook
     */
    protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void {
        // For availabilities, validate time range duration
        if (isset($this->data['start_time'], $this->data['end_time'])) {
            $startTime = Carbon::parse($this->data['start_time']);
            $endTime = Carbon::parse($this->data['end_time']);

            // Minimum 15 minutes for availability
            $minAvailabilityMinutes = 15;
            if ($startTime->diffInMinutes($endTime) < $minAvailabilityMinutes) {
                throw ValidationException::withMessage(
                    "Availability must be at least {$minAvailabilityMinutes} minutes"
                );
            }
        }
    }

    /**
     * Validate max days hook
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        // For availabilities, check date range if provided
        if (isset($this->data['start_date'], $this->data['end_date'])) {
            $start = Carbon::parse($this->data['start_date']);
            $end = Carbon::parse($this->data['end_date']);

            if ($start->diffInDays($end) > $maxDays) {
                throw ValidationException::withMessage(
                    "Availability period cannot exceed {$maxDays} days"
                );
            }
        }
    }

    /**
     * Validate timezone hook
     */
    protected function validateTimezoneHook(string $timezone): void
    {
        // Validate timezone for time fields
        if (!$this->validationService->validateTimezone($timezone)) {
            throw ValidationException::withMessage(
                "Invalid timezone: {$timezone}"
            );
        }
    }

    /**
     * Validate before create hook
     */
    protected function validateBeforeCreate(): void
    {
        // Original validation logic
        $this->availabilityValidator->validateBasicData($this->data);
        $this->validationService->parseAndValidateTimeRange($this->data);

        if ($this->availabilityChecker->hasOverlapping($this->schedulable, $this->data)) {
            throw ValidationException::withMessage('This availability overlaps with an existing one.');
        }
    }

    /**
     * Process before create hook
     */
    protected function processBeforeCreate(): void
    {
        // Merge adjacent availabilities
        $this->data = $this->availabilityMerger->mergeWithAdjacent($this->data, $this->schedulable);

        // Add schedulable info
        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);
    }

    /**
     * Execute create
     */
    protected function executeCreate(): Availability
    {
        return $this->availabilityRepository->create($this->data);
    }

    /**
     * After create hook
     */
    protected function afterCreate($availability): void
    {
        // Clear cache if enabled
        if (config('roster.cache.enabled', true)) {
            $this->clearAvailabilityCache($availability->id);
        }
    }

    /**
     * Validate before update hook
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentAvailability = $this->find($id);

        if (!$this->currentAvailability instanceof Availability) {
            throw ValidationException::withMessage('Availability not found');
        }

        if ($this->data !== []) {
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
                throw ValidationException::withMessage('This availability overlaps with an existing one.');
            }
        }
    }

    /**
     * Process before update hook
     */
    protected function processBeforeUpdate(int $id): void
    {
        // Additional processing if needed
    }

    /**
     * Execute update
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->availabilityRepository->update($id, $this->data);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        if ($result && config('roster.cache.enabled', true)) {
            $this->clearAvailabilityCache($id);
        }
    }

    // ========== ORIGINAL METHODS (adapted) ==========

    /**
     * Find an availability by its ID.
     */
    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();
        return $this->availabilityRepository->findById($id);
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

        return $this->availabilityRepository->delete($id);
    }

    /**
     * Check if there are overlaps.
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find all overlapping availabilities.
     */
    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->parseAndValidateTimeRange($data);
        return $this->availabilityRepository->findOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find adjacent availabilities.
     */
    public function findAdjacentAvailabilities(array $data): Collection
    {
        $this->validateSchedulable();
        return $this->availabilityMerger->findAdjacentAvailabilities($data, $this->schedulable);
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
        return $this->availabilityChecker->isAvailableAt($this->schedulable, $datetime);
    }

    /**
     * Check availability for a time period.
     */
    public function isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableForPeriod($this->schedulable, $start, $end, $type);
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
     */
    private function prepareCheckData(Availability $availability, array $data): array
    {
        $checkData = array_merge([
            'type' => $availability->type,
            'days' => $availability->days,
            'start_date' => $availability->start_date?->format('Y-m-d'),
            'end_date' => $availability->end_date?->format('Y-m-d'),
        ], $data);

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
     * Apply filters to the query.
     */
    protected function applyFilters(): Builder
    {
        return $this->availabilityRepository->applyFilters($this->schedulable, $this->filters);
    }

    /**
     * Clear availability cache
     */
    private function clearAvailabilityCache(int $availabilityId): void
    {
        $prefix = config('roster.cache.prefix', 'roster_');
        $cacheKey = $prefix . 'availability_' . $availabilityId;

        Cache::forget($cacheKey);
        Cache::tags(['availability_' . $availabilityId])->flush();
    }
}
