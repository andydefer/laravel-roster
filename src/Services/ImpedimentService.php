<?php
// ==== src/Services/ImpedimentService.php ====

namespace Roster\Services;

use Roster\Models\Impediment;
use Roster\Models\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;

class ImpedimentService
{
    protected ?Model $schedulable = null;
    protected array $filters = [];

    /**
     * Spécifier le modèle pour lequel gérer les impediments
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
     * Créer un nouvel impediment
     */
    public function create(array $data): Impediment
    {
        $this->validateSchedulable();

        // Valider les données de base
        $this->validateImpedimentData($data);

        // Trouver l'Availability correspondante
        $availability = $this->findMatchingAvailability($data);

        if (!$availability) {
            throw new InvalidArgumentException('No matching availability found for this impediment');
        }

        // Créer l'impediment
        $impediment = Impediment::create(array_merge($data, [
            'availability_id' => $availability->id,
        ]));

        return $impediment;
    }

    /**
     * Mettre à jour un impediment existant
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $impediment = $this->find($id);

        if (!$impediment) {
            return false;
        }

        if ($data) {
            // Si les dates changent, vérifier la nouvelle Availability
            if (isset($data['start_datetime'])) {
                $newAvailability = $this->findMatchingAvailability($data);

                if (!$newAvailability) {
                    throw new InvalidArgumentException('No matching availability found for new impediment time');
                }

                if ($newAvailability->id !== $impediment->availability_id) {
                    $data['availability_id'] = $newAvailability->id;
                }
            }
        }

        return $impediment->update($data);
    }

    /**
     * Supprimer un impediment
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();

        $impediment = $this->find($id);

        if (!$impediment) {
            return false;
        }

        return $impediment->delete();
    }

    /**
     * Trouver un impediment par son ID
     */
    public function find(int $id): ?Impediment
    {
        $this->validateSchedulable();

        return Impediment::whereHas('availability', function ($query) {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        })->find($id);
    }

    /**
     * Trouver tous les impediments
     */
    public function all(): Collection
    {
        $this->validateSchedulable();

        return $this->applyFilters()->get();
    }

    /**
     * Récupérer les impediments avec les filtres appliqués
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
     * Récupérer les impediments pour une période donnée
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
     * Vérifier si une période est bloquée par un impediment
     */
    public function isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();

        // Trouver une Availability correspondante
        $availability = $this->findAvailabilityForTimeSlot($start, $end, $type);

        if (!$availability) {
            return false;
        }

        // Vérifier les chevauchements avec des impediments
        return $availability->impediments()
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start)
            ->exists();
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
     * Valider les données de l'impediment
     */
    protected function validateImpedimentData(array $data): void
    {
        if (!isset($data['start_datetime']) || !isset($data['end_datetime'])) {
            throw new InvalidArgumentException('Start and end datetime are required');
        }

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        if ($end->lte($start)) {
            throw new InvalidArgumentException('End datetime must be after start datetime');
        }
    }

    /**
     * Trouver l'Availability correspondante pour un impediment
     */
    protected function findMatchingAvailability(array $data): ?Availability
    {
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        return $this->findAvailabilityForTimeSlot($start, $end);
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

        return $query->first();
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
     */
    protected function applyFilters()
    {
        $query = Impediment::whereHas('availability', function ($query) {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        });

        if (isset($this->filters['type'])) {
            $query->whereHas('availability', function ($q) {
                $q->where('type', $this->filters['type']);
            });
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
