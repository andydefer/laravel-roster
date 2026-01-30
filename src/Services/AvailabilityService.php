<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Domain\Helpers\TimeWindowHelper;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;
use Roster\Validation\DTOs\ViolationData;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing Availability entities.
 *
 * Handles creation, updating, validation, and time slot coverage checks
 * for availability periods with automatic day adjustment based on validity periods.
 */
class AvailabilityService extends AbstractService
{
    /**
     * Entity awaiting deletion (for cascade operations).
     *
     * @var Availability|null
     */
    protected ?Availability $pendingDeletion = null;

    /**
     * Creates a new availability record with automatic schedulable context.
     *
     * Automatically generates days within the validity period if no specific days are provided.
     *
     * @param array<string, mixed> $data Availability data including validity dates
     * @return Model Created availability entity
     * @throws ValidationFailedException If validation fails
     */
    public function create(array $data = []): Model
    {
        $this->requireContext();

        $generatedDays = roster_days_in_period(
            Carbon::parse($data['validity_start']),
            Carbon::parse($data['validity_end'])
        );

        if (empty($data['days'])) {
            $data['days'] = $generatedDays;
        }

        $this->data = array_merge($data, [
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
        ]);

        return parent::create($data);
    }

    /**
     * Finds an availability that fully covers a specific time slot.
     *
     * This method searches for an availability that:
     * 1. Belongs to the current schedulable entity
     * 2. Has the specified type (if provided)
     * 3. Includes the day of week of the slot
     * 4. Has a daily window that fully contains the slot time
     * 5. Is valid during the slot's date range
     *
     * @param Model $model Schedulable entity to check availabilities for
     * @param Carbon $start Start time of the slot
     * @param Carbon $end End time of the slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null Matching availability or null if none found
     * @throws \InvalidArgumentException When time window is invalid
     */
    public function getAvailabilityForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        TimeWindowHelper::assertDailyWindow($start, $end);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Availability> $potentialAvailabilities */
        $potentialAvailabilities = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->when($type !== null, fn($query) => $query->where('type', $type))
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->get();

        foreach ($potentialAvailabilities as $availability) {
            if ($this->doesAvailabilityCoverTimeSlot($availability, $start, $end)) {
                return $availability;
            }
        }

