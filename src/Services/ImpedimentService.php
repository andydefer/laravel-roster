<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\Services\Core\SlotFinderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Repositories\AvailabilityRepository;
use Roster\Services\Core\ValidationService;
use Roster\Traits\FilterableTrait;

/**
 * Service class to manage Impediments for a schedulable model.
 */
class ImpedimentService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected ?Model $schedulable = null;

    protected ValidationService $validationService;

    protected AvailabilityRepository $availabilityRepository;

    public function __construct(
        ValidationService $validationService,
        AvailabilityRepository $availabilityRepository
    ) {
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     * Create a new impediment with overlap validation.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    public function create(array $data): Impediment
    {
        $this->validateSchedulable();

        // Validate basic impediment data
        $this->validateImpedimentData($data);

        // Find matching availability
        $availability = $this->findMatchingAvailability($data);

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        // Check for overlapping impediments
        if ($this->hasOverlappingImpediment($availability->id, $data)) {
            throw ValidationException::withMessage('This time slot overlaps with an existing impediment');
        }

        return Impediment::create(array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'availability_id' => $availability->id,
        ]));
    }

    /**
     * Update an existing impediment with overlap validation.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $impediment = $this->find($id);

        if (!$impediment instanceof Impediment) {
            return false;
        }

        if ($data !== []) {
            $availabilityId = $impediment->availability_id;

            // Validate time range if datetime fields are being updated
            if (isset($data['start_datetime']) || isset($data['end_datetime'])) {
                $validationData = array_merge(
                    [
                        'start_datetime' => $impediment->start_datetime,
                        'end_datetime' => $impediment->end_datetime,
                    ],
                    $data
                );
                $this->validationService->parseAndValidateDateTimeRange($validationData);
            }

            // If the start date changes, validate new availability
            if (isset($data['start_datetime'])) {
                $newAvailability = $this->findMatchingAvailability($data);

                if (!$newAvailability instanceof Availability) {
                    throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
                }

                $availabilityId = $newAvailability->id;

                if ($this->hasOverlappingImpediment($availabilityId, $data, $id)) {
                    throw ValidationException::withMessage('This time slot overlaps with another impediment');
                }

                if ($newAvailability->id !== $impediment->availability_id) {
                    $data['availability_id'] = $newAvailability->id;
                }
            } else {
                // Validate overlap with existing availability
                $updateData = array_merge([
                    'start_datetime' => $impediment->start_datetime,
                    'end_datetime' => $impediment->end_datetime,
                ], $data);

                if ($this->hasOverlappingImpediment($availabilityId, $updateData, $id)) {
                    throw ValidationException::withMessage('This time slot overlaps with another impediment');
                }
            }
        }

        return $impediment->update($data);
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
     * Check if a time slot overlaps with an existing impediment.
     *
     * @param array<string, mixed> $data
     */
    protected function hasOverlappingImpediment(int $availabilityId, array $data, ?int $excludeId = null): bool
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        $query = Impediment::where('availability_id', $availabilityId)
            ->where(function ($q) use ($start, $end): void {
                $q->where('start_datetime', '<', $end)
                    ->where('end_datetime', '>', $start);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

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
     * Get impediments between two dates.
     *
     * @return Collection<int, Impediment>
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

        return $availability->impediments()
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();
    }

    /**
     * Get available time slots for a period.
     *
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return collect();
        }

        $impediments = $availability->impediments()
            ->where('start_datetime', '>=', $start->copy()->startOfDay())
            ->where('end_datetime', '<=', $end->copy()->endOfDay())
            ->orderBy('start_datetime')
            ->get();

        $slotFinderService = app(SlotFinderService::class);
        return $slotFinderService->getAvailableSlotsFromImpediments($availability, $start, $end, $impediments);
    }

    /**
     * Validate impediment data.
     *
     * @param array<string, mixed> $data
     *
     * @throws ValidationException
     */
    protected function validateImpedimentData(array $data): void
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        $this->validationService->validateMinimumDuration($start, $end, 5);
    }

    /**
     * Find matching availability for given impediment data.
     *
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
     *
     * @return Builder
     */
    protected function applyFilters()
    {
        $query = Impediment::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        $this->applyDateFilters($query);
        $this->applyTypeFilter($query);

        return $query;
    }
}
