<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates timezone strings and datetime formats for roster entities.
 * Ensures timezones are valid and datetime fields are properly formatted.
 */
#[ValidationRule(
    priority: 30,
    entities: [EntityType::AVAILABILITY, EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class TimezoneValidationRule extends AbstractRule
{
    /**
     * Validate timezone and datetime fields in the validation context.
     *
     * @param ValidationContextInterface $validationContext The context containing data to validate
     * @return void
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        $this->validateTimezoneField($validationContext);
        $this->validateDateTimeFields($validationContext);
    }

    /**
     * Validate the timezone field if present in the context.
     *
     * @param ValidationContextInterface $context The validation context
     */
    private function validateTimezoneField(ValidationContextInterface $context): void
    {
        if (!$context->has('timezone')) {
            return;
        }

        $timezone = $context->get('timezone');

        if ($timezone !== null && !TimezoneHelper::isValidTimezone($timezone)) {
            $context->setViolation(
                field: 'timezone',
                message: sprintf("Invalid timezone: '%s'", $timezone)
            );
        }
    }

    /**
     * Validate datetime fields for proper format and timezone compatibility.
     *
     * @param ValidationContextInterface $context The validation context
     */
    private function validateDateTimeFields(ValidationContextInterface $context): void
    {
        $datetimeFields = ['start_datetime', 'end_datetime', 'validity_start', 'validity_end'];

        foreach ($datetimeFields as $field) {
            $this->validateSingleDateTimeField($context, $field);
        }
    }

    /**
     * Validate a single datetime field.
     *
     * @param ValidationContextInterface $context The validation context
     * @param string $field The field name to validate
     */
    private function validateSingleDateTimeField(ValidationContextInterface $context, string $field): void
    {
        if (!$context->has($field)) {
            return;
        }

        $value = $context->get($field);

        if ($value === null) {
            return;
        }

        try {
            \Carbon\Carbon::parse($value, TimezoneHelper::getEffectiveTimezone());
        } catch (\Exception $e) {
            $context->setViolation(
                field: $field,
                message: sprintf("Invalid datetime format or timezone for field '%s'", $field)
            );
        }
    }
}
