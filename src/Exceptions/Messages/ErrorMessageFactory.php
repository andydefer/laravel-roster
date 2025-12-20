<?php

declare(strict_types=1);

namespace Roster\Exceptions\Messages;

class ErrorMessageFactory
{
    public static function pastDate(string $entity, string $field): string
    {
        return sprintf(
            '%s %s cannot be in the past',
            ucfirst($entity),
            str_replace('_', ' ', $field)
        );
    }

    public static function minimumDuration(string $entity, int $minutes): string
    {
        return sprintf('%s must be at least %d minutes', ucfirst($entity), $minutes);
    }

    public static function overlap(string $entity): string
    {
        return sprintf('This %s overlaps with an existing one.', $entity);
    }

    public static function notFound(string $entity): string
    {
        return ucfirst($entity).' not found';
    }

    public static function requiredField(string $field): string
    {
        return sprintf("Field '%s' is required", $field);
    }

    public static function invalidTimezone(string $timezone): string
    {
        return 'Invalid timezone: '.$timezone;
    }
}
