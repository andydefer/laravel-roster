<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Enums\DaysOfWeek;
use Roster\Exceptions\MergeConflictException;
use Roster\Models\Availability;
use Roster\Support\RosterMutationContext;

/**
 * Service for safely merging adjacent availabilities.
 */
class AvailabilityMergeService
{
    public function __construct(
        private AvailabilityRepositoryInterface $availabilityRepository
    ) {}

    /**
     * Attempt to merge a new availability with adjacent existing ones.
     * Only merges during CREATE operations, never during UPDATE.
     *
     * @param array<string, mixed> $newData New availability data
     * @param Availability|null $existingAvailability Existing availability to merge with
     * @param mixed $schedulable The schedulable model
     * @return array Merged data or original newData if no merge
     * @throws MergeConflictException If merge would be dangerous
     */
    public function mergeIfAdjacent(
        array $newData,
        ?Availability $existingAvailability,
        mixed $schedulable
    ): array {
        // Si pas d'entité existante ou pas de schedulable, pas de fusion
        if (!$existingAvailability instanceof Availability || !$schedulable) {
            return $newData;
        }

        // Vérifier les préconditions de fusion
        if (!$this->canMerge($existingAvailability, $newData, $schedulable)) {
            return $newData;
        }

        // Vérifier si les disponibilités sont adjacentes
        if (!$this->areAvailabilitiesAdjacent($existingAvailability, $newData)) {
            return $newData;
        }

        // Vérifier la sécurité avant fusion
        $this->checkMergeSafety($existingAvailability, $newData);

        // Fusionner les données
        $mergedData = $this->mergeData($existingAvailability, $newData);

        // Mettre à jour l'entité existante
        $this->updateExistingAvailability($existingAvailability, $mergedData);

        // Marquer pour suppression de la nouvelle entité
        $newData['_should_delete'] = true;
        $newData['_merged_into'] = $existingAvailability->id;

        return $newData;
    }

    /**
     * Check if two availabilities can be merged.
     * @param array<string, mixed> $newData
     */
    private function canMerge(Availability $availability, array $newData, mixed $schedulable): bool
    {
        // Même type
        if ($availability->type !== ($newData['type'] ?? null)) {
            return false;
        }

        // Même schedulable
        if (
            $availability->schedulable_id !== $schedulable->id ||
            $availability->schedulable_type !== get_class($schedulable)
        ) {
            return false;
        }

        return true;
    }


    /**
     * Check if availabilities are adjacent (touching in time).
     * @param array<string, mixed> $newData
     */
    private function areAvailabilitiesAdjacent(Availability $availability, array $newData): bool
    {
        // Vérifier les jours communs
        $existingDays = $availability->days;
        $newDays = $newData['days'] ?? [];

        $commonDays = array_intersect($existingDays, $newDays);
        if ($commonDays === []) {
            return false;
        }

        // Vérifier si les plages horaires se touchent
        return $this->timeRangesTouch(
            Carbon::parse($availability->daily_start),
            Carbon::parse($availability->daily_end),
            Carbon::parse($newData['daily_start']),
            Carbon::parse($newData['daily_end'])
        );
    }

    /**
     * Check if two time ranges touch exactly.
     */
    private function timeRangesTouch(Carbon $firstStart, Carbon $firstEnd, Carbon $secondStart, Carbon $secondEnd): bool
    {
        // Elles se touchent si la fin de l'une = début de l'autre
        if ($firstEnd->eq($secondStart)) {
            return true;
        }

        return $secondEnd->eq($firstStart);
    }

    /**
     * Check if merge would be safe.
     * @throws MergeConflictException
     * @param array<string, mixed> $newData
     */
    private function checkMergeSafety(Availability $availability, array $newData): void
    {
        $dependencies = [];

        // Compter les dépendances
        $schedulesCount = $availability->schedules()->count();
        $impedimentsCount = $availability->impediments()->count();

        if ($schedulesCount > 0) {
            $dependencies['schedules_count'] = $schedulesCount;
        }

        if ($impedimentsCount > 0) {
            $dependencies['impediments_count'] = $impedimentsCount;
        }

        // Vérifier les jours en conflit
        $existingDays = $availability->days;
        $newDays = $newData['days'] ?? [];

        // Si les nouveaux jours sont un sous-ensemble des jours existants, OK
        // Sinon, on va ajouter des jours, ce qui est généralement sûr
        $additionalDays = array_diff($newDays, $existingDays);
        if ($additionalDays !== []) {
            $dependencies['additional_days'] = $additionalDays;
        }

        // Seules les dépendances critiques bloquent la fusion
        $criticalDependencies = array_intersect_key($dependencies, [
            'schedules_count' => true,
            'impediments_count' => true
        ]);

        if ($criticalDependencies !== []) {
            throw MergeConflictException::fromAvailabilities($availability, null, $dependencies);
        }
    }

