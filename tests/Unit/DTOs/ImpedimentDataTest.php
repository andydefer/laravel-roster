<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use Exception;
use ReflectionClass;
use Illuminate\Support\Carbon as IlluminateCarbon;
use InvalidArgumentException;
use Roster\DTOs\ImpedimentDto;
use Roster\Models\Impediment;
use Roster\Support\RosterMutationContext;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Unit tests for ImpedimentDto Data Transfer Object.
 *
 * Tests the creation, validation, and transformation of impediment DTOs,
 * including UTC enforcement, date parsing, and model conversion.
 */
final class ImpedimentDataTest extends TestCase
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
     * Test successful creation of ImpedimentDto from array with complete data.
     */
    public function test_from_array_with_complete_data_succeeds(): void
    {
        // Arrange: Prepare complete impediment data with UTC dates
        $rawData = [
            'id' => 123,
            'availability_id' => 456,
            'start_datetime' => '2038-01-15 09:00:00',
            'end_datetime' => '2038-01-15 17:00:00',
            'reason' => 'Maintenance window',
            'metadata' => ['type' => 'scheduled', 'impact' => 'high'],
            'schedulable_id' => 789,
            'schedulable_type' => 'equipment',
        ];

        // Act: Create DTO from array data
        $impedimentData = ImpedimentDto::fromArray($rawData);

        // Assert: Verify all properties are correctly set with UTC timezone
        $this->assertSame(123, $impedimentData->id);
        $this->assertSame(456, $impedimentData->availabilityId);
        $this->assertSame('2038-01-15 09:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $impedimentData->startDatetime?->getTimezone()->getName());
        $this->assertSame('2038-01-15 17:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $impedimentData->endDatetime?->getTimezone()->getName());
        $this->assertSame('Maintenance window', $impedimentData->reason);
        $this->assertSame(['type' => 'scheduled', 'impact' => 'high'], $impedimentData->metadata);
        $this->assertSame(789, $impedimentData->schedulableId);
        $this->assertSame('equipment', $impedimentData->schedulableType);
    }

    /**
     * Test creation of ImpedimentDto from array with partial data.
     */
    public function test_from_array_with_partial_data_succeeds(): void
    {
        // Arrange: Prepare partial impediment data
        $rawData = [
            'start_datetime' => '2038-02-01 08:00:00',
            'end_datetime' => '2038-02-01 12:00:00',
            'reason' => 'Team offsite',
        ];

        // Act: Create DTO from partial array data
        $impedimentData = ImpedimentDto::fromArray($rawData);

        // Assert: Verify provided properties are set with defaults applied
        $this->assertNull($impedimentData->id);
        $this->assertNull($impedimentData->availabilityId);
        $this->assertSame('2038-02-01 08:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame('2038-02-01 12:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame('Team offsite', $impedimentData->reason);
        $this->assertSame([], $impedimentData->metadata);
        $this->assertNull($impedimentData->schedulableId);
        $this->assertNull($impedimentData->schedulableType);
    }

    /**
     * Test creation of ImpedimentDto from Illuminate Carbon instances.
     */
    public function test_from_array_with_illuminate_carbon_instances_succeeds(): void
    {
        // Arrange: Prepare impediment data with Illuminate Carbon instances
        $rawData = [
            'start_datetime' => IlluminateCarbon::create(2038, 3, 10, 10, 0, 0, 'UTC'),
            'end_datetime' => IlluminateCarbon::create(2038, 3, 10, 18, 0, 0, 'UTC'),
            'reason' => 'System upgrade',
        ];

        // Act: Create DTO from Carbon instances
        $impedimentData = ImpedimentDto::fromArray($rawData);

        // Assert: Verify Carbon instances are correctly handled
        $this->assertSame('System upgrade', $impedimentData->reason);
        $this->assertSame('2038-03-10 10:00:00', $impedimentData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame('2038-03-10 18:00:00', $impedimentData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $impedimentData->startDatetime?->getTimezone()->getName());
        $this->assertEquals('UTC', $impedimentData->endDatetime?->getTimezone()->getName());
    }

    /**
     * Test creation of ImpedimentDto from Eloquent model.
     */
    public function test_from_model_succeeds(): void
    {
        // Arrange: Create test impediment within mutation context
        $impediment = RosterMutationContext::allow(function () {
            return Impediment::create([
                'availability_id' => 1,
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'start_datetime' => '2038-04-15 09:00:00',
                'end_datetime' => '2038-04-15 13:00:00',
                'reason' => 'Emergency maintenance',
                'metadata' => ['priority' => 'critical', 'department' => 'IT'],
            ]);
        });

        // Act: Create DTO from model
        $impedimentData = ImpedimentDto::fromModel($impediment);

        // Assert: Verify DTO matches model data with UTC timezone
        $this->assertEquals($impediment->id, $impedimentData->id);
        $this->assertEquals($impediment->availability_id, $impedimentData->availabilityId);
        $this->assertEquals(
            $impediment->start_datetime->format('Y-m-d H:i:s'),
            $impedimentData->startDatetime?->format('Y-m-d H:i:s')
        );
        $this->assertEquals('UTC', $impedimentData->startDatetime?->getTimezone()->getName());
        $this->assertEquals(
            $impediment->end_datetime->format('Y-m-d H:i:s'),
            $impedimentData->endDatetime?->format('Y-m-d H:i:s')
        );
        $this->assertEquals('UTC', $impedimentData->endDatetime?->getTimezone()->getName());
        $this->assertEquals($impediment->reason, $impedimentData->reason);
        $this->assertEquals($impediment->metadata, $impedimentData->metadata);
        $this->assertEquals($impediment->schedulable_id, $impedimentData->schedulableId);
        $this->assertEquals($impediment->schedulable_type, $impedimentData->schedulableType);
    }

    /**
     * Test conversion of ImpedimentDto to array format.
     */
    public function test_to_array_conversion_succeeds(): void
    {
        // Arrange: Create DTO with complete data
        $impedimentData = ImpedimentDto::fromArray([
            'id' => 123,
            'availability_id' => 456,
            'start_datetime' => '2038-05-20 08:00:00',
            'end_datetime' => '2038-05-20 16:00:00',
            'reason' => 'Public holiday',
            'metadata' => ['holiday_name' => 'National Day'],
            'schedulable_id' => 789,
            'schedulable_type' => 'office',
        ]);

        // Act: Convert DTO to array
        $arrayData = $impedimentData->toArray();

        // Assert: Verify array format matches expectations with UTC dates
        $this->assertEquals(123, $arrayData['id']);
        $this->assertEquals(456, $arrayData['availability_id']);
        $this->assertEquals('2038-05-20 08:00:00', $arrayData['start_datetime']);
        $this->assertEquals('2038-05-20 16:00:00', $arrayData['end_datetime']);
        $this->assertEquals('Public holiday', $arrayData['reason']);
        $this->assertEquals(['holiday_name' => 'National Day'], $arrayData['metadata']);
        $this->assertEquals(789, $arrayData['schedulable_id']);
        $this->assertEquals('office', $arrayData['schedulable_type']);
    }

    /**
     * Test that toArray removes null values as expected.
     */
    public function test_to_array_removes_null_values(): void
    {
        // Arrange: Create DTO with null values
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2038-06-01 09:00:00',
            'end_datetime' => '2038-06-01 17:00:00',
            'reason' => 'Team building',
        ]);

        // Act: Get array representation
        $arrayData = $impedimentData->toArray();

        // Assert: Verify only non-null values are present
        $this->assertArrayHasKey('start_datetime', $arrayData);
        $this->assertArrayHasKey('end_datetime', $arrayData);
        $this->assertArrayHasKey('reason', $arrayData);
        $this->assertArrayHasKey('metadata', $arrayData); // Default empty array

        // These keys should NOT be present because they are null
        $this->assertArrayNotHasKey('id', $arrayData);
        $this->assertArrayNotHasKey('availability_id', $arrayData);
        $this->assertArrayNotHasKey('schedulable_id', $arrayData);
        $this->assertArrayNotHasKey('schedulable_type', $arrayData);
    }

    /**
     * Test that metadata defaults to empty array when not provided.
     */
    public function test_metadata_defaults_to_empty_array(): void
    {
        // Arrange: Create DTO without metadata
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2038-07-10 10:00:00',
            'end_datetime' => '2038-07-10 14:00:00',
            'reason' => 'Training session',
        ]);

        // Act & Assert: Verify metadata is empty array
        $this->assertSame([], $impedimentData->metadata);
        $this->assertEquals([], $impedimentData->toArray()['metadata']);
    }

    /**
     * Test that reason can be null when not provided.
     */
    public function test_reason_can_be_null_when_not_provided(): void
    {
        // Arrange: Create DTO without reason
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2038-08-15 08:00:00',
            'end_datetime' => '2038-08-15 12:00:00',
        ]);

        // Act & Assert: Verify reason is null
        $this->assertNull($impedimentData->reason);

        $arrayData = $impedimentData->toArray();
        $this->assertArrayNotHasKey('reason', $arrayData);
    }

    /**
     * Test creation with invalid datetime string throws exception.
     */
    public function test_with_invalid_datetime_string_throws_exception(): void
    {
        // Arrange: Prepare data with invalid datetime format
        $rawData = [
            'start_datetime' => 'not-a-valid-date',
            'end_datetime' => '2038-09-01 10:00:00',
            'reason' => 'Test impediment',
        ];

        // Act & Assert: Verify exception is thrown for invalid datetime
        $this->expectException(Exception::class);

        ImpedimentDto::fromArray($rawData);
    }

    /**
     * Test that empty datetime strings are handled by Carbon (returns current date).
     */
    public function test_empty_datetime_strings_are_handled_by_carbon(): void
    {
        // Arrange: Prepare data with empty datetime strings
        $rawData = [
            'start_datetime' => '', // Carbon::parse('') returns current date
            'end_datetime' => null,
            'reason' => 'Test empty dates',
        ];

        // Act: Create DTO from data with empty datetime
        $impedimentData = ImpedimentDto::fromArray($rawData);

        // Assert: Verify empty string creates Carbon instance (current date), null remains null
        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
        $this->assertNotInstanceOf(IlluminateCarbon::class, $impedimentData->endDatetime);
    }

    /**
     * Test that duration calculation works correctly.
     */
    public function test_calculates_correct_duration(): void
    {
        // Arrange: Create DTO with specific start and end times
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2038-12-01 09:00:00',
            'end_datetime' => '2038-12-01 17:00:00',
            'reason' => 'Full day impediment',
        ]);

        // Act: Calculate duration from Carbon instances
        $duration = $impedimentData->startDatetime->diffInMinutes($impedimentData->endDatetime);

        // Assert: Verify duration is correctly calculated (8 hours = 480 minutes)
        $this->assertEquals(480, $duration);
    }

    /**
     * Test that impediment dates enforce UTC timezone.
     */
    public function test_dates_enforce_utc_timezone(): void
    {
        // Arrange: Create DTO with explicit UTC dates using Illuminate Carbon
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => IlluminateCarbon::create(2038, 12, 25, 00, 0, 0, 'UTC'),
            'end_datetime' => IlluminateCarbon::create(2038, 12, 26, 00, 0, 0, 'UTC'),
            'reason' => 'Christmas holiday',
        ]);

        // Act & Assert: Verify both dates are in UTC
        $this->assertEquals('UTC', $impedimentData->startDatetime?->getTimezone()->getName());
        $this->assertEquals('UTC', $impedimentData->endDatetime?->getTimezone()->getName());

        // Verify timezone is preserved in array conversion
        $arrayData = $impedimentData->toArray();
        $this->assertStringContainsString('2038-12-25 00:00:00', (string) $arrayData['start_datetime']);
        $this->assertStringContainsString('2038-12-26 00:00:00', (string) $arrayData['end_datetime']);
    }

    /**
     * Test that DTO is immutable (properties are readonly).
     */
    public function test_dto_is_immutable(): void
    {
        // Arrange: Create DTO instance
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2039-01-01 09:00:00',
            'end_datetime' => '2039-01-01 17:00:00',
            'reason' => 'New Year holiday',
        ]);

        // Act & Assert: Verify properties are readonly (cannot be modified)
        $reflection = new ReflectionClass($impedimentData);

        foreach ($reflection->getProperties() as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    /**
     * Test that withSchedulable creates new instance with updated schedulable info.
     */
    public function test_with_schedulable_creates_new_instance(): void
    {
        // Arrange: Create original DTO
        $originalData = ImpedimentDto::fromArray([
            'start_datetime' => '2039-02-01 09:00:00',
            'end_datetime' => '2039-02-01 13:00:00',
            'reason' => 'Team meeting',
        ]);

        // Act: Create new DTO with schedulable info
        /** @var ImpedimentDto $updatedData */
        $updatedData = $originalData->withSchedulable(456, 'team');

        // Assert: Verify new instance has schedulable info, original unchanged
        $this->assertEquals(456, $updatedData->schedulableId);
        $this->assertEquals('team', $updatedData->schedulableType);
        $this->assertNull($originalData->schedulableId);
        $this->assertNull($originalData->schedulableType);

        // Verify other properties remain the same
        $this->assertEquals('Team meeting', $updatedData->reason);
        $this->assertEquals(
            '2039-02-01 09:00:00',
            $updatedData->startDatetime?->format('Y-m-d H:i:s')
        );
    }

    /**
     * Test that withSchedulable returns array with schedulable keys.
     */
    public function test_with_schedulable_array_includes_schedulable_keys(): void
    {
        // Arrange: Create DTO with schedulable info
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2039-03-01 14:00:00',
            'end_datetime' => '2039-03-01 18:00:00',
            'reason' => 'Equipment maintenance',
        ])->withSchedulable(789, 'machine');

        // Act: Get array representation
        $arrayData = $impedimentData->toArray();

        // Assert: Verify schedulable keys are present in array
        $this->assertArrayHasKey('schedulable_id', $arrayData);
        $this->assertArrayHasKey('schedulable_type', $arrayData);
        $this->assertEquals(789, $arrayData['schedulable_id']);
        $this->assertEquals('machine', $arrayData['schedulable_type']);
    }

    /**
     * Test that parseDateTime handles invalid input types.
     */
    public function test_parse_datetime_throws_exception_for_invalid_input_type(): void
    {
        // Arrange: Use reflection to test protected method
        $reflectionClass = new ReflectionClass(ImpedimentDto::class);
        $method = $reflectionClass->getMethod('parseDateTime');
        $method->setAccessible(true);

        // Act & Assert: Verify invalid input type throws exception
        $this->expectException(InvalidArgumentException::class);

        $method->invokeArgs(null, [123]); // Integer is invalid
    }

    /**
     * Test that parseDateTime returns null for null input.
     */
    public function test_parse_datetime_returns_null_for_null_input(): void
    {
        // Arrange: Use reflection to test protected method
        $reflectionClass = new ReflectionClass(ImpedimentDto::class);
        $method = $reflectionClass->getMethod('parseDateTime');
        $method->setAccessible(true);

        // Act: Parse null input
        $result = $method->invokeArgs(null, [null]);

        // Assert: Verify result is null
        $this->assertNull($result);
    }

    /**
     * Test that parseDateTime preserves Illuminate Carbon instance.
     */
    public function test_parse_datetime_preserves_illuminate_carbon_instance(): void
    {
        // Arrange: Create Illuminate Carbon instance
        $carbon = IlluminateCarbon::create(2039, 4, 1, 10, 0, 0, 'UTC');

        // Use reflection to test protected method
        $reflectionClass = new ReflectionClass(ImpedimentDto::class);
        $method = $reflectionClass->getMethod('parseDateTime');
        $method->setAccessible(true);

        // Act: Parse Illuminate Carbon instance
        $result = $method->invokeArgs(null, [$carbon]);

        // Assert: Verify same instance is returned
        $this->assertSame($carbon, $result);
    }

    /**
     * Test that toArray returns same data as fromArray after conversion.
     */
    public function test_to_array_round_trip_conserves_data(): void
    {
        // Arrange: Create original data
        $originalData = [
            'id' => 123,
            'availability_id' => 456,
            'start_datetime' => '2039-05-01 08:00:00',
            'end_datetime' => '2039-05-01 16:00:00',
            'reason' => 'System maintenance',
            'metadata' => ['maintenance_type' => 'preventive'],
            'schedulable_id' => 789,
            'schedulable_type' => 'server',
        ];

        // Act: Create DTO and convert back to array
        $impedimentData = ImpedimentDto::fromArray($originalData);
        $convertedData = $impedimentData->toArray();

        // Assert: Verify all non-null data is preserved
        foreach ($originalData as $key => $value) {
            $this->assertArrayHasKey($key, $convertedData);
            $this->assertEquals($value, $convertedData[$key]);
        }
    }

    /**
     * Test that DTO handles overlapping time periods correctly.
     */
    public function test_handles_overlapping_time_periods(): void
    {
        // Arrange: Create two impediments with overlapping times
        $impedimentData1 = ImpedimentDto::fromArray([
            'start_datetime' => '2038-10-01 09:00:00',
            'end_datetime' => '2038-10-01 12:00:00',
            'reason' => 'Morning meeting',
        ]);

        $impedimentData2 = ImpedimentDto::fromArray([
            'start_datetime' => '2038-10-01 11:00:00',
            'end_datetime' => '2038-10-01 14:00:00',
            'reason' => 'Lunch meeting',
        ]);

        // Act: Check if periods overlap
        $overlapStart = $impedimentData1->startDatetime;
        $overlapEnd = $impedimentData2->endDatetime;

        // Calculate overlap duration
        $overlapDuration = $overlapStart->diffInMinutes($overlapEnd);

        // Assert: Verify overlap is detected and duration calculated
        $this->assertTrue($impedimentData1->endDatetime->gt($impedimentData2->startDatetime));
        $this->assertEquals(300, $overlapDuration); // 5 hours from 9:00 to 14:00
    }

    /**
     * Test that DTO handles all-day impediments correctly.
     */
    public function test_handles_all_day_impediments(): void
    {
        // Arrange: Create all-day impediment
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2038-11-01 00:00:00',
            'end_datetime' => '2038-11-02 00:00:00',
            'reason' => 'All day event',
        ]);

        // Act: Calculate duration
        $duration = $impedimentData->startDatetime->diffInHours($impedimentData->endDatetime);

        // Assert: Verify it's exactly 24 hours
        $this->assertEquals(24, $duration);
        $this->assertSame('All day event', $impedimentData->reason);
    }

    /**
     * Test that DTO handles impediment with only start date.
     */
    public function test_handles_impediment_with_only_start_date(): void
    {
        // Arrange: Create impediment with only start date
        $impedimentData = ImpedimentDto::fromArray([
            'start_datetime' => '2038-12-01 09:00:00',
            'reason' => 'Start only test',
        ]);

        // Assert: Verify start date is set, end date is null
        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
        $this->assertNotInstanceOf(IlluminateCarbon::class, $impedimentData->endDatetime);
        $this->assertSame('Start only test', $impedimentData->reason);

        $arrayData = $impedimentData->toArray();
        $this->assertArrayHasKey('start_datetime', $arrayData);
        $this->assertArrayNotHasKey('end_datetime', $arrayData);
    }

    /**
     * Test that DTO handles impediment with only end date.
     */
    public function test_handles_impediment_with_only_end_date(): void
    {
        // Arrange: Create impediment with only end date
        $impedimentData = ImpedimentDto::fromArray([
            'end_datetime' => '2038-12-01 17:00:00',
            'reason' => 'End only test',
        ]);

        // Assert: Verify end date is set, start date is null
        $this->assertNotInstanceOf(IlluminateCarbon::class, $impedimentData->startDatetime);
        $this->assertInstanceOf(IlluminateCarbon::class, $impedimentData->endDatetime);
        $this->assertSame('End only test', $impedimentData->reason);

        $arrayData = $impedimentData->toArray();
        $this->assertArrayNotHasKey('start_datetime', $arrayData);
        $this->assertArrayHasKey('end_datetime', $arrayData);
    }
}
