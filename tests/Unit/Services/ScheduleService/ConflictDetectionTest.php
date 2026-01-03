<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for conflict detection and overlapping schedules.
 */
final class ConflictDetectionTest extends TestCase
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

        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);
    }

    /**
     * Test schedule update fails when overlapping with existing schedule.
     */
    public function test_update_schedule_fails_when_overlap(): void
    {
        // Arrange: Two schedules where update would cause overlap
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule1 = schedule_for($availability)->create([
            'title' => 'Meeting 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        schedule_for($availability)->create([
            'title' => 'Meeting 2',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        $updateData = [
            'start_datetime' => '2038-01-04 12:30:00',
            'end_datetime' => '2038-01-04 13:30:00',
        ];

        // Assert: Should throw validation exception for overlap
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing schedule/');

        // Act: Attempt overlapping update
        schedule_for($availability)->update(
            id: $schedule1->id,
            data: $updateData
        );
    }

    /**
     * Test schedule creation fails when overlapping with existing schedule.
     */
    public function test_create_schedule_fails_when_overlap(): void
    {
        // Arrange: Existing schedule and overlapping new schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Existing schedule',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $overlappingData = [
            'title' => 'Overlapping schedule',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/overlaps with existing schedule/');

        // Act: Attempt to create overlapping schedule
        schedule_for($availability)->create($overlappingData);
    }

    /**
     * Test concurrent schedule creation prevents double booking.
     */
    public function test_concurrent_schedule_creation_prevents_double_booking(): void
    {
        // Arrange: Existing schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'First meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Assert: Should prevent overlapping schedule
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing schedule/');

        // Act: Attempt to create overlapping schedule
        schedule_for($availability)->create([
            'title' => 'Second meeting',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ]);
    }

    /**
     * Test schedule creation fails on non-availability day.
     */
    public function test_schedule_on_non_availability_day(): void
    {
        // Arrange: Monday-only availability
        $mondayOnlyAvailability = availability_for($this->schedulable)->create([
            'type' => 'monday-only',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/The selected date 2038-01-05 \(tuesday\) is not allowed\. Allowed days: monday/'
        );

        // Act: Attempt to schedule on Tuesday
        schedule_for($mondayOnlyAvailability)->create([
            'title' => 'Tuesday meeting',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 11:00:00',
        ]);
    }

    /**
     * Test schedule creation fails outside availability hours.
     */
    public function test_schedule_outside_availability_hours(): void
    {
        // Arrange: Availability with specific start time
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert: Should prevent schedule before availability hours
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/The selected start time .* is before the availability start time .*/'
        );

        // Act: Attempt to schedule before availability start
        schedule_for($availability)->create([
            'title' => 'Too early meeting',
            'start_datetime' => '2038-01-04 08:00:00',
            'end_datetime' => '2038-01-04 09:00:00',
        ]);
    }

    /**
     * Test schedules at exact boundaries do not overlap.
     */
    public function test_schedule_exact_boundary_not_overlap(): void
    {
        // Arrange: First schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'First meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Create adjacent schedule
        $schedule2 = schedule_for($availability)->create([
            'title' => 'Second meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert: Should allow adjacent schedules
        $this->assertInstanceOf(\Roster\Models\Schedule::class, $schedule2);
        $this->assertSame('2038-01-04 11:00:00', $schedule2->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test full booking scenario.
     */
    public function test_full_booking_scenario(): void
    {
        // Arrange: Availability for booking flow
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // 1. Find a slot
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 60,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        $this->assertNotNull($slot);

        // 2. Verify it's available
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: $slot['start'],
            end: $slot['end'],
            type: 'consultation'
        );

        $this->assertTrue($isAvailable);

        // 3. Create the schedule
        $schedule = schedule_for($availability)->create([
            'title' => 'Created meeting',
            'start_datetime' => $slot['start']->format('Y-m-d H:i:s'),
            'end_datetime' => $slot['end']->format('Y-m-d H:i:s'),
        ]);

        $this->assertInstanceOf(\Roster\Models\Schedule::class, $schedule);

        // 4. Verify cannot recreate same slot
        $this->expectException(ValidationFailedException::class);

        schedule_for($availability)->create([
            'title' => 'Second meeting',
            'start_datetime' => $slot['start']->format('Y-m-d H:i:s'),
            'end_datetime' => $slot['end']->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Test reschedule scenario.
     */
    public function test_reschedule_scenario(): void
    {
        // Arrange: Two schedules for rescheduling test
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // 1. Create a schedule
        $schedule = schedule_for($availability)->create([
            'title' => 'Original meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // 2. Create another schedule
        schedule_for($availability)->create([
            'title' => 'Other meeting',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // 3. Try to move first to overlap second
        $this->expectException(ValidationFailedException::class);

        schedule_for($availability)->update(
            id: $schedule->id,
            data: [
                'start_datetime' => '2038-01-04 14:30:00',
                'end_datetime' => '2038-01-04 15:30:00',
            ]
        );

        // 4. Verify original unchanged
        $schedule->refresh();
        $this->assertSame('2038-01-04 10:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
    }
}
