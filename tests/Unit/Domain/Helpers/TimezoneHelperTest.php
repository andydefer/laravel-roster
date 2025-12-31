<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Helpers;

use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Roster\Domain\Helpers\TimezoneHelper;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * Test suite for TimezoneHelper functionality.
 */
#[CoversClass(\Roster\Domain\Helpers\TimezoneHelper::class)]
final class TimezoneHelperTest extends TestCase
{
    /**
     * Set up the test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        TimezoneHelper::resetUserTimezone();
        Config::set('app.timezone', 'UTC');
        Config::set('roster.timezone', 'Europe/Paris');
    }

    /**
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        TimezoneHelper::resetUserTimezone();
        parent::tearDown();
    }

    /**
     * Test initialization with a valid timezone configuration.
     */
    public function test_initialize_with_valid_timezone(): void
    {
        // Arrange: Set valid timezone configuration
        Config::set('roster.timezone', 'America/New_York');

        // Act: Initialize the helper
        TimezoneHelper::initialize();

        // Assert: Effective timezone should match configuration
        $this->assertSame('America/New_York', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test that initialization throws exception with invalid timezone.
     */
    public function test_initialize_throws_with_invalid_timezone(): void
    {
        // Arrange: Set invalid timezone configuration
        Config::set('roster.timezone', 'Invalid/Timezone');

        // Assert: Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid timezone configured');

        // Act: Initialize should throw
        TimezoneHelper::initialize();
    }

    /**
     * Test setting valid user timezone.
     */
    public function test_set_user_timezone_with_valid_timezone(): void
    {
        // Arrange: Initialize helper
        TimezoneHelper::initialize();

        // Act: Set user timezone
        TimezoneHelper::setUserTimezone('Asia/Tokyo');

        // Assert: Effective timezone should be user timezone
        $this->assertSame('Asia/Tokyo', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test that setting invalid user timezone throws exception.
     */
    public function test_set_user_timezone_throws_with_invalid_timezone(): void
    {
        // Arrange: Initialize helper
        TimezoneHelper::initialize();

        // Assert: Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user timezone');

        // Act: Set invalid timezone should throw
        TimezoneHelper::setUserTimezone('Invalid/Timezone');
    }

    /**
     * Test setting user timezone to null resets to default.
     */
    public function test_set_user_timezone_with_null(): void
    {
        // Arrange: Initialize and set user timezone
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Asia/Tokyo');

        // Act: Reset user timezone to null
        TimezoneHelper::setUserTimezone(null);

        // Assert: Should fall back to default timezone
        $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test timezone priority (user timezone overrides default).
     */
    public function test_get_effective_timezone_priority(): void
    {
        // Arrange: Initialize helper
        TimezoneHelper::initialize();

        // Assert: Default timezone from config
        $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());

        // Act: Set user timezone
        TimezoneHelper::setUserTimezone('America/Chicago');

        // Assert: User timezone takes priority
        $this->assertSame('America/Chicago', TimezoneHelper::getEffectiveTimezone());

        // Act: Reset and reinitialize
        TimezoneHelper::resetUserTimezone();
        TimezoneHelper::initialize();

        // Assert: Back to default timezone
        $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test system timezone constant.
     */
    public function test_get_system_timezone(): void
    {
        $this->assertSame('UTC', TimezoneHelper::getSystemTimezone());
    }

    /**
     * Test conversion from user timezone to system timezone.
     */
    public function test_to_system_conversion(): void
    {
        // Arrange: Set user timezone to New York
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('America/New_York');
        $userTime = '2024-01-01 09:00:00';

        // Act: Convert to system timezone
        $systemTime = TimezoneHelper::toSystem($userTime);

        // Assert: Should be in UTC and correctly converted
        $this->assertInstanceOf(Carbon::class, $systemTime);
        $this->assertSame('UTC', $systemTime->timezoneName);
        $this->assertSame('2024-01-01 14:00:00', $systemTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test conversion from Carbon instance to system timezone.
     */
    public function test_to_system_conversion_with_carbon_instance(): void
    {
        // Arrange: Set user timezone and create Carbon instance
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('America/New_York');
        $carbon = Carbon::parse('2024-01-01 09:00:00', 'America/New_York');

        // Act: Convert to system timezone
        $systemTime = TimezoneHelper::toSystem($carbon);

        // Assert: Should be in UTC and correctly converted
        $this->assertSame('UTC', $systemTime->timezoneName);
        $this->assertSame('2024-01-01 14:00:00', $systemTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test conversion from Carbon instance without explicit timezone.
     */
    public function test_to_system_conversion_with_carbon_instance_without_timezone(): void
    {
        // Arrange: Set user timezone and create Carbon instance with user timezone
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('America/New_York');
        $carbon = Carbon::parse('2024-01-01 09:00:00', 'America/New_York');

        // Act: Convert to system timezone
        $systemTime = TimezoneHelper::toSystem($carbon);

        // Assert: Should be in UTC and correctly converted
        $this->assertSame('UTC', $systemTime->timezoneName);
        $this->assertSame('2024-01-01 14:00:00', $systemTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test conversion to system timezone with null input.
     */
    public function test_to_system_conversion_with_null(): void
    {
        $this->assertNotInstanceOf(Carbon::class, TimezoneHelper::toSystem(null));
    }

    /**
     * Test conversion from system timezone to user timezone.
     */
    public function test_to_user_conversion(): void
    {
        // Arrange: Set user timezone to New York
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('America/New_York');
        $systemTime = '2024-01-01 14:00:00'; // UTC

        // Act: Convert to user timezone
        $userTime = TimezoneHelper::toUser($systemTime);

        // Assert: Should be in New York timezone and correctly converted
        $this->assertInstanceOf(Carbon::class, $userTime);
        $this->assertSame('America/New_York', $userTime->timezoneName);
        $this->assertSame('2024-01-01 09:00:00', $userTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test conversion from Carbon instance to user timezone.
     */
    public function test_to_user_conversion_with_carbon_instance(): void
    {
        // Arrange: Set user timezone and create UTC Carbon instance
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Europe/Paris');
        $carbon = Carbon::parse('2024-01-01 14:00:00', 'UTC');

        // Act: Convert to user timezone
        $userTime = TimezoneHelper::toUser($carbon);

        // Assert: Should be in Paris timezone and correctly converted
        $this->assertSame('Europe/Paris', $userTime->timezoneName);
        $this->assertSame('2024-01-01 15:00:00', $userTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test conversion to user timezone with null input.
     */
    public function test_to_user_conversion_with_null(): void
    {
        $this->assertNotInstanceOf(Carbon::class, TimezoneHelper::toUser(null));
    }

    /**
     * Test parsing datetime string in user timezone.
     */
    public function test_parse_in_user_timezone(): void
    {
        // Arrange: Set user timezone to Tokyo
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Asia/Tokyo');

        // Act: Parse datetime in user timezone
        $parsed = TimezoneHelper::parseInUserTimezone('2024-01-01 09:00:00');

        // Assert: Should be parsed in Tokyo timezone
        $this->assertInstanceOf(Carbon::class, $parsed);
        $this->assertSame('Asia/Tokyo', $parsed->timezoneName);
        $this->assertSame('2024-01-01 09:00:00', $parsed->format('Y-m-d H:i:s'));
    }

    /**
     * Test formatting datetime for display in user timezone.
     */
    public function test_format_for_display(): void
    {
        // Arrange: Set user timezone to Los Angeles
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('America/Los_Angeles');
        $systemTime = '2024-01-01 17:00:00'; // UTC

        // Act: Format for display in user timezone
        $displayTime = TimezoneHelper::formatForDisplay($systemTime);

        // Assert: Should display in Los Angeles timezone (UTC-8)
        $this->assertSame('2024-01-01 09:00:00', $displayTime);
    }

    /**
     * Test formatting datetime with custom format.
     */
    public function test_format_for_display_with_custom_format(): void
    {
        // Arrange: Set user timezone to London
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Europe/London');
        $systemTime = '2024-01-01 12:00:00'; // UTC

        // Act: Format with custom format
        $displayTime = TimezoneHelper::formatForDisplay($systemTime, 'H:i, j M Y');

        // Assert: Should use custom format in user timezone
        $this->assertSame('12:00, 1 Jan 2024', $displayTime);
    }

    /**
     * Test formatting null datetime.
     */
    public function test_format_for_display_with_null(): void
    {
        $this->assertNull(TimezoneHelper::formatForDisplay(null));
    }

    /**
     * Test timezone validation.
     */
    public function test_is_valid_timezone(): void
    {
        // Assert: Valid timezones should return true
        $this->assertTrue(TimezoneHelper::isValidTimezone('UTC'));
        $this->assertTrue(TimezoneHelper::isValidTimezone('Europe/Paris'));
        $this->assertTrue(TimezoneHelper::isValidTimezone('America/New_York'));
        $this->assertTrue(TimezoneHelper::isValidTimezone('Asia/Tokyo'));

        // Assert: Invalid timezones should return false
        $this->assertFalse(TimezoneHelper::isValidTimezone(''));
        $this->assertFalse(TimezoneHelper::isValidTimezone('Invalid/Timezone'));
        $this->assertFalse(TimezoneHelper::isValidTimezone('Unknown'));
        $this->assertFalse(TimezoneHelper::isValidTimezone('Europe/UnknownCity'));
    }

    /**
     * Test timezone normalization to canonical form.
     */
    public function test_normalize_timezone(): void
    {
        // Assert: Valid timezones stay the same
        $this->assertSame('UTC', TimezoneHelper::normalizeTimezone('UTC'));
        $this->assertSame('Europe/Paris', TimezoneHelper::normalizeTimezone('Europe/Paris'));

        // Assert: Case variations normalize to proper case
        $this->assertSame('America/New_York', TimezoneHelper::normalizeTimezone('america/new_york'));

        // Assert: Invalid timezones fall back to UTC
        $this->assertSame('UTC', TimezoneHelper::normalizeTimezone('Invalid/Timezone'));
    }

    /**
     * Test retrieval of all supported timezones.
     */
    public function test_get_supported_timezones(): void
    {
        // Act: Get supported timezones
        $timezones = TimezoneHelper::getSupportedTimezones();

        // Assert: Should return non-empty array with common timezones
        $this->assertIsArray($timezones);
        $this->assertNotEmpty($timezones);
        $this->assertContains('UTC', $timezones);
        $this->assertContains('Europe/Paris', $timezones);
        $this->assertContains('America/New_York', $timezones);
        $this->assertContains('Asia/Tokyo', $timezones);
    }

    /**
     * Test resetting user timezone.
     */
    public function test_reset_user_timezone(): void
    {
        // Arrange: Initialize and set user timezone
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Asia/Tokyo');

        // Assert: Pre-condition - user timezone is set
        $this->assertSame('Asia/Tokyo', TimezoneHelper::getEffectiveTimezone());

        // Act: Reset user timezone
        TimezoneHelper::resetUserTimezone();

        // Act: Reinitialize and check default
        TimezoneHelper::initialize();

        // Assert: Should be back to default timezone
        $this->assertSame('Europe/Paris', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test DST transition handling during switch.
     */
    public function test_dst_transition_handling(): void
    {
        // Arrange: Set user timezone to Paris and test DST switch time
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Europe/Paris');
        $utcTime = '2024-03-31 01:30:00'; // During DST switch

        // Act: Convert to user timezone
        $userTime = TimezoneHelper::toUser($utcTime);

        // Assert: Should be in UTC+2 during DST
        $this->assertSame('Europe/Paris', $userTime->timezoneName);
        $this->assertSame('2024-03-31 03:30:00', $userTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test DST transition handling after switch.
     */
    public function test_dst_transition_handling_after_switch(): void
    {
        // Arrange: Set user timezone to Paris, after DST switch
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Europe/Paris');
        $utcTime = '2024-04-01 12:00:00'; // After DST switch

        // Act: Convert to user timezone
        $userTime = TimezoneHelper::toUser($utcTime);

        // Assert: Should be in UTC+2 (DST)
        $this->assertSame('Europe/Paris', $userTime->timezoneName);
        $this->assertSame('2024-04-01 14:00:00', $userTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test timezone without DST.
     */
    public function test_timezone_without_dst(): void
    {
        // Arrange: Set user timezone to Singapore (no DST)
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('Asia/Singapore');
        $utcTime = '2024-06-01 12:00:00';

        // Act: Convert to user timezone
        $userTime = TimezoneHelper::toUser($utcTime);

        // Assert: Singapore is always UTC+8
        $this->assertSame('2024-06-01 20:00:00', $userTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test with empty configuration timezones.
     */
    public function test_with_empty_config_timezone(): void
    {
        // Arrange: Clear both timezone configurations
        Config::set('roster.timezone', '');
        Config::set('app.timezone', '');

        // Act: Initialize helper
        TimezoneHelper::initialize();

        // Assert: Should default to UTC
        $this->assertSame('UTC', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test with only app timezone configured.
     */
    public function test_with_only_app_timezone_configured(): void
    {
        // Arrange: Clear roster timezone, set app timezone
        Config::set('roster.timezone');
        Config::set('app.timezone', 'Australia/Sydney');

        // Act: Initialize helper
        TimezoneHelper::initialize();

        // Assert: Should use app timezone
        $this->assertSame('Australia/Sydney', TimezoneHelper::getEffectiveTimezone());
    }

    /**
     * Test automatic initialization when accessing methods.
     */
    public function test_auto_initialization(): void
    {
        // Arrange: Reset helper to uninitialized state
        TimezoneHelper::resetUserTimezone();

        // Act: Access method that should auto-initialize
        $timezone = TimezoneHelper::getEffectiveTimezone();

        // Assert: Should be initialized with default timezone
        $this->assertSame('Europe/Paris', $timezone);
    }

    /**
     * Test conversion chain: user → system → user.
     */
    public function test_conversion_chain(): void
    {
        // Arrange: Set user timezone to New York
        TimezoneHelper::initialize();
        TimezoneHelper::setUserTimezone('America/New_York');
        $originalTime = '2024-01-01 09:00:00';

        // Act: Convert to system then back to user timezone
        $systemTime = TimezoneHelper::toSystem($originalTime);
        $backToUser = TimezoneHelper::toUser($systemTime);

        // Assert: Should return to original time
        $this->assertSame('UTC', $systemTime->timezoneName);
        $this->assertSame('America/New_York', $backToUser->timezoneName);
        $this->assertSame($originalTime, $backToUser->format('Y-m-d H:i:s'));
    }
}
