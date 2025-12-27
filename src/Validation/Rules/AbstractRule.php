<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use ReflectionClass;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

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
        return $ruleAttribute?->priority ?? 50;
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

        if ($ruleAttribute === null) {
            // Default behavior for rules without attribute
            return true;
        }

        return $this->isOperationSupported($operationType, $ruleAttribute)
            && $this->isEntitySupported($entityType, $ruleAttribute);
    }

    /**
     * Gets the minimum duration in minutes for an entity type.
     *
     * @param EntityType $entityType Type of entity
     * @return int Minimum duration in minutes
     */
    protected function getMinimumDuration(EntityType $entityType): int
    {
        return match ($entityType) {
            EntityType::AVAILABILITY => config('roster.durations.minimum_availability_minutes', 15),
            EntityType::SCHEDULE => config('roster.durations.minimum_schedule_minutes', 15),
            EntityType::IMPEDIMENT => config('roster.durations.minimum_impediment_minutes', 5),
        };
    }

    /**
     * Gets the maximum number of days for date range validations.
     *
     * @return int Maximum days allowed
     */
    protected function getMaxDays(): int
    {
        return config('roster.durations.max_search_period_days', 365);
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
        return config('roster.timezone', 'UTC');
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
     * Checks if an operation type is supported by the rule attribute.
     *
     * @param OperationType $operationType Operation to check
     * @param ValidationRule $ruleAttribute Rule configuration attribute
     * @return bool True if operation supported
     */
    private function isOperationSupported(OperationType $operationType, ValidationRule $ruleAttribute): bool
    {
        foreach ($ruleAttribute->operations as $supportedOperation) {
            if ($supportedOperation === $operationType) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if an entity type is supported by the rule attribute.
     *
     * @param EntityType $entityType Entity to check
     * @param ValidationRule $ruleAttribute Rule configuration attribute
     * @return bool True if entity supported
     */
    private function isEntitySupported(EntityType $entityType, ValidationRule $ruleAttribute): bool
    {
        foreach ($ruleAttribute->entities as $supportedEntity) {
            if ($supportedEntity === $entityType) {
                return true;
            }
        }
        return false;
    }
}
