<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Schedule;

class ScheduleService extends AbstractSchedulableService
{
    protected ?Model $schedulable = null;

    protected array $filters = [];

    /**
     * Get the current schedulable model.
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Create a new schedule.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Schedule
    {
        $this->validateSchedulable();

        // Validate basic schedule data including time range
        $this->validateScheduleData($data);

        // Find matching availability
        $availability = $this->findMatchingAvailability($data);

        if (!$availability instanceof Availability) {
            throw new ValidationException(
                ValidationType::NO_MATCHING_AVAILABILITY
            );
        }

        // Create the schedule - time range validation will be done in the Schedule model
        $schedule = Schedule::create(array_merge($data, [
            'availability_id' => $availability->id,
        ]));

        return $schedule;
    }

    /**
     * Update an existing schedule.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $schedule = $this->find($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        // Validate time range if datetime fields are being updated
        if (isset($data['start_datetime']) || isset($data['end_datetime'])) {
            $validationData = array_merge(
                [
                    'start_datetime' => $schedule->start_datetime,
                    'end_datetime' => $schedule->end_datetime,
                ],
                $data
            );
            $this->validateTimeRange($validationData);
        }

        // If dates change, check new availability
        if ($data !== [] && isset($data['start_datetime'])) {
            $newAvailability = $this->findMatchingAvailability($data);
            if (!$newAvailability instanceof Availability) {
                throw new ValidationException(
                    ValidationType::NO_MATCHING_AVAILABILITY
                );
            }

            if ($newAvailability->id !== $schedule->availability_id) {
                $data['availability_id'] = $newAvailability->id;
            }
        }

        return $schedule->update($data);
    }

    /**
     * Delete a schedule.
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $schedule = $this->find($id);

        if (!$schedule instanceof Schedule) {
            return false;
        }

        return $schedule->delete();
    }

    /**
     * Find a schedule by its ID.
     */
    public function find(int $id): ?Schedule
    {
        $this->validateSchedulable();

        return Schedule::whereHas('availability', function ($query): void {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        })->find($id);
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
     * Filter by status.
     */
    public function whereStatus(string $status): self
    {
        $this->filters['status'] = $status;

        return $this;
    }

    /**
     * Get schedules for a given period.
     *
     * @return Collection<int, Schedule>
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();

        // Validate time range for the query parameters
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
     * Check availability for a time slot.
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();

        // Validate time range for the input parameters
        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        // Find a matching availability
        $availability = $this->findAvailabilityForTimeSlot($start, $end, $type);

        if (!$availability instanceof Availability) {
            return false;
        }

        // Check for overlapping schedules
        $hasOverlappingSchedule = Schedule::where('availability_id', $availability->id)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        // Check for overlapping impediments
        $hasOverlappingImpediment = $availability->impediments()
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        return !$hasOverlappingSchedule && !$hasOverlappingImpediment;
    }

    /**
     * Find the next available time slot.
     */
    public function findNextAvailableSlot(int $durationMinutes, ?string $type = null): ?array
    {
        $this->validateSchedulable();

        // Validate duration is positive
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        $now = Carbon::now();

        // Search in the next 30 days
        for ($i = 0; $i < 30; ++$i) {
            $currentDate = $now->copy()->addDays($i)->startOfDay();

            // Get all availabilities for this day
            $availabilities = $this->getAvailabilitiesForDate($currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slot = $this->findSlotInAvailability($availability, $currentDate, $durationMinutes, $i === 0);

                if ($slot) {
                    return $slot;
                }
            }
        }

        return null;
    }

    /**
     * Find all available slots in a period.
     */
    public function findAvailableSlots(Carbon $startDate, Carbon $endDate, int $durationMinutes, ?string $type = null): array
    {
        $this->validateSchedulable();

        // Validate time range for the input parameters
        if ($endDate->lte($startDate)) {
            throw new TimeRangeValidationException(
                [
                    'start_date' => $startDate->format('Y-m-d H:i:s'),
                    'end_date' => $endDate->format('Y-m-d H:i:s'),
                ]
            );
        }

        // Validate duration is positive
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->getAvailabilitiesForDate($currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $availabilitySlots = $this->findAllSlotsInAvailability(
                    $availability,
                    $currentDate,
                    $durationMinutes,
                    $currentDate->isSameDay($startDate) ? $startDate : null
                );

                $slots = array_merge($slots, $availabilitySlots);
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Validate schedule data including time range.
     *
     * @param array<string, mixed> $data
     */
    protected function validateScheduleData(array $data): void
    {
        if (!isset($data['start_datetime']) || !isset($data['end_datetime'])) {
            throw new ValidationException(
                ValidationType::INVALID_TIME_RANGE
            );
        }

        $this->validateTimeRange($data);

        $start = Carbon::parse($data['start_datetime']);
        if ($start->lt(Carbon::now())) {
            throw ValidationException::withMessage('Cannot schedule in the past');
        }
    }

    /**
     * Validate that start datetime is before end datetime.
     *
     * @param array<string, mixed> $data
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
     * Find matching availability for a schedule.
     *
     * @param array<string, mixed> $data
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        // Validate time range for the search
        if ($end->lte($start)) {
            throw new TimeRangeValidationException(
                [
                    'start_datetime' => $start->format('Y-m-d H:i:s'),
                    'end_datetime' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        // Simply look for availability for the day and type
        // Time validation will be done in the Schedule model
        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek));

        if (isset($data['type'])) {
            $query->where('type', $data['type']);
        }

        // Check period dates
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
     * Find an availability for a given time slot.
     */
    protected function findAvailabilityForTimeSlot(Carbon $start, Carbon $end, ?string $type = null): ?Availability
    {
        // Validate time range for the search
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

        // Check period dates
        $query->where(function ($q) use ($start): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $start->toDateString());
        })->where(function ($q) use ($end): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $end->toDateString());
        });

