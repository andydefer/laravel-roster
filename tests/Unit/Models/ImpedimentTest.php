<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Models\Availability;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Unit tests for the Impediment model.
 *
 * Validates model behavior, attribute casting, relationships, and temporal logic.
 */
final class ImpedimentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test schedulable instance.
     */
    private Model $testSchedulable;

    /**
     * Test availability instance.
     */
    private Availability $availability;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->testSchedulable = TestSchedulable::create();
        $this->availability = $this->createAvailability();
    }

    /**
     * Create an availability instance for testing.
     *
     * @return Availability Created availability instance
     */
    private function createAvailability(): Availability
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
     * Create an impediment model instance for testing model methods.
     *
     * @param array<string, Carbon> $attributes Additional attributes to set
     * @return ImpedimentModel Created impediment instance
     */
    private function createImpedimentModelInstance(array $attributes = []): ImpedimentModel
    {
        $impediment = impediment_for($this->availability)->create([
            'reason' => 'Test Impediment',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
            'metadata' => ['note' => 'Test'],
        ]);

        foreach ($attributes as $key => $value) {
            $impediment->$key = $value;
        }

        return $impediment;
    }

    /**
     * Test that impediment can be created with valid attributes.
     */
    public function test_impediment_can_be_created_with_valid_attributes(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Vacation',
            'start_datetime' => '2038-07-15 09:00:00',
            'end_datetime' => '2038-07-15 17:00:00',
            'metadata' => ['type' => 'annual_leave'],
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertInstanceOf(ImpedimentModel::class, $impediment);
        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
        $this->assertSame(TestSchedulable::class, $impediment->schedulable_type);
        $this->assertSame($this->availability->id, $impediment->availability_id);
        $this->assertSame('Vacation', $impediment->reason);
        $this->assertSame(['type' => 'annual_leave'], $impediment->metadata);
    }

    /**
     * Test that datetime attributes are properly cast to Carbon instances.
     */
    public function test_datetime_attributes_are_properly_cast(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Meeting',
            'start_datetime' => '2038-07-01 14:30:00',
            'end_datetime' => '2038-07-01 16:45:00',
            'metadata' => null,
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertSame('2038-07-01 14:30:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2038-07-01 16:45:00', $impediment->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test that metadata attribute is properly cast to array.
     */
    public function test_metadata_is_properly_cast_to_array(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Emergency',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
            'metadata' => ['type' => 'emergency', 'priority' => 'high'],
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertIsArray($impediment->metadata);
        $this->assertSame(['type' => 'emergency', 'priority' => 'high'], $impediment->metadata);
    }

    /**
     * Test that metadata attribute handles JSON string input from database.
     */
    public function test_metadata_handles_json_string_input(): void
    {
        // Arrange
        $impediment = $this->createImpedimentModelInstance();

        // Act
        $impediment->setRawAttributes(array_merge(
            $impediment->getAttributes(),
            ['metadata' => '{"note":"Test note","category":"technical"}']
        ));

        // Assert
        $this->assertIsArray($impediment->metadata);
        $this->assertSame(['note' => 'Test note', 'category' => 'technical'], $impediment->metadata);
    }

    /**
     * Test that metadata attribute returns empty array when null.
     */
    public function test_metadata_returns_empty_array_when_null(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Test',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
            'metadata' => null,
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertIsArray($impediment->metadata);
        $this->assertEmpty($impediment->metadata);
    }

    /**
     * Test that availability relationship returns the correct model.
     */
    public function test_availability_relationship_returns_correct_model(): void
    {
        // Arrange
        $impediment = $this->createImpedimentModelInstance();

        // Act
        $availability = $impediment->availability;

        // Assert
        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame($this->availability->id, $availability->id);
    }

    /**
     * Test that schedulable relationship returns the correct model.
     */
    public function test_schedulable_relationship_returns_correct_model(): void
    {
        // Arrange
        $impediment = $this->createImpedimentModelInstance();

        // Act
        $schedulable = $impediment->schedulable;

        // Assert
        $this->assertInstanceOf(TestSchedulable::class, $schedulable);
        $this->assertSame($this->testSchedulable->id, $schedulable->id);
    }

    /**
     * Test that overlaps_with returns true when impediment overlaps with given period.
     */
    public function test_overlaps_with_returns_true_when_impediment_overlaps(): void
    {
        // Arrange
        $impediment = $this->createImpedimentModelInstance();
        $overlapStart = Carbon::parse('2038-07-01 11:00:00');
        $overlapEnd = Carbon::parse('2038-07-01 13:00:00');

        // Act
        $overlaps = $impediment->overlapsWith($overlapStart, $overlapEnd);

        // Assert
        $this->assertTrue($overlaps);
    }

    /**
     * Test that overlaps_with returns false when impediment does not overlap with given period.
     */
    public function test_overlaps_with_returns_false_when_impediment_does_not_overlap(): void
    {
        // Arrange
        $impediment = $this->createImpedimentModelInstance();
        $beforeStart = Carbon::parse('2038-07-01 08:00:00');
        $beforeEnd = Carbon::parse('2038-07-01 09:00:00');
        $afterStart = Carbon::parse('2038-07-01 13:00:00');
        $afterEnd = Carbon::parse('2038-07-01 14:00:00');

        // Act & Assert
        $this->assertFalse($impediment->overlapsWith($beforeStart, $beforeEnd));
        $this->assertFalse($impediment->overlapsWith($afterStart, $afterEnd));
    }

    /**
     * Test that duration_minutes attribute returns correct duration.
     */
    public function test_duration_minutes_attribute_returns_correct_duration(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Long Meeting',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 12:30:00',
            'metadata' => null,
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertEqualsWithDelta(150.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
    }

    /**
     * Test that is_active returns true for currently active impediment.
     */
    public function test_is_active_returns_true_for_currently_active_impediment(): void
    {
        // Arrange
        $testTime = Carbon::parse('2038-07-01 11:00:00');
        $startTime = $testTime->copy()->subHour();
        $endTime = $testTime->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        // Act
        Carbon::setTestNow($testTime);
        $isActive = $impediment->isActive();

        // Assert
        $this->assertTrue($isActive);
        Carbon::setTestNow();
    }

    /**
     * Test that is_active returns false for past impediment.
     */
    public function test_is_active_returns_false_for_past_impediment(): void
    {
        // Arrange
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');
        $currentTime = $pastEnd->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Act
        Carbon::setTestNow($currentTime);
        $isActive = $impediment->isActive();

        // Assert
        $this->assertFalse($isActive);
        Carbon::setTestNow();
    }

    /**
     * Test that is_active returns false for future impediment.
     */
    public function test_is_active_returns_false_for_future_impediment(): void
    {
        // Arrange
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');
        $currentTime = $futureStart->copy()->subHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Act
        Carbon::setTestNow($currentTime);
        $isActive = $impediment->isActive();

        // Assert
        $this->assertFalse($isActive);
        Carbon::setTestNow();
    }

    /**
     * Test that is_upcoming returns true for future impediment.
     */
    public function test_is_upcoming_returns_true_for_future_impediment(): void
    {
        // Arrange
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');
        $currentTime = $futureStart->copy()->subHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Act
        Carbon::setTestNow($currentTime);
        $isUpcoming = $impediment->isUpcoming();

        // Assert
        $this->assertTrue($isUpcoming);
        Carbon::setTestNow();
    }

    /**
     * Test that is_upcoming returns false for past impediment.
     */
    public function test_is_upcoming_returns_false_for_past_impediment(): void
    {
        // Arrange
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');
        $currentTime = $pastEnd->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Act
        Carbon::setTestNow($currentTime);
        $isUpcoming = $impediment->isUpcoming();

        // Assert
        $this->assertFalse($isUpcoming);
        Carbon::setTestNow();
    }

    /**
     * Test that is_upcoming returns false for active impediment.
     */
    public function test_is_upcoming_returns_false_for_active_impediment(): void
    {
        // Arrange
        $testTime = Carbon::parse('2038-07-01 11:00:00');
        $startTime = $testTime->copy()->subHour();
        $endTime = $testTime->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        // Act
        Carbon::setTestNow($testTime);
        $isUpcoming = $impediment->isUpcoming();

        // Assert
        $this->assertFalse($isUpcoming);
        Carbon::setTestNow();
    }

    /**
     * Test that is_past returns true for past impediment.
     */
    public function test_is_past_returns_true_for_past_impediment(): void
    {
        // Arrange
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');
        $currentTime = $pastEnd->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Act
        Carbon::setTestNow($currentTime);
        $isPast = $impediment->isPast();

        // Assert
        $this->assertTrue($isPast);
        Carbon::setTestNow();
    }

    /**
     * Test that is_past returns false for future impediment.
     */
    public function test_is_past_returns_false_for_future_impediment(): void
    {
        // Arrange
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');
        $currentTime = $futureStart->copy()->subHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Act
        Carbon::setTestNow($currentTime);
        $isPast = $impediment->isPast();

        // Assert
        $this->assertFalse($isPast);
        Carbon::setTestNow();
    }

    /**
     * Test that is_past returns false for active impediment.
     */
    public function test_is_past_returns_false_for_active_impediment(): void
    {
        // Arrange
        $testTime = Carbon::parse('2038-07-01 11:00:00');
        $startTime = $testTime->copy()->subHour();
        $endTime = $testTime->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
        ]);

        // Act
        Carbon::setTestNow($testTime);
        $isPast = $impediment->isPast();

        // Assert
        $this->assertFalse($isPast);
        Carbon::setTestNow();
    }

    /**
     * Test that impediment duration is calculated correctly.
     */
    public function test_impediment_duration_is_calculated_correctly(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Training',
            'start_datetime' => '2038-07-01 09:00:00',
            'end_datetime' => '2038-07-01 10:30:00',
            'metadata' => null,
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertEqualsWithDelta(90.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
    }

    /**
     * Test that overlaps_with handles edge cases (touching but not overlapping periods).
     */
    public function test_overlaps_with_handles_edge_cases(): void
    {
        // Arrange
        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => Carbon::parse('2038-07-01 10:00:00'),
            'end_datetime' => Carbon::parse('2038-07-01 12:00:00'),
        ]);

        $edgeStart = Carbon::parse('2038-07-01 12:00:00');
        $edgeEnd = Carbon::parse('2038-07-01 13:00:00');
        $edgeStart2 = Carbon::parse('2038-07-01 09:00:00');
        $edgeEnd2 = Carbon::parse('2038-07-01 10:00:00');

        // Act & Assert
        $this->assertFalse($impediment->overlapsWith($edgeStart, $edgeEnd));
        $this->assertFalse($impediment->overlapsWith($edgeStart2, $edgeEnd2));
    }

    /**
     * Test that duration_minutes is calculated correctly for exact hours.
     */
    public function test_duration_minutes_for_exact_hours(): void
    {
        // Arrange
        $creationData = [
            'reason' => 'Full Day',
            'start_datetime' => '2038-07-01 09:00:00',
            'end_datetime' => '2038-07-01 17:00:00',
            'metadata' => null,
        ];

        // Act
        $impediment = impediment_for($this->availability)->create($creationData);

        // Assert
        $this->assertEqualsWithDelta(480.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
    }
}
