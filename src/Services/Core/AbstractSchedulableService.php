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
     * Scope the service to a specific parent model.
     *
     * @param Model $model The schedulable model to scope to
     * @return static
     */
    final public function for(Model $model): static
    {
        $this->schedulable = $model;

        return $this;
    }

    /**
     * Get the current schedulable model.
     *
     * @return Model|null
     */
    final public function getSchedulable(): ?Model
    {
        return $this->schedulable;
    }

    /**
     * Clear all applied filters.
     *
     * @return static
     */
    final public function resetFilters(): static
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Filter results by type.
     *
     * @param string $type The type to filter by
     * @return static
     */
    final public function whereType(string $type): static
    {
        $this->filters['type'] = $type;

        return $this;
    }

    /**
     * Return all matching results.
     *
     * @return Collection
     */
    final public function all(): Collection
    {
        return $this->get();
    }

    /**
     * Execute the query with the current filters.
     *
     * @return Collection
     * @throws MissingSchedulableException
     */
    final public function get(): Collection
    {
        $this->validateSchedulable();

        return $this->buildQueryWithFilters()->get();
    }

    /**
     * Update an entity with configuration validation.
     *
     * @param int $id The entity ID to update
     * @param array<string, mixed> $data The data to update
     * @return bool True if update was successful
     * @throws MissingSchedulableException
     * @throws ValidationException
     */
    final public function update(int $id, array $data): bool
    {
        $this->validateSchedulable();
        $this->data = $data;

        $this->data = $this->applyConfigurationRules(data: $this->data, operation: 'update');
        $this->validateConfigurationRules(operation: 'update');
        $this->validateBeforeUpdate(id: $id);
        $this->processBeforeUpdate(id: $id);

        $result = $this->executeUpdate(id: $id);

        $this->afterUpdate(id: $id, result: $result);

        return $result;
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
     * Apply configuration rules from config file.
     *
     * @param array<string, mixed> $data The input data
     * @param string $operation Either 'create' or 'update'
     * @return array<string, mixed> The processed data
     */
    final protected function applyConfigurationRules(array $data, string $operation): array
    {
        if (! isset($data['timezone'])) {
            $data['timezone'] = Config::get(key: 'roster.timezone', default: 'UTC');
        }

        return match ($operation) {
            'create' => $this->applyCreateConfigurationRules(data: $data),
            default => $this->applyUpdateConfigurationRules(data: $data),
        };
    }

    /**
     * Apply configuration rules specific to create operation.
     *
     * @param array<string, mixed> $data The input data
     * @return array<string, mixed> The processed data
     */
    final protected function applyCreateConfigurationRules(array $data): array
    {
        return $this->applyEntitySpecificDefaults(data: $data);
    }

    /**
     * Apply configuration rules specific to update operation.
     *
     * @param array<string, mixed> $data The input data
     * @return array<string, mixed> The processed data
     */
    final protected function applyUpdateConfigurationRules(array $data): array
    {
        return $data;
    }

    /**
     * Apply entity-specific default values.
     *
     * @param array<string, mixed> $data The input data
     * @return array<string, mixed> The processed data
     */
    final protected function applyEntitySpecificDefaults(array $data): array
    {
        $entityType = $this->getEntityType();

        if ($entityType === 'schedule' && ! isset($data['status'])) {
            $data['status'] = Config::get(
                key: 'roster.schedule.default_status',
                default: 'available'
            );
        }

        return $data;
    }

    /**
     * Validate configuration rules.
     *
     * @param string $operation Either 'create' or 'update'
     * @throws ValidationException
     */
    final protected function validateConfigurationRules(string $operation): void
    {
        $entityType = $this->getEntityType();
        $entityConfig = Config::get(key: "roster.validate_future_dates.{$entityType}", default: []);
        $globalEnabled = Config::get(key: 'roster.validate_future_dates.enabled', default: true);
        $entityEnabled = $entityConfig['enabled'] ?? $globalEnabled;

        if ($entityEnabled) {
            $this->validateFutureDates(
                operation: $operation,
                entityType: $entityType,
                entityConfig: $entityConfig
            );
        }

        $this->validateDurations(operation: $operation);
        $this->validateGlobalConfigurationRules(operation: $operation);
    }

    /**
     * Validate future dates based on configuration.
     *
     * @param string $operation The operation being performed
     * @param string $entityType The type of entity
     * @param array<string, mixed> $entityConfig The entity-specific configuration
     * @throws ValidationException
     */
    final protected function validateFutureDates(string $operation, string $entityType, array $entityConfig): void
    {
        $fieldName = $entityConfig['validation_field'] ?? $this->getDefaultDateField(entityType: $entityType);

        if (! isset($this->data[$fieldName])) {
            return;
        }

        try {
            $date = Carbon::parse(time: $this->data[$fieldName]);

            if ($date->isPast() && ! ($entityConfig['allow_past'] ?? false)) {
                throw ValidationException::withMessage(
                    message: ErrorMessageFactory::pastDate(entity: $entityType, field: $fieldName)
                );
            }
        } catch (Exception $exception) {
            // Not a valid date, validation will be handled elsewhere
        }
    }

    /**
     * Validate durations based on configuration.
     *
     * @param string $operation The operation being performed
     */
    final protected function validateDurations(string $operation): void
    {
        $minImpediment = Config::get(
            key: 'roster.durations.minimum_impediment_minutes',
            default: 5
        );
        $minSchedule = Config::get(
            key: 'roster.durations.minimum_schedule_minutes',
            default: 15
        );
        $defaultDuration = Config::get(
            key: 'roster.durations.default_slot_duration_minutes',
            default: 60
        );

        $this->validateDurationHook(
            operation: $operation,
            minImpedimentMinutes: $minImpediment,
            minScheduleMinutes: $minSchedule,
            defaultDurationMinutes: $defaultDuration
        );
    }

    /**
     * Validate other global configuration rules.
     *
     * @param string $operation The operation being performed
     */
    final protected function validateGlobalConfigurationRules(string $operation): void
    {
        $maxDays = Config::get(
            key: 'roster.durations.max_search_period_days',
            default: 365
        );
        $this->validateMaxDaysHook(operation: $operation, maxDays: $maxDays);

        $timezone = $this->data['timezone'] ?? Config::get(key: 'roster.timezone', default: 'UTC');
        $this->validateTimezoneHook(timezone: $timezone);
    }

    /**
     * Get entity type from class name.
     *
     * @return string The entity type in lowercase
     */
    final protected function getEntityType(): string
    {
        $className = class_basename(class: static::class);

        return strtolower(string: str_replace(
            search: 'Service',
            replace: '',
            subject: $className
        ));
    }

    /**
     * Get entity display name.
     *
     * @return string The entity display name in title case
     */
    final protected function getEntityDisplayName(): string
    {
        return ucfirst(string: $this->getEntityType());
    }

    /**
     * Get default date field name based on entity type.
     *
     * @param string $entityType The entity type
     * @return string The default date field name
     */
    protected function getDefaultDateField(string $entityType): string
    {
        return match ($entityType) {
            'availability' => 'start_date',
            default => 'start_datetime',
        };
    }

    /**
     * Get date/time fields for the entity.
     *
     * @param string $entityType The entity type
     * @return array<string> The date/time field names
     */
    protected function getDateTimeFields(string $entityType): array
    {
        return match ($entityType) {
            'availability' => ['start_date', 'end_date', 'start_time', 'end_time'],
            'schedule', 'impediment' => ['start_datetime', 'end_datetime'],
            default => [],
        };
    }

    /**
     * Validate timezone for the entity.
     *
     * @param string $timezone The timezone to validate
     * @throws ValidationException
     */
    protected function validateTimezoneHook(string $timezone): void
    {
        $entityType = $this->getEntityType();
        $dateFields = $this->getDateTimeFields(entityType: $entityType);

        foreach ($dateFields as $dateField) {
            if (isset($this->data[$dateField])) {
                $validationService = $this->getValidationService();
                if (! $validationService->validateTimezone(timezone: $timezone)) {
                    throw ValidationException::withMessage(
                        message: "Invalid timezone: {$timezone}"
                    );
                }
                break;
            }
        }
    }

    /**
     * Clear entity cache.
     *
     * @param int $entityId The entity ID to clear cache for
     */
    protected function clearEntityCache(int $entityId): void
    {
        if (! Config::get(key: 'roster.cache.enabled', default: true)) {
            return;
        }

        $prefix = Config::get(key: 'roster.cache.prefix', default: 'roster_');
        $entityType = $this->getEntityType();
        $cacheKey = "{$prefix}{$entityType}_{$entityId}";

        Cache::forget(key: $cacheKey);

        if (Config::get(key: 'roster.cache.use_tags', default: true)) {
            Cache::tags(names: ["{$entityType}_{$entityId}"])->flush();
        }
    }

    /**
     * Validate common required fields.
     *
     * @param array<string> $requiredFields Additional required fields
     * @throws ValidationException
     */
    protected function validateRequiredFields(array $requiredFields = []): void
    {
        $entityType = $this->getEntityType();
        $configFields = Config::get(
            key: "roster.validation.required_fields.{$entityType}",
            default: []
        );
        $allRequired = array_unique(array: array_merge($configFields, $requiredFields));

        foreach ($allRequired as $field) {
            if (empty($this->data[$field] ?? null)) {
                throw ValidationException::withMessage(
                    message: "Field '{$field}' is required"
                );
            }
        }
    }

    /**
     * Throw a not found exception.
     *
     * @throws ValidationException
     */
    protected function throwNotFoundException(): void
    {
        throw ValidationException::withMessage(
            message: ErrorMessageFactory::notFound(entity: $this->getEntityType())
        );
    }

    /**
     * Throw an overlap exception.
     *
     * @throws ValidationException
     */
    protected function throwOverlapException(): void
    {
        throw ValidationException::withMessage(
            message: ErrorMessageFactory::overlap(entity: $this->getEntityType())
        );
    }

    /**
     * Throw a minimum duration exception.
     *
     * @param int $minutes The minimum required minutes
     * @throws ValidationException
     */
    protected function throwMinimumDurationException(int $minutes): void
    {
        throw ValidationException::withMessage(
            message: ErrorMessageFactory::minimumDuration(
                entity: $this->getEntityType(),
                minutes: $minutes
            )
        );
    }

    /**
     * After create hook.
     *
     * @param mixed $result The created entity
     */
    protected function afterCreate(mixed $result): void
    {
        if (method_exists(object_or_class: $result, method: 'getId') || property_exists(object_or_class: $result, property: 'id')) {
            $id = $result->id ?? $result->getId();
            $this->clearEntityCache(entityId: (int) $id);
        }
    }

    /**
     * After update hook.
     *
     * @param int $id The updated entity ID
     * @param bool $result Whether the update was successful
     */
    protected function afterUpdate(int $id, bool $result): void
    {
        if ($result) {
            $this->clearEntityCache(entityId: $id);
        }
    }

    /**
     * Validate duration hook.
     *
     * @param string $operation The operation being performed
     * @param int $minImpedimentMinutes Minimum duration for impediments
     * @param int $minScheduleMinutes Minimum duration for schedules
     * @param int $defaultDurationMinutes Default slot duration
     */
    abstract protected function validateDurationHook(
        string $operation,
        int $minImpedimentMinutes,
        int $minScheduleMinutes,
        int $defaultDurationMinutes
    ): void;

    /**
     * Validate max days hook.
     *
     * @param string $operation The operation being performed
     * @param int $maxDays Maximum allowed search period in days
     */
    abstract protected function validateMaxDaysHook(string $operation, int $maxDays): void;

    /**
     * Get validation service instance.
     *
     * @return ValidationServiceInterface
     */
    abstract protected function getValidationService(): ValidationServiceInterface;

    /**
     * Validate before create hook.
     */
    abstract protected function validateBeforeCreate(): void;

    /**
     * Process before create hook.
     */
    abstract protected function processBeforeCreate(): void;

    /**
     * Execute create operation.
     *
     * @return mixed The created entity
     */
    abstract protected function executeCreate(): mixed;

    /**
     * Validate before update hook.
     *
     * @param int $id The entity ID to update
     */
    abstract protected function validateBeforeUpdate(int $id): void;

    /**
     * Process before update hook.
     *
     * @param int $id The entity ID to update
     */
    abstract protected function processBeforeUpdate(int $id): void;

    /**
     * Execute update operation.
     *
     * @param int $id The entity ID to update
     * @return bool True if update was successful
     */
    abstract protected function executeUpdate(int $id): bool;

    /**
     * Apply filters to the query.
     *
     * @return Builder
     */
    abstract protected function buildQueryWithFilters(): Builder;
}
