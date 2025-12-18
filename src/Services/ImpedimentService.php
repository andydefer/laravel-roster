<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\Services\Core\SlotFinderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Exceptions\OverlappingImpedimentException;
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

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    public function __construct(
        ValidationService $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository
    ) {
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
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

        // Check for overlapping impediments using repository
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        if ($this->impedimentRepository->hasOverlappingImpediment($availability->id, $start, $end)) {
            throw new OverlappingImpedimentException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
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

                ['start' => $start, 'end' => $end] = $this->validationService
                    ->parseAndValidateDateTimeRange(array_merge([
                        'start_datetime' => $impediment->start_datetime,
                        'end_datetime' => $impediment->end_datetime,
                    ], $data));

                if ($this->impedimentRepository->hasOverlappingImpediment($availabilityId, $start, $end, $id)) {
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

                ['start' => $start, 'end' => $end] = $this->validationService
                    ->parseAndValidateDateTimeRange($updateData);

                if ($this->impedimentRepository->hasOverlappingImpediment($availabilityId, $start, $end, $id)) {
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

        return $this->impedimentRepository->hasOverlappingImpediment($availability->id, $start, $end);
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

        $impediments = $this->impedimentRepository->findForTimeSlot($availability->id, $start, $end);

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
