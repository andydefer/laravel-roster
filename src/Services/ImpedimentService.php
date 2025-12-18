<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\TimeRangeValidationType;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;

/**
 * Service class to manage Impediments for a schedulable model.
 *
 * Handles creation, update, deletion, and retrieval of impediments
 * while ensuring they respect the corresponding Availability rules.
 */
class ImpedimentService extends AbstractSchedulableService
{
    /**
     * @var Model|null The current schedulable instance
     */
    protected ?Model $schedulable = null;

    /**
     * @var array<string, mixed> Active filters for queries
     */
    protected array $filters = [];

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
            throw new ValidationException(
                ValidationType::NO_MATCHING_AVAILABILITY
            );
        }

        // Check for overlapping impediments
        if ($this->hasOverlappingImpediment($availability->id, $data)) {
            throw ValidationException::withMessage('This time slot overlaps with an existing impediment');
        }

        // Create the impediment
        return Impediment::create(array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'availability_id' => $availability->id, // mandatory
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
                $this->validateTimeRange($validationData);
            }

            // If the start date changes, validate new availability
            if (isset($data['start_datetime'])) {
                $newAvailability = $this->findMatchingAvailability($data);

                if (!$newAvailability instanceof Availability) {
                    throw new ValidationException(
                        ValidationType::NO_MATCHING_AVAILABILITY
                    );
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
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

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
     * Filter by start date.
     */
    public function whereStartDate(Carbon $date): self
    {
        $this->filters['start_date'] = $date;

        return $this;
    }

    /**
     * Filter by end date.
     */
    public function whereEndDate(Carbon $date): self
    {
        $this->filters['end_date'] = $date;

        return $this;
    }

    /**
     * Get impediments between two dates.
     *
     * @return Collection<int, Impediment>
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();

        // Validate time range for query parameters
        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

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

        // Validate time range for input parameters
        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        $availability = $this->findAvailabilityForTimeSlot($start, $end, $type);

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

        // Validate time range for input parameters
        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        $availability = $this->findAvailabilityForTimeSlot($start, $end, $type);

        if (!$availability instanceof Availability) {
            return collect();
        }

        $impediments = $availability->impediments()
            ->where('start_datetime', '>=', $start->copy()->startOfDay())
            ->where('end_datetime', '<=', $end->copy()->endOfDay())
            ->orderBy('start_datetime')
            ->get();

        if ($impediments->isEmpty()) {
            return collect([[
                'start' => $start,
                'end' => $end,
            ]]);
        }

        $availableSlots = collect();
        $currentTime = $start->copy();

        foreach ($impediments as $impediment) {
            $impStart = Carbon::parse($impediment->start_datetime);
            $impEnd = Carbon::parse($impediment->end_datetime);

            if ($impStart > $currentTime) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            $currentTime = max($currentTime, $impEnd);
        }

        if ($currentTime < $end) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
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
        if (!isset($data['start_datetime'], $data['end_datetime'])) {
            throw new ValidationException(
                ValidationType::INVALID_TIME_RANGE
            );
        }

        $this->validateTimeRange($data);

        $minDuration = 5;
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        if ($start->diffInMinutes($end) < $minDuration) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => $minDuration, 'provided_minutes' => $start->diffInMinutes($end)]
            );
        }
    }

    /**
     * Validate that start datetime is before end datetime.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    protected function validateTimeRange(array $data): void
    {
        if (!isset($data['start_datetime']) || !isset($data['end_datetime'])) {
            throw new ValidationException(
                ValidationType::INVALID_TIME_RANGE
            );
        }

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start_datetime' => $start->format('Y-m-d H:i:s'),
                    'end_datetime' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }
    }

    /**
     * Find matching availability for given impediment data.
     *
     * @param array<string, mixed> $data
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        return $this->findAvailabilityForTimeSlot($start, $end);
    }

    /**
     * Find an availability for a given time slot.
     */
    protected function findAvailabilityForTimeSlot(Carbon $start, Carbon $end, ?string $type = null): ?Availability
    {
        // Validate time range for search
        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'));

        if ($type) {
            $query->where('type', $type);
        }

        $query->where(function ($q) use ($start): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $start->toDateString());
        })->where(function ($q) use ($end): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $end->toDateString());
        });

        return $query->first();
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

        if (isset($this->filters['start_date'])) {
            $query->where('start_datetime', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('end_datetime', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['type'])) {
            $query->whereHas('availability', function ($q) {
                $q->where('type', $this->filters['type']);
            });
        }

        return $query;
    }
}
