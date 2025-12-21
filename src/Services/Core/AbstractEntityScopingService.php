<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Messages\ErrorMessageFactory;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Exceptions\ValidationException;
use Roster\Services\Core\Components\ConfigurationRules;
use Roster\Services\Core\Components\LifecycleHooks;

/**
 * Base service for resources scoped to a schedulable model.
 *
 * Provides common functionality for entity management including filtering,
 * configuration rule application, validation, and CRUD operations with hooks.
 *
 * @template TEntity of mixed
 */
abstract class AbstractEntityScopingService extends AbstractService
{
    use LifecycleHooks;
    use ConfigurationRules;

    /**
     * Current entity being processed.
     */
    protected mixed $currentEntity = null;

    /**
     * Scope the service to a specific schedulable model.
     *
     * @param Model $model The parent model to scope operations to
     */
    final public function for(Model $model): static
    {
        $this->schedulable = $model;
        return $this;
    }

    /**
     * Set multiple filters at once.
     *
     * @param array<string, mixed> $filters Associative array of filter key-value pairs
     */
    final public function setFilters(array $filters): static
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Set a single filter.
     *
     * @param string $key Filter key
     * @param mixed $value Filter value
     */
    final public function setFilter(string $key, mixed $value): static
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * Filter by entity type.
     *
     * @param string $type Entity type to filter by
     */
    final public function whereType(string $type): static
    {
        $this->filters['type'] = $type;
        return $this;
    }

    /**
     * Clear all filters.
     */
    final public function resetFilters(): static
    {
        $this->filters = [];
        return $this;
    }

    /**
     * Get all matching results.
     *
     * @return Collection<int, TEntity>
     */
    final public function getAll(): Collection
    {
        return $this->get();
    }

    /**
     * Execute query with current filters and return results.
     *
     * @return Collection<int, TEntity>
     * @throws MissingSchedulableException When no schedulable is set
     */
    final public function get(): Collection
    {
        $this->validateSchedulable();
        return $this->buildQueryWithFilters()->get();
    }

    /**
     * Apply configuration rules based on operation type.
     *
     * @param array<string, mixed> $data Entity data
     * @param string $operation Operation type ('create' or 'update')
     * @return array<string, mixed> Modified data with configuration rules applied
     */
    final public function applyConfigurationRules(array $data, string $operation): array
    {
        if (!isset($data['timezone'])) {
            $data['timezone'] = Config::get('roster.timezone', 'UTC');
        }

        return match ($operation) {
            'create' => $this->applyCreateConfigurationRules($data),
            default => $this->applyUpdateConfigurationRules($data),
        };
    }

    /**
     * Get the entity type based on service class name.
     *
     * @return string Entity type (e.g., 'availability', 'schedule', 'impediment')
     */
    final public function getEntityType(): string
    {
        $className = class_basename(static::class);
        return strtolower(str_replace('Service', '', $className));
    }

    /**
     * Get the display name for the entity type.
     *
     * @return string Human-readable entity name
     */
    final public function getEntityDisplayName(): string
    {
        return ucfirst($this->getEntityType());
    }

    /**
     * Get the default date field for the entity type.
     *
     * @param string $entityType Entity type
     * @return string Default date field name
     */
    protected function getDefaultDateField(string $entityType): string
    {
        return match ($entityType) {
            'availability' => 'start_date',
            default => 'start_datetime',
        };
    }

    /**
     * Get date/time fields for the entity type.
     *
     * @param string $entityType Entity type
     * @return array<int, string> List of date/time field names
     */
    public function getDateTimeFields(string $entityType): array
    {
        return match ($entityType) {
            'availability' => ['start_date', 'end_date', 'start_time', 'end_time'],
            'schedule', 'impediment' => ['start_datetime', 'end_datetime'],
            default => [],
        };
    }

