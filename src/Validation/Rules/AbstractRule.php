<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use ReflectionClass;
use Roster\Contracts\Validation\RuleInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

abstract class AbstractRule implements RuleInterface
{
    public function getPriority(): int
    {
        // Vérifie si la classe a un attribut ValidationRule
        $reflectionClass = new ReflectionClass($this);
        $attributes = $reflectionClass->getAttributes(ValidationRule::class);

        if ($attributes !== []) {
            $attribute = $attributes[0]->newInstance();
            return $attribute->priority ?? 50;
        }

        return 50;
    }

    public function getName(): string
    {
        return class_basename(static::class);
    }

    public function supports(OperationType $operationType, EntityType $entityType): bool
    {
        // Vérifie si la classe a un attribut ValidationRule
        $reflectionClass = new ReflectionClass($this);
        $attributes = $reflectionClass->getAttributes(ValidationRule::class);

        if ($attributes !== []) {
            $attribute = $attributes[0]->newInstance();

            // Vérifie si l'opération est supportée
            $operationSupported = false;
            foreach ($attribute->operations as $supportedOperation) {
                if ($supportedOperation === $operationType) {
                    $operationSupported = true;
                    break;
                }
            }

            // Vérifie si l'entité est supportée
            $entitySupported = false;
            foreach ($attribute->entities as $supportedEntity) {
                if ($supportedEntity === $entityType) {
                    $entitySupported = true;
                    break;
                }
            }

            return $operationSupported && $entitySupported;
        }

        // Fallback pour les règles sans attribut
        // Par défaut, supporte tout (les enfants doivent surcharger)
        return true;
    }

    protected function getMinimumDuration(EntityType $entityType): int
    {
        return match ($entityType) {
            EntityType::AVAILABILITY => config('roster.durations.minimum_availability_minutes', 15),
            EntityType::SCHEDULE => config('roster.durations.minimum_schedule_minutes', 15),
            EntityType::IMPEDIMENT => config('roster.durations.minimum_impediment_minutes', 5),
        };
    }

    protected function getMaxDays(): int
    {
        return config('roster.durations.max_search_period_days', 365);
    }

    protected function shouldValidateFutureDates(): bool
    {
        return config('roster.validate_future_dates.enabled', true);
    }

    protected function allowPastDates(): bool
    {
        return config('roster.validation.allow_past_dates', false);
    }

    protected function getDefaultTimezone(): string
    {
        return config('roster.timezone', 'UTC');
    }

    /**
     * Helper method pour obtenir l'attribut ValidationRule
     * Utilisé par RuleScanner pour l'indexation
     */
    public function getValidationRuleAttribute(): ?ValidationRule
    {
        $reflectionClass = new ReflectionClass($this);
        $attributes = $reflectionClass->getAttributes(ValidationRule::class);

        if ($attributes !== []) {
            return $attributes[0]->newInstance();
        }

        return null;
    }
}
