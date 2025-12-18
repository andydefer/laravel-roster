<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\ValidationType;
use Roster\Models\Availability;
use Roster\Repositories\AvailabilityRepository;
use Roster\Services\Core\ValidationService;
use Roster\Traits\FilterableTrait;

class AvailabilityService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected AvailabilityValidator $validator;

    protected ValidationService $validationService;

    protected AvailabilityRepository $availabilityRepository;

    public function __construct(
        AvailabilityValidator $availabilityValidator,
        ValidationService $validationService,
        AvailabilityRepository $availabilityRepository
    ) {
        $this->validator = $availabilityValidator;
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
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

            // Check for overlaps with other availabilities (always forbidden)
            if ($this->validator->hasOverlapping($this->schedulable, $checkData, $id)) {
                throw ValidationException::withMessage('This availability overlaps with an existing one.');
            }
        }

        return $availability->update($data);
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

        return $availability->delete();
    }

    /**
     * Find an availability by its ID.
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
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->parseAndValidateTimeRange($data);

        return $this->validator->hasOverlapping($this->schedulable, $data, $exceptId);
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

        return $this->availabilityRepository->findOverlapping($this->schedulable, $data, $exceptId);
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
     */
    protected function createAvailabilityFromData(array $data): Availability
    {
        ['start' => $startTime, 'end' => $endTime] = $this->validationService
            ->parseAndValidateTimeRange($data);

        $availability = new Availability;
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
        return $this->availabilityRepository->isAvailableAt($this->schedulable, $datetime);
    }

    // Ajouter ces méthodes à la classe AvailabilityService:

    /**
     * Check availability for a time period.
     */
    public function isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        return $availability instanceof Availability;
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
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        // Validate durations are positive
        if ($durationMinutes <= 0 || $intervalMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => min($durationMinutes, $intervalMinutes)]
            );
        }

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // Generate slots inside this availability
                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    $slots[] = [
                        'start' => $slotStart->copy(),
                        'end' => $slotStart->copy()->addMinutes($durationMinutes),
                        'type' => $availability->type,
                        'availability_id' => $availability->id,
                    ];
                    $slotStart->addMinutes($intervalMinutes);
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Check if a time period has any availability.
     */
    public function hasAvailabilityBetween(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $currentDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->endOfDay();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate, $type);

            if ($availabilities->isNotEmpty()) {
                return true;
            }

            $currentDate->addDay();
        }

        return false;
    }

    /**
     * Find the next available slot.
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
        $maxDaysToCheck = 365;

        for ($i = 0; $i < $maxDaysToCheck; ++$i) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            /** @var Collection<int, Availability> $availabilities */
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate);

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
    public function availableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30
    ): array {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        // Validate durations are positive
        if ($durationMinutes <= 0 || $intervalMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => min($durationMinutes, $intervalMinutes)]
            );
        }

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // Generate slots inside this availability
                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    $slots[] = [
                        'start' => $slotStart->copy(),
                        'end' => $slotStart->copy()->addMinutes($durationMinutes),
                        'type' => $availability->type,
                        'availability_id' => $availability->id,
                    ];
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

        $this->applyTypeFilter($query, '');
        $this->applyDayFilter($query);

        return $query;
    }
}
