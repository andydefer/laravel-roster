<?php

declare(strict_types=1);

namespace Roster\Domain\Helpers;

use Exception;
use Illuminate\Support\Carbon;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Timezone management helper for handling timezone conversions and validations.
 * Provides system-to-user timezone conversions and timezone validation.
 */
final class TimezoneHelper
{
    private static ?string $defaultTimezone = null;

    private static ?string $userTimezone = null;

    private const SYSTEM_TIMEZONE = 'UTC';

    private static bool $initialized = false;

    /**
     * Initialize the timezone helper with configuration values.
     * Reads timezone from roster.timezone or app.timezone config, defaults to UTC.
     *
     * @throws InvalidArgumentException When configured timezone is invalid
     */
    public static function initialize(): void
    {
        $configValue = config('roster.timezone') ?: config('app.timezone');

        if (empty($configValue)) {
            $configValue = self::SYSTEM_TIMEZONE;
        }

        if (!self::isValidTimezone($configValue)) {
            throw new InvalidArgumentException('Invalid timezone configured: ' . $configValue);
        }

        self::$defaultTimezone = self::normalizeTimezone($configValue);
        self::$initialized = true;
    }

    /**
     * Ensure the helper is initialized before performing operations.
     */
    private static function ensureInitialized(): void
    {
        if (!self::$initialized) {
            self::initialize();
        }
    }

    /**
     * Set the user's timezone for conversions.
     *
     * @param string|null $timezone The user's timezone identifier or null to use default
     * @throws InvalidArgumentException When provided timezone is invalid
     */
    public static function setUserTimezone(?string $timezone): void
    {
        self::ensureInitialized();

        if ($timezone !== null) {
            if (!self::isValidTimezone($timezone)) {
                throw new InvalidArgumentException('Invalid user timezone: ' . $timezone);
            }

            $timezone = self::normalizeTimezone($timezone);
        }

        self::$userTimezone = $timezone;
    }

    /**
     * Get the currently effective timezone (user timezone or default).
     *
     * @return string The effective timezone identifier
     */
    public static function getEffectiveTimezone(): string
    {
        self::ensureInitialized();
        return self::$userTimezone ?? self::$defaultTimezone ?? self::SYSTEM_TIMEZONE;
    }

    /**
     * Get the system timezone (always UTC).
     *
     * @return string System timezone identifier
     */
    public static function getSystemTimezone(): string
    {
        return self::SYSTEM_TIMEZONE;
    }

    /**
     * Convert datetime to system timezone (UTC).
     *
     * @param Carbon|string|null $datetime The datetime to convert
     * @return Carbon|null Datetime in system timezone or null if input is null
     */
    public static function toSystem(Carbon|string|null $datetime): ?Carbon
    {
        if ($datetime === null) {
            return null;
        }

        $effectiveTimezone = self::getEffectiveTimezone();

        if (!($datetime instanceof Carbon)) {
            $carbon = Carbon::parse($datetime, $effectiveTimezone);
        } else {
            $carbon = $datetime->copy();
            if ($carbon->getTimezone()->getName() === date_default_timezone_get()) {
                $carbon->setTimezone($effectiveTimezone);
            }
        }

        return $carbon->setTimezone(self::SYSTEM_TIMEZONE);
    }

    /**
     * Convert datetime to user's effective timezone.
     *
     * @param Carbon|string|null $datetime The datetime to convert
     * @return Carbon|null Datetime in user timezone or null if input is null
     */
    public static function toUser(Carbon|string|null $datetime): ?Carbon
    {
        if ($datetime === null) {
            return null;
        }

        $effectiveTimezone = self::getEffectiveTimezone();

        if (!($datetime instanceof Carbon)) {
            $carbon = Carbon::parse($datetime, self::SYSTEM_TIMEZONE);
        } else {
            $carbon = $datetime->copy();
            if ($carbon->getTimezone()->getName() === date_default_timezone_get()) {
                $carbon->setTimezone(self::SYSTEM_TIMEZONE);
            }
        }

        return $carbon->setTimezone($effectiveTimezone);
    }

    /**
     * Parse a datetime string in the user's effective timezone.
     *
     * @param string $datetime The datetime string to parse
     * @return Carbon Parsed Carbon instance in user timezone
     */
    public static function parseInUserTimezone(string $datetime): Carbon
    {
        return Carbon::parse($datetime, self::getEffectiveTimezone());
    }

    /**
     * Format datetime for display in user's timezone.
     *
     * @param Carbon|string|null $datetime The datetime to format
     * @param string $format The format string (default: 'Y-m-d H:i:s')
     * @return string|null Formatted datetime string or null if input is null
     */
    public static function formatForDisplay(Carbon|string|null $datetime, string $format = 'Y-m-d H:i:s'): ?string
    {
        if ($datetime === null) {
            return null;
        }

        return self::toUser($datetime)->format($format);
    }

    /**
     * Validate if a timezone identifier is valid.
     *
     * @param string $timezone The timezone identifier to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValidTimezone(string $timezone): bool
    {
        if ($timezone === '' || $timezone === '0') {
            return false;
        }

        try {
            new DateTimeZone($timezone);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Normalize a timezone identifier to canonical form.
     *
     * @param string $timezone The timezone identifier to normalize
     * @return string Normalized timezone identifier or SYSTEM_TIMEZONE if invalid
     */
    public static function normalizeTimezone(string $timezone): string
    {
        $all = DateTimeZone::listIdentifiers();
        $key = array_search(strtolower($timezone), array_map('strtolower', $all), true);
        return $key !== false ? $all[$key] : self::SYSTEM_TIMEZONE;
    }

    /**
     * Get all supported timezone identifiers.
     *
     * @return array List of all supported timezone identifiers
     */
    public static function getSupportedTimezones(): array
    {
        return DateTimeZone::listIdentifiers();
    }

    /**
     * Reset user timezone and internal state.
     * Primarily useful for testing.
     */
    public static function resetUserTimezone(): void
    {
        self::$userTimezone = null;
        self::$initialized = false;
        self::$defaultTimezone = null;
        Carbon::setTestNow();
    }
}
