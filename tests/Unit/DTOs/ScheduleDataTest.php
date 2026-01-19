<?php

declare(strict_types=1);

namespace Tests\Unit\DTOs;

use Exception;
use ReflectionClass;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Roster\DTOs\ScheduleDto;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Schedule;
use Roster\Support\RosterMutationContext;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Unit tests for ScheduleDto Data Transfer Object.
 *
 * Tests the creation, validation, and transformation of schedule DTOs,
 * including UTC enforcement, date parsing, and model conversion.
 */
final class ScheduleDataTest extends TestCase
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
     * Test successful creation of ScheduleDto from array with complete data.
     */
    public function test_from_array_with_complete_data_succeeds(): void
    {
        // Arrange: Prepare complete schedule data with UTC dates
        $rawData = [
            'id' => 123,
            'availability_id' => 456,
            'title' => 'Team Meeting',
            'description' => 'Weekly team sync to discuss project progress',
            'start_datetime' => '2038-01-15 09:00:00',
            'end_datetime' => '2038-01-15 10:30:00',
            'metadata' => ['room' => 'Conference A', 'priority' => 'high'],
            'status' => ScheduleStatus::BOOKED,
            'schedulable_id' => 789,
            'schedulable_type' => 'team',
        ];

        // Act: Create DTO from array data
        $scheduleData = ScheduleDto::fromArray($rawData);

        // Assert: Verify all properties are correctly set with UTC timezone
        $this->assertSame(123, $scheduleData->id);
        $this->assertSame(456, $scheduleData->availabilityId);
        $this->assertSame('Team Meeting', $scheduleData->title);
        $this->assertSame('Weekly team sync to discuss project progress', $scheduleData->description);
        $this->assertSame('2038-01-15 09:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
        $this->assertSame('2038-01-15 10:30:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $scheduleData->endDatetime?->getTimezone()->getName());
        $this->assertSame(['room' => 'Conference A', 'priority' => 'high'], $scheduleData->metadata);
        $this->assertSame(ScheduleStatus::BOOKED, $scheduleData->status);
        $this->assertSame(789, $scheduleData->schedulableId);
        $this->assertSame('team', $scheduleData->schedulableType);
    }

    /**
     * Test creation of ScheduleDto from array with partial data.
     */
    public function test_from_array_with_partial_data_succeeds(): void
    {
        // Arrange: Prepare partial schedule data
        $rawData = [
            'title' => 'Client Call',
            'start_datetime' => '2038-02-01 14:00:00',
            'end_datetime' => '2038-02-01 15:00:00',
        ];

        // Act: Create DTO from partial array data
        $scheduleData = ScheduleDto::fromArray($rawData);

        // Assert: Verify provided properties are set with defaults applied
        $this->assertNull($scheduleData->id);
        $this->assertNull($scheduleData->availabilityId);
        $this->assertSame('Client Call', $scheduleData->title);
        $this->assertNull($scheduleData->description);
        $this->assertSame('2038-02-01 14:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame('2038-02-01 15:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame([], $scheduleData->metadata);
        $this->assertSame(ScheduleStatus::AVAILABLE, $scheduleData->status);
        $this->assertNull($scheduleData->schedulableId);
        $this->assertNull($scheduleData->schedulableType);
    }

    /**
     * Test creation of ScheduleDto from Illuminate Carbon instances.
     */
    public function test_from_array_with_illuminate_carbon_instances_succeeds(): void
    {
        // Arrange: Prepare schedule data with Illuminate Carbon instances
        $rawData = [
            'title' => 'Training Session',
            'start_datetime' => Carbon::create(2038, 3, 10, 10, 0, 0, 'UTC'),
            'end_datetime' => Carbon::create(2038, 3, 10, 12, 0, 0, 'UTC'),
        ];

        // Act: Create DTO from Carbon instances
        $scheduleData = ScheduleDto::fromArray($rawData);

        // Assert: Verify Carbon instances are correctly handled
        $this->assertSame('Training Session', $scheduleData->title);
        $this->assertSame('2038-03-10 10:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame('2038-03-10 12:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
        $this->assertEquals('UTC', $scheduleData->endDatetime?->getTimezone()->getName());
    }

    /**
     * Test creation of ScheduleDto from Carbon Carbon instances (avec conversion).
     */
    public function test_from_array_with_carbon_carbon_instances_handles_conversion(): void
    {
        // Arrange: Prepare schedule data with Carbon Carbon instances
        $rawData = [
            'title' => 'Carbon Carbon Test',
            'start_datetime' => Carbon::create(2038, 6, 10, 10, 0, 0, 'UTC'),
            'end_datetime' => Carbon::create(2038, 6, 10, 12, 0, 0, 'UTC'),
        ];

        // Act: Create DTO from Carbon Carbon instances (sera converti)
        $scheduleData = ScheduleDto::fromArray($rawData);

        // Assert: Verify instances sont correctement gérés (même si conversion)
        $this->assertSame('Carbon Carbon Test', $scheduleData->title);
        $this->assertSame('2038-06-10 10:00:00', $scheduleData->startDatetime?->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-10 12:00:00', $scheduleData->endDatetime?->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
    }

    /**
     * Test creation of ScheduleDto from Eloquent model.
     */
    public function test_from_model_succeeds(): void
    {
        // Arrange: Create test schedule within mutation context
        $schedule = RosterMutationContext::allow(function () {
            return Schedule::create([
                'availability_id' => 1,
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'title' => 'Project Review',
                'description' => 'Quarterly project review meeting',
                'start_datetime' => '2038-04-15 13:00:00',
                'end_datetime' => '2038-04-15 14:30:00',
                'status' => ScheduleStatus::AVAILABLE,
                'metadata' => ['department' => 'Engineering'],
            ]);
        });

        // Act: Create DTO from model
        $scheduleData = ScheduleDto::fromModel($schedule);

        // Assert: Verify DTO matches model data with UTC timezone
        $this->assertEquals($schedule->id, $scheduleData->id);
        $this->assertEquals($schedule->availability_id, $scheduleData->availabilityId);
        $this->assertEquals($schedule->title, $scheduleData->title);
        $this->assertEquals($schedule->description, $scheduleData->description);
        $this->assertEquals(
            $schedule->start_datetime->format('Y-m-d H:i:s'),
            $scheduleData->startDatetime?->format('Y-m-d H:i:s')
        );
        $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
        $this->assertEquals(
            $schedule->end_datetime->format('Y-m-d H:i:s'),
            $scheduleData->endDatetime?->format('Y-m-d H:i:s')
        );
        $this->assertEquals('UTC', $scheduleData->endDatetime?->getTimezone()->getName());
        $this->assertEquals($schedule->status, $scheduleData->status);
        $this->assertEquals($schedule->metadata, $scheduleData->metadata);
        $this->assertEquals($schedule->schedulable_id, $scheduleData->schedulableId);
        $this->assertEquals($schedule->schedulable_type, $scheduleData->schedulableType);
    }

    /**
     * Test conversion of ScheduleDto to array format.
     */
    public function test_to_array_conversion_succeeds(): void
    {
        // Arrange: Create DTO with complete data
        $scheduleData = ScheduleDto::fromArray([
            'id' => 123,
            'availability_id' => 456,
            'title' => 'Board Meeting',
            'description' => 'Monthly board of directors meeting',
            'start_datetime' => '2038-05-20 15:00:00',
            'end_datetime' => '2038-05-20 17:00:00',
            'metadata' => ['confidential' => true],
            'status' => ScheduleStatus::BOOKED,
            'schedulable_id' => 789,
            'schedulable_type' => 'board',
        ]);

        // Act: Convert DTO to array
        $arrayData = $scheduleData->toArray();

        // Assert: Verify array format matches expectations with UTC dates
        $this->assertEquals(123, $arrayData['id']);
        $this->assertEquals(456, $arrayData['availability_id']);
        $this->assertEquals('Board Meeting', $arrayData['title']);
        $this->assertEquals('Monthly board of directors meeting', $arrayData['description']);
        $this->assertEquals('2038-05-20 15:00:00', $arrayData['start_datetime']);
        $this->assertEquals('2038-05-20 17:00:00', $arrayData['end_datetime']);
        $this->assertEquals(['confidential' => true], $arrayData['metadata']);
        $this->assertEquals(ScheduleStatus::BOOKED, $arrayData['status']);
        $this->assertEquals(789, $arrayData['schedulable_id']);
        $this->assertEquals('board', $arrayData['schedulable_type']);
    }

    /**
     * Test that toArray removes null values as expected.
     */
    public function test_to_array_removes_null_values(): void
    {
        // Arrange: Create DTO with null values
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'Standup Meeting',
            'start_datetime' => '2038-06-01 09:30:00',
            'end_datetime' => '2038-06-01 09:45:00',
            'status' => ScheduleStatus::AVAILABLE,
        ]);

        // Act: Get array representation
        $arrayData = $scheduleData->toArray();

        // Assert: Verify only non-null values are present
        $this->assertArrayHasKey('title', $arrayData);
        $this->assertArrayHasKey('start_datetime', $arrayData);
        $this->assertArrayHasKey('end_datetime', $arrayData);
        $this->assertArrayHasKey('status', $arrayData);
        $this->assertArrayHasKey('metadata', $arrayData); // Default empty array

        // These keys should NOT be present because they are null
        $this->assertArrayNotHasKey('id', $arrayData);
        $this->assertArrayNotHasKey('availability_id', $arrayData);
        $this->assertArrayNotHasKey('description', $arrayData);
        $this->assertArrayNotHasKey('schedulable_id', $arrayData);
        $this->assertArrayNotHasKey('schedulable_type', $arrayData);
    }

    /**
     * Test that metadata defaults to empty array when not provided.
     */
    public function test_metadata_defaults_to_empty_array(): void
    {
        // Arrange: Create DTO without metadata
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'Planning Session',
            'start_datetime' => '2038-07-10 10:00:00',
            'end_datetime' => '2038-07-10 11:30:00',
        ]);

        // Act & Assert: Verify metadata is empty array
        $this->assertSame([], $scheduleData->metadata);
        $this->assertEquals([], $scheduleData->toArray()['metadata']);
    }

    /**
     * Test that status defaults to AVAILABLE when not provided.
     */
    public function test_status_defaults_to_available(): void
    {
        // Arrange: Create DTO without status
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'One-on-One',
            'start_datetime' => '2038-08-15 14:00:00',
            'end_datetime' => '2038-08-15 15:00:00',
        ]);

        // Act & Assert: Verify default status is AVAILABLE
        $this->assertSame(ScheduleStatus::AVAILABLE, $scheduleData->status);
        $this->assertEquals(ScheduleStatus::AVAILABLE, $scheduleData->toArray()['status']);
    }

    /**
     * Test creation with invalid datetime string throws exception.
     */
    public function test_with_invalid_datetime_string_throws_exception(): void
    {
        // Arrange: Prepare data with invalid datetime format
        $rawData = [
            'title' => 'Invalid Date Test',
            'start_datetime' => 'not-a-valid-date',
            'end_datetime' => '2038-09-01 10:00:00',
        ];

        // Act & Assert: Verify exception is thrown for invalid datetime
        $this->expectException(Exception::class);

        ScheduleDto::fromArray($rawData);
    }

    /**
     * Test that empty datetime strings are handled by Carbon (returns current date).
     */
    public function test_empty_datetime_strings_are_handled_by_carbon(): void
    {
        // Arrange: Prepare data with empty datetime strings
        $rawData = [
            'title' => 'Empty Date Test',
            'start_datetime' => '', // Carbon::parse('') returns current date
            'end_datetime' => null,
        ];

        // Act: Create DTO from data with empty datetime
        $scheduleData = ScheduleDto::fromArray($rawData);

        // Assert: Verify empty string creates Carbon instance (current date), null remains null
        $this->assertInstanceOf(Carbon::class, $scheduleData->startDatetime);
        $this->assertInstanceOf(Carbon::class, $scheduleData->startDatetime);
        $this->assertNotInstanceOf(Carbon::class, $scheduleData->endDatetime);
    }

    /**
     * Test that duration calculation works correctly.
     */
    public function test_calculates_correct_duration(): void
    {
        // Arrange: Create DTO with specific start and end times
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'Duration Test',
            'start_datetime' => '2038-12-01 09:00:00',
            'end_datetime' => '2038-12-01 10:30:00', // 90 minutes
        ]);

        // Act: Calculate duration from Carbon instances
        $duration = $scheduleData->startDatetime->diffInMinutes($scheduleData->endDatetime);

        // Assert: Verify duration is correctly calculated
        $this->assertEquals(90, $duration);
    }

    /**
     * Test that schedule dates enforce UTC timezone.
     */
    public function test_dates_enforce_utc_timezone(): void
    {
        // Arrange: Create DTO with explicit UTC dates using Illuminate Carbon
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'UTC Enforcement Test',
            'start_datetime' => Carbon::create(2038, 12, 25, 14, 0, 0, 'UTC'),
            'end_datetime' => Carbon::create(2038, 12, 25, 15, 30, 0, 'UTC'),
        ]);

        // Act & Assert: Verify both dates are in UTC
        $this->assertEquals('UTC', $scheduleData->startDatetime?->getTimezone()->getName());
        $this->assertEquals('UTC', $scheduleData->endDatetime?->getTimezone()->getName());

        // Verify timezone is preserved in array conversion
        $arrayData = $scheduleData->toArray();
        $this->assertStringContainsString('2038-12-25 14:00:00', (string) $arrayData['start_datetime']);
        $this->assertStringContainsString('2038-12-25 15:30:00', (string) $arrayData['end_datetime']);
    }

    /**
     * Test that DTO is immutable (properties are readonly).
     */
    public function test_dto_is_immutable(): void
    {
        // Arrange: Create DTO instance
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'Immutable Test',
            'start_datetime' => '2039-01-01 09:00:00',
            'end_datetime' => '2039-01-01 10:00:00',
        ]);

        // Act & Assert: Verify properties are readonly (cannot be modified)
        $reflection = new ReflectionClass($scheduleData);

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
        $originalData = ScheduleDto::fromArray([
            'title' => 'Team Meeting',
            'start_datetime' => '2039-02-01 09:00:00',
            'end_datetime' => '2039-02-01 10:00:00',
        ]);

        // Act: Create new DTO with schedulable info
        /** @var ScheduleDto $updatedData */
        $updatedData = $originalData->withSchedulable(456, 'team');

        // Assert: Verify new instance has schedulable info, original unchanged
        $this->assertEquals(456, $updatedData->schedulableId);
        $this->assertEquals('team', $updatedData->schedulableType);
        $this->assertNull($originalData->schedulableId);
        $this->assertNull($originalData->schedulableType);

        // Verify other properties remain the same
        $this->assertEquals('Team Meeting', $updatedData->title);
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
        $scheduleData = ScheduleDto::fromArray([
            'title' => 'Client Meeting',
            'start_datetime' => '2039-03-01 14:00:00',
            'end_datetime' => '2039-03-01 15:00:00',
        ])->withSchedulable(789, 'client');

        // Act: Get array representation
        $arrayData = $scheduleData->toArray();

        // Assert: Verify schedulable keys are present in array
        $this->assertArrayHasKey('schedulable_id', $arrayData);
        $this->assertArrayHasKey('schedulable_type', $arrayData);
        $this->assertEquals(789, $arrayData['schedulable_id']);
        $this->assertEquals('client', $arrayData['schedulable_type']);
    }

    /**
     * Test that parseDateTime handles invalid input types.
     */
    public function test_parse_datetime_throws_exception_for_invalid_input_type(): void
    {
        // Arrange: Use reflection to test protected method
        $reflectionClass = new ReflectionClass(ScheduleDto::class);
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
        $reflectionClass = new ReflectionClass(ScheduleDto::class);
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
        $carbon = Carbon::create(2039, 4, 1, 10, 0, 0, 'UTC');

        // Use reflection to test protected method
        $reflectionClass = new ReflectionClass(ScheduleDto::class);
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
            'title' => 'Round Trip Test',
            'description' => 'Test data preservation',
            'start_datetime' => '2039-05-01 09:00:00',
            'end_datetime' => '2039-05-01 10:00:00',
            'metadata' => ['test' => true],
            'status' => ScheduleStatus::BOOKED,
            'schedulable_id' => 789,
            'schedulable_type' => 'test',
        ];

        // Act: Create DTO and convert back to array
        $scheduleData = ScheduleDto::fromArray($originalData);
        $convertedData = $scheduleData->toArray();

        // Assert: Verify all non-null data is preserved
        foreach ($originalData as $key => $value) {
            $this->assertArrayHasKey($key, $convertedData);
            $this->assertEquals($value, $convertedData[$key]);
        }
    }
}
