<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Roster\Models\Availability;

class AvailabilityService extends AbstractSchedulableService
{

    protected AvailabilityValidator $validator;

    public function __construct(?AvailabilityValidator $availabilityValidator = null)
    {
        $this->validator = $availabilityValidator ?? new AvailabilityValidator;
    }

    /**
     * Récupérer le modèle schedulable courant
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Créer une nouvelle disponibilité avec validation des chevauchements
     */
    public function create(array $data): Availability
    {
        $this->validateSchedulable();

        // Valider les données de base
        $this->validator->validateBasicData($data);

        // Vérifier les chevauchements (toujours interdit)
        if ($this->validator->hasOverlapping($this->schedulable, $data)) {
            throw new InvalidArgumentException('This availability overlaps with an existing one.');
        }

        // Fusion automatique des disponibilités adjacentes (toujours activée)
        $data = $this->mergeWithAdjacentAvailabilities($data);

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

        $availability = $this->find($id);

        if (! $availability instanceof Availability) {
            return false;
        }

        if ($data !== []) {
            // Valider les données de base
            $this->validator->validateBasicData($data);

            // Préparer les données pour la vérification des chevauchements
            // En cas de mise à jour partielle, utiliser les valeurs existantes pour les champs non fournis
            $checkData = array_merge([
                'type' => $availability->type,
                'days' => $availability->days,
                'start_date' => $availability->start_date?->format('Y-m-d'),
                'end_date' => $availability->end_date?->format('Y-m-d'),
            ], $data);

            // Assurer que les champs de temps sont présents
            if (! isset($checkData['start_time']) && $availability->start_time) {
                $checkData['start_time'] = $availability->start_time->format('H:i:s');
            }

            if (! isset($checkData['end_time']) && $availability->end_time) {
                $checkData['end_time'] = $availability->end_time->format('H:i:s');
            }

            // Vérifier les chevauchements avec les autres disponibilités (toujours interdit)
            if ($this->validator->hasOverlapping($this->schedulable, $checkData, $id)) {
                throw new InvalidArgumentException('This availability overlaps with an existing one.');
            }
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

        if (! $availability instanceof Availability) {
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
     * Vérifier s'il y a des chevauchements
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();

        return $this->validator->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Trouver toutes les disponibilités qui chevauchent
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, Availability>
     */
    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);
        $days = $data['days'] ?? [];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

        $query = Availability::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        // Exclure l'enregistrement courant lors d'une mise à jour
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        /** @var Collection<int, Availability> $allAvailabilities */
        $allAvailabilities = $query->get();

        // Filtrer manuellement pour vérifier l'intersection des jours et le chevauchement
        return $allAvailabilities->filter(function (Availability $availability) use ($startTime, $endTime, $startDate, $endDate, $days): bool {
            // Vérifier si les jours se chevauchent
            if (! empty($days)) {
                $commonDays = array_intersect($availability->days, $days);
                if ($commonDays === []) {
                    return false;
                }
            }

            return $this->validator->overlaps($availability, $startTime, $endTime, $startDate, $endDate);
        });
    }

    /**
     * Fusionner avec les disponibilités adjacentes
     */
    protected function mergeWithAdjacentAvailabilities(array $data): array
    {
        $this->validateSchedulable();

        // Trouver les disponibilités adjacentes
        $adjacentAvailabilities = $this->findAdjacentAvailabilities($data);

        if ($adjacentAvailabilities->isEmpty()) {
            return $data;
        }

        // Fusionner toutes les disponibilités adjacentes
        $mergedData = $data;
        $idsToDelete = [];

        foreach ($adjacentAvailabilities as $adjacentAvailability) {
            try {
                // Créer un objet temporaire avec les données fusionnées
                $tempAvailability = $this->createAvailabilityFromData($mergedData);

                // Vérifier si elles sont vraiment adjacentes
                if ($this->validator->areAdjacent($tempAvailability, $adjacentAvailability)) {
                    $mergedData = $this->validator->mergeAdjacent($tempAvailability, $adjacentAvailability);
                    $idsToDelete[] = $adjacentAvailability->id;
                }
            } catch (InvalidArgumentException $e) {
                // Si la fusion échoue, continuer avec la suivante
                continue;
            }
        }

        // Supprimer toutes les disponibilités fusionnées
        if ($idsToDelete !== []) {
            Availability::whereIn('id', $idsToDelete)->delete();
        }

        return $mergedData;
    }

    /**
     * Trouver les disponibilités adjacentes
     *
     * @param  array<string, mixed>  $data
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

        // Créer un objet temporaire pour la comparaison
        $tempAvailability = $this->createAvailabilityFromData($data);

        return $availabilities->filter(function (Availability $availability) use ($tempAvailability): bool {
            return $this->validator->areAdjacent($tempAvailability, $availability);
        });
    }

    /**
     * Créer un objet Availability temporaire à partir de données
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException si end_time est avant start_time ou si les clés sont manquantes
     */
    protected function createAvailabilityFromData(array $data): Availability
    {
        // Vérifier que les champs essentiels existent
        if (! isset($data['start_time'], $data['end_time'])) {
            throw new InvalidArgumentException('Both start_time and end_time must be provided.');
        }

        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);

        // Vérification que end_time est après start_time
        if ($endTime->lessThanOrEqualTo($startTime)) {
            throw new InvalidArgumentException('End time must be after start time.');
        }

        $availability = new Availability;

        // Ajouter les attributs du schedulable
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
        $query->where(function ($q) use ($datetime): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $datetime->toDateString());
        })->where(function ($q) use ($datetime): void {
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

        for ($i = 0; $i < $maxDaysToCheck; ++$i) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            /** @var Collection<int, Availability> $availabilities */
            $availabilities = Availability::where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable))
                ->whereJsonContains('days', $dayOfWeek)
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $currentDate->toDateString());
                })
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $currentDate->toDateString());
                })
                ->orderBy('start_time')
                ->get();

            /** @var Availability $availability */
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
     *
     * @return array<array{
     *     start: Carbon,
     *     end: Carbon,
     *     type: string,
     *     availability_id: int
     * }>
     */
    public function availableSlots(Carbon $startDate, Carbon $endDate, int $durationMinutes = 60, int $intervalMinutes = 30): array
    {
        $this->validateSchedulable();

        $slots = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = strtolower($currentDate->englishDayOfWeek);

            /** @var Collection<int, Availability> $availabilities */
            $availabilities = Availability::where('schedulable_id', $this->schedulable->id)
                ->where('schedulable_type', get_class($this->schedulable))
                ->whereJsonContains('days', $dayOfWeek)
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', $currentDate->toDateString());
                })
                ->where(function ($q) use ($currentDate): void {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $currentDate->toDateString());
                })
                ->orderBy('start_time')
                ->get();

            /** @var Availability $availability */
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
     * Appliquer les filtres à la requête
     *
     * @return Builder
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
