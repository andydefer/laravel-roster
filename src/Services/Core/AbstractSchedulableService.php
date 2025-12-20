<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Roster\Contracts\Services\SchedulableServiceInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Exceptions\Messages\ErrorMessageFactory;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Exceptions\ValidationException;

/**
 * Base service for resources scoped to a schedulable model.
 *
 * Defines the execution workflow and enforces a consistent
 * usage contract for all schedulable services.
 */
abstract class AbstractSchedulableService implements SchedulableServiceInterface
{
    protected ?Model $schedulable = null;

    /**
     * @var array<string, mixed>
     */
    protected array $filters = [];

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<string, mixed>
     */
    protected array $originalData = [];

    /**
     * Scope the service to a specific parent model.
     */
    final public function for(Model $model): static
    {
        $this->schedulable = $model;

        return $this;
    }

    /**
     * Get the current schedulable model.
     */
    final public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Clear all applied filters.
     */
    final public function resetFilters(): static
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Ensure a schedulable model has been provided.
     *
     * @throws MissingSchedulableException
     */
    final protected function validateSchedulable(): void
    {
        if (! $this->schedulable instanceof Model) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Return all matching results.
     */
    final public function all(): Collection
    {
        return $this->get();
    }

    /**
     * Execute the query with the current filters.
     */
    final public function get(): Collection
    {
        $this->validateSchedulable();

        return $this->buildQueryWithFilters()->get();
    }

    /**
     * Filter results by type.
     */
    final public function whereType(string $type): static
    {
        $this->filters['type'] = $type;

        return $this;
    }

    /**
     * TEMPLATE METHOD: Update with configuration validation
     *
     * @param  array<string, mixed>  $data
     */
    final public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();
        $this->data = $data;

        // 1. Apply configuration rules to data
        $this->data = $this->applyConfigurationRules($this->data, 'update');

        // 2. Validate configuration rules
        $this->validateConfigurationRules('update');

        // 3. Validate business rules (hook for children)
        $this->validateBeforeUpdate($id);

        // 4. Process data (hook for children)
        $this->processBeforeUpdate($id);

        // 5. Execute update (abstract method)
        $result = $this->executeUpdate($id);

        // 6. Post-update hooks
        $this->afterUpdate($id, $result);

        return $result;
    }

    /**
     * Apply configuration rules from config file
     *
     * @param  array<string, mixed>  $data
     * @param  string  $operation  'create' or 'update'
     * @return array<string, mixed>
     */
    final protected function applyConfigurationRules(array $data, string $operation): array
    {
        // Set timezone if not provided
        if (! isset($data['timezone'])) {
            $data['timezone'] = Config::get('roster.timezone', 'UTC');
        }

        // Apply operation-specific rules
        if ($operation === 'create') {
            return $this->applyCreateConfigurationRules($data);
        }

        return $this->applyUpdateConfigurationRules($data);
    }

    /**
     * Apply configuration rules specific to create operation
     */
    final protected function applyCreateConfigurationRules(array $data): array
    {
        // Apply entity-specific default values
        $data = $this->applyEntitySpecificDefaults($data);

        return $data;
    }

    /**
     * Apply configuration rules specific to update operation
     */
    final protected function applyUpdateConfigurationRules(array $data): array
    {
        // Add update-specific rules here if needed
        return $data;
    }

    /**
     * Apply entity-specific default values
     *
     * @param  array<string, mixed>  $data
     */
    final protected function applyEntitySpecificDefaults(array $data): array
    {
        $entityType = $this->getEntityType();

        // Set default status for schedules if not provided
        if ($entityType === 'schedule' && ! isset($data['status'])) {
            $data['status'] = Config::get('roster.schedule.default_status', 'available');
        }

        return $data;
    }

    /**
     * Validate configuration rules
     *
     * @param  string  $operation  'create' or 'update'
     *
     * @throws ValidationException
     */
    final protected function validateConfigurationRules(string $operation): void
    {
        // 1. Get entity configuration
        $entityType = $this->getEntityType();
        $entityConfig = Config::get('roster.validate_future_dates.' . $entityType, []);
        $globalEnabled = Config::get('roster.validate_future_dates.enabled', true);

        // Check if validation is enabled for this entity
        $entityEnabled = $entityConfig['enabled'] ?? $globalEnabled;

        // 2. Validate future dates if enabled
        if ($entityEnabled) {
            $this->validateFutureDates($operation, $entityType, $entityConfig);
        }

        // 3. Validate durations based on service type
        $this->validateDurations($operation);

        // 4. Validate other global configuration rules
        $this->validateGlobalConfigurationRules($operation);
    }

    /**
     * Validate future dates based on configuration
     *
     * @param  array<string, mixed>  $entityConfig
     */
    final protected function validateFutureDates(string $operation, string $entityType, array $entityConfig): void
    {
        $fieldName = $entityConfig['validation_field'] ?? $this->getDefaultDateField($entityType);

        if (! isset($this->data[$fieldName])) {
            return;
        }

        try {
            $date = Carbon::parse($this->data[$fieldName]);

            if ($date->isPast()) {
                $allowPast = $entityConfig['allow_past'] ?? false;

                if (! $allowPast) {
                    throw ValidationException::withMessage(
                        ErrorMessageFactory::pastDate($entityType, $fieldName)
                    );
                }
            }
        } catch (Exception $exception) {
            // Not a valid date, validation will be handled elsewhere
        }
    }

    /**
     * Validate durations based on configuration
     */
    final protected function validateDurations(string $operation): void
    {
        $minImpediment = Config::get('roster.durations.minimum_impediment_minutes', 5);
        $minSchedule = Config::get('roster.durations.minimum_schedule_minutes', 15);
        $defaultDuration = Config::get('roster.durations.default_slot_duration_minutes', 60);

        $this->validateDurationHook($operation, $minImpediment, $minSchedule, $defaultDuration);
    }

