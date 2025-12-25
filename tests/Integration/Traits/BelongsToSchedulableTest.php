<?php

declare(strict_types=1);

namespace Tests\Integration\Traits;

use Roster\Validation\Exceptions\ValidationFailedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Exceptions\InvalidServiceContextException;
use Roster\Facades\Availability;
use Roster\Facades\Schedule;
use Roster\Facades\Impediment;
use Roster\Models\Schedule as ModelsSchedule;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for BelongsToSchedulable trait validation with new owner-based API.
 */
final class BelongsToSchedulableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test model instance.
     */
    private TestSchedulable $testSchedulable;

    /**
     * Second test schedulable for comparison.
     */
    private TestSchedulable $secondSchedulable;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
        $this->secondSchedulable = TestSchedulable::create();
    }

    /**
     * Test that availability creation works with proper context.
     */
    public function test_availability_creation_succeeds_with_proper_schedulable_context(): void
    {
        // Create availability with proper context
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $this->assertInstanceOf(\Roster\Models\Availability::class, $availability);
        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
        $this->assertEquals('consultation', $availability->type);
    }

    /**
     * Test that schedule creation fails without schedulable context.
     */
    public function test_schedule_creation_fails_without_schedulable_context(): void
    {
        // Create an availability first
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);


        $this->expectException(InvalidServiceContextException::class);
        $this->expectExceptionMessage(
            'Roster\Services\ScheduleService requires a valid context (schedulable and/or owner)'
        );
        $this->expectExceptionMessageMatches(
            '/requires a valid context/'
        );


        // Try to create schedule without proper context chain
        //code...
        Schedule::for($this->testSchedulable)->create([
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);
    }

    /**
     * Test that schedule creation succeeds with proper schedulable and owner context.
     */
    public function test_schedule_creation_succeeds_with_proper_context(): void
    {
        // Create an availability first
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedule with proper context chain
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        $this->assertInstanceOf(\Roster\Models\Schedule::class, $schedule);
        $this->assertSame($this->testSchedulable->id, $schedule->schedulable_id);
        $this->assertSame(TestSchedulable::class, $schedule->schedulable_type);
        $this->assertEquals($availability->id, $schedule->availability_id);
        $this->assertEquals('Test Schedule', $schedule->title);
    }

    /**
     * Test that impediment creation fails without proper context chain.
     */
    public function test_impediment_creation_fails_without_proper_context(): void
    {
        // Create an availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $this->expectException(InvalidServiceContextException::class);
        $this->expectExceptionMessageMatches(
            '/requires a valid context/'
        );

        // Try to create impediment without owner context
        Impediment::for($this->testSchedulable)->create([
            'reason' => 'Test Impediment',
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ]);
    }

    /**
     * Test that impediment creation succeeds with proper context chain.
     */
    public function test_impediment_creation_succeeds_with_proper_context(): void
    {
        // Create an availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create impediment with proper context chain
        $impediment = Impediment::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'reason' => 'Test Impediment',
                'start_datetime' => '2038-07-01 11:00:00',
                'end_datetime' => '2038-07-01 12:00:00',
            ]);

        $this->assertInstanceOf(\Roster\Models\Impediment::class, $impediment);
        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
        $this->assertSame(TestSchedulable::class, $impediment->schedulable_type);
        $this->assertEquals($availability->id, $impediment->availability_id);
        $this->assertEquals('Test Impediment', $impediment->reason);
    }

    /**
     * Test that schedule update works with proper context.
     */
    public function test_schedule_update_works_with_proper_context(): void
    {
        // Create an availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedule
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Original Title',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        // Update schedule - ⚠️ redéfinir le owner
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->update($schedule->id, [
                'title' => 'Updated Title',
            ]);

        $this->assertTrue($result);

        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
    }


    /**
     * Test that trying to update with wrong schedulable context fails.
     */
    public function test_schedule_update_fails_with_wrong_schedulable(): void
    {
        // Create an availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedule
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Original Title',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        $this->expectException(ValidationFailedException::class);

        // ⚠️ Redéfinir l'owner même avec le mauvais schedulable
        Schedule::for($this->secondSchedulable)
            ->owner($availability) // owner de l'ancien schedulable
            ->update($schedule->id, [
                'title' => 'Updated Title',
            ]);
    }

    /**
     * Test that availabilities are scoped to their schedulable.
     */
    public function test_availabilities_are_scoped_to_their_schedulable(): void
    {
        // Create availability for first schedulable
        $availability1 = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create availability for second schedulable
        $availability2 = Availability::for($this->secondSchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Test that each schedulable only sees its own availabilities
        $firstSchedulableAvailabilities = Availability::for($this->testSchedulable)->all();
        $secondSchedulableAvailabilities = Availability::for($this->secondSchedulable)->all();

        $this->assertCount(1, $firstSchedulableAvailabilities);
        $this->assertSame($availability1->id, $firstSchedulableAvailabilities->first()->id);
        $this->assertEquals('consultation', $firstSchedulableAvailabilities->first()->type);

        $this->assertCount(1, $secondSchedulableAvailabilities);
        $this->assertSame($availability2->id, $secondSchedulableAvailabilities->first()->id);
        $this->assertEquals('training', $secondSchedulableAvailabilities->first()->type);
    }

    /**
     * Test that schedules are scoped to their schedulable.
     */
    public function test_schedules_are_scoped_to_their_schedulable(): void
    {
        // Create availabilities
        $availability1 = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = Availability::for($this->secondSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedules for each schedulable
        $schedule1 = Schedule::for($this->testSchedulable)
            ->owner($availability1)
            ->create([
                'title' => 'Schedule for First',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        $schedule2 = Schedule::for($this->secondSchedulable)
            ->owner($availability2)
            ->create([
                'title' => 'Schedule for Second',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        // Test scoping
        $firstSchedules = Schedule::for($this->testSchedulable)->get();
        $secondSchedules = Schedule::for($this->secondSchedulable)->get();

        $this->assertCount(1, $firstSchedules);
        $this->assertSame($schedule1->id, $firstSchedules->first()->id);
        $this->assertEquals('Schedule for First', $firstSchedules->first()->title);

        $this->assertCount(1, $secondSchedules);
        $this->assertSame($schedule2->id, $secondSchedules->first()->id);
        $this->assertEquals('Schedule for Second', $secondSchedules->first()->title);
    }

    /**
     * Test that impediments are scoped to their schedulable.
     */
    public function test_impediments_are_scoped_to_their_schedulable(): void
    {
        // Create availabilities
        $availability1 = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = Availability::for($this->secondSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create impediments for each schedulable
        $impediment1 = Impediment::for($this->testSchedulable)
            ->owner($availability1)
            ->create([
                'reason' => 'Impediment for First',
                'start_datetime' => '2038-07-01 11:00:00',
                'end_datetime' => '2038-07-01 12:00:00',
            ]);

        $impediment2 = Impediment::for($this->secondSchedulable)
            ->owner($availability2)
            ->create([
                'reason' => 'Impediment for Second',
                'start_datetime' => '2038-07-01 11:00:00',
                'end_datetime' => '2038-07-01 12:00:00',
            ]);

        // Test scoping
        $firstImpediments = Impediment::for($this->testSchedulable)->all();
        $secondImpediments = Impediment::for($this->secondSchedulable)->all();

        $this->assertCount(1, $firstImpediments);
        $this->assertSame($impediment1->id, $firstImpediments->first()->id);
        $this->assertEquals('Impediment for First', $firstImpediments->first()->reason);

        $this->assertCount(1, $secondImpediments);
        $this->assertSame($impediment2->id, $secondImpediments->first()->id);
        $this->assertEquals('Impediment for Second', $secondImpediments->first()->reason);
    }

    /**
     * Test that find method respects schedulable scope.
     */
    public function test_find_respects_schedulable_scope(): void
    {
        // Create availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedule
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        // Should find the schedule when using the correct schedulable
        $foundSchedule = Schedule::for($this->testSchedulable)->find($schedule->id);
        $this->assertInstanceOf(\Roster\Models\Schedule::class, $foundSchedule);
        $this->assertEquals($schedule->id, $foundSchedule->id);

        // Should NOT find the schedule when using a different schedulable
        $notFoundSchedule = Schedule::for($this->secondSchedulable)->find($schedule->id);
        $this->assertNull($notFoundSchedule);
    }

    /**
     * Test that delete method respects schedulable scope.
     */
    public function test_delete_respects_schedulable_scope(): void
    {
        // Create availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedule
        $schedule = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
            ]);

        // Try to delete with wrong schedulable - should fail
        $this->expectException(ValidationFailedException::class);

        // ⚠️ Important : définir aussi l'owner pour correspondre au nouveau schedulable
        Schedule::for($this->secondSchedulable)
            ->owner($availability) // owner de l'ancien schedulable
            ->delete($schedule->id);

        // Schedule should still exist
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);

        // Delete with correct schedulable - should succeed
        $result = Schedule::for($this->testSchedulable)
            ->owner($availability)
            ->delete($schedule->id);

        $this->assertTrue($result);

        // Schedule should be deleted
        $this->assertDatabaseMissing('roster_schedules', ['id' => $schedule->id]);
    }


    /**
     * Test that trying to create directly via model fails.
     */
    public function test_direct_model_creation_fails(): void
    {
        $this->expectException(ForbiddenModelMutationException::class);
        $this->expectExceptionMessage('Direct mutation of Availability is forbidden');

        // Try to create directly via model - should be blocked by EnforceDomainMutationObserver
        \Roster\Models\Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);
    }

    /**
     * Test that trying to update directly via model fails.
     */
    public function test_direct_model_update_fails(): void
    {
        // Create via service first
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $this->expectException(ForbiddenModelMutationException::class);
        $this->expectExceptionMessage('Direct mutation of Availability is forbidden');

        // Try to update directly via model - should be blocked
        $availability->update([
            'daily_end' => '18:00:00',
        ]);
    }

    /**
     * Test that service context is properly cloned.
     */
    public function test_service_context_cloning(): void
    {
        // Create two availabilities
        $availability1 = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = Availability::for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Get service instance for first availability
        $scheduleService1 = Schedule::for($this->testSchedulable)->owner($availability1);

        // Get service instance for second availability (should be separate instance)
        $scheduleService2 = Schedule::for($this->testSchedulable)->owner($availability2);

        // Create schedules with each service
        $schedule1 = $scheduleService1->create([
            'title' => 'Schedule 1',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        $schedule2 = $scheduleService2->create([
            'title' => 'Schedule 2',
            'start_datetime' => '2038-07-01 15:00:00',
            'end_datetime' => '2038-07-01 16:00:00',
        ]);

        // Verify each schedule belongs to correct availability
        $this->assertEquals($availability1->id, $schedule1->availability_id);
        $this->assertEquals($availability2->id, $schedule2->availability_id);
    }

    /**
     * Test that service cannot be reused without proper context.
     */
    public function test_service_cannot_be_reused_without_context(): void
    {
        // Create availability
        $availability = Availability::for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Create schedule with proper context
        $scheduleService = Schedule::for($this->testSchedulable)->owner($availability);
        $scheduleService->create([
            'title' => 'First Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ]);

        // Try to reuse the same service instance without context - should fail
        $this->expectException(InvalidServiceContextException::class);

        // Create another instance but don't set owner
        $scheduleService2 = Schedule::for($this->testSchedulable);
        $scheduleService2->create([
            'title' => 'Should Fail',
            'start_datetime' => '2038-07-01 14:00:00',
            'end_datetime' => '2038-07-01 15:00:00',
        ]);
    }
}
