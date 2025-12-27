<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Models\Availability as AvailabilityModel;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Unit tests for the Availability model.
 */
final class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Model $testSchedulable;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Create an availability instance for testing.
     *
     * @param array<string, mixed> $attributes
     */
    private function createAvailability(array $attributes = []): AvailabilityModel
    {
        $defaultAttributes = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01 00:00:00',
            'validity_end' => '2038-07-31 23:59:59',
        ];

        return availability_for($this->testSchedulable)
            ->create(array_merge($defaultAttributes, $attributes));
    }

    /**
     * Test availability creation with valid attributes.
     */
    public function test_availability_can_be_created_with_valid_attributes(): void
    {
        // Arrange
        $attributes = [
            'type' => 'training',
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
        ];

        // Act
        $availability = $this->createAvailability($attributes);

        // Assert
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
        $this->assertSame('training', $availability->type);
        $this->assertSame(['monday', 'wednesday', 'friday'], $availability->days);
    }

    /**
     * Test daily times are properly cast to time format.
     */
    public function test_daily_times_are_properly_cast(): void
    {
        // Arrange
        $attributes = [
            'daily_start' => '08:30:00',
            'daily_end' => '16:45:00',
        ];

        // Act
        $availability = $this->createAvailability($attributes);

        // Assert
        $this->assertSame('08:30:00', $availability->daily_start->format('H:i:s'));
        $this->assertSame('16:45:00', $availability->daily_end->format('H:i:s'));
    }

    /**
     * Test validity dates are properly cast to datetime.
     */
    public function test_validity_dates_are_properly_cast(): void
    {
        // Arrange
        $attributes = [
            'validity_start' => '2038-07-15 00:00:00',
            'validity_end' => '2038-07-25 23:59:59',
        ];

        // Act
        $availability = $this->createAvailability($attributes);

        // Assert
        $this->assertSame('2038-07-15 00:00:00', $availability->validity_start->format('Y-m-d H:i:s'));
        $this->assertSame('2038-07-25 23:59:59', $availability->validity_end->format('Y-m-d H:i:s'));
    }

    /**
     * Test days attribute is properly cast to array.
     */
    public function test_days_are_properly_cast_to_array(): void
    {
        // Arrange
        $attributes = [
            'days' => ['tuesday', 'thursday'],
        ];

        // Act
        $availability = $this->createAvailability($attributes);

        // Assert
        $this->assertIsArray($availability->days);
        $this->assertSame(['tuesday', 'thursday'], $availability->days);
    }

    /**
     * Test availability correctly identifies available days.
     */
    public function test_is_available_on_day_returns_true_for_included_days(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['monday', 'tuesday'],
        ]);

        $monday = Carbon::parse('2038-07-05');
        $tuesday = Carbon::parse('2038-07-06');
        $wednesday = Carbon::parse('2038-07-07');

        // Act & Assert
        $this->assertTrue($availability->isActiveOnDate($monday));
        $this->assertTrue($availability->isActiveOnDate($tuesday));
        $this->assertFalse($availability->isActiveOnDate($wednesday));
    }

    /**
     * Test is_within_daily_window returns true for times within window.
     */
    public function test_is_within_daily_window_returns_true_for_times_within_window(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00');
        $end = Carbon::parse('2038-07-01 11:00:00');

        // Act & Assert
        $this->assertTrue($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_within_daily_window returns false for times outside window.
     */
    public function test_is_within_daily_window_returns_false_for_times_outside_window(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 08:00:00');
        $end = Carbon::parse('2038-07-01 09:30:00');

        // Act & Assert
        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_within_validity_period returns true for dates within period.
     */
    public function test_is_within_validity_period_returns_true_for_dates_within_period(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00');
        $end = Carbon::parse('2038-07-01 11:00:00');

        // Act & Assert
        $this->assertTrue($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_within_validity_period returns false for dates before period.
     */
    public function test_is_within_validity_period_returns_false_for_dates_before_period(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15',
            'validity_end' => '2038-07-31',
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00');
        $end = Carbon::parse('2038-07-01 11:00:00');

        // Act & Assert
        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_within_validity_period returns false for dates after period.
     */
    public function test_is_within_validity_period_returns_false_for_dates_after_period(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-20 10:00:00');
        $end = Carbon::parse('2038-07-20 11:00:00');

        // Act & Assert
        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test get_daily_duration_minutes returns correct duration.
     */
    public function test_get_daily_duration_minutes_returns_correct_duration(): void
    {
        // Arrange
        $availability = $this->createAvailability();

        // Act & Assert
        $this->assertSame(480, $availability->getDailyDurationMinutes());
    }

    /**
     * Test get_validity_duration_days returns correct duration.
     */
    public function test_get_validity_duration_days_returns_correct_duration(): void
    {
        // Arrange
        $availability = $this->createAvailability();

        // Act & Assert
        $this->assertSame(30, $availability->getValidityDurationDays());
    }

    /**
     * Test get_validity_duration_days returns null when start or end date is missing.
     */
    public function test_get_validity_duration_days_returns_null_when_start_or_end_missing(): void
    {
        // Arrange
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => null,
            'validity_end' => '2038-07-31',
        ]);

        // Act & Assert
        $this->assertNull($availability->getValidityDurationDays());
    }

    /**
     * Test has_unlimited_validity returns false when validity_end is set.
     */
    public function test_has_unlimited_validity_returns_false_when_validity_end_is_set(): void
    {
        // Arrange
        $availability = $this->createAvailability();

        // Act & Assert
        $this->assertFalse($availability->hasUnlimitedValidity());
    }

    /**
     * Test has_unlimited_validity returns true when validity_end is null.
     */
    public function test_has_unlimited_validity_returns_true_when_validity_end_is_null(): void
    {
        // Arrange
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => null,
        ]);

        // Act & Assert
        $this->assertTrue($availability->hasUnlimitedValidity());
    }

    /**
     * Test has_validity_started returns true when date is after start.
     */
    public function test_has_validity_started_returns_true_when_date_is_after_start(): void
    {
        // Arrange
        $availability = $this->createAvailability();
        $date = Carbon::parse('2038-07-15');

        // Act & Assert
        $this->assertTrue($availability->hasValidityStarted($date));
    }

    /**
     * Test has_validity_started returns false when date is before start.
     */
    public function test_has_validity_started_returns_false_when_date_is_before_start(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15',
            'validity_end' => '2038-07-31',
        ]);

        $date = Carbon::parse('2038-07-01');

        // Act & Assert
        $this->assertFalse($availability->hasValidityStarted($date));
    }

    /**
     * Test has_validity_started returns true when validity_start is null.
     */
    public function test_has_validity_started_returns_true_when_validity_start_is_null(): void
    {
        // Arrange
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => null,
            'validity_end' => '2038-07-31',
        ]);

        // Act & Assert
        $this->assertTrue($availability->hasValidityStarted());
    }

    /**
     * Test has_validity_ended returns true when date is after end.
     */
    public function test_has_validity_ended_returns_true_when_date_is_after_end(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $date = Carbon::parse('2038-07-20');

        // Act & Assert
        $this->assertTrue($availability->hasValidityEnded($date));
    }

    /**
     * Test has_validity_ended returns false when date is before end.
     */
    public function test_has_validity_ended_returns_false_when_date_is_before_end(): void
    {
        // Arrange
        $availability = $this->createAvailability();
        $date = Carbon::parse('2038-07-15');

        // Act & Assert
        $this->assertFalse($availability->hasValidityEnded($date));
    }

    /**
     * Test has_validity_ended returns false when validity_end is null.
     */
    public function test_has_validity_ended_returns_false_when_validity_end_is_null(): void
    {
        // Arrange
        $availability = new AvailabilityModel([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => null,
        ]);

        // Act & Assert
        $this->assertFalse($availability->hasValidityEnded());
    }

    /**
     * Test is_validity_active returns true when date is within validity period.
     */
    public function test_is_validity_active_returns_true_when_date_is_within_period(): void
    {
        // Arrange
        $availability = $this->createAvailability();
        $date = Carbon::parse('2038-07-15');

        // Act & Assert
        $this->assertTrue($availability->isValidityActive($date));
    }

    /**
     * Test is_validity_active returns false when date is before validity period.
     */
    public function test_is_validity_active_returns_false_when_date_is_before_period(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-15',
            'validity_end' => '2038-07-31',
        ]);

        $date = Carbon::parse('2038-07-01');

        // Act & Assert
        $this->assertFalse($availability->isValidityActive($date));
    }

    /**
     * Test is_validity_active returns false when date is after validity period.
     */
    public function test_is_validity_active_returns_false_when_date_is_after_period(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $date = Carbon::parse('2038-07-20');

        // Act & Assert
        $this->assertFalse($availability->isValidityActive($date));
    }

    /**
     * Test schedulable relationship works correctly.
     */
    public function test_schedulable_relationship_returns_correct_model(): void
    {
        // Arrange
        $availability = $this->createAvailability();

        // Act & Assert
        $this->assertInstanceOf(TestSchedulable::class, $availability->schedulable);
        $this->assertSame($this->testSchedulable->id, $availability->schedulable->id);
    }

    /**
     * Test schedules relationship works correctly.
     */
    public function test_schedules_relationship_returns_has_many(): void
    {
        // Arrange
        $availability = $this->createAvailability();

        // Act & Assert
        $this->assertInstanceOf(HasMany::class, $availability->schedules());
    }

    /**
     * Test impediments relationship works correctly.
     */
    public function test_impediments_relationship_returns_has_many(): void
    {
        // Arrange
        $availability = $this->createAvailability();

        // Act & Assert
        $this->assertInstanceOf(HasMany::class, $availability->impediments());
    }

    /**
     * Test availability correctly checks for schedule availability with all conditions.
     */
    public function test_is_available_for_schedule_returns_true_when_all_conditions_met(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00');
        $end = Carbon::parse('2038-07-01 11:00:00');

        // Act & Assert
        $this->assertTrue($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_available_for_schedule returns false when day is not available.
     */
    public function test_is_available_for_schedule_returns_false_when_day_not_available(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['monday'],
        ]);

        $start = Carbon::parse('2038-07-01 10:00:00');
        $end = Carbon::parse('2038-07-01 11:00:00');

        // Act & Assert
        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_available_for_schedule returns false when time is outside daily window.
     */
    public function test_is_available_for_schedule_returns_false_when_time_outside_window(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['thursday'],
        ]);

        $start = Carbon::parse('2038-07-01 18:00:00');
        $end = Carbon::parse('2038-07-01 19:00:00');

        // Act & Assert
        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }

    /**
     * Test is_available_for_schedule returns false when outside validity period.
     */
    public function test_is_available_for_schedule_returns_false_when_outside_validity_period(): void
    {
        // Arrange
        $availability = $this->createAvailability([
            'days' => ['thursday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-15',
        ]);

        $start = Carbon::parse('2038-07-20 10:00:00');
        $end = Carbon::parse('2038-07-20 11:00:00');

        // Act & Assert
        $this->assertFalse($availability->isAvailableForSchedule($start, $end));
    }
}
