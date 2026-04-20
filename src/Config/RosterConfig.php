<?php

declare(strict_types=1);

namespace Roster\Config;

/**
 * Centralized configuration value object for Roster package.
 * 
 * Provides a single source of truth for all performance-related constants
 * and configuration defaults used across the package.
 * 
 */
final class RosterConfig
{
    /**
     * Absolute minimum duration in minutes for any entity type.
     * This value cannot be overridden by configuration for performance and safety reasons.
     * Durations below this value would generate too many iterations and slow down the system.
     */
    public const ABSOLUTE_MIN_DURATION_MINUTES = 10;

    /**
     * Maximum iterations to prevent infinite loops in slot generation.
     */
    public const MAX_ITERATIONS = 10000;

    /**
     * Maximum number of days to iterate when searching for available slots.
     * Prevents infinite loops when no slots are found within a reasonable timeframe.
     */
    public const MAX_DAYS_ITERATION = 365;

    /**
     * Default slot interval in minutes when configuration is missing.
     */
    public const DEFAULT_SLOT_INTERVAL = 15;

    /**
     * Default priority value for rules without explicit priority attribute.
     * Lower values execute first.
     */
    public const DEFAULT_RULE_PRIORITY = 50;

    /**
     * Default timezone fallback when no configuration is set.
     */
    public const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Default values for minimum durations configuration.
     */
    public const DEFAULT_MINIMUM_AVAILABILITY_MINUTES = 15;
    public const DEFAULT_MINIMUM_SCHEDULE_MINUTES = 15;
    public const DEFAULT_MINIMUM_IMPEDIMENT_MINUTES = 5;

    /**
     * Private constructor to prevent instantiation.
     * This class is intended to be used as a constant container only.
     */
    private function __construct()
    {
        // Static class only
    }

    /**
     * Get the absolute minimum duration in minutes.
     *
     * @return int
     */
    public static function getAbsoluteMinDurationMinutes(): int
    {
        return self::ABSOLUTE_MIN_DURATION_MINUTES;
    }

    /**
     * Get the maximum iterations limit.
     *
     * @return int
     */
    public static function getMaxIterations(): int
    {
        return self::MAX_ITERATIONS;
    }

    /**
     * Get the maximum days iteration limit.
     *
     * @return int
     */
    public static function getMaxDaysIteration(): int
    {
        return self::MAX_DAYS_ITERATION;
    }

    /**
     * Get the default slot interval in minutes.
     *
     * @return int
     */
    public static function getDefaultSlotInterval(): int
    {
        return self::DEFAULT_SLOT_INTERVAL;
    }

    /**
     * Get the default rule priority.
     *
     * @return int
     */
    public static function getDefaultRulePriority(): int
    {
        return self::DEFAULT_RULE_PRIORITY;
    }

    /**
     * Get the default timezone.
     *
     * @return string
     */
    public static function getDefaultTimezone(): string
    {
        return self::DEFAULT_TIMEZONE;
    }
}
