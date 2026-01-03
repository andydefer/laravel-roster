<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Enums\DaysOfWeek;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Test suite for AvailabilityService days coherence and auto-adjustment logic.
 *
 * Validates that days are correctly adjusted based on validity period
 * and that coherence rules are enforced during creation and updates.
 */
final class AvailabilityServiceDaysCoherenceTest extends TestCase
{
    use RefreshDatabase;

    /** @var Model The schedulable model used for testing */
    private Model $schedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = TestSchedulable::create();
    }

    /**
     * Test automatic days adjustment when no days provided and period less than 7 days.
     */
    public function test_auto_adjusts_days_when_not_provided_and_period_less_than_7_days(): void
    {
        // Arrange: Period of 4 days without explicit days
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-04';

        // Act: Create availability without specifying days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert: Days should be auto-adjusted to match period days
        $expectedDays = ['thursday', 'friday', 'saturday', 'sunday'];
        $this->assertEquals($expectedDays, $availability->days);
    }

    /**
     * Test uses provided days when period less than 7 days.
     */
    public function test_uses_provided_days_when_period_less_than_7_days(): void
    {
        // Arrange: Explicit days for a short period
        $providedDays = ['thursday', 'friday'];

        // Act: Create availability with specific days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $providedDays,
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        // Assert: Provided days should be used exactly
        $this->assertEquals($providedDays, $availability->days);
    }

    /**
     * Test validation fails when provided days are not within validity period.
     */
    public function test_validation_fails_when_provided_days_not_in_period(): void
    {
        // Arrange: Invalid days outside validity period
        $invalidDays = ['monday', 'thursday'];
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-04';

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'monday' falls outside the validity period/");

        // Act: Attempt to create with invalid days
        availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $invalidDays,
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);
    }

    /**
     * Test automatic days adjustment for exact week period.
     */
    public function test_auto_adjusts_days_for_exact_week_period(): void
    {
        // Arrange: Exactly 7-day period
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-07';

        // Act: Create availability without specifying days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert: Should include all days of week
        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    /**
     * Test period more than 7 days uses all days by default.
     */
    public function test_period_more_than_7_days_uses_all_days_by_default(): void
    {
        // Arrange: Period longer than a week
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-15';

        // Act: Create availability without specifying days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert: Should include all days of week
        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    /**
     * Test update removes days not within new period.
     */
    public function test_update_removes_days_not_in_new_period(): void
    {
        // Arrange: Availability with days spanning period
        $originalDays = ['monday', 'wednesday', 'friday', 'sunday'];
        $originalValidityEnd = '2038-07-18';
        $newValidityEnd = '2038-07-10';

        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => $originalValidityEnd,
        ]);

        // Act: Update with shorter validity period
        $result = availability_for($this->schedulable)->update(
            id: $availability->id,
            data: ['validity_end' => $newValidityEnd]
        );

        // Assert: Days outside new period should be removed
        $this->assertTrue($result);

        $availability->refresh();
        $this->assertNotContains('sunday', $availability->days);
        $this->assertContains('monday', $availability->days);
        $this->assertContains('wednesday', $availability->days);
        $this->assertContains('friday', $availability->days);
    }

    /**
     * Test update does not add new days even if they are within period.
     */
    public function test_update_does_not_add_new_days_even_if_in_period(): void
    {
        // Arrange: Availability with limited days
        $originalDays = ['monday', 'wednesday'];
        $originalValidityEnd = '2038-07-11';
        $newValidityEnd = '2038-07-18';

        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => $originalValidityEnd,
        ]);

        // Act: Extend validity period
        $result = availability_for($this->schedulable)->update(
            id: $availability->id,
            data: ['validity_end' => $newValidityEnd]
        );

        // Assert: Original days should remain unchanged
        $this->assertTrue($result);

        $availability->refresh();
        $this->assertEquals($originalDays, $availability->days);
    }

    /**
     * Test update validation fails when new days are not within new period.
     */
    public function test_update_warns_when_new_days_not_in_new_period(): void
    {
        config()->set('roster.reconciliation_warning', true);
        // Arrange
        $originalDays = ['monday', 'wednesday', 'friday'];
        $invalidNewDays = ['monday', 'saturday'];
        $originalValidityEnd = '2038-07-18';
        $newValidityEnd = '2038-07-09';

        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => $originalValidityEnd,
        ]);

        $wasWarned = false;
        set_error_handler(function ($errno, $errstr) use (&$wasWarned) {
            if ($errno === E_USER_WARNING && str_contains($errstr, 'outside the validity period')) {
                $wasWarned = true;
            }
            return true; // continue execution
        });

        // Act
        availability_for($this->schedulable)->update(
            id: $availability->id,
            data: [
                'days' => $invalidNewDays,
                'validity_end' => $newValidityEnd,
            ]
        );

        restore_error_handler();

        // Assert
        $this->assertTrue($wasWarned, 'Expected a warning when invalid days are provided.');
    }

    /**
     * Test automatic days adjustment for single day period.
     */
    public function test_auto_adjusts_days_for_single_day_period(): void
    {
        // Arrange: Single day period
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-01';

        // Act: Create availability for single day
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert: Should only include the specific day
        $this->assertEquals(['thursday'], $availability->days);
    }

    /**
     * Test no automatic adjustment when days are explicitly provided.
     */
    public function test_no_auto_adjustment_when_days_explicitly_provided(): void
    {
        // Arrange: Explicit days for period
        $explicitDays = ['thursday', 'friday'];

        // Act: Create with explicit days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $explicitDays,
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        // Assert: Should use exactly the provided days
        $this->assertEquals($explicitDays, $availability->days);
    }

    /**
     * Test update with days array replaces all days.
     */
    public function test_update_with_days_array_replaces_all_days(): void
    {
        // Arrange: Existing availability with days
        $originalDays = ['monday', 'wednesday', 'friday'];
        $newDays = ['tuesday', 'thursday'];

        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-18',
        ]);

        // Act: Update with new days array
        $result = availability_for($this->schedulable)->update(
            id: $availability->id,
            data: ['days' => $newDays]
        );

        // Assert: Days should be completely replaced
        $this->assertTrue($result);

        $availability->refresh();
        $this->assertEquals($newDays, $availability->days);
    }

    /**
     * Test period spanning multiple weeks with automatic adjustment.
     */
    public function test_period_spanning_multiple_weeks_with_auto_adjustment(): void
    {
        // Arrange: 10-day period spanning multiple weeks
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-10';

        // Act: Create availability without specifying days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert: Should include all days present in the period
        $expectedDays = [
            'thursday',
            'friday',
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
        ];

        sort($expectedDays);

        $actualDays = $availability->days;
        sort($actualDays);

        $this->assertEquals($expectedDays, $actualDays);
    }
}
