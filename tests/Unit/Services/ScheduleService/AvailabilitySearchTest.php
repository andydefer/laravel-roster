<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for availability search operations (findNextSlot, isTimeSlotAvailable, etc.)
 */
final class AvailabilitySearchTest extends TestCase
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
}
