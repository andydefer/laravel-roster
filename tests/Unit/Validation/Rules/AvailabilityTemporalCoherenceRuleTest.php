<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Test suite for AvailabilityTemporalCoherenceRule.
 *
 * Validates that availability modifications respect temporal coherence
 * with existing schedules and impediments.
 */
final class AvailabilityTemporalCoherenceRuleTest extends TestCase
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
     * Test cannot shorten availability before existing future schedule.
     */
    public function test_cannot_shorten_availability_before_existing_future_schedule(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Future Meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot set validity_start/");

        // Act
        availability_for($this->testSchedulable)->update($availability->id, [
            'validity_start' => '2038-01-05',
        ]);
    }

    /**
     * Test cannot extend availability end before existing future schedule.
     */
    public function test_cannot_extend_availability_end_before_existing_future_schedule(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Future meeting',
            'start_datetime' => '2038-01-11 10:00:00',
            'end_datetime' => '2038-01-11 11:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot set validity_end/");

        // Act
        availability_for($this->testSchedulable)->update($availability->id, [
            'validity_end' => '2038-01-05',
        ]);
    }

    /**
     * Test cannot remove days with future impediments.
     */
    public function test_cannot_remove_days_with_future_impediments(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Future impediment',
            'start_datetime' => '2038-01-07 10:00:00',
            'end_datetime' => '2038-01-07 11:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/Cannot remove 'Thursday' from days because it is used by a future impediment/i"
        );

        // Act
        availability_for($this->testSchedulable)->update($availability->id, [
            'days' => ['monday'],
        ]);
    }

    /**
     * Test can update availability without conflict.
     */
    public function test_can_update_availability_without_conflict(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->update($availability->id, [
            'daily_end' => '18:00:00',
        ]);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
        ]);
    }

    /**
     * Test cannot delete availability with future schedules.
     */
    public function test_cannot_delete_availability_with_future_schedules(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Future Meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future schedules/");

        // Act
        availability_for($this->testSchedulable)->delete($availability->id);
    }

    /**
     * Test cannot delete availability with future impediments.
     */
    public function test_cannot_delete_availability_with_future_impediments(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Future impediment',
            'start_datetime' => '2038-01-07 10:00:00',
            'end_datetime' => '2038-01-07 11:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future impediments/");

        // Act
        availability_for($this->testSchedulable)->delete($availability->id);
    }

    /**
     * Test can delete availability without future conflict.
     */
    public function test_can_delete_availability_without_future_conflict(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['friday', 'saturday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-02',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->delete($availability->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('roster_availabilities', [
            'id' => $availability->id,
        ]);
    }
}
