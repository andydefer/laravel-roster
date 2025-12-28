<?php

declare(strict_types=1);

namespace Tests\Integration\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;
use TypeError;

/**
 * Test suite for BelongsToSchedulable trait validation.
 *
 * Validates proper context chaining for availability-based entities
 * and ensures domain mutation protection is enforced.
 */
final class BelongsToSchedulableTest extends TestCase
{
    use RefreshDatabase;

    /** @var Model The primary schedulable model for testing */
    private Model $primarySchedulable;

    /** @var Model The secondary schedulable model for comparison testing */
    private Model $secondarySchedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->primarySchedulable = TestSchedulable::create();
        $this->secondarySchedulable = TestSchedulable::create();
    }

    /**
     * Test availability creation succeeds with proper schedulable context.
     */
    public function test_availability_creation_succeeds_with_proper_schedulable_context(): void
    {
        // Arrange: Data for creating an availability
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act: Create availability with schedulable context
        $availability = availability_for($this->primarySchedulable)->create($availabilityData);

        // Assert: Availability should be created with correct relationships
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame($this->primarySchedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
        $this->assertSame('consultation', $availability->type);
    }

    /**
     * Test schedule creation fails without schedulable context.
     */
    public function test_schedule_creation_fails_without_schedulable_context(): void
    {
        // Arrange: Create availability first
        availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $scheduleData = [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        // Assert: Should throw type error due to missing availability context
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/must be of type .*Availability/');

        // Act: Attempt to create schedule without proper context chain
        schedule_for($this->primarySchedulable)->create($scheduleData);
    }

    /**
     * Test schedule creation succeeds with proper context chain.
     */
    public function test_schedule_creation_succeeds_with_proper_context(): void
    {
        // Arrange: Create availability as context
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $scheduleData = [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        // Act: Create schedule with availability context
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert: Schedule should inherit proper relationships
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertSame($this->primarySchedulable->id, $schedule->schedulable_id);
        $this->assertSame(TestSchedulable::class, $schedule->schedulable_type);
        $this->assertSame($availability->id, $schedule->availability_id);
        $this->assertSame('Test Schedule', $schedule->title);
    }

    /**
     * Test impediment creation fails without proper context chain.
     */
    public function test_impediment_creation_fails_without_proper_context(): void
    {
        // Arrange: Create availability first
        availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $impedimentData = [
            'reason' => 'Test Impediment',
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ];

        // Assert: Should throw type error due to missing availability context
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/must be of type .*Availability/');

        // Act: Attempt to create impediment without proper context chain
        impediment_for($this->primarySchedulable)->create($impedimentData);
    }

    /**
     * Test impediment creation succeeds with proper context chain.
     */
    public function test_impediment_creation_succeeds_with_proper_context(): void
    {
        // Arrange: Create availability as context
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $impedimentData = [
            'reason' => 'Test Impediment',
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ];

        // Act: Create impediment with availability context
        $impediment = impediment_for($availability)->create($impedimentData);

        // Assert: Impediment should inherit proper relationships
        $this->assertInstanceOf(ImpedimentModel::class, $impediment);
        $this->assertSame($this->primarySchedulable->id, $impediment->schedulable_id);
        $this->assertSame(TestSchedulable::class, $impediment->schedulable_type);
        $this->assertSame($availability->id, $impediment->availability_id);
        $this->assertSame('Test Impediment', $impediment->reason);
    }

    /**
     * Test schedule update works with proper context.
     */
    public function test_schedule_update_works_with_proper_context(): void
    {
        // Arrange: Create availability and schedule
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Original Title',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        // Act: Update schedule with correct context
        $result = schedule_for($availability)->update($schedule->id, $updateData);

        // Assert: Schedule should be updated successfully
        $this->assertTrue($result);
        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
    }

    /**
     * Test schedule update fails with wrong schedulable context.
     */
    public function test_schedule_update_fails_with_wrong_schedulable(): void
    {
        // Arrange: Create availability and schedule
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Original Title',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        // Assert: Should throw validation exception for wrong schedulable
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt update with incorrect schedulable context
        schedule_for($availability)->for($this->secondarySchedulable)->update($schedule->id, $updateData);
    }

    /**
     * Test availabilities are scoped to their schedulable.
     */
    public function test_availabilities_are_scoped_to_their_schedulable(): void
    {
        // Arrange: Create availabilities for different schedulables
        $availability1 = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->secondarySchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Retrieve availabilities for each schedulable
        $primaryAvailabilities = availability_for($this->primarySchedulable)->all();
        $secondaryAvailabilities = availability_for($this->secondarySchedulable)->all();

        // Assert: Each schedulable should only see its own availabilities
        $this->assertCount(1, $primaryAvailabilities);
        $this->assertSame($availability1->id, $primaryAvailabilities->first()->id);
        $this->assertSame('consultation', $primaryAvailabilities->first()->type);

        $this->assertCount(1, $secondaryAvailabilities);
        $this->assertSame($availability2->id, $secondaryAvailabilities->first()->id);
        $this->assertSame('training', $secondaryAvailabilities->first()->type);
    }

    /**
     * Test schedules are scoped to their schedulable.
     */
    public function test_schedules_are_scoped_to_their_schedulable(): void
    {
        // Arrange: Create availabilities and schedules for different schedulables
        $availability1 = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->secondarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $schedule1 = schedule_for($availability1)->create([
            'title' => 'Schedule for First',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        $schedule2 = schedule_for($availability2)->create([
            'title' => 'Schedule for Second',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        // Act: Retrieve schedules for each availability context
        $firstSchedules = schedule_for($availability1)->all();
        $secondSchedules = schedule_for($availability2)->all();

        // Assert: Each context should only see its own schedules
        $this->assertCount(1, $firstSchedules);
        $this->assertSame($schedule1->id, $firstSchedules->first()->id);
        $this->assertSame('Schedule for First', $firstSchedules->first()->title);

        $this->assertCount(1, $secondSchedules);
        $this->assertSame($schedule2->id, $secondSchedules->first()->id);
        $this->assertSame('Schedule for Second', $secondSchedules->first()->title);
    }

    /**
     * Test impediments are scoped to their schedulable.
     */
    public function test_impediments_are_scoped_to_their_schedulable(): void
    {
        // Arrange: Create availabilities and impediments for different schedulables
        $availability1 = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->secondarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $impediment1 = impediment_for($availability1)->create([
            'reason' => 'Impediment for First',
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ]);

        $impediment2 = impediment_for($availability2)->create([
            'reason' => 'Impediment for Second',
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ]);

        // Act: Retrieve impediments for each availability context
        $firstImpediments = impediment_for($availability1)->all();
        $secondImpediments = impediment_for($availability2)->all();

        // Assert: Each context should only see its own impediments
        $this->assertCount(1, $firstImpediments);
        $this->assertSame($impediment1->id, $firstImpediments->first()->id);
        $this->assertSame('Impediment for First', $firstImpediments->first()->reason);

        $this->assertCount(1, $secondImpediments);
        $this->assertSame($impediment2->id, $secondImpediments->first()->id);
        $this->assertSame('Impediment for Second', $secondImpediments->first()->reason);
    }

    /**
     * Test find method respects schedulable scope.
     */
    public function test_find_respects_schedulable_scope(): void
    {
        // Arrange: Create availability and schedule
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        // Act: Find schedule with correct and incorrect contexts
        $foundSchedule = schedule_for($availability)->find($schedule->id);
        $notFoundSchedule = schedule_for($availability)->for($this->secondarySchedulable)->find($schedule->id);

        // Assert: Schedule should only be found with correct context
        $this->assertInstanceOf(ScheduleModel::class, $foundSchedule);
        $this->assertSame($schedule->id, $foundSchedule->id);
        $this->assertNull($notFoundSchedule);
    }

    /**
     * Test delete method respects schedulable scope.
     */
    public function test_delete_respects_schedulable_scope(): void
    {
        // Arrange: Create availability and schedule
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        // Assert: Should throw validation exception for wrong schedulable
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt delete with wrong schedulable context
        schedule_for($availability)->for($this->secondarySchedulable)->delete($schedule->id);

        // Assert: Schedule should still exist after failed delete attempt
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);

        // Act: Delete with correct schedulable context
        $result = schedule_for($availability)->delete($schedule->id);

        // Assert: Schedule should be deleted successfully
        $this->assertTrue($result);
        $this->assertDatabaseMissing('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test direct model creation fails.
     */
    public function test_direct_model_creation_fails(): void
    {
        // Arrange: Model data for direct creation attempt
        $modelData = [
            'schedulable_id' => $this->primarySchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert: Should throw forbidden mutation exception
        $this->expectException(ForbiddenModelMutationException::class);
        $this->expectExceptionMessage('Direct mutation of Availability is forbidden');

        // Act: Attempt direct model creation (bypassing service layer)
        AvailabilityModel::create($modelData);
    }

    /**
     * Test direct model update fails.
     */
    public function test_direct_model_update_fails(): void
    {
        // Arrange: Create availability through service
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Assert: Should throw forbidden mutation exception
        $this->expectException(ForbiddenModelMutationException::class);
        $this->expectExceptionMessage('Direct mutation of Availability is forbidden');

        // Act: Attempt direct model update (bypassing service layer)
        $availability->update(['daily_end' => '18:00:00']);
    }

    /**
     * Test service context is properly cloned.
     */
    public function test_service_context_cloning(): void
    {
        // Arrange: Create two different availabilities
        $availability1 = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->primarySchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act: Create schedules with different availability contexts
        $schedule1 = schedule_for($availability1)->create([
            'title' => 'Schedule 1',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        $schedule2 = schedule_for($availability2)->create([
            'title' => 'Schedule 2',
            'start_datetime' => '2038-07-01 15:00:00',
            'end_datetime' => '2038-07-01 16:00:00',
        ]);

        // Assert: Each schedule should be linked to its respective availability
        $this->assertSame($availability1->id, $schedule1->availability_id);
        $this->assertSame($availability2->id, $schedule2->availability_id);
    }

    /**
     * Test service cannot be reused without proper context.
     */
    public function test_service_cannot_be_reused_without_context(): void
    {
        // Arrange: Create availability and schedule
        $availability = availability_for($this->primarySchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'First Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        // Assert: Should throw type error when missing availability context
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/must be of type .*Availability/');

        // Act: Attempt to create schedule without proper context
        schedule_for($this->primarySchedulable)->create([
            'title' => 'Should Fail',
            'start_datetime' => '2038-07-01 14:00:00',
            'end_datetime' => '2038-07-01 15:00:00',
        ]);
    }
}
