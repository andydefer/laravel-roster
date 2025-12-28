<?php

declare(strict_types=1);

namespace Integration\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Integration tests for Schedule database operations and business logic.
 *
 * Validates the interaction between Schedule models, database constraints,
 * and business rule enforcement at the database level.
 */
#[Group('integration')]
#[Group('database')]
#[Group('schedule')]
final class ScheduleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Availability model used for schedule testing.
     */
    private AvailabilityModel $availabilityModel;

    /**
     * Set up test environment with required entities.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $testSchedulable = TestSchedulable::create(['name' => 'Dr. Test']);

        $this->availabilityModel = availability_for($testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);
    }

    /**
     * Test successful creation of a schedule with valid data.
     */
    public function test_create_schedule_with_valid_data(): void
    {
        // Arrange: Prepare schedule data
        $scheduleData = [
            'title' => 'Annual Health Check',
            'description' => 'Comprehensive annual health examination',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => [
                'patient_id' => 1001,
                'insurance' => 'HealthPlus',
                'tests' => ['blood', 'xray'],
            ],
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->availabilityModel)->create($scheduleData);

        // Assert: Verify schedule properties and database persistence
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertEquals('Annual Health Check', $schedule->title);
        $this->assertEquals(ScheduleStatus::BOOKED, $schedule->status);
        $this->assertEquals('2038-01-04 10:00:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals(1001, $schedule->metadata['patient_id']);

        $this->assertDatabaseHas('roster_schedules', [
            'id' => $schedule->id,
            'availability_id' => $this->availabilityModel->id,
            'title' => 'Annual Health Check',
        ]);
    }

    /**
     * Test validation failure when creating overlapping schedules.
     */
    public function test_validation_fails_for_overlapping_schedules(): void
    {
        // Arrange: Create initial schedule
        schedule_for($this->availabilityModel)->create([
            'title' => 'First Appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Arrange: Prepare overlapping schedule data
        $overlappingData = [
            'title' => 'Overlapping Appointment',
            'start_datetime' => '2038-01-04 10:30:00',
            'end_datetime' => '2038-01-04 11:30:00',
        ];

        // Act & Assert: Verify overlapping schedule creation fails
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('overlap');

        schedule_for($this->availabilityModel)->create($overlappingData);
    }

    /**
     * Test schedule creation outside availability hours is rejected.
     */
    public function test_cannot_create_schedule_outside_availability_hours(): void
    {
        // Arrange: Prepare schedule data outside availability hours
        $outsideHoursData = [
            'title' => 'Late Appointment',
            'start_datetime' => '2038-01-04 18:00:00',
            'end_datetime' => '2038-01-04 19:00:00',
        ];

        // Act & Assert: Verify schedule creation fails
        $this->expectException(ValidationFailedException::class);

        schedule_for($this->availabilityModel)->create($outsideHoursData);
    }

    /**
     * Test successful update of schedule metadata.
     */
    public function test_update_schedule_metadata_successfully(): void
    {
        // Arrange: Create initial schedule
        $schedule = schedule_for($this->availabilityModel)->create([
            'title' => 'Initial Appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'metadata' => ['patient_id' => 1001],
        ]);

        // Arrange: Prepare update data
        $updateData = [
            'title' => 'Updated Appointment',
            'metadata' => [
                'patient_id' => 1001,
                'additional_tests' => ['ecg', 'ultrasound'],
                'notes' => 'Patient requires follow-up',
            ],
        ];

        // Act: Update schedule
        $updateResult = schedule_for($this->availabilityModel)->update(
            id: $schedule->id,
            data: $updateData
        );

        // Assert: Verify update was successful
        $this->assertTrue($updateResult);

        $schedule->refresh();
        $this->assertEquals('Updated Appointment', $schedule->title);
        $this->assertContains('ecg', $schedule->metadata['additional_tests']);

        $this->assertDatabaseHas('roster_schedules', [
            'id' => $schedule->id,
            'title' => 'Updated Appointment',
        ]);
    }

    /**
     * Test schedule deletion and soft delete functionality.
     */
    public function test_delete_schedule_successfully(): void
    {
        // Arrange: Create schedule to delete
        $schedule = schedule_for($this->availabilityModel)->create([
            'title' => 'Appointment to Delete',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Delete schedule
        $deleteResult = schedule_for($this->availabilityModel)->delete($schedule->id);

        // Assert: Verify soft deletion
        $this->assertTrue($deleteResult);
        $this->assertSoftDeleted('roster_schedules', ['id' => $schedule->id]);
        $this->assertNull(ScheduleModel::find($schedule->id));
    }

    /**
     * Test retrieval of all schedules with status filtering.
     */
    public function test_retrieve_schedules_with_status_filter(): void
    {
        // Arrange: Create schedules with different statuses
        schedule_for($this->availabilityModel)->create([
            'title' => 'Booked Appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
            'status' => ScheduleStatus::BOOKED,
        ]);

        schedule_for($this->availabilityModel)->create([
            'title' => 'Cancelled Appointment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'status' => ScheduleStatus::CANCELLED,
        ]);

        // Act: Retrieve filtered and all schedules
        $bookedSchedules = schedule_for($this->availabilityModel)
            ->setFilter('status', ScheduleStatus::BOOKED)
            ->all();

        $allSchedules = schedule_for($this->availabilityModel)->all();

        // Assert: Verify filtering works correctly
        $this->assertInstanceOf(Collection::class, $bookedSchedules);
        $this->assertCount(1, $bookedSchedules);
        $this->assertEquals('Booked Appointment', $bookedSchedules->first()->title);

        $this->assertCount(2, $allSchedules);
    }

    /**
     * Test find next available slot after existing schedules.
     */
    public function test_find_next_available_slot(): void
    {
        // Arrange: Create existing schedules
        schedule_for($this->availabilityModel)->create([
            'title' => 'Morning Appointment',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 10:00:00',
        ]);

        schedule_for($this->availabilityModel)->create([
            'title' => 'Late Morning Appointment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Find next available slot
        $nextSlot = schedule_for($this->availabilityModel)->findNextSlot(
            durationMinutes: 30,
            type: 'consultation',
            returnStartOnly: false,
            startFrom: Carbon::parse('2038-01-04 08:00:00')
        );

        // Assert: Verify slot properties
        $this->assertNotNull($nextSlot);
        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);

        $slotStart = $nextSlot['start'];
        $slotEnd = $nextSlot['end'];

        $this->assertInstanceOf(Carbon::class, $slotStart);
        $this->assertInstanceOf(Carbon::class, $slotEnd);

        $this->assertSame('10:00', $slotStart->format('H:i'));
        $this->assertSame('10:30', $slotEnd->format('H:i'));
    }

    /**
     * Test time slot availability check.
     */
    public function test_check_time_slot_availability(): void
    {
        // Arrange: Create schedule occupying a time slot
        schedule_for($this->availabilityModel)->create([
            'title' => 'Occupied Slot',
            'start_datetime' => '2038-01-04 14:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // Act: Check availability of occupied and free slots
        $occupiedSlotAvailable = schedule_for($this->availabilityModel)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00'),
            type: 'consultation'
        );

        $freeSlotAvailable = schedule_for($this->availabilityModel)->isTimeSlotAvailable(
            start: Carbon::parse('2038-01-04 15:00:00'),
            end: Carbon::parse('2038-01-04 16:00:00'),
            type: 'consultation'
        );

        // Assert: Verify correct availability status
        $this->assertFalse($occupiedSlotAvailable);
        $this->assertTrue($freeSlotAvailable);
    }

    /**
     * Test find available slots within date range.
     */
    public function test_find_available_slots_in_date_range(): void
    {
        // Arrange: Create schedule that blocks part of the day
        schedule_for($this->availabilityModel)->create([
            'title' => 'Monday Appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Find available slots in date range
        $availableSlots = schedule_for($this->availabilityModel)->findAvailableSlots(
            startDate: Carbon::parse('2038-01-04'),
            endDate: Carbon::parse('2038-01-06'),
            durationMinutes: 60,
            type: 'consultation'
        );

        // Assert: Verify available slots are returned
        $this->assertInstanceOf(Collection::class, $availableSlots);
        $this->assertGreaterThan(0, $availableSlots->count());

        /** @var array{start: Carbon, end: Carbon} $firstSlot */
        $firstSlot = $availableSlots->first();
        $this->assertArrayHasKey('start', $firstSlot);
        $this->assertArrayHasKey('end', $firstSlot);

        $slotDuration = $firstSlot['start']->diffInMinutes($firstSlot['end']);
        $this->assertEquals(60, $slotDuration);
    }

    /**
     * Test validation for minimum schedule duration.
     */
    public function test_validation_fails_for_too_short_duration(): void
    {
        // Arrange: Prepare schedule data with too short duration
        $tooShortData = [
            'title' => 'Too Short Appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:01:00',
        ];

        // Act & Assert: Verify validation fails
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('duration');

        schedule_for($this->availabilityModel)->create($tooShortData);
    }

    /**
     * Test schedule creation on invalid day fails.
     */
    public function test_cannot_create_schedule_on_invalid_day(): void
    {
        // Arrange: Prepare schedule data for invalid day (Tuesday when availability is Monday/Wednesday/Friday)
        $invalidDayData = [
            'title' => 'Tuesday Appointment',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 11:00:00',
        ];

        // Act & Assert: Verify validation fails
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('day');

        schedule_for($this->availabilityModel)->create($invalidDayData);
    }
}
