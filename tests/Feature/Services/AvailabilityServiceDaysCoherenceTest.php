<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Enums\DaysOfWeek;
use Roster\Models\Availability as AvailabilityModel;
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

    private Model $testSchedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Test automatic days adjustment when no days provided and period less than 7 days.
     */
    public function test_auto_adjusts_days_when_not_provided_and_period_less_than_7_days(): void
    {
        // Arrange
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-04';

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert
        $expectedDays = ['thursday', 'friday', 'saturday', 'sunday'];
        $this->assertEquals($expectedDays, $availability->days);
    }

    /**
     * Test uses provided days when period less than 7 days.
     */
    public function test_uses_provided_days_when_period_less_than_7_days(): void
    {
        // Arrange
        $providedDays = ['thursday', 'friday'];

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $providedDays,
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        // Assert
        $this->assertEquals($providedDays, $availability->days);
    }

    /**
     * Test validation fails when provided days are not within validity period.
     */
    public function test_validation_fails_when_provided_days_not_in_period(): void
    {
        // Arrange
        $invalidDays = ['monday', 'thursday'];
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-04';

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'monday' is not within the validity period/");

        // Act
        availability_for($this->testSchedulable)->create([
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
        // Arrange
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-07';

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert
        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    /**
     * Test period more than 7 days uses all days by default.
     */
    public function test_period_more_than_7_days_uses_all_days_by_default(): void
    {
        // Arrange
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-15';

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert
        $this->assertEquals(DaysOfWeek::values(), $availability->days);
    }

    /**
     * Test update removes days not within new period.
     */
    public function test_update_removes_days_not_in_new_period(): void
    {
        // Arrange
        $originalDays = ['monday', 'wednesday', 'friday', 'sunday'];
        $originalValidityEnd = '2038-07-18';
        $newValidityEnd = '2038-07-10';

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => $originalValidityEnd,
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->update(
            id: $availability->id,
            data: ['validity_end' => $newValidityEnd]
        );

        // Assert
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
        // Arrange
        $originalDays = ['monday', 'wednesday'];
        $originalValidityEnd = '2038-07-11';
        $newValidityEnd = '2038-07-18';

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => $originalValidityEnd,
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->update(
            id: $availability->id,
            data: ['validity_end' => $newValidityEnd]
        );

        // Assert
        $this->assertTrue($result);

        $availability->refresh();
        $this->assertEquals($originalDays, $availability->days);
    }

    /**
     * Test update validation fails when new days are not within new period.
     */
    public function test_update_validation_fails_when_new_days_not_in_new_period(): void
    {
        // Arrange
        $originalDays = ['monday', 'wednesday', 'friday'];
        $invalidNewDays = ['monday', 'saturday'];
        $originalValidityEnd = '2038-07-18';
        $newValidityEnd = '2038-07-09';

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => $originalValidityEnd,
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Day 'saturday' is not within the validity period/");

        // Act
        availability_for($this->testSchedulable)->update(
            id: $availability->id,
            data: [
                'days' => $invalidNewDays,
                'validity_end' => $newValidityEnd,
            ]
        );
    }

    /**
     * Test automatic days adjustment for single day period.
     */
    public function test_auto_adjusts_days_for_single_day_period(): void
    {
        // Arrange
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-01';

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert
        $this->assertEquals(['thursday'], $availability->days);
    }

    /**
     * Test no automatic adjustment when days are explicitly provided.
     */
    public function test_no_auto_adjustment_when_days_explicitly_provided(): void
    {
        // Arrange
        $explicitDays = ['thursday', 'friday'];

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $explicitDays,
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-04',
        ]);

        // Assert
        $this->assertEquals($explicitDays, $availability->days);
    }

    /**
     * Test update with days array replaces all days.
     */
    public function test_update_with_days_array_replaces_all_days(): void
    {
        // Arrange
        $originalDays = ['monday', 'wednesday', 'friday'];
        $newDays = ['tuesday', 'thursday'];

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => $originalDays,
            'validity_start' => '2038-07-05',
            'validity_end' => '2038-07-18',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->update(
            id: $availability->id,
            data: ['days' => $newDays]
        );

        // Assert
        $this->assertTrue($result);

        $availability->refresh();
        $this->assertEquals($newDays, $availability->days);
    }

    /**
     * Test period spanning multiple weeks with automatic adjustment.
     */
    public function test_period_spanning_multiple_weeks_with_auto_adjustment(): void
    {
        // Arrange
        $validityStart = '2038-07-01';
        $validityEnd = '2038-07-10';

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);

        // Assert
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
