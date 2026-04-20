<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use ReflectionClass;
use Roster\Config\RosterConfig;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;
use Illuminate\Support\Facades\Log;

/**
 * Abstract base class for validation rules with attribute-based configuration.
 *
 * Provides common functionality for rule priority, support detection,
 * and configuration retrieval through the ValidationRule attribute.
 */
abstract class AbstractRule implements RuleInterface
{
    /**
     * Retrieves the priority value for this validation rule.
     *
     * Priority determines execution order (lower numbers execute first).
     * Defaults to 50 if no priority specified in ValidationRule attribute.
     *
     * @return int Rule priority
     */
    public function getPriority(): int
    {
        $ruleAttribute = $this->getValidationRuleAttribute();

        return $ruleAttribute?->priority ?? RosterConfig::DEFAULT_RULE_PRIORITY;
    }

    /**
     * Returns a human-readable name for this validation rule.
     *
     * Uses the class basename without namespace as the rule name.
     *
     * @return string Rule name
     */
    public function getName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Returns a detailed description of what this rule validates and what it prevents.
     *
     * Default implementation returns a generic message. Child classes should override
     * this method to provide specific descriptions of their validation logic.
     *
     * @return string Detailed description of the rule's purpose
     */
    public function getDescription(): string
    {
        return sprintf(
            'Validates %s entity data according to business rules. ' .
                'This rule ensures data integrity and prevents invalid state transitions.',
            $this->getName()
        );
    }

    /**
     * Determines if this rule supports the given operation and entity type.
     *
     * Uses the ValidationRule attribute to check supported operations and entities.
     * Rules without the attribute support everything by default (should be overridden).
     *
     * @param OperationType $operationType Type of operation being validated
     * @param EntityType $entityType Type of entity being validated
     * @return bool True if rule supports the combination
     */
    public function supports(OperationType $operationType, EntityType $entityType): bool
    {
        $ruleAttribute = $this->getValidationRuleAttribute();

        if (!$ruleAttribute instanceof ValidationRule) {
            // Default behavior for rules without attribute
            return true;
        }

        return $this->isOperationSupported($operationType, $ruleAttribute)
            && $this->isEntitySupported($entityType, $ruleAttribute);
    }

    /**
     * Gets the minimum duration in minutes for an entity type.
     *
     * This method ensures the duration never goes below the absolute minimum
     * (10 minutes) for performance and safety reasons, regardless of configuration.
     *
     * @param EntityType $entityType Type of entity
     * @return int Minimum duration in minutes (guaranteed >= 10)
     */
    protected function getMinimumDuration(EntityType $entityType): int
    {
        $configuredMinutes = $this->getConfiguredMinimumDuration($entityType);

        // Force the absolute minimum - configuration cannot go below 10 minutes
        if ($configuredMinutes < RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES) {
            $this->logMinimumDurationOverride($entityType, $configuredMinutes);
            return RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES;
        }

        return $configuredMinutes;
    }

    /**
     * Gets the maximum number of days for date range validations.
     *
     * @return int Maximum days allowed
     */
    protected function getMaxDays(): int
    {
        return config('roster.durations.max_search_period_days', RosterConfig::MAX_DAYS_ITERATION);
    }

    /**
     * Determines if future dates should be validated.
     *
     * @return bool True to validate future dates
     */
    protected function shouldValidateFutureDates(): bool
    {
        return true;
    }

    /**
     * Determines if past dates are allowed.
     *
     * @return bool True to allow past dates
     */
    protected function allowPastDates(): bool
    {
        return false;
    }

    /**
     * Gets the default timezone for date validations.
     *
     * @return string Default timezone identifier
     */
    protected function getDefaultTimezone(): string
    {
        return config('roster.timezone', RosterConfig::DEFAULT_TIMEZONE);
    }

    /**
     * Retrieves the ValidationRule attribute instance for this class.
     *
     * Used by RuleScanner for rule indexing and metadata collection.
     *
     * @return ValidationRule|null Attribute instance or null
     */
    public function getValidationRuleAttribute(): ?ValidationRule
    {
        $reflectionClass = new ReflectionClass($this);
        $attributes = $reflectionClass->getAttributes(ValidationRule::class);

        return $attributes !== [] ? $attributes[0]->newInstance() : null;
    }

    /**
     * Get configured minimum duration from configuration for entity type.
     *
     * @param EntityType $entityType Type of entity
     * @return int Configured minimum duration in minutes
     */
    private function getConfiguredMinimumDuration(EntityType $entityType): int
    {
        return match ($entityType) {
            EntityType::AVAILABILITY => (int) config(
                'roster.durations.minimum_availability_minutes',
                RosterConfig::DEFAULT_MINIMUM_AVAILABILITY_MINUTES
            ),
            EntityType::SCHEDULE => (int) config(
                'roster.durations.minimum_schedule_minutes',
                RosterConfig::DEFAULT_MINIMUM_SCHEDULE_MINUTES
            ),
            EntityType::IMPEDIMENT => (int) config(
                'roster.durations.minimum_impediment_minutes',
                RosterConfig::DEFAULT_MINIMUM_IMPEDIMENT_MINUTES
            ),
        };
    }

    /**
     * Log a warning when minimum duration configuration is overridden.
     *
     * @param EntityType $entityType Entity type being validated
     * @param int $configuredMinutes The original configured value
     */
    private function logMinimumDurationOverride(EntityType $entityType, int $configuredMinutes): void
    {
        Log::warning('Minimum duration configuration overridden for performance reasons', [
            'entity_type' => $entityType->value,
            'configured_minutes' => $configuredMinutes,
            'enforced_minutes' => RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES,
            'reason' => 'Durations below 10 minutes would generate too many iterations and slow down the system',
        ]);
    }

    /**
     * Checks if an operation type is supported by the rule attribute.
     *
     * @param OperationType $operationType Operation to check
     * @param ValidationRule $validationRule Rule configuration attribute
     * @return bool True if operation supported
     */
    private function isOperationSupported(OperationType $operationType, ValidationRule $validationRule): bool
    {
        return in_array($operationType, $validationRule->operations, true);
    }

    /**
     * Checks if an entity type is supported by the rule attribute.
     *
     * @param EntityType $entityType Entity to check
     * @param ValidationRule $validationRule Rule configuration attribute
     * @return bool True if entity supported
     */
    private function isEntitySupported(EntityType $entityType, ValidationRule $validationRule): bool
    {
        return in_array($entityType, $validationRule->entities, true);
    }
}
