<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Services\Core\ValidationService;
use Roster\Traits\FilterableTrait;

class ScheduleService extends AbstractSchedulableService
{
    use FilterableTrait;

    protected ?Model $schedulable = null;

    protected ValidationService $validationService;

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    protected ScheduleRepositoryInterface $scheduleRepository;

    public function __construct(
        ValidationService $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository,
        ScheduleRepositoryInterface $scheduleRepository
    ) {
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
        $this->scheduleRepository = $scheduleRepository;
    }

    /**
     * Create a new schedule.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Schedule
    {
        $this->validateSchedulable();
        $this->validateScheduleData($data);

        // Find matching availability
        $availability = $this->findMatchingAvailability($data);

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        // Check for overlapping schedules and impediments
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        if ($this->scheduleRepository->hasOverlappingSchedule($availability->id, $start, $end)) {
            throw new OverlappingScheduleException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
        }

        if ($this->impedimentRepository->hasOverlappingImpediment($availability->id, $start, $end)) {
            throw new ScheduleImpedimentOverlapException([
                'availability_id' => $availability->id,
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s'),
            ]);
        }

        return Schedule::create(array_merge($data, [
            'availability_id' => $availability->id,
        ]));
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
            $this->validationService->parseAndValidateDateTimeRange($validationData);
        }

        // If dates change, check new availability
        if ($data !== [] && isset($data['start_datetime'])) {
            $newAvailability = $this->findMatchingAvailability($data);
            if (!$newAvailability instanceof Availability) {
                throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
            }

            // Check for overlaps with new availability
            ['start' => $start, 'end' => $end] = $this->validationService
                ->parseAndValidateDateTimeRange(array_merge([
                    'start_datetime' => $schedule->start_datetime,
                    'end_datetime' => $schedule->end_datetime,
                ], $data));

            if ($this->scheduleRepository->hasOverlappingSchedule($newAvailability->id, $start, $end, $id)) {
                throw ValidationException::withMessage('Schedule overlaps with another schedule');
            }

            if ($this->impedimentRepository->hasOverlappingImpediment($newAvailability->id, $start, $end)) {
                throw ValidationException::withMessage('Schedule overlaps with an impediment');
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
     * Get schedules for a given period.
     *
     * @return Collection<int, Schedule>
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
     * Check availability for a time slot.
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        // Find a matching availability
        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return false;
        }

        // Check for overlapping schedules using repository
        $hasOverlappingSchedule = $this->scheduleRepository->hasOverlappingSchedule($availability->id, $start, $end);

        // Check for overlapping impediments using repository
        $hasOverlappingImpediment = $this->impedimentRepository->hasOverlappingImpediment($availability->id, $start, $end);

        return !$hasOverlappingSchedule && !$hasOverlappingImpediment;
    }

    // Ajouter ces méthodes à la classe ScheduleService:

    /**
     * Check if a time period is completely available (no schedules or impediments).
     */
    public function isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        // Split into 30-minute intervals to check each slot
        $current = $start->copy();
        $interval = 30; // minutes

        while ($current->lt($end)) {
            $slotEnd = $current->copy()->addMinutes($interval);
            if ($slotEnd->gt($end)) {
                $slotEnd = $end->copy();
            }

            if (!$this->isTimeSlotAvailable($current, $slotEnd, $type)) {
                return false;
            }

            $current->addMinutes($interval);
        }

        return true;
    }

    /**
     * Get the first available period of a specific duration.
     */
    public function findFirstAvailablePeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        $currentDate = $startDate->copy();
        $interval = 15; // minutes

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // For the first day, start at the later of slot start or start date time
                if ($currentDate->isSameDay($startDate) && $slotStart->lt($startDate)) {
                    $slotStart = $startDate->copy();
                }

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

                    if ($proposedEnd->lte($currentDate) || $proposedEnd->gt($endDate)) {
                        $slotStart->addMinutes($interval);
                        continue;
                    }

                    if ($this->isTimeSlotAvailable($slotStart, $proposedEnd, $type)) {
                        return [
                            'start' => $slotStart->copy(),
                            'end' => $proposedEnd,
                            'availability_id' => $availability->id,
                            'type' => $availability->type,
                        ];
                    }

                    $slotStart->addMinutes($interval);
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
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
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $slot = $this->findSlotInAvailability(
                    $availability,
                    $currentDate,
                    $durationMinutes,
                    $i === 0,
                    $type
                );

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
    public function findAvailableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

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
            $availabilities = $this->availabilityRepository->getForDate($this->schedulable, $currentDate, $type);

            /** @var Availability $availability */
            foreach ($availabilities as $availability) {
                $availabilitySlots = $this->findAllSlotsInAvailability(
                    $availability,
                    $currentDate,
                    $durationMinutes,
                    $currentDate->isSameDay($startDate) ? $startDate : null,
                    $type
                );

                $slots = array_merge($slots, $availabilitySlots);
            }

            $currentDate->addDay()->startOfDay();
        }

        return $slots;
    }

    /**
     * Find a slot in an availability.
     */
    private function findSlotInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        bool $isToday = false,
        ?string $type = null
    ): ?array {
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
                $currentSlot = $now->copy()->addMinutes(1);
            }
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            // Validate the proposed slot's time range
            if ($proposedEnd->lte($currentSlot)) {
                continue;
            }

            // Check availability using repository methods
            if ($this->isTimeSlotAvailable($currentSlot, $proposedEnd, $type)) {
                return [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            $currentSlot->addMinutes(15);
        }

        return null;
    }

    /**
     * Find all slots in an availability for a given date.
     */
    private function findAllSlotsInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        ?Carbon $minStartTime = null,
        ?string $type = null
    ): array {
        $slots = [];
        $startTime = $availability->start_time;
        $endTime = $availability->end_time;

        $currentSlot = $date->copy()
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endOfSlot = $date->copy()
            ->setTime($endTime->hour, $endTime->minute, $endTime->second);

        if ($minStartTime instanceof Carbon && $minStartTime->gt($currentSlot)) {
            $currentSlot = $minStartTime->copy();
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            if ($proposedEnd->lte($currentSlot)) {
                $currentSlot->addMinutes(15);
                continue;
            }

            if ($this->isTimeSlotAvailable($currentSlot, $proposedEnd, $type)) {
                $slots[] = [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            $currentSlot->addMinutes(15);
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
        ['start' => $start] = $this->validationService->parseAndValidateDateTimeRange($data);
        $this->validationService->validateFutureDate($start);
    }

    /**
     * Find matching availability for a schedule.
     *
     * @param array<string, mixed> $data
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($data);

        $type = $data['type'] ?? null;
        return $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);
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

        $this->applyDateFilters($query);
        $this->applyTypeFilter($query);
        $this->applyStatusFilter($query);

        return $query;
    }
}
