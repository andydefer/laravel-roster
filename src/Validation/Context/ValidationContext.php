<?php

declare(strict_types=1);

namespace Roster\Validation\Context;

use RuntimeException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Roster\Contracts\Services\ServiceInterface;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Services\AvailabilityService;

/**
 * Context container for validation operations across different entity types.
 *
 * Provides access to validation data, entity information, and service instances
 * configured with the appropriate context for the current validation operation.
 */
class ValidationContext implements ValidationContextInterface
{
    private OperationType $operationType;

    private EntityType $entityType;

    /**
     * Raw data (may contain null values)
     *
     * @var array<string, mixed>
     */
    private array $data;

    private ?Model $schedulable;

    private mixed $currentEntity;

    /**
     * @var array<string, string|array<int, string>>
     */
    private array $violations = [];

    /**
     * @var array<string, mixed>
     */
    private array $flags = [];

    public function __construct(
        OperationType $operationType,
        EntityType $entityType,
        array $data,
        ?Model $schedulable = null,
        mixed $currentEntity = null
    ) {
        $this->operationType = $operationType;
        $this->entityType = $entityType;
        $this->data = $data;
        $this->schedulable = $schedulable;
        $this->currentEntity = $currentEntity;
    }

    /**
     * Get the type of operation being validated.
     *
     * @return OperationType The operation type (CREATE, UPDATE or UPDATE)
     */
    public function getOperation(): OperationType
    {
        return $this->operationType;
    }

    /**
     * Get the type of entity being validated.
     *
     * @return EntityType The entity type (AVAILABILITY, SCHEDULE, or IMPEDIMENT)
     */
    public function getEntityType(): EntityType
    {
        return $this->entityType;
    }

    /**
     * Get the schedulable entity associated with this validation context.
     *
     * @return Model|null The schedulable model or null if not set
     */
    public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Get the current service instance configured with the appropriate context.
     *
     * @return ServiceInterface The service instance with context configured
     *
     * @throws RuntimeException When schedulable is not set or owner is required but not available
     */
    public function getCurrentService(): ServiceInterface
    {
        $schedulable = $this->getSchedulable();

        if (!$schedulable instanceof Model) {
            throw new RuntimeException(
                'Cannot get service: schedulable is not set in validation context'
            );
        }

        return match ($this->getEntityType()) {
            EntityType::AVAILABILITY => availability_for($schedulable),
            EntityType::SCHEDULE => $this->buildScheduleService($schedulable),
            EntityType::IMPEDIMENT => $this->buildImpedimentService($schedulable),
        };
    }

    /**
     * Build Schedule service with the appropriate context.
     *
     * @param Model $schedulable The schedulable entity
     *
     * @return ServiceInterface Configured Schedule service
     *
     * @throws RuntimeException When owner is required but not available
     */
    private function buildScheduleService(Model $schedulable): ServiceInterface
    {
        $owner = $this->resolveOwner();

        if (!$owner instanceof Model) {
            throw new RuntimeException(
                'Cannot get Schedule service: owner is required but not available in validation context'
            );
        }

        return schedule_for($owner);
    }

    /**
     * Build Impediment service with the appropriate context.
     *
     * @param Model $schedulable The schedulable entity
     *
     * @return ServiceInterface Configured Impediment service
     *
     * @throws RuntimeException When owner is required but not available
     */
    private function buildImpedimentService(Model $schedulable): ServiceInterface
    {
        $owner = $this->resolveOwner();

        if (!$owner instanceof Model) {
            throw new RuntimeException(
                'Cannot get Impediment service: owner is required but not available in validation context'
            );
        }

        return impediment_for($owner);
    }

