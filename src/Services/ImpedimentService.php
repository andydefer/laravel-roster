<?php

declare(strict_types=1);

namespace Roster\Services;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Enums\TimeSlotOverlapType;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Exceptions\OverlappingImpedimentException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Services\Core\AbstractAvailabilityDependentService;
use Roster\Services\Core\Components\Cachable;
use Roster\Services\Core\Components\ExceptionHandler;
use Roster\Traits\FilterableTrait;

/**
 * Service for managing impediments (blocked time slots) for schedulable models.
 *
 * Handles creation, validation, and querying of impediments while ensuring
 * they respect availability constraints and don't overlap with existing schedules.
 */
class ImpedimentService extends AbstractAvailabilityDependentService
{
    use FilterableTrait;
    use ExceptionHandler;
    use Cachable;

    protected ValidationServiceInterface $validationService;

    protected AvailabilityRepositoryInterface $availabilityRepository;

    protected ImpedimentRepositoryInterface $impedimentRepository;

    protected ScheduleRepositoryInterface $scheduleRepository;

    protected SlotFinderInterface $slotFinder;

    /**
     * @var Impediment|null Current impediment being processed
     */
    protected ?Impediment $currentImpediment = null;

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

            if ($start->diffInMinutes($end) < $minImpedimentMinutes) {
                $this->throwMinimumDurationException($minImpedimentMinutes);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        if (isset($this->data['start_datetime'], $this->data['end_datetime'])) {
            $start = Carbon::parse($this->data['start_datetime']);
            $end = Carbon::parse($this->data['end_datetime']);

            if ($start->diffInDays($end) > $maxDays) {
                throw ValidationException::withMessage(
                    sprintf('Impediment duration cannot exceed %d days', $maxDays)
                );
            }
        }
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
    protected function beforeCreate(mixed ...$args): void
    {
        // 1️⃣ Validation
        $this->validateImpedimentData();

        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        $availabilityId = $this->data['availability_id'] ?? null;

        if (!$availabilityId) {
            throw new ValidationException(ValidationType::INVALID_AVAILABILITY);
        }

        $availability = $this->availabilityRepository->find($availabilityId);

        if (!$availability instanceof Availability) {
            throw new ValidationException(ValidationType::INVALID_AVAILABILITY);
        }

        $this->validateImpedimentAgainstAvailability($availability, $start, $end);

        if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end)) {
            throw new OverlappingImpedimentException(
                TimeSlotOverlapType::IMPEDIMENT_OVERLAP,
                [
                    'availability_id' => $availabilityId,
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        if ($this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end)) {
            throw new ScheduleImpedimentOverlapException(
                TimeSlotOverlapType::SCHEDULE_IMPEDIMENT_CONFLICT,
                [
                    'availability_id' => $availabilityId,
                    'start' => $start->format('Y-m-d H:i:s'),
                    'end' => $end->format('Y-m-d H:i:s'),
                ]
            );
        }

        // 2️⃣ Traitement / préparation des données
        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);

        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function executeCreate(): Impediment
    {
        return Impediment::create($this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentImpediment = $this->find($id);

        if (!$this->currentImpediment instanceof Impediment) {
            $this->throwNotFoundException();
        }

        if ($this->data === []) {
            return;
        }

        $availabilityId = $this->currentImpediment->availability_id;

        if (isset($this->data['start_datetime']) || isset($this->data['end_datetime'])) {
            $validationData = array_merge(
                [
                    'start_datetime' => $this->currentImpediment->start_datetime,
                    'end_datetime' => $this->currentImpediment->end_datetime,
                ],
                $this->data
            );
            $this->validationService->parseAndValidateDateTimeRange($validationData);
        }

        if (isset($this->data['start_datetime'])) {
            $newAvailability = $this->findMatchingAvailability();

            if (!$newAvailability instanceof Availability) {
                throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
            }

            $availabilityId = $newAvailability->id;

            ['start' => $start, 'end' => $end] = $this->validationService
                ->parseAndValidateDateTimeRange(array_merge([
                    'start_datetime' => $this->currentImpediment->start_datetime,
                    'end_datetime' => $this->currentImpediment->end_datetime,
                ], $this->data));

            $this->validateImpedimentAgainstAvailability($newAvailability, $start, $end);

            if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $id)) {
                $this->throwOverlapException();
            }

            if ($this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end)) {
                throw ValidationException::withMessage(
                    'Cannot create impediment that overlaps with existing schedule'
                );
            }

            if ($newAvailability->id !== $this->currentImpediment->availability_id) {
                $this->data['availability_id'] = $newAvailability->id;
            }
        } else {
            $updateData = array_merge([
                'start_datetime' => $this->currentImpediment->start_datetime,
                'end_datetime' => $this->currentImpediment->end_datetime,
            ], $this->data);

            ['start' => $start, 'end' => $end] = $this->validationService
                ->parseAndValidateDateTimeRange($updateData);

            $currentAvailability = $this->currentImpediment->availability;

            if ($currentAvailability) {
                $this->validateImpedimentAgainstAvailability($currentAvailability, $start, $end);
            }

            if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $id)) {
                $this->throwOverlapException();
            }

            if ($this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end)) {
                throw ValidationException::withMessage(
                    'Cannot update impediment to overlap with existing schedule'
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function beforeUpdate(int $id): void
    {
        // Additional processing if needed
        $this->processMetadata();
    }

    /**
     * {@inheritDoc}
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->currentImpediment->update($this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeDelete(int $id): bool
    {
        return $this->currentImpediment->delete();
    }

    /**
     * Create a new impediment with explicit availability.
     *
     * @param Availability|array<string, mixed> $availabilityOrData Availability instance or data array
     * @param array<string, mixed>|null $data Data array if first param is Availability
     * @return Impediment Created impediment
     *
     * @throws ValidationException When validation fails
     * @throws BadMethodCallException When deprecated signature is used
     * @throws InvalidArgumentException When invalid arguments are provided
     */
    public function create($availabilityOrData, ?array $data = null): Impediment
    {
        if ($availabilityOrData instanceof Availability && $data !== null) {
            return $this->createWithAvailability($availabilityOrData, $data);
        }

        if (is_array($availabilityOrData) && $data === null) {
            throw new BadMethodCallException(
                'Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.'
            );
        }

        throw new InvalidArgumentException('Invalid arguments for create method');
    }


    /**
     * Validate that the impediment time slot matches the availability constraints.
     *
     * @param Availability $availability The availability to validate against
     * @param Carbon $start Impediment start datetime
     * @param Carbon $end Impediment end datetime
     *
     * @throws ValidationException When impediment doesn't match availability constraints
     */
    private function validateImpedimentAgainstAvailability(Availability $availability, Carbon $start, Carbon $end): void
    {
        $availabilityStart = Carbon::parse($availability->start_date)->startOfDay();
        $availabilityEnd = Carbon::parse($availability->end_date)->endOfDay();

        if ($start->lt($availabilityStart) || $end->gt($availabilityEnd)) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        $dayOfWeek = strtolower($start->englishDayOfWeek);
        if (!in_array($dayOfWeek, $availability->days)) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        $availabilityStartTime = Carbon::parse($availability->start_time);
        $availabilityEndTime = Carbon::parse($availability->end_time);

        $impedimentStartTime = Carbon::parse($start->format('H:i:s'));
        $impedimentEndTime = Carbon::parse($end->format('H:i:s'));

        if ($impedimentStartTime->lt($availabilityStartTime) || $impedimentEndTime->gt($availabilityEndTime)) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }
    }

    /**
     * Validate that the availability belongs to the current schedulable.
     *
     * @param Availability $availability The availability to validate
     *
     * @throws ValidationException When availability doesn't belong to schedulable
     */


    /**
     * Find an impediment by ID for the current schedulable.
     *
     * @param int $id Impediment ID
     * @return Impediment|null Found impediment or null
     */
    public function find(int $id): ?Impediment
    {
        $this->validateSchedulable();

        return Impediment::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    /**
     * Delete an impediment by ID.
     *
     * @param int $id Impediment ID
     * @return bool True if deleted successfully
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $impediment = $this->find($id);

        if (!$impediment instanceof Impediment) {
            return false;
        }

        $this->beforeDelete($id);
        $result = $impediment->delete();
        $this->afterDelete($id, $result);

        return $result;
    }

    /**
     * Get impediments within a specific time range.
     *
     * @param Carbon $start Start datetime
     * @param Carbon $end End datetime
     * @return Collection<int, Impediment> Collection of impediments
     *
     * @throws ValidationException When time range is invalid
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        return $this->buildQueryWithFilters()
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Check if a time slot is blocked by an impediment.
     *
     * @param Carbon $start Start datetime
     * @param Carbon $end End datetime
     * @param string|null $type Availability type filter
     * @return bool True if time slot is blocked
     *
     * @throws ValidationException When time range is invalid
     */
    public function isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $this->impedimentRepository->hasOverlappingImpediments($availability->id, $start, $end);
    }

    /**
     * Get available time slots considering impediments.
     *
     * @param Carbon $start Start datetime
     * @param Carbon $end End datetime
     * @param string|null $type Availability type filter
     * @return Collection<int, array> Available time slots
     *
     * @throws ValidationException When time range is invalid
     */
    public function getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->validateTimeRange($start, $end);

        $availability = $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end, $type);

        if (!$availability instanceof Availability) {
            return collect();
        }

        $impediments = $this->impedimentRepository->findForTimeSlot($availability->id, $start, $end);

        return $this->slotFinder->getAvailableSlotsFromImpediments($start, $end, $impediments);
    }

    /**
     * Check if creating an impediment would overlap with any schedule.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start datetime
     * @param Carbon $end End datetime
     * @param int|null $exceptImpedimentId Impediment ID to exclude (for updates)
     * @return bool True if would overlap with any schedule
     */
    public function wouldOverlapWithSchedule(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        return $this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end);
    }

    /**
     * Check if creating an impediment would overlap with any other impediment.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start datetime
     * @param Carbon $end End datetime
     * @param int|null $exceptImpedimentId Impediment ID to exclude (for updates)
     * @return bool True if would overlap with any other impediment
     */
    public function wouldOverlapWithOtherImpediment(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool
    {
        return $this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $exceptImpedimentId);
    }

    /**
     * Validate impediment data including datetime range and minimum duration.
     *
     * @throws ValidationException When validation fails
     */
    protected function validateImpedimentData(): void
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        $minDuration = config('roster.durations.minimum_impediment_minutes', 5);
        $this->validationService->validateMinimumDuration($start, $end, $minDuration);
    }

    /**
     * Find availability matching the current data.
     *
     * @return Availability|null Matching availability or null
     */
    protected function findMatchingAvailability(): ?Availability
    {
        ['start' => $start, 'end' => $end] = $this->validationService
            ->parseAndValidateDateTimeRange($this->data);

        return $this->availabilityRepository->findForTimeSlot($this->schedulable, $start, $end);
    }

    /**
     * Build base query with filters applied.
     *
     * @return Builder<Impediment>
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
    protected function getEntityClass(): string
    {
        return Impediment::class;
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
     * {@inheritDoc}
     */
    protected function checkOverlapsForUpdate(int $availabilityId, Carbon $start, Carbon $end, int $exceptId): void
    {
        if ($this->impedimentRepository->hasOverlappingImpediments($availabilityId, $start, $end, $exceptId)) {
            $this->throwOverlapException();
        }

        if ($this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end)) {
            throw ValidationException::withMessage('Cannot update impediment to overlap with existing schedule');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function clearEntityCache(int $entityId): void
    {
        // Implementation of cache clearing if needed
    }

    /**
     * Process metadata JSON
     */
    protected function processMetadata(): void
    {
        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function afterCreate(mixed $result): void
    {
        // Hook après création
        $this->clearEntityCache($result->id);
    }

    /**
     * {@inheritDoc}
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        if ($result) {
            $this->clearEntityCache($id);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function afterDelete(int $id, bool $result): void
    {
        if ($result) {
            $this->clearEntityCache($id);
        }
    }
}
