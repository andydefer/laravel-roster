<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;

/**
 * Enables a model to have roster-related relationships.
 *
 * Provides morph relationships for schedules and availabilities.
 */
trait HasRoster
{
    /**
     * Defines the schedules relationship (concrete planned time slots).
     *
     * @return MorphMany
     */
    public function schedules()
    {
        return $this->morphMany(Schedule::class, 'schedulable');
    }

    /**
     * Defines the availabilities relationship (availability rules).
     *
     * @return MorphMany
     */
    public function availabilities()
    {
        return $this->morphMany(Availability::class, 'schedulable');
    }

    /**
     * Defines the impediments relationship (blocked periods).
     *
     * @return MorphMany
     */
    public function impediments()
    {
        return $this->morphMany(Impediment::class, 'schedulable');
    }

    /**
     * Récupère tous les impediments et schedules dans une période donnée.
     *
     * @param Carbon $startDate Début de la période
     * @param Carbon $endDate Fin de la période
     * @return array{
     *     impediments: Collection<int, Impediment>,
     *     schedules: Collection<int, Schedule>
     * } Tableau contenant les collections d'impediments et schedules
     */
    public function getRosterItemsInPeriod(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'impediments' => $this->getImpedimentsInPeriod($startDate, $endDate),
            'schedules' => $this->getSchedulesInPeriod($startDate, $endDate),
        ];
    }

    /**
     * Récupère tous les impediments dans une période donnée.
     *
     * @param Carbon $startDate Début de la période
     * @param Carbon $endDate Fin de la période
     * @return Collection<int, Impediment> Collection d'impediments
     */
    public function getImpedimentsInPeriod(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->impediments()
            ->where(function ($query) use ($startDate, $endDate) {
                // Cas 1: L'impediment commence avant la période et se termine pendant
                $query->where('start_datetime', '<', $endDate)
                    ->where('end_datetime', '>', $startDate);
            })
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Récupère tous les schedules dans une période donnée.
     *
     * @param Carbon $startDate Début de la période
     * @param Carbon $endDate Fin de la période
     * @return Collection<int, Schedule> Collection de schedules
     */
    public function getSchedulesInPeriod(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->schedules()
            ->where(function ($query) use ($startDate, $endDate) {
                // Cas 1: Le schedule commence avant la période et se termine pendant
                $query->where('start_datetime', '<', $endDate)
                    ->where('end_datetime', '>', $startDate);
            })
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Vérifie si une période a des conflits (impediments ou schedules).
     *
     * @param Carbon $startDate Début de la période
     * @param Carbon $endDate Fin de la période
     * @return bool True si des conflits existent, false sinon
     */
    public function hasConflictsInPeriod(Carbon $startDate, Carbon $endDate): bool
    {
        $items = $this->getRosterItemsInPeriod($startDate, $endDate);

        return !$items['impediments']->isEmpty() || !$items['schedules']->isEmpty();
    }

    /**
     * Récupère les disponibilités valides pour une période donnée.
     * Méthode d'utilité supplémentaire pour compléter le tableau.
     *
     * @param Carbon $startDate Début de la période
     * @param Carbon $endDate Fin de la période
     * @param string|null $type Type de disponibilité (optionnel)
     * @return Collection<int, Availability> Collection de disponibilités
     */
    public function getAvailabilitiesInPeriod(Carbon $startDate, Carbon $endDate, ?string $type = null): Collection
    {
        $query = $this->availabilities()
            ->where(function ($query) use ($endDate) {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $endDate);
            })
            ->where(function ($query) use ($startDate) {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $startDate);
            });

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->orderBy('daily_start')->get();
    }
}
