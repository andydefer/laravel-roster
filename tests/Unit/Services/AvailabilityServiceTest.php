<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Test suite for AvailabilityService.
 *
 * Validates CRUD operations, validation rules, and business logic
 * for availability management within the roster system.
 */
final class AvailabilityServiceTest extends TestCase
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
    }

    /**
     * Test successful availability creation.
     */
    public function test_can_create_an_availability(): void
    {
        // Arrange: Data for new availability
        $availabilityData = $this->createValidAvailabilityData();

        // Act: Create availability
        $availability = availability_for($this->schedulable)->create($availabilityData);

        // Assert: Availability should be created with correct data
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'type' => 'consultation',
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
        ]);

        $this->assertSame('consultation', $availability->type);
        $this->assertSame(['monday', 'wednesday', 'friday'], $availability->days);
        $this->assertSame($this->schedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
    }

    /**
     * Test days default to all days when not provided.
     */
    public function test_days_default_to_all_days_when_not_provided(): void
    {
        // Arrange: Availability data without days
        $availabilityData = $this->createValidAvailabilityData(days: []);

        // Act: Create availability
        $availability = availability_for($this->schedulable)->create($availabilityData);

        // Assert: Should default to all days of week
        $this->assertNotEmpty($availability->days);
        $this->assertSame(
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            $availability->days
        );
    }

    /**
     * Test successful availability update.
     */
    public function test_can_update_an_existing_availability(): void
    {
        // Arrange: Create initial availability
        $availability = $this->createTestAvailability();

        $updateData = [
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['tuesday', 'thursday'],
        ];

        // Act: Update availability
        $result = availability_for($this->schedulable)->update($availability->id, $updateData);

        // Assert: Update should succeed with new values
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => json_encode(['tuesday', 'thursday']),
        ]);
    }

    /**
     * Test update throws exception when availability not found.
     */
    public function test_update_throws_exception_when_availability_not_found(): void
    {
        // Arrange: Non-existent availability ID
        $availabilityId = 999;
        $updateData = ['daily_start' => '10:00:00'];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Update validation failed for Availability.*does not exist/'
        );

        // Act: Attempt to update non-existent availability
        availability_for($this->schedulable)->update($availabilityId, $updateData);
    }

    /**
     * Test successful availability deletion.
     */
    public function test_can_delete_an_availability(): void
    {
        // Arrange: Create availability
        $availability = $this->createTestAvailability();

        // Act: Delete availability
        $result = availability_for($this->schedulable)->delete($availability->id);

        // Assert: Should be soft deleted
        $this->assertTrue($result);
        $this->assertSoftDeleted('roster_availabilities', [
            'id' => $availability->id,
        ]);

        $trashed = AvailabilityModel::withTrashed()->find($availability->id);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }

    /**
     * Test delete throws exception when availability not found.
     */
    public function test_delete_throws_exception_when_availability_not_found(): void
    {
        // Arrange: Non-existent availability ID
        $availabilityId = 999;

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Delete validation failed for Availability.*does not exist/'
        );

        // Act: Attempt to delete non-existent availability
        availability_for($this->schedulable)->delete($availabilityId);
    }

    /**
     * Test finding availability by ID.
     */
    public function test_can_find_an_availability_by_id(): void
    {
        // Arrange: Create availability
        $availability = $this->createTestAvailability();

        // Act: Find availability by ID
        $result = availability_for($this->schedulable)->find($availability->id);

        // Assert: Should return correct availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
        $this->assertSame('consultation', $result->type);
    }

    /**
     * Test find returns null when availability not found.
     */
    public function test_find_returns_null_when_availability_not_found(): void
    {
        // Arrange: Non-existent availability ID
        $availabilityId = 999;

        // Act: Find non-existent availability
        $result = availability_for($this->schedulable)->find($availabilityId);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test first returns first availability.
     */
    public function test_first_returns_first_availability(): void
    {
        // Arrange: Create multiple availabilities
        $availability1 = $this->createTestAvailability(
            type: 'consultation',
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00',
            days: ['monday']
        );

        $availability2 = $this->createTestAvailability(
            type: 'training',
            dailyStart: '14:00:00',
            dailyEnd: '17:00:00',
            days: ['tuesday']
        );

        // Act: Get the first availability
        $result = availability_for($this->schedulable)->first();

        // Assert: Should return the first created availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability1->id, $result->id);
    }

    /**
     * Test first returns first availability with filter.
     */
    public function test_first_returns_first_availability_with_filter(): void
    {
        // Arrange: Create multiple availabilities
        $this->createTestAvailability(
            type: 'consultation',
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00',
            days: ['monday']
        );

        $availability2 = $this->createTestAvailability(
            type: 'training',
            dailyStart: '14:00:00',
            dailyEnd: '17:00:00',
            days: ['tuesday']
        );

        // Act: Apply a type filter
        $result = availability_for($this->schedulable)
            ->whereType('training')
            ->first();

        // Assert: Should return the filtered availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability2->id, $result->id);
    }

    /**
     * Test first returns null when no availabilities.
     */
    public function test_first_returns_null_when_no_availabilities(): void
    {
        // Arrange: No availabilities created

        // Act: Attempt to get the first availability
        $result = availability_for($this->schedulable)->first();

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test getting all availabilities with filters.
     */
    public function test_can_get_all_availabilities_with_filters(): void
    {
        // Arrange: Create multiple availabilities with different types
        $this->createTestAvailability(
            type: 'consultation',
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00',
            days: ['monday']
        );

        $this->createTestAvailability(
            type: 'training',
            dailyStart: '14:00:00',
            dailyEnd: '17:00:00',
            days: ['tuesday']
        );

        // Act: Filter by type
        $result = availability_for($this->schedulable)
            ->whereType('consultation')
            ->all();

        // Assert: Should only return matching availability
        $this->assertCount(1, $result);
        $this->assertSame('consultation', $result->first()->type);
    }

    /**
     * Test validation failure during creation.
     */
    public function test_handles_validation_failure_during_creation(): void
    {
        // Arrange: Invalid data with end time before start time
        $availabilityData = $this->createValidAvailabilityData(
            dailyStart: '17:00:00',
            dailyEnd: '09:00:00'
        );

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt to create with invalid data
        availability_for($this->schedulable)->create($availabilityData);
    }

    /**
     * Test validation failure during update.
     */
    public function test_handles_validation_failure_during_update(): void
    {
        // Arrange: Create valid availability
        $availability = $this->createTestAvailability();

        $updateData = [
            'validity_end' => '2038-06-30',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt to update with invalid data
        availability_for($this->schedulable)->update($availability->id, $updateData);
    }

    /**
     * Test partial date update fails when end before existing start.
     */
    public function test_validate_partial_date_update_fails_when_end_before_existing_start(): void
    {
        // Arrange: Create availability
        $availability = $this->createTestAvailability();

        $updateData = [
            'validity_end' => '2038-06-30',
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt invalid update
        availability_for($this->schedulable)->update($availability->id, $updateData);
    }

    /**
     * Test validation fails when required fields missing.
     */
    public function test_validation_fails_when_required_fields_missing(): void
    {
        // Arrange: Missing required field (daily_start)
        $availabilityData = $this->createValidAvailabilityData();
        unset($availabilityData['daily_start']);

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt to create with missing required field
        availability_for($this->schedulable)->create($availabilityData);
    }

    /**
     * Test sets and gets filters correctly.
     */
    public function test_sets_and_gets_filters_correctly(): void
    {
        // Arrange: Filters to set
        $availabilityService = availability_for($this->schedulable);
        $filters = [
            'type' => 'consultation',
            'day' => 'monday',
        ];

        // Act: Set and get filters
        $availabilityService->setFilters($filters);
        $result = $availabilityService->getFilters();

        // Assert: Filters should be correctly stored and retrieved
        $this->assertSame($filters, $result);
    }

    /**
     * Test does not merge non-adjacent availabilities.
     */
    public function test_does_not_merge_non_adjacent_availabilities(): void
    {
        // Arrange: Create first availability
        $existingAvailability = $this->createTestAvailability(
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00'
        );

        $newData = $this->createValidAvailabilityData(
            dailyStart: '14:00:00',
            dailyEnd: '17:00:00'
        );

        // Act: Create second non-adjacent availability
        $availability = availability_for($this->schedulable)->create($newData);

        // Assert: Both availabilities should exist separately
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $existingAvailability->id,
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
        ]);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
        ]);
    }

    /**
     * Test validates minimum duration.
     */
    public function test_validates_minimum_duration(): void
    {
        // Arrange: Availability with duration less than minimum
        $availabilityData = $this->createValidAvailabilityData(
            dailyStart: '09:00:00',
            dailyEnd: '09:04:00'
        );

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Daily time slot duration must be at least 15 minutes/'
        );

        // Act: Attempt to create with insufficient duration
        availability_for($this->schedulable)->create($availabilityData);
    }

    /**
     * Test validates date range order.
     */
    public function test_validates_date_range_order(): void
    {
        // Arrange: Invalid date range (end before start)
        $availabilityData = $this->createValidAvailabilityData(
            validityStart: '2038-07-31',
            validityEnd: '2038-07-01'
        );

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);

        // Act: Attempt to create with invalid date range
        availability_for($this->schedulable)->create($availabilityData);
    }

    /**
     * Test cannot update schedulable fields.
     */
    public function test_cannot_update_schedulable_fields(): void
    {
        // Arrange: Create availability
        $availability = $this->createTestAvailability();

        $anotherSchedulable = TestSchedulable::create();

        $updateData = [
            'schedulable_id' => $anotherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
        ];

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/cannot be changed/");

        // Act: Attempt to change schedulable reference
        availability_for($this->schedulable)->update($availability->id, $updateData);
    }

    /**
     * Test getting availabilities by type filter.
     */
    public function test_can_get_availabilities_by_type_filter(): void
    {
        // Arrange: Create multiple availabilities
        $this->createTestAvailability(
            type: 'consultation',
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00'
        );

        $this->createTestAvailability(
            type: 'training',
            dailyStart: '14:00:00',
            dailyEnd: '17:00:00'
        );

        // Act: Filter by consultation type
        $result = availability_for($this->schedulable)
            ->whereType('consultation')
            ->all();

        // Assert: Should only return consultation availabilities
        $this->assertCount(1, $result);
        $this->assertSame('consultation', $result->first()->type);
    }

    /**
     * Test resetting filters.
     */
    public function test_can_reset_filters(): void
    {
        // Arrange: Set some filters
        $availabilityService = availability_for($this->schedulable);
        $availabilityService->setFilters(['type' => 'consultation']);
        $availabilityService->setFilter('day', 'monday');

        // Act: Reset filters
        $availabilityService->resetFilters();

        $filters = $availabilityService->getFilters();

        // Assert: Filters should be empty after reset
        $this->assertEmpty($filters);
    }

    /**
     * Test filtering by availability ID.
     */
    public function test_can_filter_by_availability_id(): void
    {
        // Arrange: Create availability
        $availability = $this->createTestAvailability();

        // Act: Find by ID
        $result = availability_for($this->schedulable)->find($availability->id);

        // Assert: Should return correct availability
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test validation of invalid days format.
     */
    public function test_validate_invalid_days_format(): void
    {
        // Arrange: Invalid days format (string instead of array)
        $availabilityData = $this->createValidAvailabilityData(days: 'not-an-array');

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/not-an-array.*valid day/i");

        // Act: Attempt to create with invalid days format
        availability_for($this->schedulable)->create($availabilityData);
    }


    /**
     * Test partial update is allowed.
     */
    public function test_partial_update_allowed(): void
    {
        // Arrange: Create availability
        $availability = $this->createTestAvailability();

        $updateData = [
            'daily_end' => '18:00:00',
        ];

        // Act: Perform partial update
        $result = availability_for($this->schedulable)->update($availability->id, $updateData);

        // Assert: Should succeed and update only specified field
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_end' => '18:00:00',
            'daily_start' => '09:00:00',
            'days' => json_encode(['monday', 'wednesday']),
        ]);
    }

    /**
     * Test validation of invalid day value.
     */
    public function test_validate_invalid_day_value(): void
    {
        // Arrange: Invalid day name
        $availabilityData = $this->createValidAvailabilityData(
            days: ['monday', 'invalid-day']
        );

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/invalid-day.*valid day/i");

        // Act: Attempt to create with invalid day
        availability_for($this->schedulable)->create($availabilityData);
    }

    /**
     * Test validation of invalid type.
     */
    public function test_validate_invalid_type(): void
    {
        // Arrange: Configure allowed types
        config()->set('roster.allowed_types', [
            'consultation',
            'training',
            'coaching',
            'meeting',
            'support',
        ]);

        $availabilityData = $this->createValidAvailabilityData(type: 'invalid-type');

        // Assert: Should throw validation exception
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Invalid type 'invalid-type'/");

        // Act: Attempt to create with invalid type
        availability_for($this->schedulable)->create($availabilityData);
    }

    /**
     * Test type validation when config empty.
     */
    public function test_validate_type_allowed_when_config_empty(): void
    {
        // Arrange: Empty allowed types configuration
        config()->set('roster.allowed_types', []);

        // Act: Create availability with any type
        $availability = availability_for($this->schedulable)->create([
            'type' => 'anything',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Assert: Should accept any type when config is empty
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame('anything', $availability->type);
    }

    /**
     * Test update does not trigger merge.
     */
    public function test_update_does_not_trigger_merge(): void
    {
        // Arrange: Create two separate availabilities
        $availability1 = $this->createTestAvailability(
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00'
        );

        $availability2 = $this->createTestAvailability(
            dailyStart: '14:00:00',
            dailyEnd: '17:00:00'
        );

        $updateData = [
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
        ];

        // Act: Update second availability to make it adjacent
        $result = availability_for($this->schedulable)->update($availability2->id, $updateData);

        // Assert: Should not merge with first availability
        $this->assertTrue($result);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability1->id,
            'daily_start' => '09:00:00',
        ]);

        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability2->id,
            'daily_start' => '12:00:00',
        ]);
    }

    /**
     * Create a valid availability data array for testing.
     *
     * @param string $type The availability type
     * @param string $dailyStart The daily start time
     * @param string $dailyEnd The daily end time
     * @param array|string $days The days of week
     * @param string $validityStart The validity start date
     * @param string $validityEnd The validity end date
     *
     * @return array The availability data array
     */
    private function createValidAvailabilityData(
        string $type = 'consultation',
        string $dailyStart = '09:00:00',
        string $dailyEnd = '17:00:00',
        array|string $days = ['monday', 'wednesday', 'friday'],
        string $validityStart = '2038-07-01',
        string $validityEnd = '2038-07-31'
    ): array {
        return [
            'type' => $type,
            'daily_start' => $dailyStart,
            'daily_end' => $dailyEnd,
            'days' => $days,
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ];
    }

    /**
     * Create and return a test availability instance.
     *
     * @param string $type The availability type
     * @param string $dailyStart The daily start time
     * @param string $dailyEnd The daily end time
     * @param array $days The days of week
     * @param string $validityStart The validity start date
     * @param string $validityEnd The validity end date
     *
     * @return AvailabilityModel The created availability instance
     */
    private function createTestAvailability(
        string $type = 'consultation',
        string $dailyStart = '09:00:00',
        string $dailyEnd = '17:00:00',
        array $days = ['monday', 'wednesday'],
        string $validityStart = '2038-07-01',
        string $validityEnd = '2038-07-31'
    ): AvailabilityModel {
        return availability_for($this->schedulable)->create([
            'type' => $type,
            'daily_start' => $dailyStart,
            'daily_end' => $dailyEnd,
            'days' => $days,
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);
    }
}
