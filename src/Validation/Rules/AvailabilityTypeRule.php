<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

#[ValidationRule(
    priority: 80,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityTypeRule extends AbstractRule
{
    /**
     * Validates that the type is allowed based on configuration.
     *
     * @param ValidationContextInterface $validationContext The validation context
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        if (!$validationContext->has('type')) {
            return;
        }

        $type = $validationContext->get('type');

        if ($type === null) {
            return;
        }

        $allowedTypes = config('roster.allowed_types', []);

        if ($allowedTypes === []) {
            return;
        }

        if (!in_array($type, $allowedTypes, true)) {
            $this->setTypeViolation(
                $validationContext,
                $type,
                $allowedTypes
            );
        }
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates that the availability type is among the configured allowed types. It ensures consistency in availability categorization by checking against the 'roster.allowed_types' configuration. When the configuration is empty, all types are permitted, allowing for flexible implementation based on specific use cases.";
    }

    /**
     * Sets a type violation with formatted error message.
     *
     * @param ValidationContextInterface $validationContext The validation context
     * @param string $type The invalid type value
     * @param array $allowedTypes The list of allowed types
     */
    private function setTypeViolation(
        ValidationContextInterface $validationContext,
        string $type,
        array $allowedTypes
    ): void {
        $maxPreview = 10;

        $previewTypes = array_slice($allowedTypes, 0, $maxPreview);
        $preview = implode(', ', $previewTypes);

        $suffix = count($allowedTypes) > $maxPreview
            ? ' (see more in configuration: roster.allowed_types)'
            : '';

        $validationContext->setViolationFromRule(
            rule: $this,
            field: 'type',
            message: sprintf(
                "Invalid type '%s'. Allowed types: %s%s",
                $type,
                $preview,
                $suffix
            )
        );
    }
}
