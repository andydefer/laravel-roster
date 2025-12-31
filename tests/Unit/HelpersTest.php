<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Tests\TestCase;

/**
 * Unit tests for Roster package helper functions.
 *
 * Tests all helper functions including date calculations, day formatting,
 * timezone helpers, and service factory functions.
 */
final class HelpersTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        TimezoneHelper::resetUserTimezone();
        Config::set('app.timezone', 'UTC');
        Config::set('roster.timezone', 'Europe/Paris');
        TimezoneHelper::initialize();
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        TimezoneHelper::resetUserTimezone();
        parent::tearDown();
    }

    /**
     * Test roster_day_of_week function returns correct day names.
     */
    public function test_roster_day_of_week(): void
    {
        // Arrange: Test with specific dates
        $this->assertSame('thursday', roster_day_of_week('2038-07-01'));
        $this->assertSame('friday', roster_day_of_week('2038-07-02'));
        $this->assertSame('saturday', roster_day_of_week('2038-07-03'));
        $this->assertSame('sunday', roster_day_of_week('2038-07-04'));

        // Act & Assert: Test with DateTime object
        $date = Carbon::parse('2038-07-01');
        $this->assertSame('thursday', roster_day_of_week($date));

        // Act & Assert: Test with invalid date
        $this->assertNull(roster_day_of_week('invalid-date'));
    }

    /**
     * Test roster_format_period_days_for_display formats consecutive days as ranges.
     */
    public function test_roster_format_period_days_for_display(): void
    {
        // Assert: Continuous sequences
        $this->assertSame('Thursday to Sunday', roster_format_period_days_for_display(['thursday', 'friday', 'saturday', 'sunday']));
        $this->assertSame('Monday to Wednesday', roster_format_period_days_for_display(['monday', 'tuesday', 'wednesday']));

        // Assert: Non-consecutive days across weekend
        $this->assertSame('Monday, Saturday and Sunday', roster_format_period_days_for_display(['saturday', 'sunday', 'monday']));

        // Assert: Non-consecutive weekdays
        $this->assertSame('Monday, Wednesday and Friday', roster_format_period_days_for_display(['monday', 'wednesday', 'friday']));
        $this->assertSame('Monday and Thursday', roster_format_period_days_for_display(['monday', 'thursday']));

        // Assert: Single day
        $this->assertSame('Monday', roster_format_period_days_for_display(['monday']));

        // Assert: All days (continuous range)
        $this->assertSame('Monday to Sunday', roster_format_period_days_for_display(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']));

        // Assert: All days starting with Sunday
        $this->assertSame('Monday to Sunday', roster_format_period_days_for_display(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']));
    }

    /**
     * Test roster_days_in_period returns all days between two dates.
     */
    public function test_roster_days_in_period(): void
    {
        // Arrange: 4-day period (Thursday to Sunday)
        $startDate = '2038-07-01';
        $endDate = '2038-07-04';

        // Act: Get days in period
        $days = roster_days_in_period($startDate, $endDate);

        // Assert: All expected days present
        $expectedDays = ['thursday', 'friday', 'saturday', 'sunday'];
        $this->assertEquals(
            $this->sortDays($expectedDays),
            $this->sortDays($days)
        );

        // Assert: Single day period
        $this->assertSame(['thursday'], roster_days_in_period('2038-07-01', '2038-07-01'));

        // Assert: 7-day period (all days)
        $days = roster_days_in_period('2038-07-01', '2038-07-07');
        $expectedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $this->assertEquals(
            $this->sortDays($expectedDays),
            $this->sortDays($days)
        );

        // Assert: Period crossing weekend
        $days = roster_days_in_period('2038-07-03', '2038-07-05');
        $expectedDays = ['saturday', 'sunday', 'monday'];
        $this->assertEquals(
            $this->sortDays($expectedDays),
            $this->sortDays($days)
        );

        // Assert: Invalid dates
        $this->assertSame([], roster_days_in_period('invalid', '2038-07-01'));
    }

    /**
     * Test roster_format_days_for_display formats day lists with proper grammar.
     */
    public function test_roster_format_days_for_display(): void
    {
        // Assert: Single day
        $this->assertSame('Monday', roster_format_days_for_display(['monday']));

        // Assert: Two days
        $this->assertSame('Monday and Tuesday', roster_format_days_for_display(['monday', 'tuesday']));

        // Assert: Three days
        $this->assertSame('Monday, Tuesday and Wednesday', roster_format_days_for_display(['monday', 'tuesday', 'wednesday']));

        // Assert: Four days
        $this->assertSame('Monday, Tuesday, Wednesday and Thursday', roster_format_days_for_display(['monday', 'tuesday', 'wednesday', 'thursday']));

        // Assert: Empty array
        $this->assertSame('', roster_format_days_for_display([]));
    }

    /**
     * Test roster_period_duration_in_days calculates correct duration.
     */
    public function test_roster_period_duration_in_days(): void
    {
        // Assert: Single day
        $this->assertSame(1, roster_period_duration_in_days('2038-07-01', '2038-07-01'));

        // Assert: 4 days
        $this->assertSame(4, roster_period_duration_in_days('2038-07-01', '2038-07-04'));

        // Assert: 7 days
        $this->assertSame(7, roster_period_duration_in_days('2038-07-01', '2038-07-07'));

        // Assert: 10 days
        $this->assertSame(10, roster_period_duration_in_days('2038-07-01', '2038-07-10'));

        // Assert: Invalid start date
        $this->assertNull(roster_period_duration_in_days('invalid', '2038-07-01'));
    }

    /**
     * Test roster_is_day_in_period checks if day falls within date range.
     */
    public function test_roster_is_day_in_period(): void
    {
        $startDate = '2038-07-01';
        $endDate = '2038-07-04';

        // Assert: Days within period
        $this->assertTrue(roster_is_day_in_period('thursday', $startDate, $endDate));
        $this->assertTrue(roster_is_day_in_period('friday', $startDate, $endDate));
        $this->assertTrue(roster_is_day_in_period('saturday', $startDate, $endDate));
        $this->assertTrue(roster_is_day_in_period('sunday', $startDate, $endDate));

        // Assert: Days outside period
        $this->assertFalse(roster_is_day_in_period('monday', $startDate, $endDate));
        $this->assertFalse(roster_is_day_in_period('tuesday', $startDate, $endDate));
        $this->assertFalse(roster_is_day_in_period('wednesday', $startDate, $endDate));

        // Assert: Single day period
        $this->assertTrue(roster_is_day_in_period('thursday', '2038-07-01', '2038-07-01'));
        $this->assertFalse(roster_is_day_in_period('friday', '2038-07-01', '2038-07-01'));
    }

    /**
     * Test roster_get_valid_days_in_period filters and sorts days within date range.
     */
    public function test_roster_get_valid_days_in_period(): void
    {
        $startDate = '2038-07-01';
        $endDate = '2038-07-04';
        $inputDays = ['monday', 'thursday', 'friday', 'sunday'];

        // Act: Get valid days in period
        $validDays = roster_get_valid_days_in_period($inputDays, $startDate, $endDate);

        // Assert: Only days within period, sorted by weekday order
        $expectedDays = ['thursday', 'friday', 'sunday'];
        $this->assertSame($expectedDays, $validDays);

        // Assert: All days are valid
        $inputDays = ['thursday', 'friday', 'saturday', 'sunday'];
        $validDays = roster_get_valid_days_in_period($inputDays, $startDate, $endDate);
        $this->assertSame($inputDays, $validDays);

        // Assert: No valid days
        $inputDays = ['monday', 'tuesday', 'wednesday'];
        $validDays = roster_get_valid_days_in_period($inputDays, '2038-07-01', '2038-07-01');
        $this->assertSame([], $validDays);

        // Assert: Proper sorting with custom order
        $inputDays = ['sunday', 'thursday', 'friday', 'saturday'];
        $validDays = roster_get_valid_days_in_period($inputDays, $startDate, $endDate);
        $expectedDays = ['thursday', 'friday', 'saturday', 'sunday'];
        $this->assertSame($expectedDays, $validDays);
    }

    /**
     * Test roster_should_auto_adjust_days determines when to auto-adjust days based on period length.
     */
    public function test_roster_should_auto_adjust_days(): void
    {
        // Assert: Periods less than 7 days
        $this->assertTrue(roster_should_auto_adjust_days('2038-07-01', '2038-07-04'));
        $this->assertTrue(roster_should_auto_adjust_days('2038-07-01', '2038-07-01'));
        $this->assertTrue(roster_should_auto_adjust_days('2038-07-01', '2038-07-06'));

        // Assert: Periods of 7 days or more
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', '2038-07-07'));
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', '2038-07-10'));
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', '2038-07-14'));

        // Assert: Invalid dates
        $this->assertFalse(roster_should_auto_adjust_days(null, '2038-07-01'));
        $this->assertFalse(roster_should_auto_adjust_days('2038-07-01', null));
        $this->assertFalse(roster_should_auto_adjust_days('invalid', '2038-07-01'));

        // Assert: DateTime objects
        $start = Carbon::parse('2038-07-01');
        $end = Carbon::parse('2038-07-04');
        $this->assertTrue(roster_should_auto_adjust_days($start, $end));

        $end = Carbon::parse('2038-07-07');
        $this->assertFalse(roster_should_auto_adjust_days($start, $end));
    }

    /**
     * Test roster_get_valid_days_in_period removes duplicates.
     */
    public function test_roster_get_valid_days_in_period_with_duplicates(): void
    {
        // Arrange: Input with duplicate days
        $inputDays = ['monday', 'monday', 'thursday', 'thursday'];
        $startDate = '2038-07-01';
        $endDate = '2038-07-04';

        // Act: Get valid days
        $validDays = roster_get_valid_days_in_period($inputDays, $startDate, $endDate);

        // Assert: Duplicates removed, only valid days returned
        $expectedDays = ['thursday'];
        $this->assertSame($expectedDays, $validDays);
    }

    /**
     * Test roster_timezone helper returns current effective timezone.
     */
    public function test_roster_timezone(): void
    {
        // Assert: Default timezone
        $this->assertSame('Europe/Paris', roster_timezone());

        // Arrange: Set user timezone
        TimezoneHelper::setUserTimezone('America/New_York');

        // Assert: User timezone should be used
        $this->assertSame('America/New_York', roster_timezone());
    }

    /**
     * Test roster_to_utc helper converts to UTC.
     */
    public function test_roster_to_utc(): void
    {
        // Arrange: Set user timezone and create user time
        TimezoneHelper::setUserTimezone('America/New_York');
        $userTime = '2024-01-01 09:00:00';

        // Act: Convert to UTC
        $utcTime = roster_to_utc($userTime);

        // Assert: Correct UTC time returned
        $this->assertInstanceOf(Carbon::class, $utcTime);
        $this->assertSame('UTC', $utcTime->timezoneName);
        $this->assertSame('2024-01-01 14:00:00', $utcTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test roster_to_utc with null.
     */
    public function test_roster_to_utc_with_null(): void
    {
        $this->assertNotInstanceOf(Carbon::class, roster_to_utc(null));
    }

    /**
     * Test roster_to_user_timezone helper converts to user timezone.
     */
    public function test_roster_to_user_timezone(): void
    {
        // Arrange: Set user timezone and create UTC time
        TimezoneHelper::setUserTimezone('Asia/Tokyo');
        $utcTime = '2024-01-01 12:00:00';

        // Act: Convert to user timezone
        $userTime = roster_to_user_timezone($utcTime);

        // Assert: Correct local time returned
        $this->assertInstanceOf(Carbon::class, $userTime);
        $this->assertSame('Asia/Tokyo', $userTime->timezoneName);
        $this->assertSame('2024-01-01 21:00:00', $userTime->format('Y-m-d H:i:s'));
    }

    /**
     * Test roster_to_user_timezone with null.
     */
    public function test_roster_to_user_timezone_with_null(): void
    {
        $this->assertNotInstanceOf(Carbon::class, roster_to_user_timezone(null));
    }

    /**
     * Test roster_format_local helper formats in user timezone.
     */
    public function test_roster_format_local(): void
    {
        // Arrange: Set user timezone and create UTC time
        TimezoneHelper::setUserTimezone('Europe/London');
        $utcTime = '2024-01-01 12:00:00';

        // Act: Format in local timezone
        $formatted = roster_format_local($utcTime);

        // Assert: Correct local format
        $this->assertSame('2024-01-01 12:00:00', $formatted);
    }

    /**
     * Test roster_format_local with custom format.
     */
    public function test_roster_format_local_with_custom_format(): void
    {
        // Arrange: Set user timezone and create UTC time
        TimezoneHelper::setUserTimezone('America/Chicago');
        $utcTime = '2024-06-01 17:00:00';

        // Act: Format with custom format
        $formatted = roster_format_local($utcTime, 'H:i, M j, Y');

        // Assert: Correct custom format
        $this->assertSame('12:00, Jun 1, 2024', $formatted);
    }

    /**
     * Test roster_format_local with null.
     */
    public function test_roster_format_local_with_null(): void
    {
        $this->assertNull(roster_format_local(null));
    }

    /**
     * Test schedule_for returns ScheduleService instance.
     */
    public function test_schedule_for_returns_service(): void
    {
        // Arrange: Create availability with schedulable
        $availability = new Availability();
        $availability->schedulable = $this->createMockSchedulable();

        // Act: Get schedule service
        $service = schedule_for($availability);

        // Assert: Correct service type returned
        $this->assertInstanceOf(ScheduleService::class, $service);
    }

    /**
     * Test schedule_for throws exception when schedulable is missing.
     */
    public function test_schedule_for_throws_if_no_schedulable(): void
    {
        // Arrange: Create availability without schedulable
        $availability = new Availability();
        $availability->schedulable = null;

        // Assert: Exception expected
        $this->expectException(InvalidArgumentException::class);

        // Act: Attempt to get service
        schedule_for($availability);
    }

    /**
     * Test impediment_for returns ImpedimentService instance.
     */
    public function test_impediment_for_returns_service(): void
    {
        // Arrange: Create availability with schedulable
        $availability = new Availability();
        $availability->schedulable = $this->createMockSchedulable();

        // Act: Get impediment service
        $service = impediment_for($availability);

        // Assert: Correct service type returned
        $this->assertInstanceOf(ImpedimentService::class, $service);
    }

    /**
     * Test impediment_for throws exception when schedulable is missing.
     */
    public function test_impediment_for_throws_if_no_schedulable(): void
    {
        // Arrange: Create availability without schedulable
        $availability = new Availability();
        $availability->schedulable = null;

        // Assert: Exception expected
        $this->expectException(InvalidArgumentException::class);

        // Act: Attempt to get service
        impediment_for($availability);
    }

    /**
     * Test availability_for returns AvailabilityService instance.
     */
    public function test_availability_for_returns_service(): void
    {
        // Arrange: Create mock schedulable
        $mockModel = $this->createMockSchedulable();

        // Act: Get availability service
        $service = availability_for($mockModel);

        // Assert: Correct service type returned
        $this->assertInstanceOf(AvailabilityService::class, $service);
    }

    /**
     * Test timezone helpers with DST transition.
     */
    public function test_timezone_helpers_with_dst_transition(): void
    {
        // Arrange: During DST in Europe/Paris
        TimezoneHelper::setUserTimezone('Europe/Paris');
        $utcTime = '2024-06-01 12:00:00';

        // Act: Format in local timezone
        $localTime = roster_format_local($utcTime);

        // Assert: Paris is UTC+2 during DST
        $this->assertSame('2024-06-01 14:00:00', $localTime);
    }

    /**
     * Test timezone helpers with timezone without DST.
     */
    public function test_timezone_helpers_without_dst(): void
    {
        // Arrange: Singapore has no DST
        TimezoneHelper::setUserTimezone('Asia/Singapore');
        $utcTime = '2024-01-01 12:00:00';

        // Act: Format in local timezone
        $localTime = roster_format_local($utcTime);

        // Assert: Correct UTC+8 offset
        $this->assertSame('2024-01-01 20:00:00', $localTime);
    }

    /**
     * Create a mock schedulable model for testing.
     */
    private function createMockSchedulable(): Model
    {
        return new class extends Model {};
    }

    /**
     * Sort days according to their natural order.
     *
     * @param array<string> $days
     * @return array<string>
     */
    private function sortDays(array $days): array
    {
        $dayOrder = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        usort($days, function (string $firstDay, string $secondDay) use ($dayOrder): int {
            return ($dayOrder[$firstDay] ?? 8) <=> ($dayOrder[$secondDay] ?? 8);
        });

        return $days;
    }
}
