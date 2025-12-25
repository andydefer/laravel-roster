<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\DTOs\ScheduleData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\Core\AbstractAvailabilityValidatingService;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing schedules within the roster system.
 */
class ScheduleService extends AbstractAvailabilityValidatingService
{

    /**
     * {@inheritDoc}
     */
    protected function createDTOFromArray(array $data, OperationType $operationType): ScheduleData
    {
        return ScheduleData::fromArray($data);
    }

    /**
     * {@inheritDoc}
     */
    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::SCHEDULE;
    }

    /**
     * Create a new schedule with explicit availability.
     *
     * @param Availability $availability The availability to link to
     * @param array $data Entity data
     * @return Schedule Created entity
     */
    public function create(array $data): Schedule
    {
        $this->requireContext();
        $this->data = array_merge($data, [
            'availability_id' => $this->owner->id,
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable)
        ]);


        // Convert to DTO and validate with new system
        $dto = $this->createDTOFromArray($this->data, OperationType::CREATE);

        // Ajouter les infos schedulable au DTO AVANT validation
        $dto = $dto->withSchedulableInfo(
            $this->schedulable->id,
            get_class($this->schedulable)
        );

        $this->validate($dto->toArray(), OperationType::CREATE);

        // Mettre à jour les données avec le DTO complet
        $this->data = $dto->toArray();

        // Create entity
        $schedule = $this->executeCreate();

        // Clear cache
        $this->clearEntityCache($schedule->id);

        return $schedule;
    }

    /**
     * Update an existing schedule.
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $this->requireContext();
        $this->data = $data;

        // Récupérer l'entité existante AVANT validation
        $existingEntity = $this->find($id);

        if (!$existingEntity instanceof Schedule) {
            throw ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::SCHEDULE->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::SCHEDULE
            );
        }

        // Ajouter l'ID et l'entité pour la validation
        $data['id'] = $id;

        // Assurez-vous que availability_id est présent dans les données
        if (!isset($data['availability_id']) && $existingEntity->availability_id) {
            $data['availability_id'] = $existingEntity->availability_id;
        }

        // Créer le DTO avec les données mises à jour
        $scheduleData = $this->createDTOFromArray($data, OperationType::UPDATE);

        // Valider avec l'entité courante pour exclusion
        $this->validate($scheduleData->toArray(), OperationType::UPDATE, $id, $existingEntity);

        // Mettre à jour les données avec le DTO complet
        $this->data = $scheduleData->toArray();

        // Mettre à jour l'entité
        $result = $this->executeUpdate($id);

        // Nettoyer le cache si nécessaire
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * Delete a schedule.
     */
    public function delete(int $id): bool
    {
        $this->requireContext();
        $entity = $this->find($id);
        if (!$entity instanceof Schedule) {
            throw ValidationFailedException::fromViolations(
                [
                    'id' => sprintf(
                        '%s with given ID does not exist',
                        EntityType::SCHEDULE->displayName()
                    ),
                ],
                OperationType::UPDATE,
                EntityType::SCHEDULE
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
        $this->validate($deleteData, OperationType::DELETE, $id, $entity);

        // Supprimer l'entité
        $result = $this->executeDelete($id);

        // Nettoyer le cache si nécessaire
        if ($result) {
            $this->clearEntityCache($id);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function executeCreate(): Schedule
    {
        return $this->scheduleRepository->create($this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->scheduleRepository->update($id, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeDelete(int $id): bool
    {
        return $this->scheduleRepository->delete($id);
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
    public function find(int $id): ?Schedule
    {

        return Schedule::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    public function all(): Collection
    {
        return $this->scheduleRepository->all($this->schedulable, $this->owner, $this->filters);
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
        return $this->scheduleRepository->buildQueryWithFilters(
            $this->schedulable,
            $this->filters
        );
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
     * Find the next available time slot from now.
     */
    public function findNextSlot(
        int $durationMinutes,
        ?string $type = null,
        bool $returnStartOnly = false,
        ?Carbon $startFrom = null,
        ?Carbon $endBefore = null
    ): array|Carbon|null {
        $startFrom = $startFrom ?? Carbon::now();
        $endBefore = $endBefore ?? $startFrom->copy()->addDays(config('roster.durations.max_search_period_days', 30));

        // Chercher dans une fenêtre de temps progressive
        $searchDate = $startFrom->copy()->startOfDay();

        while ($searchDate->lt($endBefore)) {
            $result = $this->findAvailableSlotInDay(
                $searchDate,
                $durationMinutes,
                $type,
                // Pour le premier jour, passer l'heure de début spécifique
                $searchDate->isSameDay($startFrom) ? $startFrom : null
            );

            if ($result !== null) {
                return $returnStartOnly ? $result['start'] : $result;
            }

            // Passer au jour suivant
            $searchDate->addDay()->startOfDay();
        }

        return null;
    }

    /**
     * Check if time slot is available.
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $availability = $this->availabilityRepository->findForTimeSlotWithConflictInfo(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        return $availability instanceof Availability
            && !$availability->has_overlapping_schedules
            && !$availability->has_overlapping_impediments;
    }

    /**
     * Find available slots in a specific day.
     */
    private function findAvailableSlotInDay(
        Carbon $day,
        int $durationMinutes,
        ?string $type = null,
        ?Carbon $searchStart = null
    ): ?array {
        // Récupérer les disponibilités pour ce jour
        $availabilities = $this->availabilityRepository->getForDate(
            $this->schedulable,
            $day,
            $type
        );

        if ($availabilities->isEmpty()) {
            return null;
        }

        // Pour chaque disponibilité, chercher un créneau disponible
        foreach ($availabilities as $availability) {
            $slot = $this->findSlotInAvailability(
                $availability,
                $day,
                $durationMinutes,
                $searchStart
            );

            if ($slot !== null) {
                return $slot;
            }
        }

        return null;
    }

    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator {
        return $this->buildQueryWithFilters()
            ->paginate(
                perPage: $perPage,
                columns: $columns,
                pageName: $pageName,
                page: $page
            );
    }

    /**
     * Find available slot within a specific availability.
     */
    private function findSlotInAvailability(
        Availability $availability,
        Carbon $day,
        int $durationMinutes,
        ?Carbon $searchStart = null
    ): ?array {
        // Vérifier que daily_start et daily_end ne sont pas null
        if (!$availability->daily_start || !$availability->daily_end) {
            return null;
        }

        // Vérifier que l'accessibilité est disponible ce jour
        if (!$availability->isActiveOnDate($day)) {
            return null;
        }

        // Convertir le jour en datetime avec les heures de disponibilité
        $availabilityStart = $day->copy()->setTimeFromTimeString($availability->daily_start->format('H:i:s'));
        $availabilityEnd = $day->copy()->setTimeFromTimeString($availability->daily_end->format('H:i:s'));

        // Déterminer le point de départ de la recherche
        $slotStart = $availabilityStart->copy();

        // Si un searchStart est spécifié et qu'il est dans la même journée
        if ($searchStart instanceof Carbon && $searchStart->isSameDay($day)) {
            // Si searchStart est avant availabilityStart, commencer à availabilityStart
            if ($searchStart->lt($availabilityStart)) {
                $slotStart = $availabilityStart->copy();
            }
            // Si searchStart est après availabilityStart mais avant availabilityEnd, commencer à searchStart
            elseif ($searchStart->lt($availabilityEnd)) {
                $slotStart = $searchStart->copy();
            }
            // Si searchStart est après availabilityEnd, pas de créneau possible ce jour
            else {
                return null;
            }
        }

        // Arrondir le slotStart à l'intervalle le plus proche
        $slotInterval = config('roster.durations.default_slot_interval_minutes', 15);
        if ($slotStart->minute > 0 || $slotStart->second > 0) {
            $minutes = $slotStart->minute;
            $roundedMinutes = ceil($minutes / $slotInterval) * $slotInterval;
            $slotStart->setMinute((int)$roundedMinutes)->setSecond(0);
        }

        // Vérifier que slotStart + durée ne dépasse pas availabilityEnd
        if ($slotStart->copy()->addMinutes($durationMinutes)->gt($availabilityEnd)) {
            return null;
        }

        // Chercher un créneau par pas de l'intervalle
        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($availabilityEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

            // Vérifier que le créneau est disponible
            if ($this->isTimeSlotAvailable($slotStart, $slotEnd, $availability->type)) {
                return [
                    'start' => $slotStart->copy(),
                    'end' => $slotEnd->copy(),
                    'availability' => $availability,
                    'duration_minutes' => $durationMinutes,
                ];
            }

            // Avancer de l'intervalle
            $slotStart->addMinutes($slotInterval);
        }

        return null;
    }

    /**
     * Get all available time slots in a date range.
     */
    public function findAvailableSlots(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): Collection {
        $availableSlots = collect();
        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate)) {
            $slot = $this->findAvailableSlotInDay($currentDate, $durationMinutes, $type);

            if ($slot !== null) {
                $availableSlots->push($slot);
            }

            $currentDate->addDay();
        }

        return $availableSlots;
    }

    /**
     * Check if a time period is completely available.
     */
    public function isPeriodAvailable(
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): bool {
        // Vérifier s'il y a une disponibilité continue
        $availability = $this->availabilityRepository->getAvailabilityForTimeSlot(
            $this->schedulable,
            $start,
            $end,
            $type
        );

        if (!$availability instanceof Availability) {
            return false;
        }

        // Vérifier que la période est dans les limites de l'accessibilité
        if (!$availability->isAvailableForSchedule($start, $end)) {
            return false;
        }

        // Vérifier les conflits
        $hasScheduleConflict = $this->scheduleRepository->hasOverlappingSchedule(
            $availability->id,
            $start,
            $end
        );

        $hasImpedimentConflict = $this->impedimentRepository->hasOverlappingImpediments(
            $availability->id,
            $start,
            $end
        );

        return !$hasScheduleConflict && !$hasImpedimentConflict;
    }
}
