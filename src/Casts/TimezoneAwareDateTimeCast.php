<?php

declare(strict_types=1);

namespace Roster\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;
use Roster\Domain\Helpers\TimezoneHelper;

/**
 * Cast for datetime fields that automatically handles timezone conversion.
 *
 * This cast ensures that datetime values are always stored in UTC format
 * in the database and automatically converted to the user's timezone
 * when retrieved from the database.
 */
class TimezoneAwareDateTimeCast implements CastsAttributes
{
    /**
     * Convert the stored UTC datetime to the user's timezone.
     *
     * @param Model $model
     * @param mixed $value The UTC datetime string from database
     * @return Carbon|null Carbon instance in user timezone or null
     */
    public function get($model, string $key, $value, array $attributes): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $utcDateTime = Carbon::parse($value, TimezoneHelper::getSystemTimezone());

        return TimezoneHelper::toUser($utcDateTime);
    }

    /**
     * Convert the datetime value to UTC format for database storage.
     *
     * @param Model $model
     * @param mixed $value Carbon instance or datetime string
     * @return string|null UTC datetime string in 'Y-m-d H:i:s' format or null
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $dateTime = $value instanceof Carbon ? $value : Carbon::parse($value);
        $utcDateTime = TimezoneHelper::toSystem($dateTime);

        return $utcDateTime->format('Y-m-d H:i:s');
    }
}
