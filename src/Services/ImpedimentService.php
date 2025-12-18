<?php

declare(strict_types=1);

// ==== src/Services/ImpedimentService.php ====

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Roster\Models\Availability;
use Roster\Models\Impediment;

class ImpedimentService extends AbstractSchedulableService
{
    protected ?Model $schedulable = null;

    protected array $filters = [];

    /**
     * Créer un nouvel impediment avec vérification des chevauchements
     */
    public function create(array $data): Impediment
    {
        $this->validateSchedulable();

        // Valider les données de base
        $this->validateImpedimentData($data);

        // Trouver l'Availability correspondante
        $availability = $this->findMatchingAvailability($data);

        if (! $availability instanceof Availability) {
            throw new InvalidArgumentException('No matching availability found for this impediment');
        }

        // Vérifier les chevauchements
        if ($this->hasOverlappingImpediment($availability->id, $data)) {
            throw new InvalidArgumentException('This time slot overlaps with an existing impediment');
        }

        // Créer l'impediment
        $impediment = Impediment::create(array_merge($data, [
            'availability_id' => $availability->id,
        ]));

        return $impediment;
    }

    /**
     * Mettre à jour un impediment existant avec vérification des chevauchements
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();

        $impediment = $this->find($id);

        if (! $impediment instanceof Impediment) {
            return false;
        }

        if ($data !== []) {
            // Si les dates changent, vérifier la nouvelle Availability
            $availabilityId = $impediment->availability_id;

            if (isset($data['start_datetime'])) {
                $newAvailability = $this->findMatchingAvailability($data);

                if (! $newAvailability instanceof Availability) {
                    throw new InvalidArgumentException('No matching availability found for new impediment time');
                }

                $availabilityId = $newAvailability->id;

                // Vérifier les chevauchements avec d'autres impediments (sauf celui en cours de modification)
                if ($this->hasOverlappingImpediment($availabilityId, $data, $id)) {
                    throw new InvalidArgumentException('This time slot overlaps with another impediment');
                }

                if ($newAvailability->id !== $impediment->availability_id) {
                    $data['availability_id'] = $newAvailability->id;
                }
            } else {
                // Même availability, vérifier les chevauchements
                $updateData = array_merge([
                    'start_datetime' => $impediment->start_datetime,
                    'end_datetime' => $impediment->end_datetime,
                ], $data);

                if ($this->hasOverlappingImpediment($availabilityId, $updateData, $id)) {
                    throw new InvalidArgumentException('This time slot overlaps with another impediment');
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

        if (! $impediment instanceof Impediment) {
            return false;
        }

        return $impediment->delete();
    }

    /**
     * Vérifier si un créneau horaire chevauche un impediment existant
     *
     * @param  array<string, mixed>  $data
     */
    protected function hasOverlappingImpediment(int $availabilityId, array $data, ?int $excludeId = null): bool
    {
        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        $query = Impediment::where('availability_id', $availabilityId)
            ->where(function ($q) use ($start, $end): void {
                // Chevauchement : un impediment existe qui commence avant la fin du nouveau
                // et se termine après le début du nouveau
                $q->where(function ($inner) use ($start, $end): void {
                    $inner->where('start_datetime', '<', $end)
                        ->where('end_datetime', '>', $start);
                });
                // Ou : un impediment qui commence ou se termine exactement à la même heure
                // ->orWhere('start_datetime', '=', $start)
                // ->orWhere('end_datetime', '=', $end);
            });

        // Exclure l'impediment en cours de modification si nécessaire
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Trouver un impediment par son ID
     */
    public function find(int $id): ?Impediment
    {
        $this->validateSchedulable();

        return Impediment::whereHas('availability', function ($query): void {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        })->find($id);
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

        if (! $availability instanceof Availability) {
            return false;
        }

        // Vérifier les chevauchements avec des impediments
        return $availability->impediments()
            ->where(function ($q) use ($start, $end): void {
                $q->where('start_datetime', '<', $end)
                    ->where('end_datetime', '>', $start);
            })
            ->exists();
    }

    /**
     * Obtenir les créneaux disponibles dans une période
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $this->validateSchedulable();

        // Trouver l'Availability correspondante
        $availability = $this->findAvailabilityForTimeSlot($start, $end, $type);

        if (! $availability instanceof Availability) {
            return collect();
        }

        // Récupérer tous les impediments pour cette availability
        $impediments = $availability->impediments()
            ->where('start_datetime', '>=', $start->copy()->startOfDay())
            ->where('end_datetime', '<=', $end->copy()->endOfDay())
            ->orderBy('start_datetime')
            ->get();

        // Si pas d'impediments, tout le créneau est disponible
        if ($impediments->isEmpty()) {
            return collect([[
                'start' => $start,
                'end' => $end,
            ]]);
        }

        // Calculer les créneaux disponibles entre les impediments
        $availableSlots = collect();
        $currentTime = $start->copy();

        foreach ($impediments as $impediment) {
            $impedimentStart = Carbon::parse($impediment->start_datetime);
            $impedimentEnd = Carbon::parse($impediment->end_datetime);

            // Si l'impediment commence après le temps courant, il y a un créneau disponible
            if ($impedimentStart > $currentTime) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impedimentStart->copy(),
                ]);
            }

            // Mettre à jour le temps courant à la fin de l'impediment
            $currentTime = max($currentTime, $impedimentEnd);
        }

        // Vérifier s'il reste du temps après le dernier impediment
        if ($currentTime < $end) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }

    /**
     * Valider les données de l'impediment
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateImpedimentData(array $data): void
    {
        if (! isset($data['start_datetime']) || ! isset($data['end_datetime'])) {
            throw new InvalidArgumentException('Start and end datetime are required');
        }

        $start = Carbon::parse($data['start_datetime']);
        $end = Carbon::parse($data['end_datetime']);

        if ($end->lte($start)) {
            throw new InvalidArgumentException('End datetime must be after start datetime');
        }

        // Optionnel : vérifier une durée minimale
        $minDuration = 5; // minutes
        if ($start->diffInMinutes($end) < $minDuration) {
            throw new InvalidArgumentException(sprintf('Impediment must be at least %d minutes long', $minDuration));
        }
    }

    /**
     * Trouver l'Availability correspondante pour un impediment
     *
     * @param  array<string, mixed>  $data
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
     * Appliquer les filtres à la requête
     */
    protected function applyFilters()
    {
        $query = Impediment::whereHas('availability', function ($query): void {
            $query->where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable));
        });

        if (isset($this->filters['type'])) {
            $query->whereHas('availability', function ($q): void {
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
