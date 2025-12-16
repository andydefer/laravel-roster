<?php

namespace Roster\Services;

use Roster\Models\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class AvailabilityService
{
    protected ?Model $schedulable = null;
    protected array $filters = [];

    /**
     * Spécifier le modèle pour lequel gérer les disponibilités
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
     * Créer une nouvelle disponibilité
     */
    public function create(array $data): Availability
    {
        $this->validateSchedulable();
        $this->validateAvailabilityData($data);

        return Availability::create(array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
        ]));
    }

    /**
     * Mettre à jour une disponibilité existante
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        if ($data) {
            $this->validateAvailabilityData($data, $id);
        }

        $availability = $this->find($id);

        if (!$availability) {
            return false;
        }

        return $availability->update($data);
    }

    /**
     * Supprimer une disponibilité
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $availability = $this->find($id);

        if (!$availability) {
            return false;
        }

        return $availability->delete();
    }

    /**
     * Trouver une disponibilité par son ID
     */
    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();

        return Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    /**
     * Récupérer toutes les disponibilités
     */
    public function all(): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()->get();
    }

    /**
     * Récupérer les disponibilités avec les filtres appliqués
     */
    public function get(): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()->get();
    }

    /**
     * Filtrer par type de disponibilité
     */
    public function whereType(string $type): self
    {
        $this->filters['type'] = $type;
        return $this;
    }

    /**
     * Filtrer par jour spécifique
     */
    public function whereDay(string $day): self
    {
        $this->filters['day'] = strtolower($day);
        return $this;
    }

    /**
     * Vérifier si le schedulable est disponible à un moment donné
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

        // Vérifier les dates de validité si présentes
        $query->where(function ($q) use ($datetime) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $datetime->toDateString());
        })->where(function ($q) use ($datetime) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $datetime->toDateString());
        });

        return $query->exists();
    }

    /**
     * Trouver le prochain créneau disponible
     */
    public function nextAvailableSlot(Carbon $fromDate, int $durationMinutes = 60): ?Carbon
    {
        $this->validateSchedulable();

        $currentDate = $fromDate->copy();
        $maxDaysToCheck = 365; // Limite pour éviter les boucles infinies

        for ($i = 0; $i < $maxDaysToCheck; $i++) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            $availabilities = Availability::where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable))
                ->whereJsonContains('days', $dayOfWeek)
                ->where(function ($q) use ($currentDate) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $currentDate->toDateString());
                })
                ->where(function ($q) use ($currentDate) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $currentDate->toDateString());
                })
                ->orderBy('start_time')
                ->get();

            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // Pour le premier jour, commencer à l'heure actuelle ou à l'heure de début
                if ($i === 0 && $slotStart->lt($fromDate)) {
                    $slotStart = $fromDate->copy();
                }

                // Vérifier si on peut placer la durée dans le créneau
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
     * Récupérer tous les créneaux disponibles dans une période
     */
    public function availableSlots(Carbon $startDate, Carbon $endDate, int $durationMinutes = 60, int $intervalMinutes = 30): array
    {
        $this->validateSchedulable();

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            $availabilities = Availability::where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable))
                ->whereJsonContains('days', $dayOfWeek)
                ->where(function ($q) use ($currentDate) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $currentDate->toDateString());
                })
                ->where(function ($q) use ($currentDate) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $currentDate->toDateString());
                })
                ->orderBy('start_time')
                ->get();

            foreach ($availabilities as $availability) {
                $slotStart = $currentDate->copy()->setTimeFrom($availability->start_time);
                $slotEnd = $currentDate->copy()->setTimeFrom($availability->end_time);

                // Générer les créneaux à l'intérieur de cette disponibilité
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
     * Réinitialiser les filtres
     */
    public function resetFilters(): self
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Valider les données de disponibilité
     */
    protected function validateAvailabilityData(array $data, ?int $exceptId = null): void
    {
        // Vérifier les jours
        if (isset($data['days']) && empty($data['days'])) {
            throw new InvalidArgumentException('At least one day must be specified');
        }

        // Vérifier les horaires
        if (isset($data['start_time']) && isset($data['end_time'])) {
            $startTime = Carbon::parse($data['start_time']);
            $endTime = Carbon::parse($data['end_time']);

            if ($endTime->lte($startTime)) {
                throw new InvalidArgumentException('End time must be after start time');
            }
        }

        // Vérifier les dates de période
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            if ($endDate->lt($startDate)) {
                throw new InvalidArgumentException('End date must be after or equal to start date');
            }
        }
    }

    /**
     * Valider que le schedulable est défini
     */
    protected function validateSchedulable(): void
    {
        if (!$this->schedulable) {
            throw new \RuntimeException('No schedulable specified. Use for() method first.');
        }
    }

    /**
     * Appliquer les filtres à la requête
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
