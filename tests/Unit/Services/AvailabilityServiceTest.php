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

    private Model $testSchedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Test successful availability creation.
     */
    public function test_can_create_an_availability(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = availability_for($this->testSchedulable)->create($availabilityData);

        // Assert
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'type' => 'consultation',
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
        ]);

        $this->assertSame('consultation', $availability->type);
        $this->assertSame(['monday', 'wednesday', 'friday'], $availability->days);
        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
    }

    /**
     * Test days default to all days when not provided.
     */
    public function test_days_default_to_all_days_when_not_provided(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = availability_for($this->testSchedulable)->create($availabilityData);

        // Assert
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
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $updateData = [
            'daily_start' => '10:00:00',
            'daily_end' => '18:00:00',
            'days' => ['tuesday', 'thursday'],
        ];

        // Act
        $result = availability_for($this->testSchedulable)->update($availability->id, $updateData);

        // Assert
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
        // Arrange
        $availabilityId = 999;
        $updateData = ['daily_start' => '10:00:00'];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Update validation failed for Availability.*does not exist/'
        );

        // Act
        availability_for($this->testSchedulable)->update($availabilityId, $updateData);
    }

    /**
     * Test successful availability deletion.
     */
    public function test_can_delete_an_availability(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->delete($availability->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('roster_availabilities', [
            'id' => $availability->id,
        ]);
    }

    /**
     * Test delete throws exception when availability not found.
     */
    public function test_delete_throws_exception_when_availability_not_found(): void
    {
        // Arrange
        $availabilityId = 999;

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            '/Delete validation failed for Availability.*does not exist/'
        );

        // Act
        availability_for($this->testSchedulable)->delete($availabilityId);
    }

    /**
     * Test finding availability by ID.
     */
    public function test_can_find_an_availability_by_id(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->find($availability->id);

        // Assert
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
        $this->assertSame('consultation', $result->type);
    }

    /**
     * Test find returns null when availability not found.
     */
    public function test_find_returns_null_when_availability_not_found(): void
    {
        // Arrange
        $availabilityId = 999;

        // Act
        $result = availability_for($this->testSchedulable)->find($availabilityId);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test getting all availabilities with filters.
     */
    public function test_can_get_all_availabilities_with_filters(): void
    {
        // Arrange
        availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        availability_for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)
            ->whereType('consultation')
            ->all();

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('consultation', $result->first()->type);
    }

    /**
     * Test validation failure during creation.
     */
    public function test_handles_validation_failure_during_creation(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '17:00:00',
            'daily_end' => '09:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test validation failure during update.
     */
    public function test_handles_validation_failure_during_update(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $updateData = [
            'validity_end' => '2038-06-30',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        availability_for($this->testSchedulable)->update($availability->id, $updateData);
    }

    /**
     * Test partial date update fails when end before existing start.
     */
    public function test_validate_partial_date_update_fails_when_end_before_existing_start(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $updateData = [
            'validity_end' => '2038-06-30',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        availability_for($this->testSchedulable)->update($availability->id, $updateData);
    }

    /**
     * Test validation fails when required fields missing.
     */
    public function test_validation_fails_when_required_fields_missing(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test sets and gets filters correctly.
     */
    public function test_sets_and_gets_filters_correctly(): void
    {
        // Arrange
        $availabilityService = availability_for($this->testSchedulable);
        $filters = [
            'type' => 'consultation',
            'day' => 'monday',
        ];

        // Act
        $availabilityService->setFilters($filters);
        $result = $availabilityService->getFilters();

        // Assert
        $this->assertSame($filters, $result);
    }

    /**
     * Test does not merge non-adjacent availabilities.
     */
    public function test_does_not_merge_non_adjacent_availabilities(): void
    {
        // Arrange
        $existingAvailability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $newData = [
            'type' => 'consultation',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Act
        $availability = availability_for($this->testSchedulable)->create($newData);

        // Assert
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
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '09:04:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches('/Minimum duration/');

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test validates date range order.
     */
    public function test_validates_date_range_order(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-31',
            'validity_end' => '2038-07-01',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test cannot update schedulable fields.
     */
    public function test_cannot_update_schedulable_fields(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $anotherSchedulable = TestSchedulable::create();

        $updateData = [
            'schedulable_id' => $anotherSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/cannot be changed/");

        // Act
        availability_for($this->testSchedulable)->update($availability->id, $updateData);
    }

    /**
     * Test getting availabilities by type filter.
     */
    public function test_can_get_availabilities_by_type_filter(): void
    {
        // Arrange
        availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        availability_for($this->testSchedulable)->create([
            'type' => 'training',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)
            ->whereType('consultation')
            ->all();

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('consultation', $result->first()->type);
    }

    /**
     * Test resetting filters.
     */
    public function test_can_reset_filters(): void
    {
        // Arrange
        $availabilityService = availability_for($this->testSchedulable);
        $availabilityService->setFilters(['type' => 'consultation']);
        $availabilityService->setFilter('day', 'monday');

        // Act
        $availabilityService->resetFilters();
        $filters = $availabilityService->getFilters();

        // Assert
        $this->assertEmpty($filters);
    }

    /**
     * Test filtering by availability ID.
     */
    public function test_can_filter_by_availability_id(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Act
        $result = availability_for($this->testSchedulable)->find($availability->id);

        // Assert
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test validation of invalid days format.
     */
    public function test_validate_invalid_days_format(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => 'not-an-array',
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage("Day 'not-an-array' is not a valid day of week");

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test validation of empty days array.
     */
    public function test_validate_empty_days_array(): void
    {
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('Days array cannot be empty');

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test partial update is allowed.
     */
    public function test_partial_update_allowed(): void
    {
        // Arrange
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $updateData = [
            'daily_end' => '18:00:00',
        ];

        // Act
        $result = availability_for($this->testSchedulable)->update($availability->id, $updateData);

        // Assert
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
        // Arrange
        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'invalid-day'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage("Day 'invalid-day' is not a valid day of week");

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test validation of invalid type.
     */
    public function test_validate_invalid_type(): void
    {
        config()->set('roster.allowed_types', [
            'consultation',
            'training',
            'coaching',
            'meeting',
            'support',
        ]);

        // Arrange
        $availabilityData = [
            'type' => 'invalid-type',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ];

        // Assert
        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches("/Invalid type 'invalid-type'/");

        // Act
        availability_for($this->testSchedulable)->create($availabilityData);
    }

    /**
     * Test type validation when config empty.
     */
    public function test_validate_type_allowed_when_config_empty(): void
    {
        config()->set('roster.allowed_types', []);

        // Act
        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'anything',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        // Assert
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame('anything', $availability->type);
    }

    /**
     * Test update does not trigger merge.
     */
    public function test_update_does_not_trigger_merge(): void
    {
        // Arrange
        $availability1 = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '12:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $availability2 = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '14:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
        ]);

        $updateData = [
            'daily_start' => '12:00:00',
            'daily_end' => '17:00:00',
        ];

        // Act
        $result = availability_for($this->testSchedulable)->update($availability2->id, $updateData);

        // Assert
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
}
