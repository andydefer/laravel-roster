<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Roster\Contracts\Services\SchedulableServiceInterface;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Exceptions\ValidationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;

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

        return $this->applyFilters()->get();
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
     * TEMPLATE METHOD: Create with configuration validation
     *
     * @param array<string, mixed> $data
     */
    final public function create(array $data): mixed
    {
        $this->validateSchedulable();
        $this->data = $data;

        // 1. Apply configuration rules to data
        $this->data = $this->applyConfigurationRules($this->data, 'create');

        // 2. Validate configuration rules
        $this->validateConfigurationRules('create');

        // 3. Validate business rules (hook for children)
        $this->validateBeforeCreate();

        // 4. Process data (hook for children)
        $this->processBeforeCreate();

        // 5. Execute creation (abstract method)
        $result = $this->executeCreate();

        // 6. Post-creation hooks
        $this->afterCreate($result);

        return $result;
    }

    /**
     * TEMPLATE METHOD: Update with configuration validation
     *
     * @param array<string, mixed> $data
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
     * @param array<string, mixed> $data
     * @param string $operation 'create' or 'update'
     * @return array<string, mixed>
     */
    final protected function applyConfigurationRules(array $data, string $operation): array
    {
        // Set timezone if not provided
        if (!isset($data['timezone'])) {
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
        // Ensure future dates validation is respected
        if (Config::get('roster.validate_future_dates', true)) {
            $this->ensureFutureDatesForCreate($data);
        }

        return $data;
    }

    /**
     * Apply configuration rules specific to update operation
     */
    final protected function applyUpdateConfigurationRules(array $data): array
    {
        // Add update-specific rules here
        return $data;
    }

    /**
     * Validate configuration rules
     *
     * @param string $operation 'create' or 'update'
     * @throws ValidationException
     */
    final protected function validateConfigurationRules(string $operation): void
    {
        // 1. Validate future dates if enabled
        if (Config::get('roster.validate_future_dates', true)) {
            $this->validateFutureDates($operation);
        }

        // 2. Validate durations based on service type
        $this->validateDurations($operation);

        // 3. Validate other global configuration rules
        $this->validateGlobalConfigurationRules($operation);
    }

    /**
     * Validate future dates based on configuration
     */
    final protected function validateFutureDates(string $operation): void
    {
        $this->validateFutureDatesHook($operation);
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
        $maxDays = Config::get('roster.durations.max_days_to_check', 365);
        $this->validateMaxDaysHook($operation, $maxDays);

        // Validate timezone
        $timezone = $this->data['timezone'] ?? Config::get('roster.timezone', 'UTC');
        $this->validateTimezoneHook($timezone);
    }

    /**
     * Ensure future dates for create operation
     */
    final protected function ensureFutureDatesForCreate(array $data): void
    {
        // Check for date fields and ensure they're not in the past
        foreach ($data as $key => $value) {
            if (str_contains($key, 'date') || str_contains($key, 'datetime')) {
                try {
                    $date = Carbon::parse($value);
                    if ($date->isPast() && !$this->allowPastDatesForField($key)) {
                        throw ValidationException::withMessage(
                            sprintf("Field '%s' cannot be in the past", $key)
                        );
                    }
                } catch (Exception $e) {
                    // Not a date field, continue
                    continue;
                }
            }
        }
    }

    /**
     * Check if a field is allowed to have past dates
     */
    final protected function allowPastDatesForField(string $field): bool
    {
        // Some fields might be allowed to have past dates (e.g., historical data)
        $allowedPastDateFields = Config::get('roster.allowed_past_date_fields', []);

        return in_array($field, $allowedPastDateFields, true);
    }

    // ========== HOOK METHODS (to be implemented by children) ==========

    /**
     * Validate future dates hook
     */
    abstract protected function validateFutureDatesHook(string $operation): void;

    /**
     * Validate duration hook
     *
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
     * Validate max days hook
     */
    abstract protected function validateMaxDaysHook(string $operation, int $maxDays): void;

    /**
     * Validate timezone hook
     */
    abstract protected function validateTimezoneHook(string $timezone): void;

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
    abstract protected function afterCreate(mixed $result): void;

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
    abstract protected function afterUpdate(int $id, bool $result): void;

    /**
     * Apply filters to the query.
     *
     * @return Builder
     */
    abstract protected function applyFilters();
}
