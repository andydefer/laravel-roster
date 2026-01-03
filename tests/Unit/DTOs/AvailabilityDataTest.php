<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use Illuminate\Support\Carbon;
use Roster\DTOs\AvailabilityData;
use Roster\Models\Availability;
use Roster\Support\RosterMutationContext;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Unit tests for AvailabilityData Data Transfer Object.
 *
 * Tests the creation, validation, and business logic of availability DTOs,
 * including data transformation, update detection, and day adjustment.
 */
final class AvailabilityDataTest extends TestCase
{
    private TestSchedulable $testSchedulable;

    /**
     * Set up test dependencies before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Test successful creation of AvailabilityData from array with complete data.
     */
    public function test_from_array_with_complete_data_succeeds(): void
    {
        // Arrange: Prepare complete availability data
        $rawData = [
            'id' => 123,
            'type' => 'consultation',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'schedulable_id' => 456,
            'schedulable_type' => 'user',
        ];

        // Act: Create DTO from array data
        $availabilityData = AvailabilityData::fromArray($rawData);

        // Assert: Verify all properties are correctly set
        $this->assertSame(123, $availabilityData->id);
        $this->assertSame('consultation', $availabilityData->type);
        $this->assertSame(['monday', 'wednesday', 'friday'], $availabilityData->days);
        $this->assertSame('2038-01-01', $availabilityData->validityStart?->format('Y-m-d'));
        $this->assertSame('2038-12-31', $availabilityData->validityEnd?->format('Y-m-d'));
        $this->assertSame('09:00:00', $availabilityData->dailyStart?->format('H:i:s'));
        $this->assertSame('17:00:00', $availabilityData->dailyEnd?->format('H:i:s'));
        $this->assertSame(456, $availabilityData->schedulableId);
        $this->assertSame('user', $availabilityData->schedulableType);
    }

    /**
     * Test creation of AvailabilityData from array with partial data.
     */
    public function test_from_array_with_partial_data_succeeds(): void
    {
        // Arrange: Prepare partial availability data
        $rawData = [
            'type' => 'training',
            'daily_start' => '08:00:00',
            'daily_end' => '16:00:00',
        ];

        // Act: Create DTO from partial array data
        $availabilityData = AvailabilityData::fromArray($rawData);

        // Assert: Verify provided properties are set, others are null
        $this->assertNull($availabilityData->id);
        $this->assertSame('training', $availabilityData->type);
        $this->assertEmpty($availabilityData->days);
        $this->assertNotInstanceOf(Carbon::class, $availabilityData->validityStart);
        $this->assertNotInstanceOf(Carbon::class, $availabilityData->validityEnd);
        $this->assertSame('08:00:00', $availabilityData->dailyStart?->format('H:i:s'));
        $this->assertSame('16:00:00', $availabilityData->dailyEnd?->format('H:i:s'));
    }

    /**
     * Test creation of AvailabilityData from Eloquent model.
     */
    public function test_from_model_succeeds(): void
    {
        // Arrange: Create test availability within mutation context
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Act: Create DTO from model
        $availabilityData = AvailabilityData::fromModel($availability);

        // Assert: Verify DTO matches model data
        $this->assertEquals($availability->id, $availabilityData->id);
        $this->assertEquals($availability->type, $availabilityData->type);
        $this->assertEquals($availability->days, $availabilityData->days);
        $this->assertEquals(
            $availability->validity_start->format('Y-m-d'),
            $availabilityData->validityStart?->format('Y-m-d')
        );
        $this->assertEquals(
            $availability->validity_end->format('Y-m-d'),
            $availabilityData->validityEnd?->format('Y-m-d')
        );
        $this->assertEquals(
            $availability->daily_start->format('H:i:s'),
            $availabilityData->dailyStart?->format('H:i:s')
        );
        $this->assertEquals(
            $availability->daily_end->format('H:i:s'),
            $availabilityData->dailyEnd?->format('H:i:s')
        );
    }

