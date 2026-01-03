<?php

declare(strict_types=1);

namespace Roster\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Domain\Helpers\TimeSlotHelper;
use Roster\DTOs\AvailabilityData;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\Core\AbstractService;
use Roster\Validation\DTOs\ViolationData;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Service for managing Availability entities.
 *
 * Handles creation, updating, and validation of availability periods
 * with automatic day adjustment based on validity periods.
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

        $this->triggerInvalidDaysWarningIfNeeded($invalidDays);

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
     * @return void
     */
    private function triggerInvalidDaysWarningIfNeeded(array $invalidDays): void
    {
        if (!empty($invalidDays) && config('roster.reconciliation_warning')) {
            trigger_error(
                sprintf(
                    'The following days were outside the validity period and have been removed: %s',
                    implode(', ', $invalidDays)
                ),
                E_USER_WARNING
            );
        }
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
