<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
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
final class ImpedimentServiceTest extends TestCase
{
    use RefreshDatabase;

    private Model $testSchedulable;
    private AvailabilityModel $availabilityModel;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();

        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);

        $this->availabilityModel = availability_for($this->testSchedulable)->create([
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
        // Arrange
        $impedimentData = [
            'reason' => 'System maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => ['priority' => 'high'],
        ];

        // Act
        $impediment = impediment_for($this->availabilityModel)->create($impedimentData);

        // Assert
        $this->assertInstanceOf(ImpedimentModel::class, $impediment);
        $this->assertSame('System maintenance', $impediment->reason);
        $this->assertSame($this->availabilityModel->id, $impediment->availability_id);
        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
        $this->assertSame(['priority' => 'high'], $impediment->metadata);
    }

    /**
     * Test impediment creation without metadata.
     */
    public function test_create_impediment_without_metadata(): void
    {
        // Arrange
        $impedimentData = [
            'reason' => 'Training',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 16:00:00',
        ];

        // Act
        $impediment = impediment_for($this->availabilityModel)->create($impedimentData);

        // Assert
        $this->assertSame('Training', $impediment->reason);
        $this->assertEmpty($impediment->metadata);
    }

    /**
     * Test impediment creation fails when end time is before start time.
     */
    public function test_create_impediment_fails_when_end_before_start(): void
    {
        // Arrange
        $invalidData = [
            'reason' => 'Invalid test',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 10:00:00',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/End datetime must be after start datetime/');

        // Act
        impediment_for($this->availabilityModel)->create($invalidData);
    }

    /**
     * Test impediment creation fails when duration is too short.
     */
    public function test_create_impediment_fails_when_too_short(): void
    {
        // Arrange
        Config::set('roster.durations.minimum_impediment_minutes', 15);

        $shortData = [
            'reason' => 'Too short test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 10:05:00',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        impediment_for($this->availabilityModel)->create($shortData);
    }

    /**
     * Test successful impediment update.
     */
    public function test_update_impediment_successfully(): void
    {
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Original reason',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => ['original' => true],
        ]);

        $updateData = [
            'reason' => 'Updated reason',
            'metadata' => ['updated' => true],
        ];

        // Act
        $result = impediment_for($this->availabilityModel)->update($impediment->id, $updateData);

        // Assert
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
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Original',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $result = impediment_for($this->availabilityModel)->update($impediment->id, [
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 15:00:00',
        ]);

        // Assert
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
        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Impediment with given ID does not exist/');

        // Act
        impediment_for($this->availabilityModel)->update(999999, ['reason' => 'test']);
    }

    /**
     * Test successful impediment deletion.
     */
    public function test_delete_impediment_successfully(): void
    {
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'To delete',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $result = impediment_for($this->availabilityModel)->delete($impediment->id);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(ImpedimentModel::find($impediment->id));
    }

    /**
     * Test impediment deletion fails when impediment not found.
     */
    public function test_delete_impediment_throws_exception_when_not_found(): void
    {
        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Impediment with given ID does not exist/');

        // Act
        impediment_for($this->availabilityModel)->delete(999999);
    }

    /**
     * Test finding impediment by ID.
     */
    public function test_find_impediment_by_id(): void
    {
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Test find',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $found = impediment_for($this->availabilityModel)->find($impediment->id);

        // Assert
        $this->assertInstanceOf(ImpedimentModel::class, $found);
        $this->assertSame($impediment->id, $found->id);
    }

    /**
     * Test find returns null for impediment belonging to different schedulable.
     */
    public function test_find_returns_null_for_wrong_schedulable(): void
    {
        // Arrange
        $otherSchedulable = TestSchedulable::create();

        $otherAvailability = availability_for($otherSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $impediment = impediment_for($otherAvailability)->create([
            'reason' => 'For other schedulable',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $found = impediment_for($this->availabilityModel)->find($impediment->id);

        // Assert
        $this->assertNull($found);
    }

    /**
     * Test retrieving all impediments.
     */
    public function test_get_all_impediments(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-01-05 14:00:00',
            'end_datetime' => '2038-01-05 16:00:00',
        ]);

        // Act
        $result = impediment_for($this->availabilityModel)->all();

        // Assert
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
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'January',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'February',
            'start_datetime' => '2038-02-04 10:00:00',
            'end_datetime' => '2038-02-04 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Late January',
            'start_datetime' => '2038-01-25 10:00:00',
            'end_datetime' => '2038-01-25 12:00:00',
        ]);

        // Act
        $result = impediment_for($this->availabilityModel)
            ->setFilter('start_datetime', '2038-01-01')
            ->setFilter('end_datetime', '2038-01-31')
            ->all();

        // Assert
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
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'System maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Security training',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Network maintenance',
            'start_datetime' => '2038-01-06 10:00:00',
            'end_datetime' => '2038-01-06 12:00:00',
        ]);

        // Act
        $result = impediment_for($this->availabilityModel)
            ->setFilter('reason', 'Maintenance')
            ->all();

        // Assert
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
        // Arrange
        schedule_for($this->availabilityModel)->create([
            'title' => 'Existing meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act
        $wouldOverlap = impediment_for($this->availabilityModel)->wouldOverlapWithSchedule(
            availabilityId: $this->availabilityModel->id,
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00')
        );

        // Assert
        $this->assertTrue($wouldOverlap);
    }

    /**
     * Test checking overlap with existing schedule returns false.
     */
    public function test_would_overlap_with_schedule_returns_false(): void
    {
        // Arrange
        schedule_for($this->availabilityModel)->create([
            'title' => 'Existing meeting',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act
        $wouldOverlap = impediment_for($this->availabilityModel)->wouldOverlapWithSchedule(
            availabilityId: $this->availabilityModel->id,
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert
        $this->assertFalse($wouldOverlap);
    }

    /**
     * Test checking overlap with schedule excluding current impediment.
     */
    public function test_would_overlap_with_schedule_excluding_current_impediment(): void
    {
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Existing',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act
        $wouldOverlap = impediment_for($this->availabilityModel)->wouldOverlapWithSchedule(
            availabilityId: $this->availabilityModel->id,
            start: Carbon::parse('2038-01-04 12:00:00'),
            end: Carbon::parse('2038-01-04 14:00:00'),
            exceptImpedimentId: $impediment->id
        );

        // Assert
        $this->assertFalse($wouldOverlap);
    }

    /**
     * Test checking overlap with other impediment returns true.
     */
    public function test_would_overlap_with_other_impediment_returns_true(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Existing impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act
        $wouldOverlap = impediment_for($this->availabilityModel)->wouldOverlapWithOtherImpediment(
            availabilityId: $this->availabilityModel->id,
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 12:00:00')
        );

        // Assert
        $this->assertTrue($wouldOverlap);
    }

    /**
     * Test checking overlap with other impediment returns false.
     */
    public function test_would_overlap_with_other_impediment_returns_false(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Existing impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        // Act
        $wouldOverlap = impediment_for($this->availabilityModel)->wouldOverlapWithOtherImpediment(
            availabilityId: $this->availabilityModel->id,
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert
        $this->assertFalse($wouldOverlap);
    }

    /**
     * Test checking time slot is blocked returns true.
     */
    public function test_is_time_slot_blocked_returns_true(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Test block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $isBlocked = impediment_for($this->availabilityModel)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 11:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00')
        );

        // Assert
        $this->assertTrue($isBlocked);
    }

    /**
     * Test checking time slot is blocked returns false.
     */
    public function test_is_time_slot_blocked_returns_false(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Test block',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $isBlocked = impediment_for($this->availabilityModel)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 14:00:00'),
            end: Carbon::parse('2038-01-04 15:00:00')
        );

        // Assert
        $this->assertFalse($isBlocked);
    }

    /**
     * Test checking time slot is blocked with type filter.
     */
    public function test_is_time_slot_blocked_with_type_filter(): void
    {
        // Arrange
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

        // Act
        $isBlocked = impediment_for($this->availabilityModel)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 20:00:00'),
            end: Carbon::parse('2038-01-04 20:30:00'),
            type: 'consultation'
        );

        // Assert
        $this->assertFalse($isBlocked);
    }

    /**
     * Test getting available time slots.
     */
    public function test_get_available_time_slots(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Meeting',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $slots = impediment_for($this->availabilityModel)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert
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
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Full day meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 17:00:00',
        ]);

        // Act
        $slots = impediment_for($this->availabilityModel)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert
        $this->assertInstanceOf(Collection::class, $slots);
        $this->assertCount(0, $slots);
    }

    /**
     * Test impediment prevents schedule creation.
     */
    public function test_impediment_prevents_schedule_creation(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Medical appointment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Schedule overlaps with existing impediment/');

        // Act
        schedule_for($this->availabilityModel)->create([
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
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Morning meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 10:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Lunch break',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Training',
            'start_datetime' => '2038-01-04 15:00:00',
            'end_datetime' => '2038-01-04 16:00:00',
        ]);

        // Act
        $allImpediments = impediment_for($this->availabilityModel)->all();

        // Assert
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
        // Arrange
        $complexMetadata = [
            'category' => 'maintenance',
            'priority' => 'high',
            'teams' => ['IT', 'Support'],
            'notes' => 'Critical system',
        ];

        // Act
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Complex maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => $complexMetadata,
        ]);

        // Assert
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
        // Act
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Test duration',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:30:00',
        ]);

        // Assert
        $this->assertSame(150.0, $impediment->duration_minutes);
    }

    /**
     * Test impediment exact time boundary conditions.
     */
    public function test_impediment_exact_time_boundary(): void
    {
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Exact boundary',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert - Overlap inside
        $this->assertTrue($impediment->overlapsWith(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00')
        ));

        // Assert - Overlap at boundary
        $this->assertTrue($impediment->overlapsWith(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 10:30:00')
        ));

        // Assert - No overlap (starts exactly at end)
        $this->assertFalse($impediment->overlapsWith(
            start: Carbon::parse('2038-01-04 12:00:00'),
            end: Carbon::parse('2038-01-04 13:00:00')
        ));
    }

    /**
     * Test impediment active status live.
     */
    public function test_impediment_active_status_live(): void
    {
        $now = now();

        if ($now->format('H') == 23 && (int)$now->format('i') >= 40) {
            $this->markTestSkipped('Cannot test live time slot crossing midnight.');
        }

        // Arrange
        $dailyStart = $now->copy();
        $dailyEnd   = $now->copy()->addMinutes(20);
        $validityEnd = $now->copy()->addHour();

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'instant-test',
            'daily_start' => $dailyStart->format('H:i:s'),
            'daily_end' => $dailyEnd->format('H:i:s'),
            'days' => [strtolower($now->englishDayOfWeek)],
            'validity_start' => $now->copy(),
            'validity_end' => $validityEnd,
        ]);

        $start = $now->copy()->addSecond();
        $end   = $now->copy()->addMinutes(10);

        $impediment = impediment_for($availability)->create([
            'reason' => 'Live active test',
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        sleep(2);

        // Assert
        $this->assertTrue($impediment->isActive());
        $this->assertFalse($impediment->isPast());
        $this->assertFalse($impediment->isUpcoming());
    }

    /**
     * Test concurrent impediment creation prevents overlap.
     */
    public function test_concurrent_impediment_creation_prevents_overlap(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'First impediment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/overlaps with existing impediment/');

        // Act
        impediment_for($this->availabilityModel)->create([
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
        // Arrange
        $mondayOnlyAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'monday-only',
            'daily_start' => '20:00:00',
            'daily_end' => '22:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/failed for Impediment.*not allowed.*only permits/i');

        // Act
        impediment_for($mondayOnlyAvailability)->create([
            'reason' => 'Tuesday impediment',
            'start_datetime' => '2038-01-05 21:00:00',
            'end_datetime' => '2038-01-07 21:30:00',
        ]);
    }

    /**
     * Test impediment creation fails outside availability hours.
     */
    public function test_impediment_outside_availability_hours(): void
    {
        // Arrange
        $limitedAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'limited',
            'daily_start' => '20:00:00',
            'daily_end' => '23:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/selected start time .* is before the availability start time/');

        // Act
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
        // Arrange
        $impediment1 = impediment_for($this->availabilityModel)->create([
            'reason' => 'First impediment',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act
        $impediment2 = impediment_for($this->availabilityModel)->create([
            'reason' => 'Second impediment',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Assert
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
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-01-04 11:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        // Act
        $slots = impediment_for($this->availabilityModel)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert
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
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Morning meeting',
            'start_datetime' => '2038-01-04 09:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Lunch break',
            'start_datetime' => '2038-01-04 12:00:00',
            'end_datetime' => '2038-01-04 13:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Afternoon meeting',
            'start_datetime' => '2038-01-04 13:00:00',
            'end_datetime' => '2038-01-04 17:00:00',
        ]);

        // Act
        $isBlocked = impediment_for($this->availabilityModel)->isTimeSlotBlocked(
            start: Carbon::parse('2038-01-04 10:00:00'),
            end: Carbon::parse('2038-01-04 11:00:00')
        );

        $slots = impediment_for($this->availabilityModel)->getAvailableTimeSlots(
            start: Carbon::parse('2038-01-04 09:00:00'),
            end: Carbon::parse('2038-01-04 17:00:00')
        );

        // Assert
        $this->assertTrue($isBlocked);
        $this->assertCount(0, $slots);
    }

    /**
     * Test impediment with JSON string metadata.
     */
    public function test_impediment_with_json_string_metadata(): void
    {
        // Arrange
        $jsonMetadata = json_encode([
            'client' => 'ABC Corp',
            'priority' => 'urgent',
            'notify' => true,
        ]);

        // Act
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Urgent maintenance',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
            'metadata' => $jsonMetadata,
        ]);

        // Assert
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
        // Arrange
        $startDate = Carbon::parse('2038-01-04');
        for ($i = 0; $i < 25; ++$i) {
            $date = $startDate->copy()->addWeeks(intdiv($i, 5))->addDays($i % 5);

            impediment_for($this->availabilityModel)->create([
                'reason' => "Impediment " . ($i + 1),
                'start_datetime' => $date->setTime(10, 0, 0)->toDateTimeString(),
                'end_datetime' => $date->setTime(12, 0, 0)->toDateTimeString(),
            ]);
        }

        // Act
        $paginator = impediment_for($this->availabilityModel)->paginate(10);

        // Assert
        $this->assertSame(25, $paginator->total());
        $this->assertSame(10, $paginator->perPage());
        $this->assertSame(3, $paginator->lastPage());
        $this->assertCount(10, $paginator->items());
    }

    /**
     * Test resetting filters.
     */
    public function test_reset_filters(): void
    {
        // Arrange
        impediment_for($this->availabilityModel)->create([
            'reason' => 'Test 1',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        impediment_for($this->availabilityModel)->create([
            'reason' => 'Test 2',
            'start_datetime' => '2038-01-05 10:00:00',
            'end_datetime' => '2038-01-05 12:00:00',
        ]);

        // Act
        $filtered = impediment_for($this->availabilityModel)
            ->setFilter('start_date', '2038-01-05')
            ->resetFilters()
            ->all();

        // Assert
        $this->assertCount(2, $filtered);
    }

    /**
     * Test clearing all service data.
     */
    public function test_clear_all_data(): void
    {
        // Arrange
        $impediment = impediment_for($this->availabilityModel)->create([
            'reason' => 'Test clear',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 12:00:00',
        ]);

        $impedimentService = impediment_for($this->availabilityModel);

        // Act
        $impedimentService->clear();

        // Assert
        $this->assertEmpty($impedimentService->getFilters());
        $this->assertEmpty($impedimentService->getData());
        $this->assertNotNull(ImpedimentModel::find($impediment->id));
    }
}
