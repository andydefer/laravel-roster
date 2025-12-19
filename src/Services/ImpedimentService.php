<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Roster\Exceptions\OverlappingImpedimentException;
use Roster\Services\Core\AbstractSchedulableService;
use Roster\Traits\FilterableTrait;

/**
 * Service class to manage Impediments for a schedulable model.
 */
class ImpedimentService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected ValidationServiceInterface $validationService;

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    protected ?Impediment $currentImpediment = null;

    public function __construct(
        ValidationServiceInterface $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository
    ) {
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
    }

    // ========== HOOK METHODS IMPLEMENTATION ==========

    /**
     * Validate future dates hook
     */
    protected function validateFutureDatesHook(string $operation): void
    {
        // For impediments, we validate start_datetime is not in the past
        if (isset($this->data['start_datetime'])) {
            $startDatetime = Carbon::parse($this->data['start_datetime']);

            if ($startDatetime->isPast() && $operation === 'create') {
                throw ValidationException::withMessage(
                    'Impediment start datetime cannot be in the past'
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
        // For impediments, validate minimum duration
        if (isset($this->data['start_datetime'], $this->data['end_datetime'])) {
            $start = Carbon::parse($this->data['start_datetime']);
            $end = Carbon::parse($this->data['end_datetime']);

            if ($start->diffInMinutes($end) < $minImpedimentMinutes) {
                throw ValidationException::withMessage(
                    sprintf('Impediment must be at least %d minutes', $minImpedimentMinutes)
                );
            }
        }
    }

    /**
     * Validate max days hook
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        // For impediments, check if duration exceeds max days
        if (isset($this->data['start_datetime'], $this->data['end_datetime'])) {
            $start = Carbon::parse($this->data['start_datetime']);
            $end = Carbon::parse($this->data['end_datetime']);

            if ($start->diffInDays($end) > $maxDays) {
                throw ValidationException::withMessage(
                    sprintf('Impediment duration cannot exceed %d days', $maxDays)
                );
            }
        }
    }

    /**
     * Validate timezone hook
     */
    protected function validateTimezoneHook(string $timezone): void
    {
        // Validate timezone for datetime fields
        if ((isset($this->data['start_datetime']) || isset($this->data['end_datetime'])) && !$this->validationService->validateTimezone($timezone)) {
            throw ValidationException::withMessage(
                'Invalid timezone: ' . $timezone
            );
        }
    }

    /**
     * Validate before create hook
     */
    protected function validateBeforeCreate(): void
    {
        // Original validation logic from create()
        $this->validateImpedimentData($this->data);

        // Find matching availability
        $availability = $this->findMatchingAvailability($this->data);

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        // Check for overlapping impediments
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        if ($this->impedimentRepository->hasOverlappingImpediments($availability->id, $start, $end)) {
            throw new OverlappingImpedimentException(
                TimeSlotOverlapType::IMPEDIMENT_OVERLAP,
                [
                    'availability_id' => $availability->id,
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        // Store availability ID in data for creation
        $this->data['availability_id'] = $availability->id;
        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);
    }

    /**
     * Process before create hook
     */
    protected function processBeforeCreate(): void
    {
        // Additional processing if needed
        // Ensure metadata is properly formatted
        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }
    }

    /**
     * Execute create
     */
    protected function executeCreate(): Impediment
    {
        return Impediment::create($this->data);
    }

    /**
     * After create hook
     */
    protected function afterCreate($impediment): void
    {
        // Clear cache if enabled
        if (config('roster.cache.enabled', true)) {
            $this->clearImpedimentCache($impediment->id);
        }
    }

    /**
     * Validate before update hook
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentImpediment = $this->find($id);

        if (!$this->currentImpediment instanceof Impediment) {
            throw ValidationException::withMessage('Impediment not found');
        }

        if ($this->data !== []) {
            $availabilityId = $this->currentImpediment->availability_id;

            // Validate time range if datetime fields are being updated
            if (isset($this->data['start_datetime']) || isset($this->data['end_datetime'])) {
                $validationData = array_merge(
                    [
                        'start_datetime' => $this->currentImpediment->start_datetime,
                        'end_datetime' => $this->currentImpediment->end_datetime,
                    ],
                    $this->data
                );
                $this->validationService->parseAndValidateDateTimeRange($validationData);
            }

            // If the start date changes, validate new availability
            if (isset($this->data['start_datetime'])) {
                $newAvailability = $this->findMatchingAvailability($this->data);

                if (!$newAvailability instanceof Availability) {
                    throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
                }

                $availabilityId = $newAvailability->id;

                ['start' => $start, 'end' => $end] = $this->validationService
                    ->parseAndValidateDateTimeRange(array_merge([
                        'start_datetime' => $this->currentImpediment->start_datetime,
                        'end_datetime' => $this->currentImpediment->end_datetime,
                    ], $this->data));

                if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $id)) {
                    throw ValidationException::withMessage('This time slot overlaps with another impediment');
                }

                if ($newAvailability->id !== $this->currentImpediment->availability_id) {
                    $this->data['availability_id'] = $newAvailability->id;
                }
            } else {
                // Validate overlap with existing availability
                $updateData = array_merge([
                    'start_datetime' => $this->currentImpediment->start_datetime,
                    'end_datetime' => $this->currentImpediment->end_datetime,
                ], $this->data);

                ['start' => $start, 'end' => $end] = $this->validationService
                    ->parseAndValidateDateTimeRange($updateData);

                if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $id)) {
                    throw ValidationException::withMessage('This time slot overlaps with another impediment');
                }
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
        return $this->currentImpediment->update($this->data);
    }

    /**
     * After update hook
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        if ($result && config('roster.cache.enabled', true)) {
            $this->clearImpedimentCache($id);
        }
    }

    // ========== ORIGINAL METHODS (adapted) ==========

    /**
     * Find an impediment by its ID.
     */
    public function find(int $id): ?Impediment
    {
        $this->validateSchedulable();

        return Impediment::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    /**
     * Delete an impediment.
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $impediment = $this->find($id);

        if (!$impediment instanceof Impediment) {
            return false;
        }

        return $impediment->delete();
    }

    /**
     * Get impediments between two dates.
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        return $this->applyFilters()
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Check if a time slot is blocked by an impediment.
     */
    public function isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $this->impedimentRepository->hasOverlappingImpediments($availability->id, $start, $end);
    }

    /**
     * Get available time slots for a period.
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return collect();
        }

        $impediments = $this->impedimentRepository->findForTimeSlot($availability->id, $start, $end);

        $slotFinderService = app(SlotFinderInterface::class);
        return $slotFinderService->getAvailableSlotsFromImpediments($start, $end, $impediments);
    }

    /**
     * Validate impediment data.
     * @param array<string, mixed> $data
     */
    protected function validateImpedimentData(array $data): void
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        $minDuration = config('roster.durations.minimum_impediment_minutes', 5);
        $this->validationService->validateMinimumDuration($start, $end, $minDuration);
    }

    /**
     * Find matching availability for given impediment data.
     * @param array<string, mixed> $data
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        return $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end);
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters(): Builder
    {
        $query = Impediment::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        $this->applyDateFilters($query);
        $this->applyTypeFilter($query);

        return $query;
    }

    /**
     * Clear impediment cache
     */
    private function clearImpedimentCache(int $impedimentId): void
    {
        $prefix = config('roster.cache.prefix', 'roster_');
        $cacheKey = $prefix . 'impediment_' . $impedimentId;

        Cache::forget($cacheKey);
    }
}
