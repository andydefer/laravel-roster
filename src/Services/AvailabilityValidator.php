<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Roster\Models\Availability;

class AvailabilityValidator
{
    /**
     * Valider les données de base d'une disponibilité
     *
     * @param  array<string, mixed>  $data
     */
    public function validateBasicData(array $data): void
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
     * Vérifier s'il y a un chevauchement avec des disponibilités existantes
     * Vérifie toujours les chevauchements, quel que soit le type
     *
     * @param  array<string, mixed>  $data
     */
    public function hasOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): bool {
        $startTime = Carbon::parse($data['start_time']);
        $endTime = Carbon::parse($data['end_time']);
        $days = $data['days'] ?? [];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

        $query = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model));

        // Exclure l'enregistrement courant lors d'une mise à jour
        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        /** @var Collection<int, Availability> $existingAvailabilities */
        $existingAvailabilities = $query->get();

        foreach ($existingAvailabilities as $existingAvailability) {
            // Vérifier si les jours se chevauchent
            $commonDays = array_intersect($existingAvailability->days, $days);
            if ($commonDays === []) {
                continue;
            }

            if ($this->overlaps($existingAvailability, $startTime, $endTime, $startDate, $endDate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier si deux plages se chevauchent
     */
    public function overlaps(
        Availability $availability,
        Carbon $newStartTime,
        Carbon $newEndTime,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool {
        // Vérifier le chevauchement horaire
        if (! $this->timeOverlaps($availability->start_time, $availability->end_time, $newStartTime, $newEndTime)) {
            return false;
        }

        // Vérifier le chevauchement des dates
        return $this->dateRangesOverlap(
            $availability->start_date,
            $availability->end_date,
            $newStartDate,
            $newEndDate
        );
    }

    /**
     * Vérifier le chevauchement des plages horaires
     */
    public function timeOverlaps(
        Carbon $existingStart,
        Carbon $existingEnd,
        Carbon $newStart,
        Carbon $newEnd
    ): bool {
        // Deux plages se chevauchent si:
        // 1. La nouvelle commence avant que l'existante ne se termine ET
        // 2. La nouvelle se termine après que l'existante ne commence
        return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
    }

    /**
     * Vérifier le chevauchement des périodes de dates
     */
    public function dateRangesOverlap(
        ?Carbon $existingStartDate,
        ?Carbon $existingEndDate,
        ?Carbon $newStartDate,
        ?Carbon $newEndDate
    ): bool {
        // Si aucune date n'est spécifiée pour l'existant, c'est valable indéfiniment
        if (! $existingStartDate instanceof Carbon && ! $existingEndDate instanceof Carbon) {
            return true;
        }

        // Si aucune date n'est spécifiée pour la nouvelle, c'est valable indéfiniment
        if (! $newStartDate instanceof Carbon && ! $newEndDate instanceof Carbon) {
            return true;
        }

        // Calculer les bornes effectives
        $effectiveExistingStart = $existingStartDate ?? Carbon::minValue();
        $effectiveExistingEnd = $existingEndDate ?? Carbon::maxValue();
        $effectiveNewStart = $newStartDate ?? Carbon::minValue();
        $effectiveNewEnd = $newEndDate ?? Carbon::maxValue();

        // Deux périodes se chevauchent si:
        // 1. La nouvelle commence avant que l'existante ne se termine ET
        // 2. La nouvelle se termine après que l'existante ne commence
        return $effectiveNewStart->lte($effectiveExistingEnd) &&
            $effectiveNewEnd->gte($effectiveExistingStart);
    }

    /**
     * Valider si deux disponibilités sont adjacentes (se touchent)
     */
    public function areAdjacent(
        Availability $first,
        Availability $second
    ): bool {
        // Même schedulable
        if (
            $first->schedulable_id !== $second->schedulable_id ||
            $first->schedulable_type !== $second->schedulable_type
        ) {
            return false;
        }

        // Mêmes jours (au moins un jour en commun)
        $commonDays = array_intersect($first->days, $second->days);
        if ($commonDays === []) {
            return false;
        }

        // Même type
        if ($first->type !== $second->type) {
            return false;
        }

        // Périodes de dates compatibles (doivent se chevaucher)
        if (! $this->dateRangesOverlap(
            $first->start_date,
            $first->end_date,
            $second->start_date,
            $second->end_date
        )) {
            return false;
        }

        // Vérifier l'adjacence horaire (les plages se touchent exactement)
        if ($first->end_time->eq($second->start_time)) {
            return true;
        }

        return (bool) $second->end_time->eq($first->start_time);
    }

    /**
     * Fusionner deux disponibilités adjacentes
     *
     * @return array{
     *     type: string,
     *     start_time: string,
     *     end_time: string,
     *     days: array<string>,
     *     start_date: string|null,
     *     end_date: string|null
     * }
     */
    public function mergeAdjacent(
        Availability $first,
        Availability $second
    ): array {
        if (! $this->areAdjacent($first, $second)) {
            throw new InvalidArgumentException('Cannot merge non-adjacent availabilities');
        }

        $startTime = min($first->start_time->timestamp, $second->start_time->timestamp);
        $endTime = max($first->end_time->timestamp, $second->end_time->timestamp);

        // Gérer les dates de période
        $startDate = null;
        $endDate = null;

        if ($first->start_date !== null || $second->start_date !== null) {
            $firstStart = $first->start_date ? $first->start_date->timestamp : PHP_INT_MAX;
            $secondStart = $second->start_date ? $second->start_date->timestamp : PHP_INT_MAX;
            $startDate = Carbon::createFromTimestamp(min($firstStart, $secondStart));
        }

        if ($first->end_date !== null || $second->end_date !== null) {
            $firstEnd = $first->end_date ? $first->end_date->timestamp : PHP_INT_MIN;
            $secondEnd = $second->end_date ? $second->end_date->timestamp : PHP_INT_MIN;
            $endDate = Carbon::createFromTimestamp(max($firstEnd, $secondEnd));
        }

        return [
            'type' => $first->type,
            'start_time' => Carbon::createFromTimestamp($startTime)->format('H:i:s'),
            'end_time' => Carbon::createFromTimestamp($endTime)->format('H:i:s'),
            'days' => array_values(array_unique(array_merge($first->days, $second->days))),
            'start_date' => $startDate?->format('Y-m-d'),
            'end_date' => $endDate?->format('Y-m-d'),
        ];
    }
}
