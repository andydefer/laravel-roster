<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Exceptions\OverlappingScheduleException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\Core\AbstractAvailabilityDependentService;

/**
 * Service for managing schedules within the roster system.
 */
class ScheduleService extends AbstractAvailabilityDependentService
{
    protected ValidationServiceInterface $validationService;

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    protected ScheduleRepositoryInterface $scheduleRepository;

    protected SlotFinderInterface $slotFinder;

    public function __construct(
        ValidationServiceInterface $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        ImpedimentRepositoryInterface $impedimentRepository,
        ScheduleRepositoryInterface $scheduleRepository,
        SlotFinderInterface $slotFinder
    ) {
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->impedimentRepository = $impedimentRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->slotFinder = $slotFinder;
    }

    // ============================================
    // Implémentation des méthodes abstraites
    // ============================================

    protected function getEntityClass(): string
    {
        return Schedule::class;
    }

    protected function getAvailabilityRepository(): AvailabilityRepositoryInterface
    {
        return $this->availabilityRepository;
    }

    protected function getScheduleRepository(): ScheduleRepositoryInterface
    {
        return $this->scheduleRepository;
    }

    protected function getImpedimentRepository(): ImpedimentRepositoryInterface
    {
        return $this->impedimentRepository;
    }

    /**
     * {@inheritDoc}
     */
    protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void {
        if (isset($this->data['start_datetime'], $this->data['end_datetime'])) {
            $start = Carbon::parse($this->data['start_datetime']);
            $end = Carbon::parse($this->data['end_datetime']);

            if ($start->diffInMinutes($end) < $minScheduleMinutes) {
                $this->throwMinimumDurationException($minScheduleMinutes);
            }
        }
    }

    /**
     * Find the next available time slot from now.
     *
     * @param int $durationMinutes Required duration in minutes
     * @param string|null $type Optional availability type filter
     * @param bool $returnStartOnly Return only the start time if true
     * @return array|Carbon|null Array with 'start' and 'end' keys, start time only, or null if no slot found
     *
     * @throws ValidationException When duration is invalid
     */
    public function findNextAvailableSlot(
        int $durationMinutes,
        ?string $type = null,
        bool $returnStartOnly = false
    ): array|Carbon|null {
        $this->validateSchedulable();

        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        return $this->slotFinder->findNextSlot(
            model: $this->schedulable,
            durationMinutes: $durationMinutes,
            type: $type,
            returnStartOnly: $returnStartOnly
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        // Not applicable for schedules
    }

    /**
     * {@inheritDoc}
     */
    protected function getValidationService(): ValidationServiceInterface
    {
        return $this->validationService;
    }

    /**
     * {@inheritDoc}
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentEntity = $this->find($id);

        if (!$this->currentEntity instanceof Schedule) {
            $this->throwNotFoundException();
        }

        if ($this->data === []) {
            return;
        }

        if (isset($this->data['start_datetime']) || isset($this->data['end_datetime'])) {
            $validationData = array_merge(
                [
                    'start_datetime' => $this->currentEntity->start_datetime,
                    'end_datetime' => $this->currentEntity->end_datetime,
                ],
                $this->data
            );
            $this->validationService->parseAndValidateDateTimeRange($validationData);
        }

        if (isset($this->data['start_datetime'])) {
            $this->validateUpdateWithDateChanges();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function checkOverlapsForUpdate(int $availabilityId, Carbon $start, Carbon $end, int $exceptId): void
    {
        if ($this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end, $exceptId)) {
            $this->throwOverlapException();
        }

        if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end)) {
            throw ValidationException::withMessage('Schedule overlaps with an impediment');
        }
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

    // ============================================
    // Hooks de cycle de vie
    // ============================================

    /**
     * {@inheritDoc}
     */
    protected function beforeCreate(mixed ...$args): void
    {
        $this->validateScheduleData();

        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        if ($this->scheduleRepository->hasOverlappingSchedule($this->data['availability_id'], $start, $end)) {
            throw new OverlappingScheduleException(
                TimeSlotOverlapType::SCHEDULE_OVERLAP,
                [
                    'availability_id' => $this->data['availability_id'],
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        if ($this->impedimentRepository->hasOverlappingImpediments($this->data['availability_id'], $start, $end)) {
            throw new ScheduleImpedimentOverlapException(
                TimeSlotOverlapType::SCHEDULE_IMPEDIMENT_CONFLICT,
                [
                    'availability_id' => $this->data['availability_id'],
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        $this->processMetadata();
    }

    /**
     * {@inheritDoc}
     */
    protected function beforeUpdate(int $id): void
    {
        $this->processMetadata();
    }

    // ============================================
    // Méthodes spécifiques
    // ============================================

    /**
     * Build query with filters
     */
    protected function buildQueryWithFilters(): Builder
    {
        return $this->scheduleRepository->buildQueryWithFilters(
            $this->schedulable->id,
            get_class($this->schedulable),
            $this->filters
        );
    }

    /**
     * Validate schedule data
     */
    protected function validateScheduleData(): void
    {
        ['start' => $start] = $this->validationService->parseAndValidateDateTimeRange($this->data);
        $this->validationService->validateFutureDate($start);
    }

    /**
     * Check if time slot is available
     */
    public function isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

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
     * Find first available period
     */
    public function findFirstAvailablePeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?string $type = null
    ): ?array {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($startDate, $endDate, 'date');

        if ($durationMinutes <= 0) {
            throw new ValidationException(
                ValidationType::MINIMUM_DURATION_NOT_MET,
                ['minimum_minutes' => 1, 'provided_minutes' => $durationMinutes]
            );
        }

        return $this->slotFinder->findFirstAvailablePeriod(
            $this->schedulable,
            $startDate,
            $endDate,
            $durationMinutes,
            $type
        );
    }
}
