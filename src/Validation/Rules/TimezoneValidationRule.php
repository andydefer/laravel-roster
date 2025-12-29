<?php

declare(strict_types=1);

namespace Roster\Validation\Rules;

use Carbon\Carbon;
use Exception;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Attributes\ValidationRule;

/**
 * Validates timezone strings and datetime formats for roster entities.
 *
 * This rule ensures that:
 * - Timezone identifiers are valid (using TimezoneHelper::isValidTimezone)
 * - Datetime fields can be parsed correctly with the effective timezone
 * - Date-only fields (validity_start, validity_end) are properly formatted
 *
 * The validation applies to all datetime-related fields across availability,
 * schedule, and impediment entities for both CREATE and UPDATE operations.
 */
#[ValidationRule(
    priority: 30,
    entities: [EntityType::AVAILABILITY, EntityType::SCHEDULE, EntityType::IMPEDIMENT],
    operations: [OperationType::CREATE, OperationType::UPDATE]
)]
class TimezoneValidationRule extends AbstractRule
{
    /**
     * Array of datetime fields to validate across all entity types.
     */
    private const DATETIME_FIELDS = ['start_datetime', 'end_datetime', 'validity_start', 'validity_end'];

    /**
     * Validates timezone and datetime fields in the validation context.
     *
     * @param ValidationContextInterface $validationContext The context containing data to validate
     * @return void
     */
    public function validate(ValidationContextInterface $validationContext): void
    {
        // Cette règle ne s'applique qu'aux opérations CREATE et UPDATE
        // Pour DELETE, on ne valide pas les timezones et datetime
        if ($validationContext->getOperation() === OperationType::DELETE) {
            return;
        }

        $this->validateTimezoneField($validationContext);
        $this->validateDateTimeFields($validationContext);
    }

    /**
     * Returns a detailed description of what this rule validates.
     *
     * @return string Detailed description
     */
    public function getDescription(): string
    {
        return "This rule validates timezone identifiers and datetime field formats for roster entities. " .
            "It ensures that timezone strings are valid according to the PHP timezone database, " .
            "that datetime fields (start_datetime, end_datetime) can be parsed correctly with the " .
            "effective timezone context, and that date-only fields (validity_start, validity_end) " .
            "follow proper date formatting conventions. The rule supports both explicit timezone " .
            "specification in the data and fallback to configured or user timezones for parsing. " .
            "Note: This rule only applies to CREATE and UPDATE operations.";
    }

    /**
     * Validates the timezone field if present in the context.
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
            $context->setViolationFromRule(
                rule: $this,
                field: 'timezone',
                message: sprintf("Invalid timezone: '%s'", $timezone)
            );
        }
    }

    /**
     * Validates datetime fields for proper format and timezone compatibility.
     *
     * @param ValidationContextInterface $context The validation context
     */
    private function validateDateTimeFields(ValidationContextInterface $context): void
    {
        foreach (self::DATETIME_FIELDS as $field) {
            $this->validateSingleDateTimeField($context, $field);
        }
    }

    /**
     * Validates a single datetime field.
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
            Carbon::parse($value, TimezoneHelper::getEffectiveTimezone());
        } catch (Exception $exception) {
            $context->setViolationFromRule(
                rule: $this,
                field: $field,
                message: sprintf("Invalid datetime format or timezone for field '%s'", $field)
            );
        }
    }
}
