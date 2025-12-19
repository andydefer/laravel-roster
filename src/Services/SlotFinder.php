<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;

class SlotFinder implements SlotFinderInterface
{
    public function __construct(
        private AvailabilityRepositoryInterface $repository,
        private ValidationServiceInterface $validationService
    ) {}

    /**
     * Find all available slots between two dates.
     */
    public function findAvailableSlotsBetween(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array {
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        if ($durationMinutes <= 0 || $intervalMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => min($durationMinutes, $intervalMinutes)]
            );
        }

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->repository->getForDate($schedulable, $currentDate, $type);

            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

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
     * Find the next available slot.
     */
    public function nextAvailableSlot(
        object $schedulable,
        Carbon $fromDate,
        int $durationMinutes = 60
    ): ?Carbon {
        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        $currentDate = $fromDate->copy();
        $maxDaysToCheck = 365;

        for ($i = 0; $i < $maxDaysToCheck; ++$i) {
            $availabilities = $this->repository->getForDate($schedulable, $currentDate);

            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                if ($i === 0 && $slotStart->lt($fromDate)) {
                    $slotStart = $fromDate->copy();
                }

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
     * Get all available slots in a period (with blocked slots check).
     *
     * @return array<array{
     *     start: Carbon,
     *     end: Carbon,
     *     type: string,
     *     availability_id: int
     * }>
     */
    public function availableSlots(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30
    ): array {
        $availabilities = $this->repository
            ->getForDateRange($schedulable, $startDate, $endDate)
            ->load(['schedules' => function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('start_datetime', [$startDate, $endDate]);
            }, 'impediments' => function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('start_datetime', [$startDate, $endDate]);
            }]);

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dailyAvailabilities = $availabilities->filter(function ($availability) use ($currentDate): bool {
                return in_array(strtolower($currentDate->englishDayOfWeek), $availability->days);
            });

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

                $blockedSlots = $this->getBlockedSlotsForAvailability($dailyAvailability, $currentDate);

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    if (!$this->isSlotBlocked($slotStart, $slotStart->copy()->addMinutes($durationMinutes), $blockedSlots)) {
                        $slots[] = [
                            'start' => $slotStart->copy(),
                            'end' => $slotStart->copy()->addMinutes($durationMinutes),
                            'type' => $dailyAvailability->type,
                            'availability_id' => $dailyAvailability->id,
                        ];
                    }
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
    public function hasAvailabilityBetween(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        $this->validationService->validateTimeRange($start, $end);

        $currentDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->endOfDay();

        while ($currentDate->lte($endDate)) {
            $availabilities = $this->repository->getForDate($schedulable, $currentDate, $type);

            if ($availabilities->isNotEmpty()) {
                return true;
            }

            $currentDate->addDay();
        }

        return false;
    }

    private function getBlockedSlotsForAvailability(Availability $availability, Carbon $date): array
    {
        $blocks = [];

        foreach ($availability->schedules as $schedule) {
            if ($schedule->start_datetime->isSameDay($date)) {
                $blocks[] = [
                    'start' => $schedule->start_datetime,
                    'end' => $schedule->end_datetime
                ];
            }
        }

        foreach ($availability->impediments as $impediment) {
            if ($impediment->start_datetime->isSameDay($date)) {
                $blocks[] = [
                    'start' => $impediment->start_datetime,
                    'end' => $impediment->end_datetime
                ];
            }
        }

        return $blocks;
    }

    private function isSlotBlocked(Carbon $slotStart, Carbon $slotEnd, array $blockedSlots): bool
    {
        foreach ($blockedSlots as $blockedSlot) {
            if ($slotStart->lt($blockedSlot['end']) && $slotEnd->gt($blockedSlot['start'])) {
                return true;
            }
        }

        return false;
    }

    public function getAvailableSlotsFromImpediments(
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection {
        if ($impediments->isEmpty()) {
            return collect([['start' => $start, 'end' => $end]]);
        }

        $availableSlots = collect();
        $currentTime = $start->copy();

        /** @var Impediment $impediment */
        foreach ($impediments as $impediment) {
            $impStart = $impediment->start_datetime;
            $impEnd = $impediment->end_datetime;

            // Si l'impediment commence après le temps courant, il y a un créneau disponible
            if ($impStart->gt($currentTime)) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            // Mettre à jour le temps courant à la fin de l'impediment
            $currentTime = $currentTime->gt($impEnd) ? $currentTime : $impEnd;
        }

        // Vérifier s'il reste du temps après le dernier impediment
        if ($currentTime->lt($end)) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }
}