        return null;
    }

    /**
     * Determines if an availability entity fully covers a given time slot.
     *
     * Checks daily time window coverage and validity period constraints.
     *
     * @param Availability $availability Availability entity to check
     * @param Carbon $slotStart Start time of the slot to verify
     * @param Carbon $slotEnd End time of the slot to verify
     * @return bool True if the availability fully covers the time slot
     */
    private function doesAvailabilityCoverTimeSlot(
        Availability $availability,
        Carbon $slotStart,
        Carbon $slotEnd
    ): bool {
        if (!$this->isSlotWithinDailyWindow($availability, $slotStart, $slotEnd)) {
            return false;
        }

        if (!$this->isSlotWithinValidityPeriod($availability, $slotStart)) {
            return false;
        }

        return true;
    }

    /**
     * Verifies if a time slot falls within an availability's daily time window.
     *
     * @param Availability $availability Availability entity
     * @param Carbon $slotStart Slot start time
     * @param Carbon $slotEnd Slot end time
     * @return bool True if slot is within daily window
     */
    private function isSlotWithinDailyWindow(
        Availability $availability,
        Carbon $slotStart,
        Carbon $slotEnd
    ): bool {
        $slotStartTime = $slotStart->format('H:i:s');
        $slotEndTime = $slotEnd->format('H:i:s');
        $availabilityStartTime = $availability->daily_start->format('H:i:s');
        $availabilityEndTime = $availability->daily_end->format('H:i:s');

        return $slotStartTime >= $availabilityStartTime && $slotEndTime <= $availabilityEndTime;
    }

    /**
     * Verifies if a slot date falls within an availability's validity period.
     *
     * @param Availability $availability Availability entity
     * @param Carbon $slotStart Slot start time (date part is used)
     * @return bool True if slot date is within validity period
     */
    private function isSlotWithinValidityPeriod(
        Availability $availability,
        Carbon $slotStart
    ): bool {
        $slotDate = $slotStart->toDateString();

        $isAfterStart = !$availability->validity_start ||
            $slotDate >= $availability->validity_start->toDateString();

        $isBeforeEnd = !$availability->validity_end ||
            $slotDate <= $availability->validity_end->toDateString();

        return $isAfterStart && $isBeforeEnd;
    }

    /**
     * Updates an existing availability record.
     *
     * Reconciles requested days with the validity period and removes invalid days.
     *
     * @param int $id Availability identifier
     * @param array<string, mixed> $data Update data
     * @return bool True if update successful
     * @throws ValidationFailedException If validation fails or entity not found
     */
    public function update(int $id, array $data): bool
    {
        $availability = $this->find($id);
        $this->assertAvailabilityExists($availability, OperationType::UPDATE);

        $data['id'] = $id;

        $reconciliationResult = $this->reconcileDaysWithPeriod(
            requestedDays: $data['days'] ?? $availability->days,
            validityStart: isset($data['validity_start'])
                ? Carbon::parse($data['validity_start'])
                : $availability->validity_start,
            validityEnd: isset($data['validity_end'])
                ? Carbon::parse($data['validity_end'])
                : $availability->validity_end
        );

        [$validDays, $invalidDays] = $reconciliationResult;
        $data['days'] = $validDays;

        $this->warnAboutInvalidDaysIfEnabled($invalidDays);

        return parent::update($id, $data);
    }

    /**
     * Reconciles requested availability days with the validity period.
     *
     * Ensures that only days within the start and end dates are kept.
     *
     * @param array<int, string> $requestedDays Days requested for availability update
     * @param Carbon $validityStart Start date of the availability period
     * @param Carbon $validityEnd End date of the availability period
     * @return array{0: array<int,string>, 1: array<int,string>} Tuple of [validDays, invalidDays]
     */
    private function reconcileDaysWithPeriod(array $requestedDays, Carbon $validityStart, Carbon $validityEnd): array
    {
        $periodDays = roster_days_in_period($validityStart, $validityEnd);
        $invalidDays = array_diff($requestedDays, $periodDays);
        $validDays = array_values(array_intersect($periodDays, $requestedDays));

        return [$validDays, $invalidDays];
    }

    /**
     * Triggers a warning if invalid days were detected and warnings are enabled.
     *
     * @param array<int, string> $invalidDays Days outside the validity period
     */
    private function warnAboutInvalidDaysIfEnabled(array $invalidDays): void
    {
        if (empty($invalidDays) || !config('roster.reconciliation_warning')) {
            return;
        }

        trigger_error(
            sprintf(
                'The following days were outside the validity period and have been removed: %s',
                implode(', ', $invalidDays)
            ),
            E_USER_WARNING
        );
    }

    /**
     * Returns the entity type for this service.
     *
     * @return EntityType Availability entity type
     */
    protected function getEntityTypeEnum(): EntityType
    {
        return EntityType::AVAILABILITY;
    }

    /**
     * Validates that an availability entity exists.
     *
     * @param mixed $entity Entity to validate
     * @param OperationType $operationType Current operation type
     * @return Availability Validated availability entity
     * @throws ValidationFailedException If entity does not exist
     */
    protected function assertAvailabilityExists(mixed $entity, OperationType $operationType): Availability
    {
        if ($entity instanceof Availability) {
            return $entity;
        }

        throw ValidationFailedException::fromViolations(
            [
                new ViolationData(
                    field: 'id',
                    message: sprintf(
                        '%s with given ID does not exist',
                        EntityType::AVAILABILITY->displayName()
                    )
                )
            ],
            $operationType,
            EntityType::AVAILABILITY
        );
    }
}
