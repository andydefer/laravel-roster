<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;

class SlotFinderService implements SlotFinderInterface
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository,
        private ValidationServiceInterface $validationService
    ) {}

    // ============ MÉTHODES DE SCHEDULESLOTFINDER (avec conflits stricts) ============

    /**
     * Find the next available time slot (with strict conflict checking).
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

        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $model,
            $startDate,
            $endDate,
            $type
        );

        // Recherche optimisée
        for ($i = 0; $i < 30; ++$i) {
            $currentDate = $now->copy()->addDays($i)->startOfDay();

            /** @var Collection<int, Availability> $dailyAvailabilities */
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
     * Find all available slots in a period (with strict conflict checking).
     */
    public function findAvailableSlots(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): array {
        // Chargement unique avec eager loading
        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $model,
            $startDate,
            $endDate,
            $type
        );

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Filtrage en mémoire au lieu de requêtes par jour
            /** @var Collection<int, Availability> $dailyAvailabilities */
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
     * Get the first available period of a specific duration (with strict conflict checking).
     */
    public function findFirstAvailablePeriod(
        Model $model,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        // Optimisation similaire
        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $model,
            $startDate,
            $endDate,
            $type
        );

        $currentDate = $startDate->copy();
        $interval = 15; // minutes

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
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
     * Check if a time period is completely available (with strict conflict checking).
     */
    public function isPeriodAvailable(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        // Optimisation : charger toutes les availabilities en une fois
        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $model,
            $start,
            $end,
            $type
        );

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

    // ============ MÉTHODES DE SLOTFINDERSERVICE (adaptées avec vérification conflits) ============

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

        // Charger avec conflits
        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $schedulable,
            $startDate,
            $endDate,
            $type
        );

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->filterAvailabilitiesForDate($availabilities, $currentDate);

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    // Vérifier les conflits
                    if ($this->isTimeSlotAvailableOptimized($dailyAvailability, $slotStart, $slotStart->copy()->addMinutes($durationMinutes))) {
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
     * Find the next available slot (return Carbon).
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

        // Charger les availabilities avec conflits
        $startDate = $fromDate->copy()->startOfDay();
        $endDate = $startDate->copy()->addDays($maxDaysToCheck)->endOfDay();

        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $schedulable,
            $startDate,
            $endDate
        );

        for ($i = 0; $i < $maxDaysToCheck; ++$i) {
            $checkDate = $fromDate->copy()->addDays($i)->startOfDay();
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->filterAvailabilitiesForDate($availabilities, $checkDate);

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $checkDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $checkDate->copy()->setTimeFrom($dailyAvailability->end_time);

                if ($i === 0 && $slotStart->lt($fromDate)) {
                    $slotStart = $fromDate->copy();
                }

                $proposedEnd = $slotStart->copy()->addMinutes($durationMinutes);

                if (
                    $proposedEnd->lte($slotEnd) &&
                    $this->isTimeSlotAvailableOptimized($dailyAvailability, $slotStart, $proposedEnd)
                ) {
                    return $slotStart;
                }
            }

            $currentDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Get all available slots in a period (with conflict checking).
     */
    public function availableSlots(
        object $schedulable,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30
    ): array {
        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $schedulable,
            $startDate,
            $endDate
        );

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->filterAvailabilitiesForDate($availabilities, $currentDate);

            foreach ($dailyAvailabilities as $dailyAvailability) {
                $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

                while ($slotStart->copy()->addMinutes($durationMinutes)->lte($slotEnd)) {
                    if ($this->isTimeSlotAvailableOptimized($dailyAvailability, $slotStart, $slotStart->copy()->addMinutes($durationMinutes))) {
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

        // Charger toutes les availabilities en une fois
        $availabilities = $this->loadAvailabilitiesWithConflicts(
            $schedulable,
            $start,
            $end,
            $type
        );

        while ($currentDate->lte($endDate)) {
            /** @var Collection<int, Availability> $dailyAvailabilities */
            $dailyAvailabilities = $this->filterAvailabilitiesForDate($availabilities, $currentDate);

            if ($dailyAvailabilities->isNotEmpty()) {
                // Vérifier si au moins une availability a un créneau non conflictuel
                foreach ($dailyAvailabilities as $dailyAvailability) {
                    // Vérifier simplement s'il y a de la place (sans conflits)
                    $slotStart = $currentDate->copy()->setTimeFrom($dailyAvailability->start_time);
                    $slotEnd = $currentDate->copy()->setTimeFrom($dailyAvailability->end_time);

                    // Ajuster pour le premier jour
                    if ($currentDate->isSameDay($start) && $slotStart->lt($start)) {
                        $slotStart = $start->copy();
                    }

                    // Ajuster pour le dernier jour
                    if ($currentDate->isSameDay($end) && $slotEnd->gt($end)) {
                        $slotEnd = $end->copy();
                    }

                    if ($slotStart->lt($slotEnd)) {
                        return true;
                    }
                }
            }

            $currentDate->addDay();
        }

        return false;
    }

    /**
     * Get available slots from impediments.
     */
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

    // ============ MÉTHODES PRIVÉES COMMUNES (optimisées avec conflits) ============

    /**
     * Load availabilities with conflicts (schedules and impediments).
     */
    private function loadAvailabilitiesWithConflicts(
        object $schedulable,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): Collection {
        $availabilities = $this->availabilityRepository->getForDateRange($schedulable, $start, $end, $type);

        // Toujours charger les conflits (approche stricte)
        return $availabilities->load(['schedules', 'impediments']);
    }

    /**
     * Filter availabilities for specific date.
     */
    private function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection
    {
        return $availabilities->filter(function (Availability $availability) use ($date): bool {
            return $this->availabilityAppliesToDate($availability, $date);
        });
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
     * Find a slot in an availability with conflict checking.
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

            // Vérification stricte des conflits
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
     * Find all slots in an availability for a given date with conflict checking.
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
     * Check if a time slot is available (optimized version with strict conflict checking).
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
}
