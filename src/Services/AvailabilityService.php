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
use Roster\Services\Core\AbstractSchedulableService;
use Roster\Traits\FilterableTrait;

class AvailabilityService extends AbstractSchedulableService
{
    use FilterableTrait;

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

    // ========== HOOK METHODS IMPLEMENTATION ==========

    protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void {
        if (isset($this->data['start_time'], $this->data['end_time'])) {
            $startTime = Carbon::parse($this->data['start_time']);
            $endTime = Carbon::parse($this->data['end_time']);

            $minAvailabilityMinutes = config('roster.durations.minimum_availability_minutes', 15);
            if ($startTime->diffInMinutes($endTime) < $minAvailabilityMinutes) {
                $this->throwMinimumDurationException($minAvailabilityMinutes);
            }
        }
    }

    protected function validateMaxDaysHook(string $operation, int $maxDays): void
    {
        if (isset($this->data['start_date'], $this->data['end_date'])) {
            $start = Carbon::parse($this->data['start_date']);
            $end = Carbon::parse($this->data['end_date']);

            if ($start->diffInDays($end) > $maxDays) {
                throw ValidationException::withMessage(
                    sprintf('Availability period cannot exceed %d days', $maxDays)
                );
            }
        }
    }

    protected function getValidationService(): ValidationServiceInterface
    {
        return $this->validationService;
    }

    protected function validateBeforeCreate(): void
    {
        $this->availabilityValidator->validateBasicData($this->data);
        $this->validationService->parseAndValidateTimeRange($this->data);

        if ($this->availabilityChecker->hasOverlapping($this->schedulable, $this->data)) {
            $this->throwOverlapException();
        }
    }

    protected function processBeforeCreate(): void
    {
        $this->data = $this->availabilityMerger->mergeWithAdjacent($this->data, $this->schedulable);
        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);
    }

    protected function executeCreate(): Availability
    {
        return $this->availabilityRepository->create($this->data);
    }

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

    protected function processBeforeUpdate(int $id): void
    {
        // Additional processing if needed
    }

    protected function executeUpdate(int $id): bool
    {
        return $this->availabilityRepository->update($id, $this->data);
    }

    // ========== ORIGINAL METHODS ==========

    public function find(int $id): ?Availability
    {
        $this->validateSchedulable();
        return $this->availabilityRepository->findById($id);
    }

    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $availability = $this->find($id);

        if (!$availability instanceof Availability) {
            return false;
        }

        return $this->availabilityRepository->delete($id);
    }

    public function hasOverlapping(array $data, ?int $exceptId = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->hasOverlapping($this->schedulable, $data, $exceptId);
    }

    public function findOverlapping(array $data, ?int $exceptId = null): Collection
    {
        $this->validateSchedulable();
        $this->validationService->parseAndValidateTimeRange($data);
        return $this->availabilityRepository->findOverlapping($this->schedulable, $data, $exceptId);
    }

    public function findAdjacentAvailabilities(array $data): Collection
    {
        $this->validateSchedulable();
        return $this->availabilityMerger->findAdjacentAvailabilities($data, $this->schedulable);
    }

    public function whereDay(string $day): self
    {
        $this->filters['day'] = strtolower($day);
        return $this;
    }

    public function isAvailableAt(Carbon $datetime): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableAt($this->schedulable, $datetime);
    }

    public function isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null): bool
    {
        $this->validateSchedulable();
        return $this->availabilityChecker->isAvailableForPeriod($this->schedulable, $start, $end, $type);
    }

    public function findSlotsInPeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes = 60,
        int $intervalMinutes = 30,
        ?string $type = null
    ): array {
        $this->validateSchedulable();
        return $this->slotFinder->findSlotsInPeriod(
            $this->schedulable,
            $startDate,
            $endDate,
            $durationMinutes,
            $intervalMinutes,
            $type
        );
    }

    private function prepareCheckData(Availability $availability, array $data): array
    {
        $checkData = array_merge([
            'type' => $availability->type,
            'days' => $availability->days,
            'start_date' => $availability->start_date?->format('Y-m-d'),
            'end_date' => $availability->end_date?->format('Y-m-d'),
        ], $data);

        if (!isset($checkData['start_time']) && $availability->start_time) {
            $checkData['start_time'] = $availability->start_time->format('H:i:s');
        }

        if (!isset($checkData['end_time']) && $availability->end_time) {
            $checkData['end_time'] = $availability->end_time->format('H:i:s');
        }

        $this->validationService->parseAndValidateTimeRange($checkData);

        return $checkData;
    }

    protected function applyFilters(): Builder
    {
        return $this->availabilityRepository->applyFilters($this->schedulable, $this->filters);
    }
}