    /**
     * Validate that a schedulable model is set.
     *
     * @throws MissingSchedulableException When no schedulable is set
     */
    final public function validateSchedulable(): void
    {
        if (!$this->schedulable instanceof Model) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Validate all configuration rules for an operation.
     *
     * @param string $operation Operation type ('create' or 'update')
     * @throws ValidationException When validation fails
     */
    final public function validateConfiguration(string $operation): void
    {
        $entityType = $this->getEntityType();
        $entityConfig = Config::get('roster.validate_future_dates.' . $entityType, []);
        $globalEnabled = Config::get('roster.validate_future_dates.enabled', true);
        $entityEnabled = $entityConfig['enabled'] ?? $globalEnabled;

        if ($entityEnabled) {
            $this->validateFutureDates($operation, $entityType, $entityConfig);
        }

        $this->validateDurations($operation);
        $this->validateGlobalConfigurationRules($operation);
    }

    /**
     * Validate that dates are not in the past unless explicitly allowed.
     *
     * @param string $operation Operation type
     * @param string $entityType Entity type
     * @param array<string, mixed> $entityConfig Entity-specific configuration
     * @throws ValidationException When a past date is not allowed
     */
    final public function validateFutureDates(string $operation, string $entityType, array $entityConfig): void
    {
        $fieldName = $entityConfig['validation_field'] ?? $this->getDefaultDateField($entityType);

        if (!isset($this->data[$fieldName])) {
            return;
        }

        try {
            $date = Carbon::parse($this->data[$fieldName]);

            if ($date->isPast() && !($entityConfig['allow_past'] ?? false)) {
                throw ValidationException::withMessage(
                    ErrorMessageFactory::pastDate($entityType, $fieldName)
                );
            }
        } catch (Exception) {
            // Invalid dates are validated elsewhere
        }
    }

    /**
     * Validate duration-related configuration rules.
     *
     * @param string $operation Operation type
     */
    final public function validateDurations(string $operation): void
    {
        $minImpediment = Config::get('roster.durations.minimum_impediment_minutes', 5);
        $minSchedule = Config::get('roster.durations.minimum_schedule_minutes', 15);
        $defaultDuration = Config::get('roster.durations.default_slot_duration_minutes', 60);

        $this->validateDurationHook($operation, $minImpediment, $minSchedule, $defaultDuration);
    }

    /**
     * Validate global configuration rules.
     *
     * @param string $operation Operation type
     */
    final public function validateGlobalConfigurationRules(string $operation): void
    {
        $maxDays = Config::get('roster.durations.max_search_period_days', 365);
        $this->validateMaxDaysHook($operation, $maxDays);

        $timezone = $this->data['timezone'] ?? Config::get('roster.timezone', 'UTC');
        $this->validateTimezoneHook($timezone);
    }

    /**
     * Validate timezone configuration.
     *
     * @param string $timezone Timezone to validate
     * @throws ValidationException When timezone is invalid
     */
    public function validateTimezoneHook(string $timezone): void
    {
        $entityType = $this->getEntityType();
        $dateFields = $this->getDateTimeFields($entityType);

        foreach ($dateFields as $dateField) {
            if (isset($this->data[$dateField])) {
                $validationService = $this->getValidationService();
                if (!$validationService->validateTimezone($timezone)) {
                    throw ValidationException::withMessage('Invalid timezone: ' . $timezone);
                }

                break;
            }
        }
    }

    /**
     * Validate required fields.
     *
     * @param array<int, string> $requiredFields Additional required fields beyond configuration
     * @throws ValidationException When required fields are empty
     */
    public function validateRequiredFields(array $requiredFields = []): void
    {
        $entityType = $this->getEntityType();
        $configFields = Config::get('roster.validation.required_fields.' . $entityType, []);
        $allRequired = array_unique(array_merge($configFields, $requiredFields));

        foreach ($allRequired as $field) {
            if (empty($this->data[$field] ?? null)) {
                throw ValidationException::withMessage(sprintf("Field '%s' is required", $field));
            }
        }
    }

    /**
     * Create a new entity.
     *
     * @param array<string, mixed> $data Entity data
     * @return TEntity Created entity
     * @throws MissingSchedulableException When no schedulable is set
     * @throws ValidationException When validation fails
     */
    public function create(array $data): mixed
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->data = $this->applyConfigurationRules($this->data, 'create');
        $this->validateConfiguration('create');

        $this->beforeCreate();

        $result = $this->executeCreate();

        $this->afterCreate($result);

        return $result;
    }

    /**
     * Update an existing entity.
     *
     * @param int $id Entity ID
     * @param array<string, mixed> $data Updated entity data
     * @return bool True if update was successful
     * @throws MissingSchedulableException When no schedulable is set
     * @throws ValidationException When validation fails
     */
    public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->data = $this->applyConfigurationRules($this->data, 'update');
        $this->validateConfiguration('update');
        $this->validateBeforeUpdate($id);

        $this->beforeUpdate($id);

        $result = $this->executeUpdate($id);

        $this->afterUpdate($id, $result);

        return $result;
    }

    /**
     * Delete an entity.
     *
     * @param int $id Entity ID
     * @return bool True if deletion was successful
     * @throws MissingSchedulableException When no schedulable is set
     */
    public function delete(int $id): bool
    {
        $this->validateSchedulable();
        $entity = $this->find($id);

        if (!$entity) {
            return false;
        }

        $this->beforeDelete($id);

        $result = $this->executeDelete($id);

        $this->afterDelete($id, $result);

        return $result;
    }

    /**
     * Validate duration constraints.
     *
     * @param string $operation Operation type
     * @param int $minImpedimentMinutes Minimum impediment duration in minutes
     * @param int $minScheduleMinutes Minimum schedule duration in minutes
     * @param int $defaultDurationMinutes Default slot duration in minutes
     */
    abstract protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void;

    /**
     * Validate maximum period constraints.
     *
     * @param string $operation Operation type
     * @param int $maxDays Maximum allowed period in days
     */
    abstract protected function validateMaxDaysHook(string $operation, int $maxDays): void;

    /**
     * Get the validation service instance.
     */
    abstract protected function getValidationService(): ValidationServiceInterface;

    /**
     * Validate entity before update.
     *
     * @param int $id Entity ID
     */
    abstract protected function validateBeforeUpdate(int $id): void;

    /**
     * Execute the update operation.
     *
     * @param int $id Entity ID
     * @return bool True if update was successful
     */
    abstract protected function executeUpdate(int $id): bool;

    /**
     * Execute the delete operation.
     *
     * @param int $id Entity ID
     * @return bool True if deletion was successful
     */
    abstract protected function executeDelete(int $id): bool;

    /**
     * Build query with current filters applied.
     *
     * @return Builder Query builder instance
     */
    abstract protected function buildQueryWithFilters(): Builder;

    /**
     * Find entity by ID.
     *
     * @param int $id Entity ID
     * @return TEntity|null Entity or null if not found
     */
    abstract public function find(int $id): mixed;

    /**
     * Clear entity cache.
     *
     * @param int $entityId Entity ID
     */
    abstract protected function clearEntityCache(int $entityId): void;
}