        /** @var Availability|null $availability */
        $availability = $query->first();

        return $availability;
    }

    /**
     * Get availabilities for a given date.
     *
     * @return Collection<int, Availability>
     */
    protected function getAvailabilitiesForDate(Carbon $date, ?string $type = null): Collection
    {
        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type) {
            $query->where('type', $type);
        }

        // Check period dates
        $query->where(function ($q) use ($date): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $date->toDateString());
        })->where(function ($q) use ($date): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $date->toDateString());
        });

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $query->orderBy('start_time')->get();

        return $availabilities;
    }

    /**
     * Find a slot in an availability.
     */
    protected function findSlotInAvailability(Availability $availability, Carbon $date, int $durationMinutes, bool $isToday = false): ?array
    {
        $startTime = $availability->start_time;
        $endTime = $availability->end_time;

        $currentSlot = $date->copy()
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endOfSlot = $date->copy()
            ->setTime($endTime->hour, $endTime->minute, $endTime->second);

        // If it's today and current time is after start time, start at current time
        if ($isToday) {
            $now = Carbon::now();
            if ($now->gt($currentSlot) && $now->lt($endOfSlot)) {
                $currentSlot = $now->copy()->addMinutes(1); // Start at next minute
            }
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            // Validate the proposed slot's time range
            if ($proposedEnd->lte($currentSlot)) {
                // This shouldn't happen with positive duration, but just in case
                continue;
            }

            // Check availability
            if ($this->isTimeSlotAvailable($currentSlot, $proposedEnd, $availability->type)) {
                return [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            // Advance by 15 minutes (or by configurable increment)
            $currentSlot->addMinutes(15);
        }

        return null;
    }

    /**
     * Find all slots in an availability for a given date.
     */
    protected function findAllSlotsInAvailability(Availability $availability, Carbon $date, int $durationMinutes, ?Carbon $minStartTime = null): array
    {
        $slots = [];
        $startTime = $availability->start_time;
        $endTime = $availability->end_time;

        $currentSlot = $date->copy()
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endOfSlot = $date->copy()
            ->setTime($endTime->hour, $endTime->minute, $endTime->second);

        // If a minimum start time is specified
        if ($minStartTime instanceof Carbon && $minStartTime->gt($currentSlot)) {
            $currentSlot = $minStartTime->copy();
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            // Validate the proposed slot's time range
            if ($proposedEnd->lte($currentSlot)) {
                // Skip invalid slots
                $currentSlot->addMinutes(15);
                continue;
            }

            // Check availability
            if ($this->isTimeSlotAvailable($currentSlot, $proposedEnd, $availability->type)) {
                $slots[] = [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            // Advance by 15 minutes
            $currentSlot->addMinutes(15);
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
        $query = Schedule::whereHas('availability', function ($query): void {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        });

        if (isset($this->filters['type'])) {
            $query->whereHas('availability', function ($q): void {
                $q->where('type', $this->filters['type']);
            });
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['start_date'])) {
            $query->where('start_datetime', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('end_datetime', '<=', $this->filters['end_date']);
        }

        return $query;
    }
}
