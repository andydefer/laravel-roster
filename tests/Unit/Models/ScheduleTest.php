<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Availability;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Support\RosterMutationContext;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Unit tests for the Schedule model.
 *
 * Validates model behavior, attribute casting, relationships, and temporal logic.
 */
final class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test schedulable instance.
     */
    private Model $testSchedulable;

    /**
     * Test availability instance.
     */
    private Availability $testAvailability;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
        $this->testAvailability = $this->createTestAvailability();
    }


    /**
     * Create an availability instance for testing.
     */
    private function createTestAvailability(): Availability
    {
        return availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-07-01 00:00:00',
            'validity_end' => '2038-07-31 23:59:59',
        ]);
    }

    /**
     * Create a schedule model instance for testing model methods.
     *
     * @param array<string, mixed> $attributes Additional attributes to set
     */
    private function createScheduleModelInstance(array $attributes = []): ScheduleModel
    {
        $schedule = schedule_for($this->testAvailability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'description' => 'Test description',
            'metadata' => ['note' => 'Test'],
        ]);

        foreach ($attributes as $key => $value) {
            $schedule->$key = $value;
        }

        return $schedule;
    }

    /**
     * Test that schedule can be created with valid attributes.
     */
    public function test_schedule_can_be_created_with_valid_attributes(): void
    {
        // Arrange: Define valid schedule data
        $creationData = [
            'title' => 'Patient Consultation',
            'description' => 'Annual checkup with lab tests',
            'start_datetime' => '2038-07-15 10:00:00',
            'end_datetime' => '2038-07-15 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['patient_id' => 123, 'insurance' => 'ABC'],
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify schedule properties
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertSame($this->testSchedulable->id, $schedule->schedulable_id);
        $this->assertSame(TestSchedulable::class, $schedule->schedulable_type);
        $this->assertSame($this->testAvailability->id, $schedule->availability_id);
        $this->assertSame('Patient Consultation', $schedule->title);
        $this->assertSame('Annual checkup with lab tests', $schedule->description);
        $this->assertSame(ScheduleStatus::BOOKED, $schedule->status);
        $this->assertSame(['patient_id' => 123, 'insurance' => 'ABC'], $schedule->metadata);
    }

    /**
     * Test that datetime attributes are properly cast to Carbon instances.
     */
    public function test_datetime_attributes_are_properly_cast(): void
    {
        // Arrange: Define schedule with specific datetime values
        $creationData = [
            'title' => 'Team Meeting',
            'start_datetime' => '2038-07-01 14:30:00',
            'end_datetime' => '2038-07-01 16:45:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => null,
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify datetime casting to Carbon instances
        $this->assertSame('2038-07-01 14:30:00', $schedule->start_datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2038-07-01 16:45:00', $schedule->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test that status attribute is properly cast to ScheduleStatus enum.
     */
    public function test_status_is_properly_cast_to_schedule_status_enum(): void
    {
        // Arrange: Define schedule test cases with expected enum values
        $testCases = [
            ['input' => 'available', 'expected' => ScheduleStatus::AVAILABLE],
            ['input' => 'booked', 'expected' => ScheduleStatus::BOOKED],
            ['input' => 'cancelled', 'expected' => ScheduleStatus::CANCELLED],
            ['input' => 'blocked', 'expected' => ScheduleStatus::BLOCKED],
        ];

        // Arrange: Define unique start and end times to avoid overlap
        $startTimes = ['10:00', '11:00', '12:00', '13:00'];
        $endTimes   = ['11:00', '12:00', '13:00', '14:00'];

        foreach ($testCases as $index => $testCase) {
            // Act: Create schedule with specific status
            $schedule = schedule_for($this->testAvailability)->create([
                'title' => 'Test Schedule',
                'start_datetime' => sprintf('2038-07-01 %s:00', $startTimes[$index]),
                'end_datetime'   => sprintf('2038-07-01 %s:00', $endTimes[$index]),
                'status' => ScheduleStatus::from($testCase['input']),
                'metadata' => null,
            ]);

            // Assert: Verify status is properly cast to enum
            $this->assertSame($testCase['expected'], $schedule->status);
            $this->assertInstanceOf(ScheduleStatus::class, $schedule->status);
        }
    }


    /**
     * Test that metadata attribute is properly cast to array.
     */
    public function test_metadata_is_properly_cast_to_array(): void
    {
        // Arrange: Define schedule with metadata array
        $creationData = [
            'title' => 'Emergency Appointment',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => ['type' => 'emergency', 'priority' => 'high', 'patient_name' => 'John Doe'],
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify metadata is properly cast to array
        $this->assertIsArray($schedule->metadata);
        $this->assertSame([
            'type' => 'emergency',
            'priority' => 'high',
            'patient_name' => 'John Doe',
        ], $schedule->metadata);
    }

    /**
     * Test that metadata attribute handles JSON string input from database.
     */
    public function test_metadata_handles_json_string_input(): void
    {
        // Arrange: Create base schedule and set raw JSON string attribute
        $schedule = $this->createScheduleModelInstance();

        // Act: Simulate database JSON string input
        $schedule->setRawAttributes(array_merge(
            $schedule->getAttributes(),
            ['metadata' => '{"patient_id":456,"notes":"Important follow-up"}']
        ));

        // Assert: Verify JSON string is properly cast to array
        $this->assertIsArray($schedule->metadata);
        $this->assertSame(['patient_id' => 456, 'notes' => 'Important follow-up'], $schedule->metadata);
    }

    /**
     * Test that metadata attribute returns empty array when null.
     */
    public function test_metadata_returns_empty_array_when_null(): void
    {
        // Arrange: Define schedule with null metadata
        $creationData = [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => null,
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify null metadata returns empty array
        $this->assertIsArray($schedule->metadata);
        $this->assertEmpty($schedule->metadata);
    }

    /**
     * Test that availability relationship returns the correct model.
     */
    public function test_availability_relationship_returns_correct_model(): void
    {
        // Arrange: Create schedule instance
        $schedule = $this->createScheduleModelInstance();

        // Act: Access availability relationship
        $availability = $schedule->availability;

        // Assert: Verify correct availability model is returned
        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame($this->testAvailability->id, $availability->id);
    }

    /**
     * Test that schedulable relationship returns the correct model through availability.
     */
    public function test_schedulable_relationship_returns_correct_model(): void
    {
        // Arrange: Create schedule instance
        $schedule = $this->createScheduleModelInstance();

        // Act: Access schedulable relationship through availability
        $schedulable = $schedule->schedulable;

        // Assert: Verify correct schedulable model is returned
        $this->assertInstanceOf(TestSchedulable::class, $schedulable);
        $this->assertSame($this->testSchedulable->id, $schedulable->id);
    }

    /**
     * Test that type attribute returns the type from parent availability.
     */
    public function test_type_attribute_returns_type_from_parent_availability(): void
    {
        // Arrange: Create schedule with parent availability
        $schedule = $this->createScheduleModelInstance();

        // Act: Access type attribute
        $type = $schedule->type;

        // Assert: Verify type matches parent availability type
        $this->assertSame('consultation', $type);
    }

    /**
     * Test that overlaps_with returns true when schedule overlaps with given period.
     */
    public function test_overlaps_with_returns_true_when_schedule_overlaps(): void
    {
        // Arrange: Create schedule and overlapping period
        $schedule = $this->createScheduleModelInstance();
        $overlapStart = Carbon::parse('2038-07-01 10:30:00');
        $overlapEnd = Carbon::parse('2038-07-01 11:30:00');

        // Act: Check overlap
        $overlaps = $schedule->overlapsWith($overlapStart, $overlapEnd);

        // Assert: Verify overlap is detected
        $this->assertTrue($overlaps);
    }

    /**
     * Test that overlaps_with returns false when schedule does not overlap with given period.
     */
    public function test_overlaps_with_returns_false_when_schedule_does_not_overlap(): void
    {
        // Arrange: Create schedule and non-overlapping periods
        $schedule = $this->createScheduleModelInstance();
        $beforeStart = Carbon::parse('2038-07-01 08:00:00');
        $beforeEnd = Carbon::parse('2038-07-01 09:00:00');
        $afterStart = Carbon::parse('2038-07-01 12:00:00');
        $afterEnd = Carbon::parse('2038-07-01 13:00:00');

        // Act & Assert: Verify no overlap for periods before and after
        $this->assertFalse($schedule->overlapsWith($beforeStart, $beforeEnd));
        $this->assertFalse($schedule->overlapsWith($afterStart, $afterEnd));
    }

    /**
     * Test that duration_minutes attribute returns correct duration.
     */
    public function test_duration_minutes_attribute_returns_correct_duration(): void
    {
        // Arrange: Define schedule with 90-minute duration
        $creationData = [
            'title' => 'Training Session',
            'start_datetime' => '2038-07-01 13:00:00',
            'end_datetime' => '2038-07-01 14:30:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => null,
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify correct duration calculation
        $this->assertEqualsWithDelta(90.0, $schedule->duration_minutes, PHP_FLOAT_EPSILON);
    }

    /**
     * Test that is_active returns true for currently active schedule.
     */
    public function test_is_active_returns_true_for_currently_active_schedule(): void
    {
        // Arrange: Create schedule active at test time
        $testTime = Carbon::parse('2038-07-01 10:30:00');
        $startTime = $testTime->copy()->subMinutes(30);
        $endTime = $testTime->copy()->addMinutes(30);

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        // Act: Set test time and check active status
        Carbon::setTestNow($testTime);
        $isActive = $schedule->isActive();

        // Assert: Verify schedule is active
        $this->assertTrue($isActive);
        Carbon::setTestNow();
    }

    /**
     * Test that is_active returns false for past schedule.
     */
    public function test_is_active_returns_false_for_past_schedule(): void
    {
        // Arrange: Create past schedule
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');
        $currentTime = $pastEnd->copy()->addHour();

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Act: Set test time and check active status
        Carbon::setTestNow($currentTime);
        $isActive = $schedule->isActive();

        // Assert: Verify schedule is not active
        $this->assertFalse($isActive);
        Carbon::setTestNow();
    }

    /**
     * Test that is_active returns false for future schedule.
     */
    public function test_is_active_returns_false_for_future_schedule(): void
    {
        // Arrange: Create future schedule
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');
        $currentTime = $futureStart->copy()->subHour();

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Act: Set test time and check active status
        Carbon::setTestNow($currentTime);
        $isActive = $schedule->isActive();

        // Assert: Verify schedule is not active
        $this->assertFalse($isActive);
        Carbon::setTestNow();
    }

    /**
     * Test that is_upcoming returns true for future schedule.
     */
    public function test_is_upcoming_returns_true_for_future_schedule(): void
    {
        // Arrange: Create future schedule
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');
        $currentTime = $futureStart->copy()->subHour();

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Act: Set test time and check upcoming status
        Carbon::setTestNow($currentTime);
        $isUpcoming = $schedule->isUpcoming();

        // Assert: Verify schedule is upcoming
        $this->assertTrue($isUpcoming);
        Carbon::setTestNow();
    }

    /**
     * Test that is_upcoming returns false for past schedule.
     */
    public function test_is_upcoming_returns_false_for_past_schedule(): void
    {
        // Arrange: Create past schedule
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');
        $currentTime = $pastEnd->copy()->addHour();

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Act: Set test time and check upcoming status
        Carbon::setTestNow($currentTime);
        $isUpcoming = $schedule->isUpcoming();

        // Assert: Verify schedule is not upcoming
        $this->assertFalse($isUpcoming);
        Carbon::setTestNow();
    }

    /**
     * Test that is_upcoming returns false for active schedule.
     */
    public function test_is_upcoming_returns_false_for_active_schedule(): void
    {
        // Arrange: Create active schedule
        $testTime = Carbon::parse('2038-07-01 10:30:00');
        $startTime = $testTime->copy()->subMinutes(30);
        $endTime = $testTime->copy()->addMinutes(30);

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        // Act: Set test time and check upcoming status
        Carbon::setTestNow($testTime);
        $isUpcoming = $schedule->isUpcoming();

        // Assert: Verify active schedule is not upcoming
        $this->assertFalse($isUpcoming);
        Carbon::setTestNow();
    }

    /**
     * Test that is_past returns true for past schedule.
     */
    public function test_is_past_returns_true_for_past_schedule(): void
    {
        // Arrange: Create past schedule
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');
        $currentTime = $pastEnd->copy()->addHour();

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Act: Set test time and check past status
        Carbon::setTestNow($currentTime);
        $isPast = $schedule->isPast();

        // Assert: Verify schedule is past
        $this->assertTrue($isPast);
        Carbon::setTestNow();
    }

    /**
     * Test that is_past returns false for future schedule.
     */
    public function test_is_past_returns_false_for_future_schedule(): void
    {
        // Arrange: Create future schedule
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');
        $currentTime = $futureStart->copy()->subHour();

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Act: Set test time and check past status
        Carbon::setTestNow($currentTime);
        $isPast = $schedule->isPast();

        // Assert: Verify schedule is not past
        $this->assertFalse($isPast);
        Carbon::setTestNow();
    }

    /**
     * Test that is_past returns false for active schedule.
     */
    public function test_is_past_returns_false_for_active_schedule(): void
    {
        // Arrange: Create active schedule
        $testTime = Carbon::parse('2038-07-01 10:30:00');
        $startTime = $testTime->copy()->subMinutes(30);
        $endTime = $testTime->copy()->addMinutes(30);

        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        // Act: Set test time and check past status
        Carbon::setTestNow($testTime);
        $isPast = $schedule->isPast();

        // Assert: Verify active schedule is not past
        $this->assertFalse($isPast);
        Carbon::setTestNow();
    }

    /**
     * Test that schedule duration is calculated correctly.
     */
    public function test_schedule_duration_is_calculated_correctly(): void
    {
        // Arrange: Define schedule with 45-minute duration
        $creationData = [
            'title' => 'Quick Consultation',
            'start_datetime' => '2038-07-01 09:15:00',
            'end_datetime' => '2038-07-01 10:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => null,
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify correct duration calculation
        $this->assertEqualsWithDelta(45.0, $schedule->duration_minutes, PHP_FLOAT_EPSILON);
    }

    /**
     * Test that overlaps_with handles edge cases (touching but not overlapping periods).
     */
    public function test_overlaps_with_handles_edge_cases(): void
    {
        // Arrange: Create schedule with specific time range
        $schedule = $this->createScheduleModelInstance([
            'start_datetime' => Carbon::parse('2038-07-01 10:00:00'),
            'end_datetime' => Carbon::parse('2038-07-01 11:00:00'),
        ]);

        $edgeStart = Carbon::parse('2038-07-01 11:00:00');
        $edgeEnd = Carbon::parse('2038-07-01 12:00:00');
        $edgeStart2 = Carbon::parse('2038-07-01 09:00:00');
        $edgeEnd2 = Carbon::parse('2038-07-01 10:00:00');

        // Act & Assert: Verify touching periods do not overlap
        $this->assertFalse($schedule->overlapsWith($edgeStart, $edgeEnd));
        $this->assertFalse($schedule->overlapsWith($edgeStart2, $edgeEnd2));
    }

    /**
     * Test that duration_minutes is calculated correctly for exact hours.
     */
    public function test_duration_minutes_for_exact_hours(): void
    {
        // Arrange: Define schedule with exact 2-hour duration
        $creationData = [
            'title' => 'Extended Meeting',
            'start_datetime' => '2038-07-01 14:00:00',
            'end_datetime' => '2038-07-01 16:00:00',
            'status' => ScheduleStatus::BOOKED,
            'metadata' => null,
        ];

        // Act: Create schedule
        $schedule = schedule_for($this->testAvailability)->create($creationData);

        // Assert: Verify correct duration calculation for exact hours
        $this->assertEqualsWithDelta(120.0, $schedule->duration_minutes, PHP_FLOAT_EPSILON);
    }

    /**
     * Test that schedule can be soft deleted.
     */
    public function test_schedule_can_be_soft_deleted(): void
    {
        // Arrange: Create schedule
        $schedule = $this->createScheduleModelInstance();

        // Act: Soft delete schedule inside allowed mutation context
        RosterMutationContext::allow(function () use ($schedule): void {
            $schedule->delete();
        });

        // Assert: Verify schedule is soft deleted
        $this->assertSoftDeleted('roster_schedules', [
            'id' => $schedule->id,
        ]);

        // Restore the schedule inside allowed mutation context
        RosterMutationContext::allow(function () use ($schedule): void {
            $schedule->restore();
        });

        $this->assertDatabaseHas('roster_schedules', [
            'id' => $schedule->id,
            'deleted_at' => null,
        ]);
    }


    /**
     * Test that different schedule statuses work correctly.
     */
    public function test_different_schedule_statuses_work_correctly(): void
    {
        // Arrange: Test cases for different statuses
        $testCases = [
            [
                'status' => ScheduleStatus::AVAILABLE,
                'title' => 'Available Slot',
            ],
            [
                'status' => ScheduleStatus::BLOCKED,
                'title' => 'Blocked Appointment',
            ],
            [
                'status' => ScheduleStatus::BOOKED,
                'title' => 'Booked Appointment',
            ],
            [
                'status' => ScheduleStatus::CANCELLED,
                'title' => 'Cancelled Appointment',
            ],
        ];

        // Horaires distincts pour éviter le chevauchement
        $startTimes = ['10:00', '11:00', '12:00', '13:00'];
        $endTimes   = ['11:00', '12:00', '13:00', '14:00'];

        foreach ($testCases as $index => $testCase) {
            // Act: Create schedule with specific status
            $schedule = schedule_for($this->testAvailability)->create([
                'title' => $testCase['title'],
                'start_datetime' => sprintf('2038-07-01 %s:00', $startTimes[$index]),
                'end_datetime' => sprintf('2038-07-01 %s:00', $endTimes[$index]),
                'status' => $testCase['status'],
                'metadata' => null,
            ]);

            // Assert: Verify status is correctly set
            $this->assertSame($testCase['status'], $schedule->status);
            $this->assertSame($testCase['title'], $schedule->title);
        }
    }
}
