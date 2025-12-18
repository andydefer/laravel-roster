<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Models\Availability;

class ScheduleSlotFinder
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository
    ) {}

    /**
     * Find the next available time slot.
     */
    public function findNextAvailableSlot(
        Model $model,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $now = Carbon::now();

        // Utiliser getForDateRange au lieu de boucler
        $startDate = $now->copy()->startOfDay();
        $endDate = $now->copy()->addDays(30)->endOfDay();

        $availabilities = $this->availabilityRepository->getForDateRange(
            $model,
            $startDate,
            $endDate,
            $type
        )->load(['schedules', 'impediments']);

        // Recherche optimisée
        for ($i = 0; $i < 30; ++$i) {
            $currentDate = $now->copy()->addDays($i)->startOfDay();

            $dailyAvailabilities = $availabilities->filter(function (Availability $availability) use ($currentDate): bool {
                return $this->availabilityAppliesToDate($availability, $currentDate);
            });

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slot = $this->findSlotInAvailability(
                    $dailyAvailability,
                    $currentDate,
                    $durationMinutes,
                    $i === 0
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
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array {
        // Chargement unique avec eager loading
        $availabilities = $this->availabilityRepository->getForDateRange(
            $model,
            $startDate,
            $endDate,
            $type
        )->load(['schedules', 'impediments']); // Eager loading des relations

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Filtrage en mémoire au lieu de requêtes par jour
            $dailyAvailabilities = $availabilities->filter(function (Availability $availability) use ($currentDate): bool {
                return $this->availabilityAppliesToDate($availability, $currentDate);
            });

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $availabilitySlots = $this->findAllSlotsInAvailability(
                    $dailyAvailability,
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
     * Get the first available period of a specific duration.
     */
    public function findFirstAvailablePeriod(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        // Optimisation similaire
        $availabilities = $this->availabilityRepository->getForDateRange(
            $model,
            $startDate,
            $endDate,
            $type
        )->load(['schedules', 'impediments']);

        $currentDate = $startDate->copy();
        $interval = 15; // minutes

        while ($currentDate->lte($endDate)) {
            $dailyAvailabilities = $availabilities->filter(function (Availability $availability) use ($currentDate): bool {
                return $this->availabilityAppliesToDate($availability, $currentDate);
            });

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

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

                    if ($this->isTimeSlotAvailableOptimized($dailyAvailability, $slotStart, $proposedEnd)) {
                        return [
                            'start' => $slotStart->copy(),
                            'end' => $proposedEnd,
                            'availability_id' => $dailyAvailability->id,
                            'type' => $dailyAvailability->type,
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
     * Check if availability applies to specific date.
     */
    private function availabilityAppliesToDate(Availability $availability, Carbon $date): bool
    {
        // Vérifier le jour de la semaine
        $dayOfWeek = strtolower($date->englishDayOfWeek);
        if (!in_array($dayOfWeek, $availability->days)) {
            return false;
        }

        // Vérifier les dates de période
        if ($availability->start_date && $date->lt($availability->start_date)) {
            return false;
        }

        return !($availability->end_date && $date->gt($availability->end_date));
    }

    /**
     * Find a slot in an availability.
     */
    private function findSlotInAvailability(
        Availability $availability,
        Carbon $date,
        int $durationMinutes,
        bool $isToday = false
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

            // Check availability avec méthode optimisée
            if ($this->isTimeSlotAvailableOptimized($availability, $currentSlot, $proposedEnd)) {
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
        ?Carbon $minStartTime = null
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

            if ($this->isTimeSlotAvailableOptimized($availability, $currentSlot, $proposedEnd)) {
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
     * Check if a time slot is available (optimized version using loaded relations).
     */
    private function isTimeSlotAvailableOptimized(
        Availability $availability,
        Carbon $start,
        Carbon $end
    ): bool {
        // Vérifier les chevauchements avec les schedules chargés
        $hasOverlappingSchedule = $availability->schedules->contains(function ($schedule) use ($start, $end) {
            return $schedule->overlapsWith($start, $end);
        });

        // Vérifier les chevauchements avec les impediments chargés
        $hasOverlappingImpediment = $availability->impediments->contains(function ($impediment) use ($start, $end) {
            return $impediment->overlapsWith($start, $end);
        });

        return !$hasOverlappingSchedule && !$hasOverlappingImpediment;
    }

    /**
     * Check if a time period is completely available.
     */
    public function isPeriodAvailable(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        // Optimisation : charger toutes les availabilities en une fois
        $availabilities = $this->availabilityRepository->getForDateRange(
            $model,
            $start,
            $end,
            $type
        )->load(['schedules', 'impediments']);

        $current = $start->copy();
        $interval = 30; // minutes

        while ($current->lt($end)) {
            $slotEnd = $current->copy()->addMinutes($interval);
            if ($slotEnd->gt($end)) {
                $slotEnd = $end->copy();
            }

            // Trouver l'availability correspondante
            $availability = $availabilities->first(function ($availability) use ($current, $slotEnd, $type): bool {
                if ($type && $availability->type !== $type) {
                    return false;
                }

                return $this->availabilityAppliesToDate($availability, $current) &&
                    $availability->start_time->format('H:i') <= $current->format('H:i') &&
                    $availability->end_time->format('H:i') >= $slotEnd->format('H:i');
            });

            if (!$availability || !$this->isTimeSlotAvailableOptimized($availability, $current, $slotEnd)) {
                return false;
            }

            $current->addMinutes($interval);
        }

        return true;
    }
}
