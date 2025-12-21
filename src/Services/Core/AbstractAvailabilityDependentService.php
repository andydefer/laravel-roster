<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use BadMethodCallException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Roster\Exceptions\Enums\ValidationType;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;

/**
 * Abstract service for managing schedulable entities that depend on Availability.
 *
 * This base class provides common functionality for Schedule and Impediment services,
 * handling creation, deletion, and date-based operations while ensuring proper
 * ownership validation and availability matching.
 */
abstract class AbstractAvailabilityDependentService extends AbstractEntityScopingService
{
    /**
     * Create a new entity with explicit availability.
     *
     * @param Availability|array $availabilityOrData Either an Availability instance with data,
     *                                               or data array (deprecated)
     * @param array|null $data                       Entity data when first param is Availability
     *
     * @return mixed The created entity
     *
     * @throws BadMethodCallException When using deprecated array-only syntax
     * @throws InvalidArgumentException When arguments are invalid
     */
    public function create($availabilityOrData, ?array $data = null): mixed
    {
        if ($availabilityOrData instanceof Availability && $data !== null) {
            return $this->createWithAvailability($availabilityOrData, $data);
        }

        if (is_array($availabilityOrData) && $data === null) {
            throw new BadMethodCallException(
                sprintf(
                    'Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead for %s.',
                    $this->getEntityDisplayName()
                )
            );
        }

        throw new InvalidArgumentException('Invalid arguments for create method');
    }

    /**
     * Template method for creating an entity with a specific availability.
     *
     * @param Availability $availability The availability to associate with the entity
     * @param array $data Entity data
     *
     * @return mixed The created entity
     */
    protected function createWithAvailability(Availability $availability, array $data): mixed
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->validateAvailabilityOwnership($availability);
        $this->data['availability_id'] = $availability->id;

        $this->data['schedulable_id'] = $this->schedulable->id;
        $this->data['schedulable_type'] = get_class($this->schedulable);

        $this->data = $this->applyConfigurationRules($this->data, 'create');
        $this->validateConfiguration('create');

        $this->beforeCreate();

        $entity = $this->executeCreate();
        $this->afterCreate($entity);

        return $entity;
    }

    /**
     * Validate that the availability belongs to the current schedulable.
     *
     * @param Availability $availability The availability to validate
     *
     * @throws ValidationException When availability does not belong to schedulable
     */
    protected function validateAvailabilityOwnership(Availability $availability): void
    {
        if (
            $availability->schedulable_id !== $this->schedulable->id ||
            $availability->schedulable_type !== get_class($this->schedulable)
        ) {
            throw new ValidationException(ValidationType::INVALID_AVAILABILITY);
        }
    }

    /**
     * Find an entity by ID for the current schedulable.
     *
     * @param int $id Entity ID
     *
     * @return mixed The found entity or null
     */
    public function find(int $id): mixed
    {
        $this->validateSchedulable();

        $entityClass = $this->getEntityClass();

        return $entityClass::where('schedulable_id', $this->schedulable->id)
            ->where('schedulable_type', get_class($this->schedulable))
            ->find($id);
    }

    /**
     * Get the entity class name.
     *
     * @return string Fully qualified entity class name
     */
    abstract protected function getEntityClass(): string;

    /**
     * Delete an entity by ID.
     *
     * @param int $id Entity ID
     *
     * @return bool True if deleted, false if not found
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $entity = $this->find($id);

        if (!$entity) {
            return false;
        }

        $this->beforeDelete($id);
        $result = $entity->delete();
        $this->afterDelete($id, $result);

        return $result;
    }

    /**
     * Get entities between two dates.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     *
     * @return Collection Entities within the date range
     */
    public function between(Carbon $start, Carbon $end): Collection
    {
        $this->validateSchedulable();
        $validationService = $this->getValidationService();
        $validationService->validateTimeRange($start, $end);

        return $this->buildQueryWithFilters()
            ->where('start_datetime', '>=', $start)
            ->where('end_datetime', '<=', $end)
            ->orderBy('start_datetime')
            ->get();
    }

    /**
     * Process metadata JSON field.
     *
     * Converts JSON string metadata to array if needed.
     */
    protected function processMetadata(): void
    {
        if (isset($this->data['metadata']) && !is_array($this->data['metadata'])) {
            $this->data['metadata'] = json_decode($this->data['metadata'], true) ?? [];
        }
    }

    /**
     * Validate update operations that involve date changes.
     *
     * Ensures date changes don't violate availability constraints.
     *
     * @throws ValidationException When no matching availability found
     */
    protected function validateUpdateWithDateChanges(): void
    {
        if (!isset($this->data['start_datetime'])) {
            return;
        }

        $newAvailability = $this->findMatchingAvailability();
        if (!$newAvailability instanceof Availability) {
            throw new ValidationException(ValidationType::NO_MATCHING_AVAILABILITY);
        }

        $validationService = $this->getValidationService();
        ['start' => $start, 'end' => $end] = $validationService
            ->parseAndValidateDateTimeRange(array_merge([
                'start_datetime' => $this->currentEntity->start_datetime,
                'end_datetime' => $this->currentEntity->end_datetime,
            ], $this->data));

        $this->checkOverlapsForUpdate(
            availabilityId: $newAvailability->id,
            start: $start,
            end: $end,
            exceptId: $this->currentEntity->id
        );

        if ($newAvailability->id !== $this->currentEntity->availability_id) {
            $this->data['availability_id'] = $newAvailability->id;
        }
    }

    /**
     * Check for overlapping entities during update operations.
     *
     * @param int $availabilityId The availability ID to check
     * @param Carbon $start Start datetime
     * @param Carbon $end End datetime
     * @param int $exceptId Entity ID to exclude from overlap check
     *
     * @throws ValidationException When overlaps are detected
     */
    abstract protected function checkOverlapsForUpdate(
        int $availabilityId,
        Carbon $start,
        Carbon $end,
        int $exceptId
    ): void;

    /**
     * Find matching availability for current data.
     *
     * @return Availability|null Matching availability or null
     */
    protected function findMatchingAvailability(): ?Availability
    {
        $validationService = $this->getValidationService();
        ['start' => $start, 'end' => $end] = $validationService
            ->parseAndValidateDateTimeRange($this->data);

        $repository = $this->getAvailabilityRepository();
        $type = $this->data['type'] ?? null;

        return $repository->findForTimeSlot(
            schedulable: $this->schedulable,
            start: $start,
            end: $end,
            type: $type
        );
    }

    /**
     * Get the availability repository instance.
     *
     * @return mixed Availability repository
     */
    abstract protected function getAvailabilityRepository(): mixed;

    /**
     * Get the schedule repository instance.
     *
     * @return mixed Schedule repository
     */
    abstract protected function getScheduleRepository(): mixed;

    /**
     * Get the impediment repository instance.
     *
     * @return mixed Impediment repository
     */
    abstract protected function getImpedimentRepository(): mixed;
}
