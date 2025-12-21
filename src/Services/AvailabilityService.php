<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Services\AvailabilityCheckerInterface;
use Roster\Contracts\Services\AvailabilityMergerInterface;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractEntityScopingService;

/**
 * Service for managing availability records within the scheduling system.
 */
class AvailabilityService extends AbstractEntityScopingService
{
    private AvailabilityValidatorInterface $availabilityValidator;

    private ValidationServiceInterface $validationService;

    private AvailabilityRepositoryInterface $availabilityRepository;

    private AvailabilityMergerInterface $availabilityMerger;

    private SlotFinderInterface $slotFinder;

    private AvailabilityCheckerInterface $availabilityChecker;

    private ?Availability $currentAvailability = null;

    public function __construct(
        AvailabilityValidatorInterface $availabilityValidator,
        ValidationServiceInterface $validationService,
        AvailabilityRepositoryInterface $availabilityRepository,
        AvailabilityMergerInterface $availabilityMerger,
        SlotFinderInterface $slotFinder,
        AvailabilityCheckerInterface $availabilityChecker
    ) {
        $this->availabilityValidator = $availabilityValidator;
        $this->validationService = $validationService;
        $this->availabilityRepository = $availabilityRepository;
        $this->availabilityMerger = $availabilityMerger;
        $this->slotFinder = $slotFinder;
        $this->availabilityChecker = $availabilityChecker;
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
        if (!isset($this->data['start_time'], $this->data['end_time'])) {
            return;
        }

        $startTime = Carbon::parse($this->data['start_time']);
        $endTime = Carbon::parse($this->data['end_time']);

        $minimumAvailabilityMinutes = config('roster.durations.minimum_availability_minutes', 15);

        if ($startTime->diffInMinutes($endTime) < $minimumAvailabilityMinutes) {
            $this->throwMinimumDurationException($minimumAvailabilityMinutes);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        if (!isset($this->data['start_date'], $this->data['end_date'])) {
            return;
        }

        $startDate = Carbon::parse($this->data['start_date']);
        $endDate = Carbon::parse($this->data['end_date']);

        if ($startDate->diffInDays($endDate) > $maxDays) {
            throw ValidationException::withMessage(
                sprintf('Availability period cannot exceed %d days', $maxDays)
            );
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
    protected function validateBeforeUpdate(int $id): void
    {
        $this->currentAvailability = $this->find($id);

        if (!$this->currentAvailability instanceof Availability) {
            $this->throwNotFoundException();
        }

        if ($this->data === []) {
            return;
        }

        $this->availabilityValidator->validateBasicData($this->data);

        if (isset($this->data['start_time']) || isset($this->data['end_time'])) {
            $validationData = array_merge(
                [
                    'start_time' => $this->currentAvailability->start_time?->format('H:i:s'),
                    'end_time' => $this->currentAvailability->end_time?->format('H:i:s'),
                ],
                $this->data
            );
            $this->validationService->parseAndValidateTimeRange($validationData);
        }

        $checkData = $this->prepareCheckData($this->currentAvailability, $this->data);

        if ($this->availabilityChecker->hasOverlapping($this->schedulable, $checkData, $id)) {
            $this->throwOverlapException();
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function executeCreate(): Availability
    {
        return $this->availabilityRepository->create($this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeUpdate(int $id): bool
    {
        return $this->availabilityRepository->update($id, $this->data);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeDelete(int $id): bool
    {
        return $this->availabilityRepository->delete($id);
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
    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();
        return $this->availabilityRepository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function beforeCreate(mixed ...$args): void
    {
        $this->availabilityValidator->validateBasicData($this->data);
        $this->validationService->parseAndValidateTimeRange($this->data);

        if ($this->availabilityChecker->hasOverlapping($this->schedulable, $this->data)) {
            $this->throwOverlapException();
        }

        $this->data = $this->availabilityMerger->mergeAdjacentAvailabilities($this->data, $this->schedulable);
        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);
    }

    /**
     * {@inheritDoc}
     */
    protected function beforeUpdate(int $id): void
    {
        // Traitement supplémentaire avant mise à jour si nécessaire
    }

    /**
     * {@inheritDoc}
     */
    protected function afterCreate(mixed $result): void
    {
        // Hook après création (ex: log, notification, cache, etc.)
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

    /**
     * Create a new availability record.
     */
    public function create(array $data): Availability
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->data = $this->applyConfigurationRules($this->data, 'create');
        $this->validateConfiguration('create');

        $this->beforeCreate();

        $availability = $this->executeCreate();

        $this->afterCreate($availability);

        return $availability;
    }

    /**
     * Delete an availability record.
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $availability = $this->find($id);

        if (!$availability instanceof Availability) {
            return false;
        }

        $this->beforeDelete($id);

        $result = $this->executeDelete($id);

        $this->afterDelete($id, $result);

        return $result;
    }

    /**
     * Check if the given availability data overlaps with existing records.
     */
    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find all availability records that overlap with the given data.
     */
    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->parseAndValidateTimeRange($data);

        return $this->availabilityRepository->findOverlapping($this->schedulable, $data, $exceptId);
    }

    /**
     * Find availability records by type.
     */
    public function findByType(array $data): Collection
    {
        $this->validateSchedulable();
        return $this->availabilityMerger->getAdjacentAvailabilities($data, $this->schedulable);
    }

    /**
     * Filter availability records by day of week.
     */
    public function filterByDay(string $day): self
    {
        $this->filters['day'] = strtolower($day);
        return $this;
    }

    /**
     * Check if the schedulable is available at a specific datetime.
     */
    public function isAvailableAt(Carbon $datetime): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableAt($this->schedulable, $datetime);
    }

    /**
     * Check if the schedulable is available for a continuous period.
     */
    public function isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableForPeriod($this->schedulable, $start, $end, $type);
    }

    /**
     * Find available time slots within a specified period.
     */
    public function findSlotsInPeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array {
        $this->validateSchedulable();

        return $this->slotFinder->findSlotsInPeriod(
            model: $this->schedulable,
            startDate: $startDate,
            endDate: $endDate,
            durationMinutes: $durationMinutes,
            intervalMinutes: $intervalMinutes,
            type: $type
        );
    }

    /**
     * Prepare data for overlap checking by merging current availability with update data.
     */
    private function prepareCheckData(Availability $availability, array $updateData): array
    {
        $checkData = array_merge([
            'type' => $availability->type,
            'days' => $availability->days,
            'start_date' => $availability->start_date?->format('Y-m-d'),
            'end_date' => $availability->end_date?->format('Y-m-d'),
        ], $updateData);

        if (!isset($checkData['start_time']) && $availability->start_time) {
            $checkData['start_time'] = $availability->start_time->format('H:i:s');
        }

        if (!isset($checkData['end_time']) && $availability->end_time) {
            $checkData['end_time'] = $availability->end_time->format('H:i:s');
        }

        $this->validationService->parseAndValidateTimeRange($checkData);

        return $checkData;
    }

    /**
     * {@inheritDoc}
     */
    protected function buildQueryWithFilters(): Builder
    {
        return $this->availabilityRepository->buildQueryWithFilters($this->schedulable, $this->filters);
    }
}
