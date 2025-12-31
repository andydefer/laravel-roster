<?php

declare(strict_types=1);

namespace Integration\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Integration tests for Availability database operations and business logic.
 *
 * These tests validate availability creation, updates, deletions, and conflict detection
 * at the database level with proper constraint enforcement.
 */
final class AvailabilityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @var TestSchedulable The schedulable entity used for testing */
    private TestSchedulable $schedulable;

    /**
     * Set up test environment with required entity and configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = TestSchedulable::create(['name' => 'Dr. Availability']);

        $this->configureRosterSettings();
    }

    /**
     * Test successful creation of availability with valid data.
     */
    public function test_create_availability_with_valid_data(): void
    {
        // Arrange: Valid availability data
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ];

        // Act: Create availability
        $availability = availability_for($this->schedulable)->create($availabilityData);

        // Assert: Availability should be created with correct attributes
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertEquals('consultation', $availability->type);
        $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
        $this->assertEquals('09:00:00', $availability->daily_start->format('H:i:s'));
        $this->assertEquals('17:00:00', $availability->daily_end->format('H:i:s'));

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => get_class($this->schedulable),
            'type' => 'consultation',
        ]);
    }

    /**
     * Test automatic day adjustment for short validity periods.
     */
    public function test_days_auto_adjusted_for_short_validity_period(): void
    {
        // Arrange: Data for a 3-day period without explicit days
        $shortPeriodData = [
            'type' => 'surgery',
            'daily_start' => '08:00:00',
            'daily_end' => '12:00:00',
            'validity_start' => '2038-06-01',
            'validity_end' => '2038-06-03',
        ];

        // Act: Create availability without specifying days
        $availability = availability_for($this->schedulable)->create($shortPeriodData);

        // Assert: Days should be auto-adjusted to match period days
        $this->assertContains('tuesday', $availability->days);
        $this->assertContains('wednesday', $availability->days);
        $this->assertContains('thursday', $availability->days);
        $this->assertCount(3, $availability->days);
        $this->assertEquals(['tuesday', 'wednesday', 'thursday'], $availability->days);
    }

    /**
     * Test validation fails when end time is before start time.
     */
    public function test_validation_fails_when_end_time_before_start_time(): void
    {
        // Arrange: Invalid time range (end before start)
        $invalidTimeData = [
            'type' => 'consultation',
            'daily_start' => '17:00:00',
            'daily_end' => '09:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/end time must be after start time/i'
        );


        // Act: Attempt to create with invalid time range
        availability_for($this->schedulable)->create($invalidTimeData);
    }

    /**
     * Test validation fails for insufficient duration.
     */
    public function test_validation_fails_for_insufficient_duration(): void
    {
        // Arrange: Duration too short (5 minutes)
        $shortDurationData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '09:05:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('duration');

        // Act: Attempt to create with insufficient duration
        availability_for($this->schedulable)->create($shortDurationData);
    }

    /**
     * Test detection of overlapping availabilities.
     */
    public function test_cannot_create_overlapping_availabilities(): void
    {
        // Arrange: Existing availability
        availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        // Overlapping availability data
        $overlappingData = [
            'type' => 'consultation',
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-03-01',
            'validity_end' => '2038-09-30',
        ];

        // Assert: Should throw overlap validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('overlap');

        // Act: Attempt to create overlapping availability
        availability_for($this->schedulable)->create($overlappingData);
    }

    /**
     * Test successful update of availability hours.
     */
    public function test_update_availability_hours_successfully(): void
    {
        // Arrange: Existing availability
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Update data
        $updateData = [
            'daily_end' => '18:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
        ];

        // Act: Update availability
        $updateResult = availability_for($this->schedulable)->update(
            id: $availability->id,
            data: $updateData
        );

        // Assert: Update should succeed and reflect changes
        $this->assertTrue($updateResult);
        $availability->refresh();
        $this->assertEquals('18:00:00', $availability->daily_end->format('H:i:s'));
        $this->assertContains('friday', $availability->days);
        $this->assertCount(3, $availability->days);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
        ]);
    }

    /**
     * Test successful deletion of availability without future schedules.
     */
    public function test_delete_availability_without_future_schedules(): void
    {
        // Arrange: Existing availability
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Delete availability
        $deleteResult = availability_for($this->schedulable)->delete($availability->id);

        // Assert: Should be soft deleted
        $this->assertTrue($deleteResult);

        $this->assertSoftDeleted('roster_availabilities', [
            'id' => $availability->id,
        ]);

        $trashedAvailability = AvailabilityModel::withTrashed()->find($availability->id);
        $this->assertNotNull($trashedAvailability);
        $this->assertNotNull($trashedAvailability->deleted_at);
    }

    /**
     * Test retrieval of all availabilities for a schedulable.
     */
    public function test_retrieve_all_availabilities(): void
    {
        // Arrange: Multiple availabilities
        availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        availability_for($this->schedulable)->create([
            'type' => 'surgery',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        // Act: Retrieve all availabilities
        $allAvailabilities = availability_for($this->schedulable)->all();

        // Assert: Should return collection with all availabilities
        $this->assertInstanceOf(Collection::class, $allAvailabilities);
        $this->assertCount(2, $allAvailabilities);

        $availabilityTypes = $allAvailabilities->pluck('type')->toArray();
        $this->assertContains('consultation', $availabilityTypes);
        $this->assertContains('surgery', $availabilityTypes);
    }

    /**
     * Test creation of availability with different valid types.
     */
    public function test_create_availability_with_different_valid_types(): void
    {
        // Arrange: All valid availability types
        $validTypes = ['consultation', 'surgery', 'emergency', 'training', 'room_a', 'echography', 'scan'];

        foreach ($validTypes as $validType) {
            $availabilityData = [
                'type' => $validType,
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-01-01',
                'validity_end' => '2038-01-31',
            ];

            // Act: Create availability for each type
            $availability = availability_for($this->schedulable)->create($availabilityData);

            // Assert: Each type should be accepted
            $this->assertEquals($validType, $availability->type);
            $this->assertDatabaseHas('roster_availabilities', [
                'id' => $availability->id,
                'type' => $validType,
            ]);
        }
    }

    /**
     * Test validation fails for invalid availability type.
     */
    public function test_validation_fails_for_invalid_availability_type(): void
    {
        // Arrange: Invalid type data
        $invalidTypeData = [
            'type' => 'invalid_type',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('type');

        // Act: Attempt to create with invalid type
        availability_for($this->schedulable)->create($invalidTypeData);
    }

    /**
     * Test validation fails for invalid day names.
     */
    public function test_validation_fails_for_invalid_day_names(): void
    {
        // Arrange: Data with invalid day name
        $invalidDaysData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['invalid_day', 'monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('day');

        // Act: Attempt to create with invalid day names
        availability_for($this->schedulable)->create($invalidDaysData);
    }

    /**
     * Test validation fails when validity period ends before it starts.
     */
    public function test_validation_fails_when_validity_period_ends_before_start(): void
    {
        // Arrange: Invalid validity period (end before start)
        $invalidValidityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-12-31',
            'validity_end' => '2038-01-01',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('validation failed');

        // Act: Attempt to create with invalid validity period
        availability_for($this->schedulable)->create($invalidValidityData);
    }

    /**
     * Test non-overlapping availabilities can coexist.
     */
    public function test_non_overlapping_availabilities_can_coexist(): void
    {
        // Arrange: Morning availability
        $morningAvailability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        // Act: Create non-overlapping afternoon availability
        $afternoonAvailability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '13:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-06-30',
        ]);

        // Assert: Both availabilities should exist without conflict
        $this->assertNotNull($afternoonAvailability);
        $this->assertNotEquals($morningAvailability->id, $afternoonAvailability->id);
        $this->assertDatabaseCount('roster_availabilities', 2);
    }

    /**
     * Configure roster settings for testing.
     */
    private function configureRosterSettings(): void
    {
        config([
            'roster.durations.default_slot_interval_minutes' => 15,
            'roster.durations.max_search_period_days' => 365,
            'roster.allowed_types' => [
                'consultation',
                'surgery',
                'emergency',
                'training',
                'room_a',
                'echography',
                'scan',
            ],
        ]);
    }
}
