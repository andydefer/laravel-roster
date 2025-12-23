<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\DTOs\ImpedimentData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Services\Core\AbstractAvailabilityValidatingService;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing impediments (blocked time slots) for schedulable models.
 */
class ImpedimentService extends AbstractAvailabilityValidatingService
{
    private AvailabilityRepositoryInterface $availabilityRepository;

    private ImpedimentRepositoryInterface $impedimentRepository;

    private ScheduleRepositoryInterface $scheduleRepository;

    private SlotFinderInterface $slotFinder;

    public function __construct(
        ValidatorInterface $validator,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository,
        ScheduleRepositoryInterface $scheduleRepository,
        SlotFinderInterface $slotFinder
    ) {
        parent::__construct($validator);
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->slotFinder = $slotFinder;
    }

    /**
     * {@inheritDoc}
     */
    protected function createDTOFromArray(array $data, OperationType $operationType): ImpedimentData
    {
        return ImpedimentData::fromArray($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::IMPEDIMENT;
    }

    /**
     * Create a new impediment with explicit availability.
     *
     * @param Availability $availability The availability to link to
     * @param array $data Entity data
     * @return Impediment Created entity
     */
    public function create(Availability $availability, array $data): Impediment
    {
        $this->data = array_merge($data, [
            'availability_id' => $availability->id,
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);

        // Convert to DTO and validate with new system
        $dto = $this->createDTOFromArray($this->data, OperationType::CREATE);

        // Ajouter les infos schedulable au DTO
        $dto = $dto->withSchedulableInfo(
            $this->schedulable->id,
            get_class($this->schedulable)
        );

        // Valider le DTO avec les infos schedulable
        $this->validate($dto->toArray(), OperationType::CREATE);

        // Mettre à jour les données avec le DTO complet
        $this->data = $dto->toArray();

        // Create entity using repository
        $impediment = $this->impedimentRepository->create($this->data);

        // Clear cache
        $this->clearEntityCache($impediment->id);

        return $impediment;
    }

    /**
     * Update an existing impediment.
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $this->data = $data;

        // Trouver l'impediment existant
        $existingImpediment = $this->find($id);
        if (!$existingImpediment instanceof Impediment) {
            throw ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::IMPEDIMENT->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::IMPEDIMENT
            );
        }

        // Ajouter l'ID pour la validation
        $data['id'] = $id;

        // Créer le DTO initial
        $impedimentData = $this->createDTOFromArray($data, OperationType::UPDATE);


        // Valider le DTO avec les infos schedulable
        $this->validate($impedimentData->toArray(), OperationType::UPDATE, $id);

        // Mettre à jour les données avec le DTO complet
        $this->data = $impedimentData->toArray();

        // Mettre à jour l'entité via le repository
        $result = $this->impedimentRepository->update($id, $this->data);

        // Nettoyer le cache si nécessaire
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * Delete an impediment.
     */
    public function delete(int $id): bool
    {
        $entity = $this->find($id);
        if (!$entity instanceof Impediment) {
            throw ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::IMPEDIMENT->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::IMPEDIMENT
            );
        }
        // Préparer les données de validation avec les infos schedulable
        $deleteData = [
            'id' => $id,
            'schedulable_id' => $entity->schedulable_id,
            'schedulable_type' => $entity->schedulable_type,
            'availability_id' => $entity->availability_id,
        ];

        // Valider la suppression
        $this->validate($deleteData, OperationType::DELETE, $id);

        // Supprimer l'entité via le repository
        $result = $this->impedimentRepository->delete($id);

        // Nettoyer le cache si nécessaire
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function executeCreate(): Impediment
    {
        // Cette méthode n'est plus utilisée, car on utilise directement le repository
        // Gardée pour compatibilité avec la classe parente abstraite
        return $this->impedimentRepository->create($this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeUpdate(int $id): bool
    {
        // Cette méthode n'est plus utilisée, car on utilise directement le repository
        // Gardée pour compatibilité avec la classe parente abstraite
        return $this->impedimentRepository->update($id, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeDelete(int $id): bool
    {
        // Cette méthode n'est plus utilisée, car on utilise directement le repository
        // Gardée pour compatibilité avec la classe parente abstraite
        return $this->impedimentRepository->delete($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function clearEntityCache(int $entityId): void
    {
        // Implémentation du cache si nécessaire
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Impediment
    {
        return Impediment::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function get(): Collection
    {
        return $this->buildQueryWithFilters()->get();
    }

    /**
     * {@inheritDoc}
     */
    protected function buildQueryWithFilters(): Builder
    {
        $query = Impediment::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable));

        $this->applyDateFilters($query);
        $this->applyTypeFilter($query);
        $this->applyReasonFilter($query);

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getAvailabilityRepository(): AvailabilityRepositoryInterface
    {
        return $this->availabilityRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function getScheduleRepository(): ScheduleRepositoryInterface
    {
        return $this->scheduleRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function getImpedimentRepository(): ImpedimentRepositoryInterface
    {
        return $this->impedimentRepository;
    }

    /**
     * Check if a time slot is blocked by an impediment.
     */
    public function isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $this->impedimentRepository->hasOverlappingImpediments($availability->id, $start, $end);
    }

    /**
     * Get available time slots considering impediments.
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return collect();
        }

        $impediments = $this->impedimentRepository->findForTimeSlot($availability->id, $start, $end);

        return $this->slotFinder->getAvailableSlotsFromImpediments($start, $end, $impediments);
    }

    /**
     * Check if creating an impediment would overlap with any schedule.
     */
    public function wouldOverlapWithSchedule(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        return $this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end, $exceptImpedimentId);
    }

    /**
     * Check if creating an impediment would overlap with any other impediment.
     */
    public function wouldOverlapWithOtherImpediment(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        return $this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $exceptImpedimentId);
    }
}