    /**
     * Merge data from new availability into existing one.
     * @param array<string, mixed> $newData
     * @return array<string, string|list<mixed>|null>
     */
    private function mergeData(Availability $availability, array $newData): array
    {
        // Fusionner les heures de début et fin
        $startTimes = [
            Carbon::parse($availability->daily_start),
            Carbon::parse($newData['daily_start'])
        ];
        $endTimes = [
            Carbon::parse($availability->daily_end),
            Carbon::parse($newData['daily_end'])
        ];

        $mergedStartTime = min($startTimes[0]->timestamp, $startTimes[1]->timestamp);
        $mergedEndTime = max($endTimes[0]->timestamp, $endTimes[1]->timestamp);

        // Fusionner les jours (union)
        $mergedDays = array_values(array_unique(array_merge($availability->days, $newData['days'])));

        // Trier les jours selon l'ordre de la semaine
        $dayOrder = DaysOfWeek::values();
        usort($mergedDays, function ($a, $b) use ($dayOrder): int {
            return array_search($a, $dayOrder, true) <=> array_search($b, $dayOrder, true);
        });

        // Fusionner les dates de validité
        $existingValidityStart = $availability->validity_start ? Carbon::parse($availability->validity_start) : null;
        $existingValidityEnd = $availability->validity_end ? Carbon::parse($availability->validity_end) : null;
        $newValidityStart = isset($newData['validity_start']) ? Carbon::parse($newData['validity_start']) : null;
        $newValidityEnd = isset($newData['validity_end']) ? Carbon::parse($newData['validity_end']) : null;

        $startDates = array_filter([$existingValidityStart, $newValidityStart]);
        $endDates = array_filter([$existingValidityEnd, $newValidityEnd]);

        $mergedValidityStart = $startDates === []
            ? null
            : Carbon::createFromTimestamp(min(array_map(fn($date) => $date->timestamp, $startDates)));

        $mergedValidityEnd = $endDates === []
            ? null
            : Carbon::createFromTimestamp(max(array_map(fn($date) => $date->timestamp, $endDates)));

        return [
            'daily_start' => Carbon::createFromTimestamp($mergedStartTime)->format('H:i:s'),
            'daily_end' => Carbon::createFromTimestamp($mergedEndTime)->format('H:i:s'),
            'days' => $mergedDays,
            'validity_start' => $mergedValidityStart?->format('Y-m-d'),
            'validity_end' => $mergedValidityEnd?->format('Y-m-d'),
        ];
    }

    /**
     * Update existing availability with merged data.
     * @param array<string, list<mixed>|string|null> $mergedData
     */
    // ==== src/Services/AvailabilityMergeService.php ===
    // Ajoutez cette méthode à la classe AvailabilityMergeService

    private function updateExistingAvailability(Availability $availability, array $mergedData): void
    {
        // Wrapper la mise à jour dans le contexte de mutation autorisé
        RosterMutationContext::allow(function () use ($availability, $mergedData): void {
            DB::transaction(function () use ($availability, $mergedData): void {
                // Utiliser le repository pour la mise à jour
                $this->availabilityRepository->update($availability->id, [
                    'daily_start' => $mergedData['daily_start'],
                    'daily_end' => $mergedData['daily_end'],
                    'days' => $mergedData['days'],
                    'validity_start' => $mergedData['validity_start'],
                    'validity_end' => $mergedData['validity_end'],
                ]);
            });
        });
    }

    /**
     * Find adjacent availabilities for a new availability.
     *
     * @param array<string, mixed> $newData New availability data
     * @param mixed $schedulable The schedulable model
     * @return array<Availability> Array of adjacent availabilities
     */
    public function findAdjacentAvailabilities(array $newData, mixed $schedulable): array
    {
        $availabilities = $this->availabilityRepository->findForSchedulable(
            $schedulable,
            $newData['type'] ?? null
        );

        $adjacent = [];

        foreach ($availabilities as $availability) {
            if ($this->areAvailabilitiesAdjacent($availability, $newData)) {
                $adjacent[] = $availability;
            }
        }

        return $adjacent;
    }
}
