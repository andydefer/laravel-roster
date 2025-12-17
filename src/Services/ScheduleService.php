<?php
// ==== src/Services/ScheduleService.php ====

namespace Roster\Services;

use Roster\Models\Schedule;
use Roster\Models\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;

class ScheduleService
{
    protected ?Model $schedulable = null;
    protected array $filters = [];

    /**
     * Spécifier le modèle pour lequel gérer les schedules
     */
    public function for(Model $schedulable): self
    {
        $this->schedulable = $schedulable;
        return $this;
    }

    /**
     * Récupérer le modèle schedulable courant
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Créer un nouveau schedule
     */
    public function create(array $data): Schedule
    {
        $this->validateSchedulable();

        // Valider les données de base
        $this->validateScheduleData($data);

        // Trouver l'Availability correspondante
        $availability = $this->findMatchingAvailability($data);

        if (!$availability) {
            throw new InvalidArgumentException('No matching availability found for this schedule');
        }

        // Créer le schedule - la validation des horaires se fera dans le modèle Schedule
        $schedule = Schedule::create(array_merge($data, [
            'availability_id' => $availability->id,
        ]));

        return $schedule;
    }


    /**
     * Mettre à jour un schedule existant
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $schedule = $this->find($id);

        if (!$schedule) {
            return false;
        }

        if ($data) {
            // Si les dates changent, vérifier la nouvelle Availability
            if (isset($data['start_datetime'])) {
                $newAvailability = $this->findMatchingAvailability($data);

                if (!$newAvailability) {
                    throw new InvalidArgumentException('No matching availability found for new schedule time');
                }

                if ($newAvailability->id !== $schedule->availability_id) {
                    $data['availability_id'] = $newAvailability->id;
                }
            }
        }

        return $schedule->update($data);
    }

    /**
     * Supprimer un schedule
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $schedule = $this->find($id);

        if (!$schedule) {
            return false;
        }

        return $schedule->delete();
    }

    /**
     * Trouver un schedule par son ID
     */
    public function find(int $id): ?Schedule
    {
        $this->validateSchedulable();

        return Schedule::whereHas('availability', function ($query) {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        })->find($id);
    }

