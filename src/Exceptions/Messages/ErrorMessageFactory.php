<?php

declare(strict_types=1);

namespace Roster\Exceptions\Messages;

/**
 * Factory for generating consistent error messages throughout the application.
 */
class ErrorMessageFactory
{
    /**
     * Create error message for date/time values in the past.
     *
     * @param string $entity Entity name (e.g., 'availability', 'shift')
     * @param string $field Field name (e.g., 'start_date', 'end_time')
     * @return string Formatted error message
     */
    public static function pastDate(string $entity, string $field): string
    {
        return sprintf(
            '%s %s cannot be in the past',
            ucfirst($entity),
            str_replace('_', ' ', $field)
        );
    }

    /**
     * Create error message for minimum duration requirement.
     *
     * @param string $entity Entity name
     * @param int $minutes Minimum required minutes
     * @return string Formatted error message
     */
    public static function minimumDuration(string $entity, int $minutes): string
    {
        return sprintf('%s must be at least %d minutes', ucfirst($entity), $minutes);
    }

    /**
     * Create error message for overlapping entities.
     *
     * @param string $entity Entity name
     * @return string Formatted error message
     */
    public static function overlap(string $entity): string
    {
        return sprintf('This %s overlaps with an existing one.', $entity);
    }

    /**
     * Create error message for entity not found.
     *
     * @param string $entity Entity name
     * @return string Formatted error message
     */
    public static function notFound(string $entity): string
    {
        return ucfirst($entity) . ' not found';
    }

    /**
     * Create error message for required field.
     *
     * @param string $field Field name
     * @return string Formatted error message
     */
    public static function requiredField(string $field): string
    {
        return sprintf("Field '%s' is required", $field);
    }

    /**
     * Create error message for invalid timezone.
     *
     * @param string $timezone Invalid timezone string
     * @return string Formatted error message
     */
    public static function invalidTimezone(string $timezone): string
    {
        return 'Invalid timezone: ' . $timezone;
    }
}
