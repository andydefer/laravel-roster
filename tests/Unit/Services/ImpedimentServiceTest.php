<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Group;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Test suite for ImpedimentService.
 *
 * Validates CRUD operations, conflict detection, and availability blocking
 * scenarios for impediment management within the roster system.
 */
#[Group('services')]
#[Group('impediment')]
final class ImpedimentServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test schedulable entity.
     */
    private Model $testSchedulable;

    /**
     * Test availability entity.
     */
    private AvailabilityModel $testAvailability;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();

        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);

        $this->testAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);
    }

    /**
     * Test successful impediment creation.
     */
    public function test_create_impediment_successfully(): void
    {
        // Arrange: Prepare impediment data
        $impedimentData = [
            'reason' => 'System maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => ['priority' => 'high'],
        ];

        // Act: Create impediment
        $impediment = impediment_for($this->testAvailability)->create($impedimentData);

        // Assert: Verify impediment properties
        $this->assertInstanceOf(ImpedimentModel::class, $impediment);
        $this->assertSame('System maintenance', $impediment->reason);
        $this->assertSame($this->testAvailability->id, $impediment->availability_id);
        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
        $this->assertSame(['priority' => 'high'], $impediment->metadata);
    }

    /**
     * Test impediment creation without metadata.
     */
    public function test_create_impediment_without_metadata(): void
    {
        // Arrange: Prepare impediment data without metadata
        $impedimentData = [
            'reason' => 'Training',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 16:00:00',
        ];

        // Act: Create impediment
        $impediment = impediment_for($this->testAvailability)->create($impedimentData);

        // Assert: Verify metadata is empty
        $this->assertSame('Training', $impediment->reason);
        $this->assertEmpty($impediment->metadata);
    }

    /**
     * Test impediment creation fails when end time is before start time.
     */
    public function test_create_impediment_fails_when_end_before_start(): void
    {
        // Arrange: Prepare invalid impediment data
        $invalidData = [
            'reason' => 'Invalid test',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 10:00:00',
        ];

        // Assert: Expect validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act: Attempt to create invalid impediment
        impediment_for($this->testAvailability)->create($invalidData);
    }

    /**
     * Test impediment creation fails when duration is too short.
     */
    public function test_create_impediment_fails_when_too_short(): void
    {
        // Arrange: Set minimum impediment duration
        Config::set('roster.durations.minimum_impediment_minutes', 15);

        $shortData = [
            'reason' => 'Too short test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:05:00',
        ];

        // Assert: Expect validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act: Attempt to create impediment with insufficient duration
        impediment_for($this->testAvailability)->create($shortData);
    }

    /**
     * Test successful impediment update.
     */
    public function test_update_impediment_successfully(): void
    {
        // Arrange: Create initial impediment
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Original reason',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => ['original' => true],
        ]);

        $updateData = [
            'reason' => 'Updated reason',
            'metadata' => ['updated' => true],
        ];

        // Act: Update impediment
        $result = impediment_for($this->testAvailability)->update($impediment->id, $updateData);

        // Assert: Verify update was successful
        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertSame('Updated reason', $impediment->reason);
        $this->assertSame(['updated' => true], $impediment->metadata);
    }

    /**
     * Test impediment update with datetime changes.
     */
    public function test_update_impediment_with_datetime_changes(): void
    {
        // Arrange: Create initial impediment
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Original',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Update impediment with new datetime values
        $result = impediment_for($this->testAvailability)->update($impediment->id, [
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // Assert: Verify datetime values were updated
        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $impediment->start_datetime);
        $this->assertEquals(Carbon::parse('2038-01-04 15:00:00'), $impediment->end_datetime);
    }

    /**
     * Test impediment update fails when impediment not found.
     */
    public function test_update_impediment_throws_exception_when_not_found(): void
    {
        // Assert: Expect validation exception for non-existent impediment
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Impediment with given ID does not exist/');

        // Act: Attempt to update non-existent impediment
        impediment_for($this->testAvailability)->update(999999, ['reason' => 'test']);
    }

    /**
     * Test successful impediment deletion.
     */
    public function test_delete_impediment_successfully(): void
    {
        // Arrange: Create impediment to delete
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'To delete',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Delete impediment
        $result = impediment_for($this->testAvailability)->delete($impediment->id);

        // Assert: Verify deletion was successful
        $this->assertTrue($result);
        $this->assertNull(ImpedimentModel::find($impediment->id));
    }

    /**
     * Test impediment deletion fails when impediment not found.
     */
    public function test_delete_impediment_throws_exception_when_not_found(): void
    {
        // Assert: Expect validation exception for non-existent impediment
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Impediment with given ID does not exist/');

        // Act: Attempt to delete non-existent impediment
        impediment_for($this->testAvailability)->delete(999999);
    }

    /**
     * Test finding impediment by ID.
     */
    public function test_find_impediment_by_id(): void
    {
        // Arrange: Create impediment to find
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Test find',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Find impediment by ID
        $found = impediment_for($this->testAvailability)->find($impediment->id);

        // Assert: Verify correct impediment was found
        $this->assertInstanceOf(ImpedimentModel::class, $found);
        $this->assertSame($impediment->id, $found->id);
    }

    /**
     * Test find returns null for impediment belonging to different schedulable.
     */
    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange: Create separate schedulable and availability
        $otherSchedulable = TestSchedulable::create();

        $otherAvailability = availability_for($otherSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Create impediment under different schedulable
        $impediment = impediment_for($otherAvailability)->create([
            'reason' => 'For other schedulable',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Attempt to find impediment from wrong schedulable context
        $found = impediment_for($this->testAvailability)->find($impediment->id);

        // Assert: Verify impediment not found in wrong context
        $this->assertNull($found);
    }

    /**
     * Test retrieving all impediments.
     */
    public function test_get_all_impediments(): void
    {
        // Arrange: Create multiple impediments
        impediment_for($this->testAvailability)->create([
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 16:00:00',
        ]);

        // Act: Retrieve all impediments
        $result = impediment_for($this->testAvailability)->all();

        // Assert: Verify all impediments were retrieved
        $this->assertCount(2, $result);
        $reasons = $result->pluck('reason')->toArray();
        $this->assertContains('Impediment 1', $reasons);
        $this->assertContains('Impediment 2', $reasons);
    }

    /**
     * Test retrieving impediments with date range filter.
     */
    public function test_get_with_filters(): void
    {
        // Arrange: Create impediments in different months
        impediment_for($this->testAvailability)->create([
            'reason' => 'January',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'February',
            'start_datetime' => '2038-02-04 10:00:00',
            'end_datetime' => '2038-02-04 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Late January',
            'start_datetime' => '2038-01-25 10:00:00',
            'end_datetime' => '2038-01-25 12:00:00',
        ]);

        // Act: Apply date range filter
        $result = impediment_for($this->testAvailability)
            ->setFilter('start_datetime', '2038-01-01')
            ->setFilter('end_datetime', '2038-01-31')
            ->all();

        // Assert: Verify only January impediments returned
        $this->assertCount(2, $result);
        $reasons = $result->pluck('reason')->toArray();
        $this->assertContains('January', $reasons);
        $this->assertContains('Late January', $reasons);
        $this->assertNotContains('February', $reasons);
    }

    /**
     * Test retrieving impediments with reason filter.
     */
    public function test_get_with_reason_filter(): void
    {
        // Arrange: Create impediments with different reasons
        impediment_for($this->testAvailability)->create([
            'reason' => 'System maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Security training',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Network maintenance',
            'start_datetime' => '2038-01-06 10:00:00',
            'end_datetime' => '2038-01-06 12:00:00',
        ]);

        // Act: Apply reason filter containing "Maintenance"
        $result = impediment_for($this->testAvailability)
            ->setFilter('reason', 'Maintenance')
            ->all();

        // Assert: Verify only maintenance-related impediments returned
        $this->assertCount(2, $result);
        $reasons = $result->pluck('reason')->toArray();
        $this->assertContains('System maintenance', $reasons);
        $this->assertContains('Network maintenance', $reasons);
        $this->assertNotContains('Security training', $reasons);
    }

    /**
     * Test checking overlap with existing schedule returns true.
     */
    public function test_would_overlap_with_schedule_returns_true(): void
    {
        // Arrange: Create existing schedule
        schedule_for($this->testAvailability)->create([
            'title' => 'Existing meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act: Check if potential impediment would overlap
        $wouldOverlap = impediment_for($this->testAvailability)->wouldOverlapWithSchedule(
            availabilityId: $this->testAvailability->id,
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00')
        );

        // Assert: Verify overlap detected
        $this->assertTrue($wouldOverlap);
    }

    /**
     * Test checking overlap with existing schedule returns false.
     */
    public function test_would_overlap_with_schedule_returns_false(): void
    {
        // Arrange: Create existing schedule
        schedule_for($this->testAvailability)->create([
            'title' => 'Existing meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act: Check if potential impediment would not overlap
        $wouldOverlap = impediment_for($this->testAvailability)->wouldOverlapWithSchedule(
            availabilityId: $this->testAvailability->id,
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert: Verify no overlap detected
        $this->assertFalse($wouldOverlap);
    }

    /**
     * Test checking overlap with schedule excluding current impediment.
     */
    public function test_would_overlap_with_schedule_excluding_current_impediment(): void
    {
        // Arrange: Create existing impediment
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Existing',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act: Check overlap excluding specific impediment
        $wouldOverlap = impediment_for($this->testAvailability)->wouldOverlapWithSchedule(
            availabilityId: $this->testAvailability->id,
            start: Carbon::parse('2038-01-04 12:00:00'),
            end: Carbon::parse('2038-01-04 14:00:00'),
            exceptImpedimentId: $impediment->id
        );

        // Assert: Verify no overlap when excluding current impediment
        $this->assertFalse($wouldOverlap);
    }

    /**
     * Test checking overlap with other impediment returns true.
     */
    public function test_would_overlap_with_other_impediment_returns_true(): void
    {
        // Arrange: Create existing impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'Existing impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act: Check if potential impediment would overlap with existing one
        $wouldOverlap = impediment_for($this->testAvailability)->wouldOverlapWithOtherImpediment(
            availabilityId: $this->testAvailability->id,
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00')
        );

        // Assert: Verify overlap detected
        $this->assertTrue($wouldOverlap);
    }

    /**
     * Test checking overlap with other impediment returns false.
     */
    public function test_would_overlap_with_other_impediment_returns_false(): void
    {
        // Arrange: Create existing impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'Existing impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act: Check if potential impediment would not overlap
        $wouldOverlap = impediment_for($this->testAvailability)->wouldOverlapWithOtherImpediment(
            availabilityId: $this->testAvailability->id,
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert: Verify no overlap detected
        $this->assertFalse($wouldOverlap);
    }

    /**
     * Test checking time slot is blocked returns true.
     */
    public function test_is_time_slot_blocked_returns_true(): void
    {
        // Arrange: Create impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'Test block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Check if time slot is blocked
        $isBlocked = impediment_for($this->testAvailability)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00')
        );

        // Assert: Verify time slot is blocked
        $this->assertTrue($isBlocked);
    }

    /**
     * Test checking time slot is blocked returns false.
     */
    public function test_is_time_slot_blocked_returns_false(): void
    {
        // Arrange: Create impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'Test block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Check if time slot is not blocked
        $isBlocked = impediment_for($this->testAvailability)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert: Verify time slot is not blocked
        $this->assertFalse($isBlocked);
    }

    /**
     * Test checking time slot is blocked with type filter.
     */
    public function test_is_time_slot_blocked_with_type_filter(): void
    {
        // Arrange: Create separate availability and impediment
        $otherAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'emergency',
            'daily_start' => '18:00:00',
            'daily_end' => '21:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        impediment_for($otherAvailability)->create([
            'reason' => 'Emergency block',
            'start_datetime' => '2038-01-04 19:00:00',
            'end_datetime' => '2038-01-04 21:00:00',
        ]);

        // Act: Check time slot with type filter
        $isBlocked = impediment_for($this->testAvailability)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 20:00:00'),
            end: Carbon::parse('2038-01-04 20:30:00'),
            type: 'consultation'
        );

        // Assert: Verify time slot not blocked for consultation type
        $this->assertFalse($isBlocked);
    }

    /**
     * Test getting available time slots.
     */
    public function test_get_available_time_slots(): void
    {
        // Arrange: Create impediment blocking part of day
        impediment_for($this->testAvailability)->create([
            'reason' => 'Meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Get available time slots
        $slots = impediment_for($this->testAvailability)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert: Verify correct time slots returned
        $this->assertInstanceOf(Collection::class, $slots);
        $this->assertCount(2, $slots);
        $this->assertSame('2038-01-04 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-01-04 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-01-04 12:00:00', $slots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-01-04 17:00:00', $slots[1]['end']->format('Y-m-d H:i:s'));
    }

    /**
     * Test getting available time slots when no availability.
     */
    public function test_get_available_time_slots_when_no_availability(): void
    {
        // Arrange: Create impediment blocking entire day
        impediment_for($this->testAvailability)->create([
            'reason' => 'Full day meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 17:00:00',
        ]);

        // Act: Get available time slots
        $slots = impediment_for($this->testAvailability)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert: Verify no time slots available
        $this->assertInstanceOf(Collection::class, $slots);
        $this->assertCount(0, $slots);
    }

    /**
     * Test impediment prevents schedule creation.
     */
    public function test_impediment_prevents_schedule_creation(): void
    {
        // Arrange: Create impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'Medical appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert: Expect validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing impediment/');

        // Act: Attempt to create overlapping schedule
        schedule_for($this->testAvailability)->create([
            'title' => 'New appointment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);
    }

    /**
     * Test multiple impediments on same day.
     */
    public function test_multiple_impediments_on_same_day(): void
    {
        // Arrange: Create multiple impediments throughout the day
        impediment_for($this->testAvailability)->create([
            'reason' => 'Morning meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 10:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Lunch break',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Training',
            'start_datetime' => '2038-01-04 15:00:00',
            'end_datetime' => '2038-01-04 16:00:00',
        ]);

        // Act: Retrieve all impediments
        $allImpediments = impediment_for($this->testAvailability)->all();

        // Assert: Verify all impediments created
        $this->assertCount(3, $allImpediments);
        $reasons = $allImpediments->pluck('reason')->toArray();
        $this->assertContains('Morning meeting', $reasons);
        $this->assertContains('Lunch break', $reasons);
        $this->assertContains('Training', $reasons);
    }

    /**
     * Test impediment metadata serialization.
     */
    public function test_impediment_metadata_serialization(): void
    {
        // Arrange: Prepare complex metadata
        $complexMetadata = [
            'category' => 'maintenance',
            'priority' => 'high',
            'teams' => ['IT', 'Support'],
            'notes' => 'Critical system',
        ];

        // Act: Create impediment with complex metadata
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Complex maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => $complexMetadata,
        ]);

        // Assert: Verify metadata serialized correctly
        $this->assertSame('maintenance', $impediment->metadata['category']);
        $this->assertSame('high', $impediment->metadata['priority']);
        $this->assertSame(['IT', 'Support'], $impediment->metadata['teams']);
        $this->assertSame('Critical system', $impediment->metadata['notes']);
    }

    /**
     * Test impediment duration calculation.
     */
    public function test_impediment_duration_calculation(): void
    {
        // Act: Create impediment with specific duration
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Test duration',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:30:00',
        ]);

        // Assert: Verify duration calculated correctly
        $this->assertEqualsWithDelta(150.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
    }

    /**
     * Test impediment exact time boundary conditions.
     */
    public function test_impediment_exact_time_boundary(): void
    {
        // Arrange: Create impediment
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Exact boundary',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert: Verify overlap detection at boundaries
        $this->assertTrue($impediment->overlapsWith(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00')
        ));

        $this->assertTrue($impediment->overlapsWith(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 10:30:00')
        ));

        $this->assertFalse($impediment->overlapsWith(
            start: Carbon::parse('2038-01-04 12:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00')
        ));
    }

    /**
     * Test concurrent impediment creation prevents overlap.
     */
    public function test_concurrent_impediment_creation_prevents_overlap(): void
    {
        // Arrange: Create first impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'First impediment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert: Expect validation exception for overlapping impediment
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/overlaps with existing impediment/');

        // Act: Attempt to create overlapping impediment
        impediment_for($this->testAvailability)->create([
            'reason' => 'Second impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);
    }

    /**
     * Test impediment creation fails on non-availability day.
     */
    public function test_impediment_on_non_availability_day(): void
    {
        // Arrange: Create Monday-only availability
        $mondayOnlyAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'monday-only',
            'daily_start' => '20:00:00',
            'daily_end' => '22:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert: Expect validation exception for wrong day
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/failed for Impediment.*not allowed.*Allowed days/i');


        // Act: Attempt to create impediment on Tuesday
        impediment_for($mondayOnlyAvailability)->create([
            'reason' => 'Tuesday impediment',
            'start_datetime' => '2038-01-07 21:00:00',
            'end_datetime' => '2038-01-07 21:30:00',
        ]);
    }

    /**
     * Test impediment creation fails outside availability hours.
     */
    public function test_impediment_outside_availability_hours(): void
    {
        // Arrange: Create limited availability
        $limitedAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'limited',
            'daily_start' => '20:00:00',
            'daily_end' => '23:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert: Expect validation exception for time outside availability
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/selected start time .* is before the availability start time/');

        // Act: Attempt to create impediment before availability start time
        impediment_for($limitedAvailability)->create([
            'reason' => 'Too early impediment',
            'start_datetime' => '2038-01-04 16:00:00',
            'end_datetime' => '2038-01-04 19:00:00',
        ]);
    }

    /**
     * Test impediments at exact boundaries do not overlap.
     */
    public function test_impediment_exact_boundary_not_overlap(): void
    {
        // Arrange: Create first impediment
        $impediment1 = impediment_for($this->testAvailability)->create([
            'reason' => 'First impediment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Create adjacent impediment starting exactly when first ends
        $impediment2 = impediment_for($this->testAvailability)->create([
            'reason' => 'Second impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert: Verify impediments do not overlap
        $this->assertInstanceOf(ImpedimentModel::class, $impediment2);
        $this->assertSame('2038-01-04 11:00:00', $impediment2->start_datetime->format('Y-m-d H:i:s'));
        $this->assertFalse($impediment1->overlapsWith(
            start: $impediment2->start_datetime,
            end: $impediment2->end_datetime
        ));
    }

    /**
     * Test finding available slots with adjacent impediments.
     */
    public function test_find_available_slots_with_adjacent_impediments(): void
    {
        // Arrange: Create two adjacent impediments
        impediment_for($this->testAvailability)->create([
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act: Get available time slots
        $slots = impediment_for($this->testAvailability)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert: Verify correct time slots around impediments
        $this->assertCount(2, $slots);
        $this->assertSame('2038-01-04 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-01-04 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-01-04 12:00:00', $slots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-01-04 17:00:00', $slots[1]['end']->format('Y-m-d H:i:s'));
    }

    /**
     * Test complete blocking scenario.
     */
    public function test_complete_blocking_scenario(): void
    {
        // Arrange: Create impediments blocking entire day
        impediment_for($this->testAvailability)->create([
            'reason' => 'Morning meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Lunch break',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Afternoon meeting',
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 17:00:00',
        ]);

        // Act: Test blocking and get available slots
        $isBlocked = impediment_for($this->testAvailability)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00')
        );

        $slots = impediment_for($this->testAvailability)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert: Verify complete blocking scenario
        $this->assertTrue($isBlocked);
        $this->assertCount(0, $slots);
    }

    /**
     * Test impediment with JSON string metadata.
     */
    public function test_impediment_with_json_string_metadata(): void
    {
        // Arrange: Prepare JSON metadata
        $jsonMetadata = [
            'client' => 'ABC Corp',
            'priority' => 'urgent',
            'notify' => true,
        ];

        // Act: Create impediment with JSON metadata
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Urgent maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => $jsonMetadata,
        ]);

        // Assert: Verify metadata properly stored
        $this->assertIsArray($impediment->metadata);
        $this->assertSame('ABC Corp', $impediment->metadata['client']);
        $this->assertSame('urgent', $impediment->metadata['priority']);
        $this->assertTrue($impediment->metadata['notify']);
    }

    /**
     * Test paginating impediments.
     */
    public function test_paginate_impediments(): void
    {
        // Arrange: Create multiple impediments
        $startDate = Carbon::parse('2038-01-04');
        for ($i = 0; $i < 25; ++$i) {
            $date = $startDate->copy()->addWeeks(intdiv($i, 5))->addDays($i % 5);

            impediment_for($this->testAvailability)->create([
                'reason' => "Impediment " . ($i + 1),
                'start_datetime' => $date->setTime(10, 0, 0)->toDateTimeString(),
                'end_datetime' => $date->setTime(12, 0, 0)->toDateTimeString(),
            ]);
        }

        // Act: Paginate impediments
        $lengthAwarePaginator = impediment_for($this->testAvailability)->paginate(10);

        // Assert: Verify pagination properties
        $this->assertSame(25, $lengthAwarePaginator->total());
        $this->assertSame(10, $lengthAwarePaginator->perPage());
        $this->assertSame(3, $lengthAwarePaginator->lastPage());
        $this->assertCount(10, $lengthAwarePaginator->items());
    }

    /**
     * Test resetting filters.
     */
    public function test_reset_filters(): void
    {
        // Arrange: Create impediments
        impediment_for($this->testAvailability)->create([
            'reason' => 'Test 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->testAvailability)->create([
            'reason' => 'Test 2',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 12:00:00',
        ]);

        // Act: Apply filter and reset
        $filtered = impediment_for($this->testAvailability)
            ->setFilter('start_date', '2038-01-05')
            ->resetFilters()
            ->all();

        // Assert: Verify filters reset and all items returned
        $this->assertCount(2, $filtered);
    }

    /**
     * Test clearing all service data.
     */
    public function test_clear_all_data(): void
    {
        // Arrange: Create impediment and get service instance
        $impediment = impediment_for($this->testAvailability)->create([
            'reason' => 'Test clear',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        $impedimentService = impediment_for($this->testAvailability);

        // Act: Clear service data
        $impedimentService->clear();

        // Assert: Verify service data cleared but impediment persists
        $this->assertEmpty($impedimentService->getFilters());
        $this->assertEmpty($impedimentService->getData());
        $this->assertNotNull(ImpedimentModel::find($impediment->id));
    }
}
