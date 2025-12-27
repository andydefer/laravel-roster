<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Test suite for ImpedimentScheduleDaysCoherenceRule validation rule.
 *
 * Validates that impediments and schedules can only be created on days
 * allowed by their parent availability.
 */
final class ImpedimentScheduleDaysCoherenceRuleTest extends TestCase
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
     * Test impediment cannot be created on non-availability day.
     */
    public function test_cannot_create_impediment_on_non_availability_day(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/failed for Impediment.*not allowed.*following days/i');

        // Act
        impediment_for($availability)->create([
            'reason' => 'Monday impediment',
            'start_datetime' => '2038-01-04 10:00:00', // Monday
            'end_datetime' => '2038-01-04 11:00:00',
        ]);
    }

    /**
     * Test schedule cannot be created on non-availability day.
     */
    public function test_cannot_create_schedule_on_non_availability_day(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/failed for Schedule.*not allowed because this availability only permits/"
        );

        // Act
        schedule_for($availability)->create([
            'title' => 'Wednesday schedule',
            'start_datetime' => '2038-01-06 10:00:00', // Wednesday
            'end_datetime' => '2038-01-06 11:00:00',
        ]);
    }

    /**
     * Test impediment can be created on allowed day.
     */
    public function test_can_create_impediment_on_allowed_day(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $impediment = impediment_for($availability)->create([
            'reason' => 'Tuesday impediment',
            'start_datetime' => '2038-01-05 10:00:00', // Tuesday
            'end_datetime' => '2038-01-05 11:00:00',
        ]);

        // Assert
        $this->assertNotNull($impediment);
        $this->assertDatabaseHas('roster_impediments', ['id' => $impediment->id]);
    }
}
