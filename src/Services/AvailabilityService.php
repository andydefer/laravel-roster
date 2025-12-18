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

class AvailabilityService extends AbstractSchedulableService
{
    protected AvailabilityValidator $validator;

    public function __construct(?AvailabilityValidator $availabilityValidator = null)
    {
        $this->validator = $availabilityValidator ?? new AvailabilityValidator;
    }

    /**
     * Get the current schedulable model.
     *
     * @return Model|null
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Create a new availability with overlap validation.
     *
     * @param array<string, mixed> $data
     * @return Availability
     */
    public function create(array $data): Availability
    {
        $this->validateSchedulable();

        // Validate basic data including time range
        $this->validator->validateBasicData($data);

        // Validate time range
        $this->validateTimeRange($data);

        // Check for overlaps (always forbidden)
        if ($this->validator->hasOverlapping($this->schedulable, $data)) {
            throw ValidationException::withMessage('This availability overlaps with an existing one.');
        }

        // Automatic merging of adjacent availabilities (always enabled)
        $data = $this->mergeWithAdjacentAvailabilities($data);

        return Availability::create(array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
        ]));
    }

    /**
     * Update an existing availability.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return bool
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
                $this->validateTimeRange($validationData);
            }

            // Prepare data for overlap check
            // In case of partial update, use existing values for non-provided fields
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
            $this->validateTimeRange($checkData);

            // Check for overlaps with other availabilities (always forbidden)
            if ($this->validator->hasOverlapping($this->schedulable, $checkData, $id)) {
                throw ValidationException::withMessage('This availability overlaps with an existing one.');
            }
        }

        return $availability->update($data);
    }

    /**
     * Delete an availability.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $availability = $this->find($id);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $availability->delete();
    }

    /**
     * Find an availability by its ID.
     *
     * @param int $id
     * @return Availability|null
     */
    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();

        return Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    /**
     * Check if there are overlaps.
     *
     * @param array<string, mixed> $data
     * @param int|null $exceptId
     * @return bool
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();

        // Validate time range for input data
        $this->validateTimeRange($data);

        return $this->validator->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find all overlapping availabilities.
     *
     * @param array<string, mixed> $data
     * @param int|null $exceptId
     * @return Collection<int, Availability>
     */
    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();

        // Validate time range for input data
        $this->validateTimeRange($data);

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);
        $days = $data['days'] ?? [];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        // Exclude current record during update
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        /** @var Collection<int, Availability> $allAvailabilities */
        $allAvailabilities = $query->get();

        // Filter manually to check day intersection and overlap
        return $allAvailabilities->filter(function (Availability $availability) use ($startTime, $endTime, $startDate, $endDate, $days): bool {
            // Check if days overlap
            if (!empty($days)) {
                $commonDays = array_intersect($availability->days, $days);
                if ($commonDays === []) {
                    return false;
                }
            }

            return $this->validator->overlaps($availability, $startTime, $endTime, $startDate, $endDate);
        });
    }

    /**
     * Validate that start time is before end time.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    protected function validateTimeRange(array $data): void
    {
        if (!isset($data['start_time']) || !isset($data['end_time'])) {
            throw new ValidationException(
                ValidationType::INVALID_TIME_RANGE
            );
        }

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        if ($endTime->lte($startTime)) {
            throw new TimeRangeValidationException(
                [
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                ]
            );
        }
    }

    /**
     * Merge with adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mergeWithAdjacentAvailabilities(array $data): array
    {
        $this->validateSchedulable();

        // Find adjacent availabilities
        $adjacentAvailabilities = $this->findAdjacentAvailabilities($data);

        if ($adjacentAvailabilities->isEmpty()) {
            return $data;
        }

        // Merge all adjacent availabilities
        $mergedData = $data;
        $idsToDelete = [];

        foreach ($adjacentAvailabilities as $adjacentAvailability) {
            try {
                // Create a temporary object with merged data
                $tempAvailability = $this->createAvailabilityFromData($mergedData);

                // Check if they are really adjacent
                if ($this->validator->areAdjacent($tempAvailability, $adjacentAvailability)) {
                    $mergedData = $this->validator->mergeAdjacent($tempAvailability, $adjacentAvailability);
                    $idsToDelete[] = $adjacentAvailability->id;
                }
            } catch (ValidationException $e) {
                // If merge fails, continue with next one
                continue;
            }
        }

        // Delete all merged availabilities
        if ($idsToDelete !== []) {
            Availability::whereIn('id', $idsToDelete)->delete();
        }

        return $mergedData;
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

        $type = $data['type'] ?? null;

        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        if ($type !== null) {
            $query->where('type', $type);
        }

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $query->get();

        // Create a temporary object for comparison
        $tempAvailability = $this->createAvailabilityFromData($data);

        return $availabilities->filter(function (Availability $availability) use ($tempAvailability): bool {
            return $this->validator->areAdjacent($tempAvailability, $availability);
        });
    }

    /**
     * Create a temporary Availability object from data.
     *
     * @param array<string, mixed> $data
     * @return Availability
     */
    protected function createAvailabilityFromData(array $data): Availability
    {
        // Check that essential fields exist
        if (!isset($data['start_time'], $data['end_time'])) {
            throw new ValidationException(
                ValidationType::INVALID_TIME_RANGE
            );
        }

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        // Validate that end_time is after start_time
        if ($endTime->lessThanOrEqualTo($startTime)) {
            throw new TimeRangeValidationException(
                [
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                ]
            );
        }

        $availability = new Availability;

        // Add schedulable attributes
        $availability->schedulable_id = $this->schedulable->id;
        $availability->schedulable_type = get_class($this->schedulable);
        $availability->start_time = $startTime;
        $availability->end_time = $endTime;
        $availability->days = $data['days'] ?? [];
        $availability->type = $data['type'] ?? null;
        $availability->start_date = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $availability->end_date = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

        return $availability;
    }

    /**
     * Filter by specific day.
     *
     * @param string $day
     * @return self
     */
    public function whereDay(string $day): self
    {
        $this->filters['day'] = strtolower($day);

        return $this;
    }

    /**
     * Check if the schedulable is available at a given time.
     *
     * @param Carbon $datetime
     * @return bool
     */
    public function isAvailableAt(Carbon $datetime): bool
    {
        $this->validateSchedulable();

        $dayOfWeek = strtolower($datetime->englishDayOfWeek);
        $time = $datetime->format('H:i:s');

        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->whereJsonContains('days', $dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time);

        // Check validity dates if present
        $query->where(function ($q) use ($datetime): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $datetime->toDateString());
        })->where(function ($q) use ($datetime): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $datetime->toDateString());
        });

        return $query->exists();
    }

    /**
     * Find the next available slot.
     *
     * @param Carbon $fromDate
     * @param int $durationMinutes
     * @return Carbon|null
     */
    public function nextAvailableSlot(Carbon $fromDate, int $durationMinutes = 60): ?Carbon
    {
        $this->validateSchedulable();

        // Validate duration is positive
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        $currentDate = $fromDate->copy();
        $maxDaysToCheck = 365; // Limit to avoid infinite loops

        for ($i = 0; $i < $maxDaysToCheck; ++$i) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            /** @var Collection<int, Availability> $availabilities */
            $availabilities = Availability::where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable))
                ->whereJsonContains('days', $dayOfWeek)
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $currentDate->toDateString());
                })
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $currentDate->toDateString());
                })
                ->orderBy('start_time')
                ->get();

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // For the first day, start at current time or start time
                if ($i === 0 && $slotStart->lt($fromDate)) {
                    $slotStart = $fromDate->copy();
                }

                // Check if we can place the duration in the slot
                $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if ($proposedEnd->lte($slotEnd)) {
                    return $slotStart;
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
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
    public function availableSlots(Carbon $startDate, Carbon $endDate, int $durationMinutes = 60, int $intervalMinutes = 30): array
    {
        $this->validateSchedulable();

        // Validate time range for input parameters
        if ($endDate->lte($startDate)) {
            throw new TimeRangeValidationException(
                [
                    'start_date' => $startDate->format('Y-m-d H:i:s'),
                    'end_date' => $endDate->format('Y-m-d H:i:s'),
                ]
            );
        }

        // Validate durations are positive
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        if ($intervalMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $intervalMinutes]
            );
        }

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            /** @var Collection<int, Availability> $availabilities */
            $availabilities = Availability::where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable))
                ->whereJsonContains('days', $dayOfWeek)
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $currentDate->toDateString());
                })
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $currentDate->toDateString());
                })
                ->orderBy('start_time')
                ->get();

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // Generate slots inside this availability
                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    $slot = [
                        'start' => $slotStart->copy(),
                        'end' => $slotStart->copy()->addMinutes($durationMinutes),
                        'type' => $availability->type,
                        'availability_id' => $availability->id,
                    ];

                    $slots[] = $slot;
                    $slotStart->addMinutes($intervalMinutes);
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Apply filters to the query.
     *
     * @return Builder
     */
    protected function applyFilters()
    {
        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        if (isset($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (isset($this->filters['day'])) {
            $query->whereJsonContains('days', $this->filters['day']);
        }

        return $query;
    }
}
