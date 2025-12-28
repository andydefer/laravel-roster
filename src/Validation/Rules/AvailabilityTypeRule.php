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

        $validationContext->setViolation(
            'type',
            sprintf(
                "Invalid type '%s'. Allowed types: %s%s",
                $type,
                $preview,
                $suffix
            )
        );
    }
}
