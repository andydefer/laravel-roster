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
use Roster\Models\Availability as AvailabilityModel;
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

    private Model $testSchedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();

        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);
    }

    /**
     * Test successful schedule creation.
     */
    public function test_create_schedule_successfully(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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
            'status' => ScheduleStatus::BOOKED->value,
            'metadata' => ['priority' => 'high'],
        ];

        // Act
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert
        $this->assertSame(ScheduleStatus::AVAILABLE->value, $schedule->status->value);
    }

    /**
     * Test schedule creation fails when end time is before start time.
     */
    public function test_create_schedule_fails_when_end_before_start(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act
        schedule_for($availability)->create($scheduleData);
    }

    /**
     * Test schedule creation fails when duration is too short.
     */
    public function test_create_schedule_fails_when_too_short(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        schedule_for($availability)->create($scheduleData);
    }

    /**
     * Test schedule creation fails with incorrect availability.
     */
    public function test_create_schedule_fails_when_no_availability(): void
    {
        // Arrange
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

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Create validation failed for Schedule: availability_id → Invalid availability ID/');

        // Act
        schedule_for($availabilityForSchedulable1)
            ->for($schedulable2)
            ->create($scheduleData);
    }

    /**
     * Test successful schedule update.
     */
    public function test_update_schedule_successfully(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $result = schedule_for($availability)->update($schedule->id, $updateData);

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $result = schedule_for($availability)->update($schedule->id, [
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 14:00:00',
        ]);

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing schedule/');

        // Act
        schedule_for($availability)->update($schedule1->id, $updateData);
    }

    /**
     * Test schedule creation fails when overlapping with existing schedule.
     */
    public function test_create_schedule_fails_when_overlap(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/overlaps with existing schedule/');

        // Act
        schedule_for($availability)->create($overlappingData);
    }

    /**
     * Test schedule update fails when schedule not found.
     */
    public function test_update_schedule_fails_when_not_found(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule with given ID does not exist/');

        // Act
        schedule_for($availability)->update(999999, ['title' => 'test']);
    }

    /**
     * Test successful schedule deletion.
     */
    public function test_delete_schedule_successfully(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $result = schedule_for($availability)->delete($schedule->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(ScheduleModel::find($schedule->id));
    }

    /**
     * Test schedule deletion fails when schedule not found.
     */
    public function test_delete_schedule_fails_when_not_found(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule with given ID does not exist/');

        // Act
        schedule_for($availability)->delete(999999);
    }

    /**
     * Test finding schedule by ID.
     */
    public function test_find_schedule_by_id(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $found = schedule_for($availability)->find($schedule->id);

        // Assert
        $this->assertInstanceOf(ScheduleModel::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Meeting to find', $found->title);
    }

    /**
     * Test find returns null for schedule belonging to different schedulable.
     */
    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange
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

        // Act
        $found = schedule_for($availability1)->find($scheduleForSchedulable2->id);

        // Assert
        $this->assertNull($found);
    }

    /**
     * Test retrieving all schedules.
     */
    public function test_all_schedules(): void
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
            'title' => 'Meeting 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        schedule_for($availability)->create([
            'title' => 'Meeting 2',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 15:00:00',
        ]);

        // Act
        $result = schedule_for($availability)->all();

        // Assert
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
            'title' => 'Available',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::AVAILABLE->value,
        ]);

        schedule_for($availability)->create([
            'title' => 'Booked',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 11:00:00',
            'status' => ScheduleStatus::BOOKED->value,
        ]);

        schedule_for($availability)->create([
            'title' => 'Available 2',
            'start_datetime' => '2038-01-11 10:00:00',
            'end_datetime' => '2038-01-11 11:00:00',
            'status' => ScheduleStatus::AVAILABLE->value,
        ]);

        // Act
        $result = schedule_for($availability)
            ->setFilter('status', ScheduleStatus::AVAILABLE->value)
            ->all();

        // Assert
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
            'title' => 'January',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        schedule_for($availability)->create([
            'title' => 'Late January',
            'start_datetime' => '2038-01-25 10:00:00',
            'end_datetime' => '2038-01-25 11:00:00',
        ]);

        // Act
        $result = schedule_for($availability)
            ->setFilter('start_datetime', '2038-01-01 00:00:00')
            ->setFilter('end_datetime', '2038-01-31 23:59:59')
            ->all();

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 120,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $startOnly = schedule_for($availability)->findNextSlot(
            durationMinutes: 30,
            type: 'consultation',
            returnStartOnly: true,
            startFrom: Carbon::parse('2038-01-04 09:00:00')
        );

        // Assert
        $this->assertNotNull($startOnly);
        $this->assertInstanceOf(Carbon::class, $startOnly);
        $this->assertSame('2038-01-04 09:00:00', $startOnly->format('Y-m-d H:i:s'));
    }

    /**
     * Test finding next slot respects availability hours.
     */
    public function test_find_next_slot_respects_availability_hours(): void
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
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 120,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 16:00:00')
        );

        // Assert
        $this->assertNotNull($slot);
        $this->assertSame('2038-01-05', $slot['start']->format('Y-m-d'));
        $this->assertSame('09:00:00', $slot['start']->format('H:i:s'));
    }

    /**
     * Test finding next slot returns null when no availability.
     */
    public function test_find_next_slot_returns_null_when_no_availability(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'limited',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-10',
        ]);

        // Act
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 60,
            type: 'limited',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-05 09:00:00')
        );

        // Assert
        $this->assertNull($slot);
    }

    /**
     * Test checking time slot availability returns true.
     */
    public function test_is_time_slot_available_returns_true(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertTrue($isAvailable);
    }

    /**
     * Test checking time slot availability returns false when schedule overlap.
     */
    public function test_is_time_slot_available_returns_false_when_schedule_overlap(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking time slot availability returns false when impediment overlap.
     */
    public function test_is_time_slot_available_returns_false_when_impediment_overlap(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking time slot availability returns false when outside availability.
     */
    public function test_is_time_slot_available_returns_false_when_outside_availability(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $isAvailable = schedule_for($availability)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 18:00:00'),
            end: Carbon::parse('2038-01-04 19:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking time slot availability with type filter.
     */
    public function test_is_time_slot_available_with_type_filter(): void
    {
        // Arrange
        $availabilityConsultation = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $availabilityTraining = availability_for($this->testSchedulable)->create([
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

        // Act
        $isAvailable = schedule_for($availabilityConsultation)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertTrue($isAvailable);
    }

    /**
     * Test finding available slots in range.
     */
    public function test_find_available_slots_in_range(): void
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
            'title' => 'Meeting 10h-12h',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $slots = schedule_for($availability)->findAvailableSlots(
            startDate: Carbon::parse('2038-01-04'),
            endDate: Carbon::parse('2038-01-05'),
            durationMinutes: 60,
            type: 'consultation'
        );

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertTrue($isAvailable);
    }

    /**
     * Test checking period availability returns false when schedule conflict.
     */
    public function test_is_period_available_returns_false_when_schedule_conflict(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking period availability returns false when impediment conflict.
     */
    public function test_is_period_available_returns_false_when_impediment_conflict(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /**
     * Test checking period availability returns false when no availability.
     */
    public function test_is_period_available_returns_false_when_no_availability(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $isAvailable = schedule_for($availability)->isPeriodAvailable(
            start: Carbon::parse('2038-01-04 18:00:00'),
            end: Carbon::parse('2038-01-04 19:00:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isAvailable);
    }

    /**
     * Test concurrent schedule creation prevents double booking.
     */
    public function test_concurrent_schedule_creation_prevents_double_booking(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing schedule/');

        // Act
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
        // Arrange
        $mondayOnlyAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'monday-only',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/The selected date 2038-01-05 \(tuesday\) is not allowed because this availability only permits the following days: monday/'
        );

        // Act
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/The selected start time .* is before the availability start time .*/'
        );

        // Act
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $schedule2 = schedule_for($availability)->create([
            'title' => 'Second meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert
        $this->assertInstanceOf(ScheduleModel::class, $schedule2);
        $this->assertSame('2038-01-04 11:00:00', $schedule2->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test finding next slot with adjacent impediments.
     */
    public function test_find_next_slot_with_adjacent_impediments(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $slot = schedule_for($availability)->findNextSlot(
            durationMinutes: 30,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 10:00:00')
        );

        // Assert
        $this->assertNotNull($slot);
        $this->assertTrue($slot['start']->gte(Carbon::parse('2038-01-04 12:00:00')));
    }

    /**
     * Test schedule metadata serialization.
     */
    public function test_schedule_metadata_serialization(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        // Act
        $schedule = schedule_for($availability)->create([
            'title' => 'Meeting with metadata',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'metadata' => $complexMetadata,
        ]);

        // Assert
        $this->assertSame($complexMetadata, $schedule->metadata);
        $this->assertSame('John Doe', $schedule->metadata['client']);
        $this->assertSame(['urgent', 'follow-up'], $schedule->metadata['tags']);
    }

    /**
     * Test schedule duration calculation.
     */
    public function test_schedule_duration_calculation(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act
        $schedule = schedule_for($availability)->create([
            'title' => 'Long meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ]);

        // Assert
        $this->assertSame(90.0, $schedule->duration_minutes);
    }

    /**
     * Test full booking scenario.
     */
    public function test_full_booking_scenario(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
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

        schedule_for($availability)->update($schedule->id, [
            'start_datetime' => '2038-01-04 14:30:00',
            'end_datetime' => '2038-01-04 15:30:00',
        ]);

        // 4. Verify original unchanged
        $schedule->refresh();
        $this->assertSame('2038-01-04 10:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test complex availability scenario.
     */
    public function test_complex_availability_scenario(): void
    {
        // Arrange
        $morningAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $afternoonAvailability = availability_for($this->testSchedulable)->create([
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