    /**
     * Test conversion of AvailabilityData to array format.
     */
    public function test_to_array_conversion_succeeds(): void
    {
        // Arrange: Create DTO with complete data
        $availabilityData = AvailabilityData::fromArray([
            'id' => 123,
            'type' => 'meeting',
            'days' => ['wednesday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '10:00:00',
            'daily_end' => '12:00:00',
            'schedulable_id' => 456,
            'schedulable_type' => 'team',
        ]);

        // Act: Convert DTO to array
        $arrayData = $availabilityData->toArray();

        // Assert: Verify array format matches expectations (all keys present since all have values)
        $this->assertEquals(123, $arrayData['id']);
        $this->assertEquals('meeting', $arrayData['type']);
        $this->assertEquals(['wednesday'], $arrayData['days']);
        $this->assertEquals('2038-07-01 00:00:00', $arrayData['validity_start']);
        $this->assertEquals('2038-07-31 00:00:00', $arrayData['validity_end']);
        $this->assertEquals('10:00:00', $arrayData['daily_start']);
        $this->assertEquals('12:00:00', $arrayData['daily_end']);
        $this->assertEquals(456, $arrayData['schedulable_id']);
        $this->assertEquals('team', $arrayData['schedulable_type']);
    }

    /**
     * Test creation of new DTO instance with updated days.
     */
    public function test_with_days_creates_new_instance_with_updated_days(): void
    {
        // Arrange: Create original DTO
        $originalData = AvailabilityData::fromArray([
            'type' => 'consultation',
            'days' => ['monday'],
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act: Create new DTO with different days
        $updatedData = $originalData->withDays(['tuesday', 'thursday']);

        // Assert: Verify new instance has updated days, original unchanged
        $this->assertSame(['tuesday', 'thursday'], $updatedData->days);
        $this->assertSame(['monday'], $originalData->days);
        $this->assertSame('consultation', $updatedData->type);
        $this->assertSame('09:00:00', $updatedData->dailyStart?->format('H:i:s'));
    }

    /**
     * Test that hasDailyTimes returns true when both daily times are set.
     */
    public function test_has_daily_times_returns_true_when_both_times_present(): void
    {
        // Arrange: Create DTO with complete daily times
        $availabilityData = AvailabilityData::fromArray([
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act & Assert: Verify hasDailyTimes returns true
        $this->assertTrue($availabilityData->hasDailyTimes());
    }

    /**
     * Test that hasDailyTimes returns false when daily times are missing.
     */
    public function test_has_daily_times_returns_false_when_times_missing(): void
    {
        // Arrange: Create DTO without daily times
        $availabilityDataWithoutTimes = AvailabilityData::fromArray([
            'type' => 'consultation',
        ]);

        // Arrange: Create DTO with only start time
        $availabilityDataOnlyStart = AvailabilityData::fromArray([
            'daily_start' => '09:00:00',
        ]);

        // Arrange: Create DTO with only end time
        $availabilityDataOnlyEnd = AvailabilityData::fromArray([
            'daily_end' => '17:00:00',
        ]);

        // Act & Assert: Verify hasDailyTimes returns false for all cases
        $this->assertFalse($availabilityDataWithoutTimes->hasDailyTimes());
        $this->assertFalse($availabilityDataOnlyStart->hasDailyTimes());
        $this->assertFalse($availabilityDataOnlyEnd->hasDailyTimes());
    }

    /**
     * Test that hasValidDateRange returns true for valid date ranges.
     */
    public function test_has_valid_date_range_returns_true_for_valid_ranges(): void
    {
        // Arrange: Create DTO with valid date range
        $availabilityData = AvailabilityData::fromArray([
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Act & Assert: Verify hasValidDateRange returns true
        $this->assertTrue($availabilityData->hasValidDateRange());
    }

    /**
     * Test that hasValidDateRange returns false for invalid date ranges.
     */
    public function test_has_valid_date_range_returns_false_for_invalid_ranges(): void
    {
        // Arrange: Create DTO with end before start
        $invalidRangeData = AvailabilityData::fromArray([
            'validity_start' => '2038-12-31',
            'validity_end' => '2038-01-01',
        ]);

        // Arrange: Create DTO with only start date
        $onlyStartData = AvailabilityData::fromArray([
            'validity_start' => '2038-01-01',
        ]);

        // Arrange: Create DTO with only end date
        $onlyEndData = AvailabilityData::fromArray([
            'validity_end' => '2038-12-31',
        ]);

        // Act & Assert: Verify hasValidDateRange returns false for all cases
        $this->assertFalse($invalidRangeData->hasValidDateRange());
        $this->assertFalse($onlyStartData->hasValidDateRange());
        $this->assertFalse($onlyEndData->hasValidDateRange());
    }

    /**
     * Test that isUpdateOperation returns false for new entities.
     */
    public function test_is_update_operation_returns_false_for_new_entities(): void
    {
        // Arrange: Create DTO without ID (new entity)
        $availabilityData = AvailabilityData::fromArray([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act & Assert: Verify isUpdateOperation returns false
        $this->assertFalse($availabilityData->isUpdateOperation());
        $this->assertNotInstanceOf(Availability::class, $availabilityData->getExistingEntity());
    }

    /**
     * Test that DTO loads existing entity when ID is provided.
     */
    public function test_loads_existing_entity_when_id_provided(): void
    {
        // Arrange: Create availability in database
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Act: Create DTO with existing ID
        $availabilityData = AvailabilityData::fromArray(['id' => $availability->id]);

        // Assert: Verify entity is loaded and update operation detected
        $this->assertTrue($availabilityData->isUpdateOperation());
        $this->assertInstanceOf(Availability::class, $availabilityData->getExistingEntity());
        $this->assertEquals($availability->id, $availabilityData->getExistingEntity()?->id);
    }

    /**
     * Test that getExistingEntity returns null for non-existent IDs.
     */
    public function test_get_existing_entity_returns_null_for_non_existent_id(): void
    {
        // Arrange: Create DTO with non-existent ID
        $availabilityData = AvailabilityData::fromArray(['id' => 99999]);

        // Act & Assert: Verify getExistingEntity returns null
        $this->assertFalse($availabilityData->isUpdateOperation());
        $this->assertNotInstanceOf(Availability::class, $availabilityData->getExistingEntity());
    }

    /**
     * Test that explicitly provided days are preserved in array conversion.
     */
    public function test_explicitly_provided_days_are_preserved(): void
    {
        // Arrange: Create DTO with explicit days
        $availabilityData = AvailabilityData::fromArray([
            'days' => ['monday', 'friday'],
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act: Get array representation
        $arrayData = $availabilityData->toArray();

        // Assert: Verify explicit days are preserved
        $this->assertEquals(['monday', 'friday'], $arrayData['days']);
    }

    /**
     * Test that string days are handled correctly (not converted to array automatically).
     */
    public function test_string_days_are_handled_as_single_element_array(): void
    {
        // Arrange: Create DTO with string days
        $availabilityData = AvailabilityData::fromArray([
            'days' => 'monday,tuesday', // String format
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act: Check the days property
        $days = $availabilityData->days;
        $arrayData = $availabilityData->toArray();

        // Assert: Verify string days are treated as array with single element

        $this->assertSame(['monday,tuesday'], $days);
        $this->assertEquals(['monday,tuesday'], $arrayData['days']);
    }

    /**
     * Test that comma-separated string days can be parsed if needed (test de la logique réelle).
     */
    public function test_comma_separated_days_string_can_be_parsed_manually(): void
    {
        // Arrange: Simulate the actual parsing that might be done elsewhere
        $rawDays = 'monday,tuesday,wednesday';

        // Act: Simulate parsing logic
        $parsedDays = explode(',', $rawDays);

        // Create DTO with parsed array
        $availabilityData = AvailabilityData::fromArray([
            'days' => $parsedDays,
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Assert: Verify correctly parsed days
        $this->assertSame(['monday', 'tuesday', 'wednesday'], $availabilityData->days);
    }

    /**
     * Test conversion to array removes null values as expected.
     */
    public function test_to_array_removes_null_values(): void
    {
        // Arrange: Create DTO with null dates
        $availabilityData = AvailabilityData::fromArray([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act: Get array representation
        $arrayData = $availabilityData->toArray();

        // Assert: Verify only non-null values are present in the array
        $this->assertArrayHasKey('type', $arrayData);
        $this->assertArrayHasKey('daily_start', $arrayData);
        $this->assertArrayHasKey('daily_end', $arrayData);
        $this->assertArrayHasKey('days', $arrayData); // days might be adjusted to non-null

        // These keys should NOT be present because they are null
        $this->assertArrayNotHasKey('validity_start', $arrayData);
        $this->assertArrayNotHasKey('validity_end', $arrayData);
        $this->assertArrayNotHasKey('schedulable_id', $arrayData);
        $this->assertArrayNotHasKey('schedulable_type', $arrayData);
        $this->assertArrayNotHasKey('id', $arrayData);
    }

    /**
     * Test that withSchedulable creates new instance with updated schedulable info.
     */
    public function test_with_schedulable_creates_new_instance(): void
    {
        // Arrange: Create original DTO
        $originalData = AvailabilityData::fromArray([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ]);

        // Act: Create new DTO with schedulable info
        /** @var AvailabilityData $updatedData */
        $updatedData = $originalData->withSchedulable(123, 'user');

        // Assert: Verify new instance has schedulable info, original unchanged
        $this->assertEquals(123, $updatedData->schedulableId);
        $this->assertEquals('user', $updatedData->schedulableType);
        $this->assertNull($originalData->schedulableId);
        $this->assertNull($originalData->schedulableType);

        // Verify other properties remain the same
        $this->assertEquals('consultation', $updatedData->type);
        $this->assertEquals('09:00:00', $updatedData->dailyStart?->format('H:i:s'));
    }

    /**
     * Test that withSchedulable returns array with schedulable keys.
     */
    public function test_with_schedulable_array_includes_schedulable_keys(): void
    {
        // Arrange: Create DTO with schedulable info
        $availabilityData = AvailabilityData::fromArray([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ])->withSchedulable(123, 'user');

        // Act: Get array representation
        $arrayData = $availabilityData->toArray();

        // Assert: Verify schedulable keys are present in array
        $this->assertArrayHasKey('schedulable_id', $arrayData);
        $this->assertArrayHasKey('schedulable_type', $arrayData);
        $this->assertEquals(123, $arrayData['schedulable_id']);
        $this->assertEquals('user', $arrayData['schedulable_type']);
    }
}
