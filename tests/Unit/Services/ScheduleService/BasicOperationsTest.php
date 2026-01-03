<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for basic CRUD operations of ScheduleService.
 */
final class BasicOperationsTest extends TestCase
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
}