    /**
     * Trouver tous les schedules
     */
    public function all(): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()->get();
    }

    /**
     * Récupérer les schedules avec les filtres appliqués
     */
    public function get(): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()->get();
    }

    /**
     * Filtrer par type d'activité
     */
    public function whereType(string $type): self
    {
        $this->filters['type'] = $type;
        return $this;
    }

    /**
     * Filtrer par date de début
     */
    public function whereStartDate(Carbon $date): self
    {
        $this->filters['start_date'] = $date;
        return $this;
    }

    /**
     * Filtrer par date de fin
     */
    public function whereEndDate(Carbon $date): self
    {
        $this->filters['end_date'] = $date;
        return $this;
    }

    /**
     * Filtrer par statut
     */
    public function whereStatus(string $status): self
    {
        $this->filters['status'] = $status;
        return $this;
    }

    /**
     * Récupérer les schedules pour une période donnée
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Vérifier la disponibilité pour un créneau
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();

        // Trouver une Availability correspondante
        $availability = $this->findAvailabilityForTimeSlot($start, $end, $type);

        if (!$availability) {
            return false;
        }

        // Vérifier les chevauchements avec d'autres schedules
        $hasOverlappingSchedule = Schedule::where('availability_id', $availability->id)
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        // Vérifier les chevauchements avec des impediments
        $hasOverlappingImpediment = $availability->impediments()
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();

        return !$hasOverlappingSchedule && !$hasOverlappingImpediment;
    }

    /**
     * Trouver le prochain créneau disponible
     */
    public function findNextAvailableSlot(int $durationMinutes, ?string $type = null): ?array
    {
        $this->validateSchedulable();

        $now = Carbon::now();

        // Chercher dans les 30 prochains jours
        for ($i = 0; $i < 30; $i++) {
            $currentDate = $now->copy()->addDays($i)->startOfDay();

            // Récupérer toutes les availabilities pour ce jour
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
     * Trouver tous les créneaux disponibles dans une période
     */
    public function findAvailableSlots(Carbon $startDate, Carbon $endDate, int $durationMinutes, ?string $type = null): array
    {
        $this->validateSchedulable();

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
     * Réinitialiser les filtres
     */
    public function resetFilters(): self
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Valider les données du schedule
     */
    protected function validateScheduleData(array $data): void
    {
        if (!isset($data['start_datetime']) || !isset($data['end_datetime'])) {
            throw new InvalidArgumentException('Start and end datetime are required');
        }

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        if ($end->lte($start)) {
            throw new InvalidArgumentException('End datetime must be after start datetime');
        }

        if ($start->lt(Carbon::now())) {
            throw new InvalidArgumentException('Cannot schedule in the past');
        }
    }

    /**
     * Trouver l'Availability correspondante pour un schedule
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        // Chercher simplement une availability pour le jour et le type
        // La validation horaire se fera dans le modèle Schedule
        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek));

        if (isset($data['type'])) {
            $query->where('type', $data['type']);
        }

        // Vérifier les dates de période
        $query->where(function ($q) use ($start) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $start->toDateString());
        })->where(function ($q) use ($end) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $end->toDateString());
        });

        return $query->first();
    }

    /**
     * Trouver une Availability pour un créneau donné
     */
    protected function findAvailabilityForTimeSlot(Carbon $start, Carbon $end, ?string $type = null): ?Availability
    {
        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'));

        if ($type) {
            $query->where('type', $type);
        }

        // Vérifier les dates de période
        $query->where(function ($q) use ($start) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $start->toDateString());
        })->where(function ($q) use ($end) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $end->toDateString());
        });

        /** @var Availability|null $availability */
        $availability = $query->first();

        return $availability;
    }

    /**
     * Récupérer les availabilities pour une date donnée
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

        // Vérifier les dates de période
        $query->where(function ($q) use ($date) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $date->toDateString());
        })->where(function ($q) use ($date) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $date->toDateString());
        });

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $query->orderBy('start_time')->get();

        return $availabilities;
    }

    /**
     * Trouver un créneau dans une availability
     */
    protected function findSlotInAvailability(Availability $availability, Carbon $date, int $durationMinutes, bool $isToday = false): ?array
    {
        $startTime = $availability->start_time;
        $endTime = $availability->end_time;

        $currentSlot = $date->copy()
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        $endOfSlot = $date->copy()
            ->setTime($endTime->hour, $endTime->minute, $endTime->second);

        // Si c'est aujourd'hui et que l'heure actuelle est après l'heure de début, commencer à l'heure actuelle
        if ($isToday) {
            $now = Carbon::now();
            if ($now->gt($currentSlot) && $now->lt($endOfSlot)) {
                $currentSlot = $now->copy()->addMinutes(1); // Commencer à la minute suivante
            }
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            // Vérifier la disponibilité
            if ($this->isTimeSlotAvailable($currentSlot, $proposedEnd, $availability->type)) {
                return [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            // Avancer de 15 minutes (ou par incrément configurable)
            $currentSlot->addMinutes(15);
        }

        return null;
    }

    /**
     * Trouver tous les créneaux dans une availability pour une date donnée
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

        // Si une heure de début minimale est spécifiée
        if ($minStartTime && $minStartTime->gt($currentSlot)) {
            $currentSlot = $minStartTime->copy();
        }

        while ($currentSlot->copy()->addMinutes($durationMinutes)->lte($endOfSlot)) {
            $proposedEnd = $currentSlot->copy()->addMinutes($durationMinutes);

            // Vérifier la disponibilité
            if ($this->isTimeSlotAvailable($currentSlot, $proposedEnd, $availability->type)) {
                $slots[] = [
                    'start' => $currentSlot->copy(),
                    'end' => $proposedEnd,
                    'availability_id' => $availability->id,
                    'type' => $availability->type,
                    'availability' => $availability,
                ];
            }

            // Avancer de 15 minutes
            $currentSlot->addMinutes(15);
        }

        return $slots;
    }

    /**
     * Valider que le schedulable est défini
     */
    protected function validateSchedulable(): void
    {
        if (!$this->schedulable) {
            throw new RuntimeException('No schedulable specified. Use for() method first.');
        }
    }

    /**
     * Appliquer les filtres à la requête
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyFilters()
    {
        $query = Schedule::whereHas('availability', function ($query) {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        });

        if (isset($this->filters['type'])) {
            $query->whereHas('availability', function ($q) {
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
