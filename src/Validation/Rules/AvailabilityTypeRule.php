<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates availability type against configured allowed types.
 *
 * Ensures that availability types match the predefined configuration
 * while handling partial updates gracefully.
 */
#[ValidationRule(
    priority: 80,
    entities: [EntityType::AVAILABILITY],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class AvailabilityTypeRule extends AbstractRule
{
    /**
     * Validates the availability type.
     *
     * @param ValidationContextInterface $validationContext Validation context with data
     * @return void
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
            $validationContext->setViolation(
                'type',
                sprintf("Invalid type '%s'", $type)
            );
        }
    }
}