    /**
     * Resolve the owner entity from validation context.
     *
     * @return Model|null Resolved owner entity or null if not found
     */
    private function resolveOwner(): ?Model
    {
        if (isset($this->data['availability_id']) && $this->schedulable instanceof Model) {
            try {
                return AvailabilityModel::find($this->data['availability_id']);
            } catch (Exception $exception) {
                // Continue to other resolution methods if not found
            }
        }

        if ($this->currentEntity instanceof Model) {
            if (method_exists($this->currentEntity, 'availability')) {
                return $this->currentEntity->availability;
            }

            if ($this->currentEntity instanceof AvailabilityModel) {
                return $this->currentEntity;
            }
        }

        if ($this->hasFlag('availability')) {
            $owner = $this->getFlag('availability');
            if ($owner instanceof Model) {
                return $owner;
            }
        }

        return null;
    }

    /**
     * Get an Availability service instance configured with the schedulable context.
     *
     * @return AvailabilityService Configured Availability service
     *
     * @throws RuntimeException When schedulable is not set
     */
    public function getAvailabilityService(): AvailabilityService
    {
        $schedulable = $this->getSchedulable();

        if (!$schedulable instanceof Model) {
            throw new RuntimeException(
                'Cannot get Availability service: schedulable is not set in validation context'
            );
        }

        return availability_for($schedulable);
    }

    /**
     * Get the current entity being validated.
     *
     * @return mixed The current entity model or null
     */
    public function getCurrentEntity(): mixed
    {
        return $this->currentEntity;
    }

    /**
     * Check if a key exists in the data and is not null.
     *
     * @param string $key The key to check
     *
     * @return bool True if key exists and value is not null
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data) && $this->data[$key] !== null;
    }

    /**
     * Get data with null values filtered out.
     *
     * @return array<string, mixed> Data without null values
     */
    public function safeData(): array
    {
        return array_filter($this->data, static fn($value): bool => $value !== null);
    }

    /**
     * Safe getter with fallback for partial updates.
     *
     * - For CREATE operations: returns the value from safeData() or default.
     * - For UPDATE operations: first tries safeData(), then currentEntity property if missing.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if not found
     *
     * @return mixed The retrieved value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->safeData();

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        if ($this->operationType === OperationType::UPDATE && $this->currentEntity instanceof Model) {
            if (property_exists($this->currentEntity, $key)) {
                return $this->currentEntity->{$key};
            }

            $getter = 'get' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this->currentEntity, $getter)) {
                return $this->currentEntity->{$getter}();
            }
        }

        return $default;
    }

    /**
     * Get raw data value including null values.
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     *
     * @return mixed The raw value or default
     */
    public function rawGet(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if a key exists in raw data (including null values).
     *
     * @param string $key The key to check
     *
     * @return bool True if key exists in raw data
     */
    public function rawHas(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Get all raw data including null values.
     *
     * @return array<string, mixed> All data including null values
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get all raw data including null values.
     *
     * @return array<string, mixed> All data including null values
     */
    public function rawData(): array
    {
        return $this->data;
    }

    /**
     * Set a value in the data array.
     *
     * @param string $key The key to set
     * @param mixed $value The value to assign
     */
    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Add a validation violation for a specific field.
     *
     * @param string $field The field name with the violation
     * @param string $message The violation message
     */
    public function setViolation(string $field, string $message): void
    {
        $this->violations[$field] = $message;
    }

    /**
     * Get all validation violations.
     *
     * @return array<string, string|array<int, string>> Array of violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Check if any violations have been recorded.
     *
     * @return bool True if there are any violations
     */
    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    /**
     * Set a flag with an optional value.
     *
     * @param string $flag The flag name
     * @param mixed $value The flag value (defaults to true)
     */
    public function setFlag(string $flag, mixed $value = true): void
    {
        $this->flags[$flag] = $value;
    }

    /**
     * Check if a flag is set and truthy.
     *
     * @param string $flag The flag name
     *
     * @return bool True if flag exists and has a truthy value
     */
    public function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]) && $this->flags[$flag];
    }

    /**
     * Get the value of a flag.
     *
     * @param string $flag The flag name
     * @param mixed $default Default value if flag doesn't exist
     *
     * @return mixed The flag value or default
     */
    public function getFlag(string $flag, mixed $default = false): mixed
    {
        return $this->flags[$flag] ?? $default;
    }
}
