<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Support\Carbon;
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

    private Model $schedulable;

    /**
     * Set up test environment with a fresh schedulable instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->schedulable = TestSchedulable::create();
    }

    /**
     * Test that availability cannot be shortened when future schedules exist.
     */
    public function test_cannot_shorten_availability_before_existing_future_schedule(): void
    {
        // Arrange: Create an availability with a future schedule
        $availability = availability_for($this->schedulable)->create([
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

        // Assert: Expect validation exception when trying to shorten availability
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot set validity_start/");

        // Act: Attempt to shorten availability start date past existing schedule
        availability_for($this->schedulable)->update($availability->id, [
            'validity_start' => '2038-01-05',
        ]);
    }

    /**
     * Test that availability end cannot be extended before existing future schedule.
     */
    public function test_cannot_extend_availability_end_before_existing_future_schedule(): void
    {
        // Arrange: Create an availability with a future schedule
        $availability = availability_for($this->schedulable)->create([
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

        // Assert: Expect validation exception when trying to extend availability end
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot set validity_end/");

        // Act: Attempt to extend availability end date before existing schedule
        availability_for($this->schedulable)->update($availability->id, [
            'validity_end' => '2038-01-05',
        ]);
    }

    /**
     * Test that days with future impediments cannot be removed.
     */
    public function test_cannot_remove_days_with_future_impediments(): void
    {
        // Arrange: Create an availability with a future impediment on Thursday
        $availability = availability_for($this->schedulable)->create([
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

        // Assert: Expect validation exception when trying to remove day with impediment
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/Cannot remove 'Thursday' from days because it is used by a future impediment/i"
        );

        // Act: Attempt to remove Thursday from days array
        availability_for($this->schedulable)->update($availability->id, [
            'days' => ['monday'],
        ]);
    }

    /**
     * Test that availability can be updated without conflict.
     */
    public function test_can_update_availability_without_conflict(): void
    {
        // Arrange: Create a basic availability without conflicts
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Update daily end time (non-conflicting change)
        $result = availability_for($this->schedulable)->update($availability->id, [
            'daily_end' => '18:00:00',
        ]);

        // Assert: Update should succeed and persist to database
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
        ]);
    }

    /**
     * Test that availability with future schedules cannot be deleted.
     */
    public function test_cannot_delete_availability_with_future_schedules(): void
    {
        // Arrange: Create an availability with a future schedule
        $availability = availability_for($this->schedulable)->create([
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

        // Assert: Expect validation exception when trying to delete
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future schedules/");

        // Act: Attempt to delete availability with future schedule
        availability_for($this->schedulable)->delete($availability->id);
    }

    /**
     * Test that availability with future impediments cannot be deleted.
     */
    public function test_cannot_delete_availability_with_future_impediments(): void
    {
        // Arrange: Create an availability with a future impediment
        $availability = availability_for($this->schedulable)->create([
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

        // Assert: Expect validation exception when trying to delete
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future impediments/");

        // Act: Attempt to delete availability with future impediment
        availability_for($this->schedulable)->delete($availability->id);
    }

    /**
     * Test that availability without future conflicts can be deleted.
     */
    public function test_can_delete_availability_without_future_conflict(): void
    {
        // Arrange: Create an availability without any schedules or impediments
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['friday', 'saturday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-02',
        ]);

        // Act: Delete the availability (should succeed)
        $result = availability_for($this->schedulable)->delete($availability->id);

        // Assert: Delete should succeed and availability should be soft-deleted
        $this->assertTrue($result);
        $this->assertSoftDeleted('roster_availabilities', [
            'id' => $availability->id,
        ]);
    }

    /**
     * Test that availability with both schedules and impediments cannot be deleted.
     */
    public function test_cannot_delete_availability_with_both_schedules_and_impediments(): void
    {
        // Arrange: Create an availability with both future schedules and impediments
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Future Schedule',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Future Impediment',
            'start_datetime' => '2038-01-06 14:00:00',
            'end_datetime' => '2038-01-06 15:00:00',
        ]);

        // Assert: Expect validation exception when trying to delete
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Cannot delete availability with future schedules and impediments/");

        // Act: Attempt to delete availability with both entity types
        availability_for($this->schedulable)->delete($availability->id);
    }

    /**
     * Test that availability can be updated when extending validity period beyond existing schedules.
     */
    public function test_can_extend_availability_beyond_existing_future_schedule(): void
    {
        // Arrange: Create an availability with a future schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-15',
        ]);

        schedule_for($availability)->create([
            'title' => 'Future meeting',
            'start_datetime' => '2038-01-11 10:00:00',
            'end_datetime' => '2038-01-11 11:00:00',
        ]);

        // Act: Extend availability end date beyond existing schedule
        $result = availability_for($this->schedulable)->update($availability->id, [
            'validity_end' => '2038-01-20',
        ]);

        // Assert: Update should succeed (extending beyond is allowed)
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'validity_end' => Carbon::parse('2038-01-20')->startOfDay(),
        ]);
    }

    /**
     * Test that availability can be updated when moving start date earlier than existing schedules.
     */
    public function test_can_move_start_date_earlier_than_existing_future_schedule(): void
    {
        // Arrange: Create an availability with a future schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-10',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Future meeting',
            'start_datetime' => '2038-01-11 10:00:00',
            'end_datetime' => '2038-01-11 11:00:00',
        ]);

        // Act: Move start date earlier than existing schedule
        $result = availability_for($this->schedulable)->update($availability->id, [
            'validity_start' => '2038-01-01',
        ]);

        // Assert: Update should succeed (moving earlier is allowed)
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'validity_start' => '2038-01-01 00:00:00',
        ]);
    }

    /**
     * Test that availability days can be updated when adding new days.
     */
    public function test_can_add_new_days_to_availability(): void
    {
        // Arrange: Create an availability without conflicts
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Add new days to availability
        $result = availability_for($this->schedulable)->update($availability->id, [
            'days' => ['monday', 'tuesday', 'wednesday'],
        ]);

        // Assert: Update should succeed (adding days is allowed)
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'days' => json_encode(['monday', 'tuesday', 'wednesday']),
        ]);
    }

    /**
     * Test that availability can be updated with only daily times changed.
     */
    public function test_can_update_only_daily_times_without_conflict(): void
    {
        // Arrange: Create an availability with a future schedule
        $availability = availability_for($this->schedulable)->create([
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

        // Act: Update only daily times (non-conflicting change)
        $result = availability_for($this->schedulable)->update($availability->id, [
            'daily_start' => '08:00:00',
            'daily_end' => '16:00:00',
        ]);

        // Assert: Update should succeed
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '08:00:00',
            'daily_end' => '16:00:00',
        ]);
    }
}
