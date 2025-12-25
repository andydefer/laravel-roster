<?php

declare(strict_types=1);

namespace Roster\Services;

use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\RosterDataInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\DTOs\AvailabilityData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Exceptions\MergeConflictException;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractValidatingService;
use Roster\Validation\Exceptions\ValidationFailedException;

class AvailabilityService extends AbstractValidatingService
{

    protected ?Availability $pendingDeletion = null;

    public function __construct(
        ValidatorInterface $validator,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository,
        ScheduleRepositoryInterface $scheduleRepository,
    ) {
        parent::__construct($validator, $availabilityRepository, $impedimentRepository, $scheduleRepository);
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
     * Create a new availability with safe merging.
     *
     * @param array $data The data for creation
     * @return Availability The created entity
     */
    public function create(array $data = [])
    {
        $this->requireContext();

        // Ajouter les informations schedulable
        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

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

        // Créer une nouvelle entité
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

        if (! $entity instanceof Availability) {
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
        if (array_key_exists('days', $data)) {
            $dto = $dto->withDaysInfo($data['days']);
        } else {
            $dto = $dto->withAutoFilteredDaysForUpdate(
                $entity->days,
                $entity->validity_start,
                $entity->validity_end
            );
        }

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

    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->availabilityRepository
            ->paginate(
                perPage: $perPage,
                columns: $columns,
                pageName: $pageName,
                page: $page
            );
    }

    /**
     * Delete an availability.
     */
    public function delete(int $id): bool
    {
        $this->requireContext();
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

    protected function executeCreate(): Availability
    {
        // Si on a un flag de suppression en attente (cas de fusion)
        if ($this->pendingDeletion instanceof Availability) {
            DB::transaction(function (): void {
                $this->pendingDeletion->delete();
                $this->pendingDeletion = null;
            });
        }

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

    public function all(): Collection
    {
        return $this->availabilityRepository->all(
            schedulable: $this->schedulable,
            filters: $this->filters
        );
    }
}
