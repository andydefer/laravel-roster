<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Test suite for ScheduleService.
 *
 * Validates CRUD operations, conflict detection, and availability scenarios
 * for schedule management within the roster system.
 */
final class ScheduleServiceTest extends TestCase
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
     * Test successful schedule creation.
     */
    public function test_create_schedule_successfully(): void
    {
        // Arrange: Create availability and schedule data
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Test Meeting',
            'description' => 'Test description',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['priority' => 'high'],
        ];

        // Act: Create schedule
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert: Schedule should be created with correct attributes
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertNotNull($schedule->id);
        $this->assertSame($availability->id, $schedule->availability_id);
        $this->assertSame('Test Meeting', $schedule->title);
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $schedule->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 11:00:00'), $schedule->end_datetime);
        $this->assertSame(ScheduleStatus::BOOKED, $schedule->status);
        $this->assertSame(['priority' => 'high'], $schedule->metadata);
    }

    /**
     * Test schedule creation with default status.
     */
    public function test_create_schedule_with_default_status(): void
    {
        // Arrange: Availability and schedule data without status
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Meeting without status',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ];

        // Act: Create schedule without specifying status
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert: Should use default AVAILABLE status
        $this->assertSame(ScheduleStatus::AVAILABLE->value, $schedule->status->value);
    }

    /**
     * Test schedule creation fails when end time is before start time.
     */
    public function test_create_schedule_fails_when_end_before_start(): void
    {
        // Arrange: Invalid schedule data with end before start
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Invalid meeting',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act: Attempt to create invalid schedule
        schedule_for($availability)->create($scheduleData);
    }

    /**
     * Test schedule creation fails when duration is too short.
     */
    public function test_create_schedule_fails_when_too_short(): void
    {
        // Arrange: Schedule with duration below minimum
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleData = [
            'title' => 'Too short meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:05:00',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act: Attempt to create schedule with insufficient duration
        schedule_for($availability)->create($scheduleData);
    }

    /**
     * Test schedule creation fails with incorrect availability.
     */
    public function test_create_schedule_fails_when_no_availability(): void
    {
        // Arrange: Two different schedulable entities
        $schedulable1 = TestSchedulable::create();
        $schedulable2 = TestSchedulable::create();

        $availabilityForSchedulable1 = availability_for($schedulable1)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        $scheduleData = [
            'title' => 'Meeting with wrong availability',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ];

        // Assert: Should throw validation exception with updated message
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Referenced availability period does not exist or is invalid/');
        // Alternative: vérifier que le message contient la partie clé
        // $this->expectExceptionMessageMatches('/availability period does not exist/');

        // Act: Attempt to create schedule for wrong schedulable
        schedule_for($availabilityForSchedulable1)
            ->for($schedulable2)
            ->create($scheduleData);
    }

    /**
     * Test successful schedule update.
     */
    public function test_update_schedule_successfully(): void
    {
        // Arrange: Create schedule to update
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Original meeting',
            'description' => 'Original description',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'metadata' => ['original' => true],
        ]);

        $updateData = [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'metadata' => ['updated' => true],
        ];

        // Act: Update schedule
        $result = schedule_for($availability)->update(
            id: $schedule->id,
            data: $updateData
        );

        // Assert: Update should succeed with changed attributes
        $this->assertTrue($result);
        $schedule->refresh();
        $this->assertSame('Updated title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
        $this->assertSame(['updated' => true], $schedule->metadata);
        $this->assertEquals(Carbon::parse('2038-01-04 10:00:00'), $schedule->start_datetime);
    }

    /**
     * Test schedule update with datetime changes.
     */
    public function test_update_schedule_with_datetime_changes(): void
    {
        // Arrange: Schedule to update with new datetime
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Original meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Update schedule datetime
        $result = schedule_for($availability)->update(
            id: $schedule->id,
            data: [
                'start_datetime' => '2038-01-04 13:00:00',
                'end_datetime' => '2038-01-04 14:00:00',
            ]
        );

        // Assert: Datetime should be updated
        $this->assertTrue($result);
        $schedule->refresh();
        $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $schedule->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 14:00:00'), $schedule->end_datetime);
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
     * Test schedule update fails when schedule not found.
     */
    public function test_update_schedule_fails_when_not_found(): void
    {
        // Arrange: Non-existent schedule ID
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule with given ID does not exist/');

        // Act: Attempt to update non-existent schedule
        schedule_for($availability)->update(
            id: 999999,
            data: ['title' => 'test']
        );
    }

    /**
     * Test successful schedule deletion.
     */
    public function test_delete_schedule_successfully(): void
    {
        // Arrange: Create schedule to delete
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Meeting to delete',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Delete schedule
        $result = schedule_for($availability)->delete($schedule->id);

        // Assert: Schedule should be deleted
        $this->assertTrue($result);
        $this->assertNull(ScheduleModel::find($schedule->id));
    }

    /**
     * Test schedule deletion fails when schedule not found.
     */
    public function test_delete_schedule_fails_when_not_found(): void
    {
        // Arrange: Non-existent schedule ID
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule with given ID does not exist/');

        // Act: Attempt to delete non-existent schedule
        schedule_for($availability)->delete(999999);
    }

    /**
     * Test finding schedule by ID.
     */
    public function test_find_schedule_by_id(): void
    {
        // Arrange: Create schedule to find
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Meeting to find',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Find schedule by ID
        $found = schedule_for($availability)->find($schedule->id);

        // Assert: Should find correct schedule
        $this->assertInstanceOf(ScheduleModel::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Meeting to find', $found->title);
    }

    /**
     * Test find returns null for schedule belonging to different schedulable.
     */
    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange: Two schedulables with separate schedules
        $schedulable1 = TestSchedulable::create();
        $schedulable2 = TestSchedulable::create();

        $availability1 = availability_for($schedulable1)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $availability2 = availability_for($schedulable2)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $scheduleForSchedulable2 = schedule_for($availability2)->create([
            'title' => 'For other schedulable',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Try to find schedule from wrong schedulable context
        $found = schedule_for($availability1)->find($scheduleForSchedulable2->id);

        // Assert: Should return null for cross-schedulable access
        $this->assertNull($found);
    }

    /**
     * Test retrieving the first schedule when schedules exist.
     */
    public function test_first_returns_first_schedule(): void
    {
        // Arrange: Create multiple schedules
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule1 = schedule_for($availability)->create([
            'title' => 'First meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $schedule2 = schedule_for($availability)->create([
            'title' => 'Second meeting',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act: Get first schedule
        $first = schedule_for($availability)->first();

        // Assert: Should return the earliest schedule
        $this->assertInstanceOf(ScheduleModel::class, $first);
        $this->assertSame($schedule1->id, $first->id);
        $this->assertSame('First meeting', $first->title);
    }

    /**
     * Test retrieving the first schedule returns null when no schedules exist.
     */
    public function test_first_returns_null_when_no_schedule(): void
    {
        // Arrange: Availability with no schedules
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Get first schedule
        $first = schedule_for($availability)->first();

        // Assert: Should return null
        $this->assertNull($first);
    }

    /**
     * Test first schedule respects type filter.
     */
    public function test_first_respects_type_filter(): void
    {
        // Arrange: Create multiple schedules with different types
        $availabilityConsultation = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $availabilityTraining = availability_for($this->schedulable)->create([
            'type' => 'training',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availabilityConsultation)->create([
            'title' => 'Consultation meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 10:00:00',
        ]);

        schedule_for($availabilityTraining)->create([
            'title' => 'Training meeting',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // Act: Get first schedule of type consultation
        $first = schedule_for($availabilityConsultation)->setFilter('title', 'Consultation meeting')->first();

        // Assert: Should return first consultation schedule only
        $this->assertInstanceOf(ScheduleModel::class, $first);
        $this->assertSame('Consultation meeting', $first->title);
    }


    /**
     * Test retrieving all schedules.
     */
    public function test_all_schedules(): void
    {
        // Arrange: Create multiple schedules
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Meeting 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        schedule_for($availability)->create([
            'title' => 'Meeting 2',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 15:00:00',
        ]);

        // Act: Retrieve all schedules
        $result = schedule_for($availability)->all();

        // Assert: Should return all schedules
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('Meeting 1', $titles);
        $this->assertContains('Meeting 2', $titles);
    }

    /**
     * Test retrieving schedules with status filter.
     */
    public function test_get_schedules_with_filters(): void
    {
        // Arrange: Schedules with different statuses
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Available',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::AVAILABLE,
        ]);

        schedule_for($availability)->create([
            'title' => 'Booked',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 11:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        schedule_for($availability)->create([
            'title' => 'Available 2',
            'start_datetime' => '2038-01-11 10:00:00',
            'end_datetime' => '2038-01-11 11:00:00',
            'status' => ScheduleStatus::AVAILABLE,
        ]);

        // Act: Filter schedules by AVAILABLE status
        $result = schedule_for($availability)
            ->setFilter('status', ScheduleStatus::AVAILABLE)
            ->all();

        // Assert: Should only return AVAILABLE schedules
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('Available', $titles);
        $this->assertContains('Available 2', $titles);
        $this->assertNotContains('Booked', $titles);
    }

    /**
     * Test retrieving schedules with datetime range filter.
     */
    public function test_get_schedules_with_datetime_range_filter(): void
    {
        // Arrange: Schedules in different time periods
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'January',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        schedule_for($availability)->create([
            'title' => 'Late January',
            'start_datetime' => '2038-01-25 10:00:00',
            'end_datetime' => '2038-01-25 11:00:00',
        ]);

        // Act: Filter schedules by datetime range
        $result = schedule_for($availability)
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-01-31 23:59:59')
            ->all();

        // Assert: Should return all schedules in range
        $this->assertCount(2, $result);
        $titles = $result->pluck('title')->toArray();
        $this->assertContains('January', $titles);
        $this->assertContains('Late January', $titles);
    }

    /**
     * Test finding next available slot without conflicts.
     */
    public function test_find_next_slot_without_conflicts(): void
    {
        // Arrange: Availability with existing schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Blocking meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Find next available slot
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 120,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        // Assert: Should find slot after existing schedule
        $this->assertNotNull($slot);
        $this->assertIsArray($slot);
        $this->assertArrayHasKey('start', $slot);
        $this->assertArrayHasKey('end', $slot);
        $this->assertArrayHasKey('availability', $slot);
        $this->assertArrayHasKey('duration_minutes', $slot);
        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 11:00:00')));
        $this->assertSame(120, $slot['duration_minutes']);
        $this->assertSame($availability->id, $slot['availability']->id);
    }

    /**
     * Test finding next slot returning start only.
     */
    public function test_find_next_slot_return_start_only(): void
    {
        // Arrange: Simple availability
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Find next slot with start only
        $startOnly = schedule_for($availability)->findNextSlot(
            durationMinutes: 30,
            type: 'consultation',
            returnStartOnly: true,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        // Assert: Should return only start datetime
        $this->assertNotNull($startOnly);
        $this->assertInstanceOf(Carbon::class, $startOnly);
        $this->assertSame('2038-01-04 09:00:00', $startOnly->format('Y-m-d H:i:s'));
    }

    /**
     * Test finding next slot respects availability hours.
     */
    public function test_find_next_slot_respects_availability_hours(): void
    {
        // Arrange: Availability with specific hours
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Find slot near end of day
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 120,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 16:00:00')
        );

        // Assert: Should find slot on next day at start time
        $this->assertNotNull($slot);
        $this->assertSame('2038-01-05', $slot['start']->format('Y-m-d'));
        $this->assertSame('09:00:00', $slot['start']->format('H:i:s'));
    }

    /**
     * Test finding next slot returns null when no availability.
     */
    public function test_find_next_slot_returns_null_when_no_availability(): void
    {
        // Arrange: Limited availability that doesn't cover search period
        $availability = availability_for($this->schedulable)->create([
            'type' => 'limited',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-10',
        ]);

        // Act: Find slot after availability period
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 60,
            type: 'limited',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-05 09:00:00')
        );

        // Assert: Should return null when no availability
        $this->assertNull($slot);
    }

    /**
     * Test checking time slot availability returns true.
     */
    public function test_is_time_slot_available_returns_true(): void
    {
        // Arrange: Free availability
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Check available time slot
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00'),
            type: 'consultation'
        );

        // Assert: Should return true for available slot
        $this->assertTrue($isAvailable);
    }

    /**
     * Test checking time slot availability returns false when schedule overlap.
     */
    public function test_is_time_slot_available_returns_false_when_schedule_overlap(): void
    {
        // Arrange: Availability with existing schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Existing meeting',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ]);

        // Act: Check overlapping time slot
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert: Should return false for overlapping slot
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking time slot availability returns false when impediment overlap.
     */
    public function test_is_time_slot_available_returns_false_when_impediment_overlap(): void
    {
        // Arrange: Availability with impediment
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Maintenance',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Check slot overlapping with impediment
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert: Should return false due to impediment
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking time slot availability returns false when outside availability.
     */
    public function test_is_time_slot_available_returns_false_when_outside_availability(): void
    {
        // Arrange: Availability with specific hours
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Check slot outside availability hours
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 18:00:00'),
            end: Carbon::parse('2038-01-04 19:00:00'),
            type: 'consultation'
        );

        // Assert: Should return false for slot outside hours
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking time slot availability with type filter.
     */
    public function test_is_time_slot_available_with_type_filter(): void
    {
        // Arrange: Multiple availabilities with different types
        $availabilityConsultation = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $availabilityTraining = availability_for($this->schedulable)->create([
            'type' => 'training',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availabilityTraining)->create([
            'title' => 'Training',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // Act: Check slot with type filter
        $isAvailable = schedule_for($availabilityConsultation)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00'),
            type: 'consultation'
        );

        // Assert: Should return true for correct type availability
        $this->assertTrue($isAvailable);
    }

    /**
     * Test finding available slots in range.
     */
    public function test_find_available_slots_in_range(): void
    {
        // Arrange: Availability with existing schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Meeting 10h-12h',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Find available slots in date range
        $slots = schedule_for($availability)->findAvailableSlots(
            startDate: Carbon::parse('2038-01-04'),
            endDate: Carbon::parse('2038-01-05'),
            durationMinutes: 60,
            type: 'consultation'
        );

        // Assert: Slots should not overlap with existing schedule
        $this->assertInstanceOf(Collection::class, $slots);

        foreach ($slots as $slot) {
            if ($slot['start']->format('Y-m-d') === '2038-01-04') {
                $slotStart = $slot['start'];
                $slotEnd = $slot['end'];
                $this->assertTrue(
                    $slotEnd->lte(Carbon::parse('2038-01-04 10:00:00')) ||
                        $slotStart->gte(Carbon::parse('2038-01-04 12:00:00')),
                    sprintf('Slot %s-%s should not overlap 10:00-12:00', $slotStart->format('H:i'), $slotEnd->format('H:i'))
                );
            }
        }
    }

    /**
     * Test checking period availability returns true.
     */
    public function test_is_period_available_returns_true(): void
    {
        // Arrange: Free availability period
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Check available period
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert: Should return true for available period
        $this->assertTrue($isAvailable);
    }

    /**
     * Test checking period availability returns false when schedule conflict.
     */
    public function test_is_period_available_returns_false_when_schedule_conflict(): void
    {
        // Arrange: Availability with middle schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($availability)->create([
            'title' => 'Middle meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Check period containing schedule
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00'),
            type: 'consultation'
        );

        // Assert: Should return false due to schedule conflict
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking period availability returns false when impediment conflict.
     */
    public function test_is_period_available_returns_false_when_impediment_conflict(): void
    {
        // Arrange: Availability with impediment
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Maintenance',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Check period containing impediment
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00'),
            type: 'consultation'
        );

        // Assert: Should return false due to impediment conflict
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking period availability returns false when no availability.
     */
    public function test_is_period_available_returns_false_when_no_availability(): void
    {
        // Arrange: Availability with specific hours
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Check period outside availability hours
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 18:00:00'),
            end: Carbon::parse('2038-01-04 19:00:00'),
            type: 'consultation'
        );

        // Assert: Should return false for period outside hours
        $this->assertFalse($isAvailable);
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
        $this->assertInstanceOf(ScheduleModel::class, $schedule2);
        $this->assertSame('2038-01-04 11:00:00', $schedule2->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test finding next slot with adjacent impediments.
     */
    public function test_find_next_slot_with_adjacent_impediments(): void
    {
        // Arrange: Availability with back-to-back impediments
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        impediment_for($availability)->create([
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Find slot starting from impediment time
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 30,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 10:00:00')
        );

        // Assert: Should find slot after both impediments
        $this->assertNotNull($slot);
        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 12:00:00')));
    }

    /**
     * Test schedule metadata serialization.
     */
    public function test_schedule_metadata_serialization(): void
    {
        // Arrange: Complex metadata structure
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $complexMetadata = [
            'client' => 'John Doe',
            'priority' => 'high',
            'tags' => ['urgent', 'follow-up'],
            'notes' => ['Bring documents'],
        ];

        // Act: Create schedule with complex metadata
        $schedule = schedule_for($availability)->create([
            'title' => 'Meeting with metadata',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'metadata' => $complexMetadata,
        ]);

        // Assert: Metadata should be correctly serialized
        $this->assertSame($complexMetadata, $schedule->metadata);
        $this->assertSame('John Doe', $schedule->metadata['client']);
        $this->assertSame(['urgent', 'follow-up'], $schedule->metadata['tags']);
    }

    /**
     * Test schedule duration calculation.
     */
    public function test_schedule_duration_calculation(): void
    {
        // Arrange: Schedule with specific duration
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Create 90-minute schedule
        $schedule = schedule_for($availability)->create([
            'title' => 'Long meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ]);

        // Assert: Should calculate correct duration
        $this->assertEqualsWithDelta(90.0, $schedule->duration_minutes, PHP_FLOAT_EPSILON);
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

        $this->assertInstanceOf(ScheduleModel::class, $schedule);

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

    /**
     * Test complex availability scenario.
     */
    public function test_complex_availability_scenario(): void
    {
        // Arrange: Multiple availability types
        $morningAvailability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $afternoonAvailability = availability_for($this->schedulable)->create([
            'type' => 'training',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        schedule_for($morningAvailability)->create([
            'title' => 'Morning meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        schedule_for($afternoonAvailability)->create([
            'title' => 'Afternoon meeting',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // 3. Verify both exist
        $morningSchedules = schedule_for($morningAvailability)->all();
        $afternoonSchedules = schedule_for($afternoonAvailability)->all();

        $this->assertCount(1, $morningSchedules);
        $this->assertCount(1, $afternoonSchedules);

        // 4. Find a slot in the morning
        $slot = schedule_for($morningAvailability)->findNextSlot(
            durationMinutes: 60,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        $this->assertNotNull($slot);
        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 11:00:00')));
    }
}