    /**
     * Validate other global configuration rules
     */
    final protected function validateGlobalConfigurationRules(string $operation): void
    {
        // Check max days to check for date ranges
        $maxDays = Config::get('roster.durations.max_search_period_days', 365);
        $this->validateMaxDaysHook($operation, $maxDays);

        // Validate timezone
        $timezone = $this->data['timezone'] ?? Config::get('roster.timezone', 'UTC');
        $this->validateTimezoneHook($timezone);
    }

    /**
     * Get entity type from class name
     */
    final protected function getEntityType(): string
    {
        $className = class_basename(static::class);

        return strtolower(str_replace('Service', '', $className));
    }

    /**
     * Get entity display name
     */
    final protected function getEntityDisplayName(): string
    {
        return ucfirst($this->getEntityType());
    }

    /**
     * Get default date field name based on entity type
     */
    protected function getDefaultDateField(string $entityType): string
    {
        return match ($entityType) {
            'availability' => 'start_date',
            default => 'start_datetime'
        };
    }

    /**
     * Get date/time fields for the entity
     */
    protected function getDateTimeFields(string $entityType): array
    {
        return match ($entityType) {
            'availability' => ['start_date', 'end_date', 'start_time', 'end_time'],
            'schedule', 'impediment' => ['start_datetime', 'end_datetime'],
            default => []
        };
    }

    /**
     * Validate timezone for the entity
     */
    protected function validateTimezoneHook(string $timezone): void
    {
        $entityType = $this->getEntityType();
        $dateFields = $this->getDateTimeFields($entityType);

        // Check if any date/time field is being set
        $hasDateFields = false;
        foreach ($dateFields as $dateField) {
            if (isset($this->data[$dateField])) {
                $hasDateFields = true;
                break;
            }
        }

        if ($hasDateFields) {
            $validationService = $this->getValidationService();
            if (! $validationService->validateTimezone($timezone)) {
                throw ValidationException::withMessage(
                    'Invalid timezone: ' . $timezone
                );
            }
        }
    }

    /**
     * Clear entity cache
     */
    protected function clearEntityCache(int $entityId): void
    {
        if (! Config::get('roster.cache.enabled', true)) {
            return;
        }

        $prefix = Config::get('roster.cache.prefix', 'roster_');
        $entityType = $this->getEntityType();
        $cacheKey = $prefix . $entityType . '_' . $entityId;

        Cache::forget($cacheKey);

        // Clear tags if enabled
        if (Config::get('roster.cache.use_tags', true)) {
            Cache::tags([$entityType . '_' . $entityId])->flush();
        }
    }

    /**
     * Throw not found exception
     */
    protected function throwNotFoundException(): void
    {
        throw ValidationException::withMessage(
            ErrorMessageFactory::notFound($this->getEntityType())
        );
    }

    /**
     * Throw overlap exception
     */
    protected function throwOverlapException(): void
    {
        throw ValidationException::withMessage(
            ErrorMessageFactory::overlap($this->getEntityType())
        );
    }

    /**
     * Throw minimum duration exception
     */
    protected function throwMinimumDurationException(int $minutes): void
    {
        throw ValidationException::withMessage(
            ErrorMessageFactory::minimumDuration($this->getEntityType(), $minutes)
        );
    }

    /**
     * Validate common required fields
     */
    protected function validateRequiredFields(array $requiredFields = []): void
    {
        $entityType = $this->getEntityType();
        $configFields = Config::get('roster.validation.required_fields.' . $entityType, []);
        $allRequired = array_unique(array_merge($configFields, $requiredFields));

        foreach ($allRequired as $field) {
            if (! isset($this->data[$field]) || empty($this->data[$field])) {
                throw ValidationException::withMessage(
                    sprintf("Field '%s' is required", $field)
                );
            }
        }
    }

    /**
     * Validate duration hook
     *
     * @param  int  $minImpedimentMinutes  Minimum duration for impediments
     * @param  int  $minScheduleMinutes  Minimum duration for schedules
     * @param  int  $defaultDurationMinutes  Default slot duration
     */
    abstract protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void;

    /**
     * Validate max days hook
     */
    abstract protected function validateMaxDaysHook(string $operation, int $maxDays): void;

    /**
     * Get validation service instance
     */
    abstract protected function getValidationService(): ValidationServiceInterface;

    /**
     * Validate before create hook
     */
    abstract protected function validateBeforeCreate(): void;

    /**
     * Process before create hook
     */
    abstract protected function processBeforeCreate(): void;

    /**
     * Execute create (to be implemented by children)
     */
    abstract protected function executeCreate(): mixed;

    /**
     * After create hook
     */
    protected function afterCreate(mixed $result): void
    {
        // Default implementation: clear cache
        if (method_exists($result, 'getId') || property_exists($result, 'id')) {
            $id = $result->id ?? $result->getId();
            $this->clearEntityCache((int) $id);
        }
    }

    /**
     * Validate before update hook
     */
    abstract protected function validateBeforeUpdate(int $id): void;

    /**
     * Process before update hook
     */
    abstract protected function processBeforeUpdate(int $id): void;

    /**
     * Execute update (to be implemented by children)
     */
    abstract protected function executeUpdate(int $id): bool;

    /**
     * After update hook
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        // Default implementation: clear cache if update was successful
        if ($result) {
            $this->clearEntityCache($id);
        }
    }

    /**
     * Apply filters to the query.
     *
     * @return Builder
     */
    abstract protected function buildQueryWithFilters();
}
