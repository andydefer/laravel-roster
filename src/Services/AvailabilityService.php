<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\DTOs\AvailabilityData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractValidatingService;
use Roster\Validation\Exceptions\ValidationFailedException;

class AvailabilityService extends AbstractValidatingService
{
    private AvailabilityRepositoryInterface $availabilityRepository;

    public function __construct(
        ValidatorInterface $validator,
        AvailabilityRepositoryInterface $availabilityRepository,
    ) {
        parent::__construct($validator);
        $this->availabilityRepository = $availabilityRepository;
    }

    protected function createDTOFromArray(array $data, OperationType $operationType): AvailabilityData
    {
        return AvailabilityData::fromArray($data);
    }

    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::AVAILABILITY;
    }

    /**
     * Create a new availability.
     *
     * @param array $data The data for creation
     * @return Availability The created entity
     */
    public function create(array $data): Availability
    {
        $this->data = $data;

        // Convertir en DTO
        $dto = $this->createDTOFromArray($data, OperationType::CREATE);

        // Ajouter les jours ajustés automatiquement si non fournis
        $adjustedDays = $dto->getAutoAdjustedDays();
        $dto = $dto->withDaysInfo($adjustedDays);

        // Valider le DTO
        $this->validate($dto->toArray(), OperationType::CREATE);

        // Ajouter les infos schedulable au DTO
        $dto = $dto->withSchedulableInfo(
            $this->schedulable->id,
            get_class($this->schedulable)
        );

        // Mettre à jour les données avec le DTO complet
        $this->data = $dto->toArray();

        // Fusionner les availabilities adjacentes
        $mergedData = $this->mergeWithAdjacentAvailabilities($this->data);
        if ($mergedData !== $this->data) {
            $this->data = $mergedData;
        }

        // Créer l'entité
        $availability = $this->executeCreate();

        // Nettoyer le cache si nécessaire
        $this->clearEntityCache($availability->id);

        return $availability;
    }

    /**
     * Update an existing availability.
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $entity = $this->find($id);

        if (!$entity instanceof Availability) {
            throw ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::AVAILABILITY->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::AVAILABILITY
            );
        }

        // Conserver les données originales pour l'update
        $this->data = $data;

        // Ajouter l'ID pour la validation
        $data['id'] = $id;

        // Créer le DTO pour l'update
        $dto = $this->createDTOFromArray($data, OperationType::UPDATE);

        // Gestion des jours
        $dto = array_key_exists('days', $data)
            ? $dto->withDaysInfo($data['days'])
            : $dto->withAutoFilteredDaysForUpdate(
                $entity->days,
                $entity->validity_start,
                $entity->validity_end
            );

        // Validation (les jours sont déjà cohérents à ce stade)
        $this->validate($dto->toArray(), OperationType::UPDATE, $id);

        // Injecter les jours filtrés uniquement s'ils existent
        if ($dto->days !== null && $dto->days !== []) {
            $this->data['days'] = $dto->days;
        }

        // Exécuter la mise à jour
        $updated = $this->executeUpdate($id);

        // Nettoyer le cache si nécessaire
        if ($updated) {
            $this->clearEntityCache($id);
        }

        return $updated;
    }

    /**
     * Delete an availability.
     */
    public function delete(int $id): bool
    {
        $entity = $this->find($id);
        if (!$entity instanceof Availability) {
            throw ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::AVAILABILITY->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::AVAILABILITY
            );
        }

        // Valider la suppression si nécessaire
        $this->validate(['id' => $id], OperationType::DELETE, $id);

        // Supprimer l'entité
        $result = $this->executeDelete($id);

        // Nettoyer le cache si nécessaire
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    // Les autres méthodes restent inchangées...
    protected function executeCreate(): Availability
    {
        return $this->availabilityRepository->create($this->data);
    }

    protected function executeUpdate(int $id): bool
    {
        return $this->availabilityRepository->update($id, $this->data);
    }

    protected function executeDelete(int $id): bool
    {
        return $this->availabilityRepository->delete($id);
    }

    protected function clearEntityCache(int $entityId): void
    {
        // Implémentation du cache si nécessaire
    }

    public function find(int $id): ?Availability
    {
        return $this->availabilityRepository->find($id);
    }

    public function get(): Collection
    {
        return $this->buildQueryWithFilters()->get();
    }

    protected function buildQueryWithFilters(): Builder
    {
        return $this->availabilityRepository->buildQueryWithFilters($this->schedulable, $this->filters);
    }

    /**
     * Merge new availability data with adjacent existing ones.
     *
     * This method identifies availabilities that are adjacent to the new data,
     * merges them when possible, and removes the merged entities to avoid duplicates.
     *
     * @param array<string, mixed> $data The new availability data to merge
     * @return array<string, mixed> The merged availability data
     */
    private function mergeWithAdjacentAvailabilities(array $data): array
    {
        if (!$this->schedulable instanceof Model) {
            return $data;
        }

        // Récupérer les availabilities existantes pour ce schedulable
        $existingAvailabilities = $this->availabilityRepository->findForSchedulable(
            $this->schedulable,
            $data['type'] ?? null
        );

        foreach ($existingAvailabilities as $existingAvailability) {
            if ($this->areAvailabilitiesAdjacent($existingAvailability, $data)) {
                // Fusionner les données
                $data = $this->mergeAdjacentAvailabilityData($existingAvailability, $data);

                // Supprimer l'ancienne availability
                $existingAvailability->delete();
            }
        }

        return $data;
    }

    /**
     * Check if two availabilities are adjacent.
     *
     * Two availabilities are adjacent if they share common properties
     * and their time ranges touch exactly.
     *
     * @param Availability $availability Existing availability
     * @param array<string, mixed> $newData New availability data
     * @return bool True if availabilities are adjacent
     */
    private function areAvailabilitiesAdjacent(Availability $availability, array $newData): bool
    {
        // Vérifier le même schedulable
        if (
            $availability->schedulable_id !== $this->schedulable->id ||
            $availability->schedulable_type !== get_class($this->schedulable)
        ) {
            return false;
        }

        // Vérifier le même type
        if ($availability->type !== ($newData['type'] ?? null)) {
            return false;
        }

        // Vérifier les jours communs
        $commonDays = array_intersect($availability->days, $newData['days'] ?? []);
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
     *
     * @param Carbon $firstStart First time range start
     * @param Carbon $firstEnd First time range end
     * @param Carbon $secondStart Second time range start
     * @param Carbon $secondEnd Second time range end
     * @return bool True if time ranges touch
     */
    private function timeRangesTouch(
        Carbon $firstStart,
        Carbon $firstEnd,
        Carbon $secondStart,
        Carbon $secondEnd
    ): bool {
        if ($firstEnd->eq($secondStart)) {
            return true;
        }

        return $secondEnd->eq($firstStart);
    }

    /**
     * Merge two adjacent availability data arrays.
     *
     * @param Availability $availability Existing availability
     * @param array<string, mixed> $newData New availability data
     * @return array<string, mixed> Merged availability data
     */
    private function mergeAdjacentAvailabilityData(Availability $availability, array $newData): array
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

        // Fusionner les dates
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
            'type' => $availability->type,
            'daily_start' => Carbon::createFromTimestamp($mergedStartTime)->format('H:i:s'),
            'daily_end' => Carbon::createFromTimestamp($mergedEndTime)->format('H:i:s'),
            'days' => $mergedDays,
            'validity_start' => $mergedValidityStart?->format('Y-m-d'),
            'validity_end' => $mergedValidityEnd?->format('Y-m-d'),
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
        ];
    }
}
