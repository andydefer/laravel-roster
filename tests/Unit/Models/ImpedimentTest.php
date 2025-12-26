<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Roster\Facades\Impediment;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Models\Availability;
use Tests\Support\TestSchedulable;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

/**
 * Unit tests for the Impediment model.
 */
final class ImpedimentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test schedulable instance.
     */
    private TestSchedulable $schedulable;

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

        $this->schedulable = TestSchedulable::create();
        $this->availability = $this->createAvailability();
    }

    /**
     * Helper method to create an availability instance.
     */
    private function createAvailability(): Availability
    {
        return \Roster\Facades\Availability::for($this->schedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'validity_start' => '2038-07-01 00:00:00',
                'validity_end' => '2038-07-31 23:59:59',
            ]);
    }

    /**
     * Helper method to create an impediment instance for testing model methods.
     */
    private function createImpedimentModelInstance(array $attributes = []): ImpedimentModel
    {
        // Create via facade first to get valid instance
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Test Impediment',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 12:00:00',
                'metadata' => ['note' => 'Test'],
            ]);

        // Then update with test attributes if needed
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $impediment->$key = $value;
            }
        }

        return $impediment;
    }

    /**
     * Test that impediment can be created with valid attributes.
     */
    public function test_impediment_can_be_created_with_valid_attributes(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Vacation',
                'start_datetime' => '2038-07-15 09:00:00', // Wednesday
                'end_datetime' => '2038-07-15 17:00:00',
                'metadata' => ['type' => 'annual_leave'],
            ]);

        $this->assertInstanceOf(ImpedimentModel::class, $impediment);
        $this->assertSame($this->schedulable->id, $impediment->schedulable_id);
        $this->assertSame(TestSchedulable::class, $impediment->schedulable_type);
        $this->assertSame($this->availability->id, $impediment->availability_id);
        $this->assertEquals('Vacation', $impediment->reason);
        $this->assertEquals(['type' => 'annual_leave'], $impediment->metadata);
    }

    /**
     * Test that start_datetime and end_datetime are properly cast.
     */
    public function test_datetime_attributes_are_properly_cast(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Meeting',
                'start_datetime' => '2038-07-01 14:30:00',
                'end_datetime' => '2038-07-01 16:45:00',
                'metadata' => null,
            ]);

        $this->assertEquals('2038-07-01 14:30:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals('2038-07-01 16:45:00', $impediment->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test that metadata attribute is properly cast to array.
     */
    public function test_metadata_is_properly_cast_to_array(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Emergency',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
                'metadata' => ['type' => 'emergency', 'priority' => 'high'],
            ]);

        $this->assertIsArray($impediment->metadata);
        $this->assertEquals(['type' => 'emergency', 'priority' => 'high'], $impediment->metadata);
    }

    /**
     * Test that metadata attribute handles JSON string input.
     */
    public function test_metadata_handles_json_string_input(): void
    {
        $impediment = $this->createImpedimentModelInstance();

        // Simulate direct database access with JSON string
        $impediment->setRawAttributes(array_merge(
            $impediment->getAttributes(),
            ['metadata' => '{"note":"Test note","category":"technical"}']
        ));

        $this->assertIsArray($impediment->metadata);
        $this->assertEquals(['note' => 'Test note', 'category' => 'technical'], $impediment->metadata);
    }

    /**
     * Test that metadata attribute returns empty array when null.
     */
    public function test_metadata_returns_empty_array_when_null(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Test',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 11:00:00',
                'metadata' => null,
            ]);

        $this->assertIsArray($impediment->metadata);
        $this->assertEmpty($impediment->metadata);
    }

    /**
     * Test that availability relationship works correctly.
     */
    public function test_availability_relationship_returns_correct_model(): void
    {
        $impediment = $this->createImpedimentModelInstance();

        $this->assertInstanceOf(Availability::class, $impediment->availability);
        $this->assertEquals($this->availability->id, $impediment->availability->id);
    }

    /**
     * Test that schedulable relationship works correctly.
     */
    public function test_schedulable_relationship_returns_correct_model(): void
    {
        $impediment = $this->createImpedimentModelInstance();

        $this->assertInstanceOf(TestSchedulable::class, $impediment->schedulable);
        $this->assertEquals($this->schedulable->id, $impediment->schedulable->id);
    }

    /**
     * Test that overlaps_with returns true when impediment overlaps with period.
     */
    public function test_overlaps_with_returns_true_when_impediment_overlaps(): void
    {
        $impediment = $this->createImpedimentModelInstance();

        // Overlapping period (starts during impediment)
        $overlapStart = Carbon::parse('2038-07-01 11:00:00');
        $overlapEnd = Carbon::parse('2038-07-01 13:00:00');

        $this->assertTrue($impediment->overlapsWith($overlapStart, $overlapEnd));
    }

    /**
     * Test that overlaps_with returns false when impediment does not overlap.
     */
    public function test_overlaps_with_returns_false_when_impediment_does_not_overlap(): void
    {
        $impediment = $this->createImpedimentModelInstance();

        // Non-overlapping period (before impediment)
        $beforeStart = Carbon::parse('2038-07-01 08:00:00');
        $beforeEnd = Carbon::parse('2038-07-01 09:00:00');

        // Non-overlapping period (after impediment)
        $afterStart = Carbon::parse('2038-07-01 13:00:00');
        $afterEnd = Carbon::parse('2038-07-01 14:00:00');

        $this->assertFalse($impediment->overlapsWith($beforeStart, $beforeEnd));
        $this->assertFalse($impediment->overlapsWith($afterStart, $afterEnd));
    }

    /**
     * Test that duration_minutes attribute returns correct duration.
     */
    public function test_duration_minutes_attribute_returns_correct_duration(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Long Meeting',
                'start_datetime' => '2038-07-01 10:00:00',
                'end_datetime' => '2038-07-01 12:30:00', // 2.5 hours = 150 minutes
                'metadata' => null,
            ]);

        $this->assertEquals(150.0, $impediment->duration_minutes);
    }

    /**
     * Test that is_active returns true for currently active impediment (using future date).
     */
    public function test_is_active_returns_true_for_currently_active_impediment(): void
    {
        // Create impediment that would be active at a specific time
        $testTime = Carbon::parse('2038-07-01 11:00:00');
        $start = $testTime->copy()->subHour();
        $end = $testTime->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        // Test with the specific time
        Carbon::setTestNow($testTime);
        $this->assertTrue($impediment->isActive());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_active returns false for past impediment.
     */
    public function test_is_active_returns_false_for_past_impediment(): void
    {
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Set current time to after the impediment
        Carbon::setTestNow($pastEnd->copy()->addHour());
        $this->assertFalse($impediment->isActive());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_active returns false for future impediment.
     */
    public function test_is_active_returns_false_for_future_impediment(): void
    {
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Set current time to before the impediment
        Carbon::setTestNow($futureStart->copy()->subHour());
        $this->assertFalse($impediment->isActive());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_upcoming returns true for future impediment.
     */
    public function test_is_upcoming_returns_true_for_future_impediment(): void
    {
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Set current time to before the impediment
        Carbon::setTestNow($futureStart->copy()->subHour());
        $this->assertTrue($impediment->isUpcoming());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_upcoming returns false for past impediment.
     */
    public function test_is_upcoming_returns_false_for_past_impediment(): void
    {
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Set current time to after the impediment
        Carbon::setTestNow($pastEnd->copy()->addHour());
        $this->assertFalse($impediment->isUpcoming());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_upcoming returns false for active impediment.
     */
    public function test_is_upcoming_returns_false_for_active_impediment(): void
    {
        $testTime = Carbon::parse('2038-07-01 11:00:00');
        $start = $testTime->copy()->subHour();
        $end = $testTime->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        Carbon::setTestNow($testTime);
        $this->assertFalse($impediment->isUpcoming());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_past returns true for past impediment.
     */
    public function test_is_past_returns_true_for_past_impediment(): void
    {
        $pastStart = Carbon::parse('2038-07-01 10:00:00');
        $pastEnd = Carbon::parse('2038-07-01 11:00:00');

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $pastStart,
            'end_datetime' => $pastEnd,
        ]);

        // Set current time to after the impediment
        Carbon::setTestNow($pastEnd->copy()->addHour());
        $this->assertTrue($impediment->isPast());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_past returns false for future impediment.
     */
    public function test_is_past_returns_false_for_future_impediment(): void
    {
        $futureStart = Carbon::parse('2038-07-15 10:00:00');
        $futureEnd = Carbon::parse('2038-07-15 12:00:00');

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $futureStart,
            'end_datetime' => $futureEnd,
        ]);

        // Set current time to before the impediment
        Carbon::setTestNow($futureStart->copy()->subHour());
        $this->assertFalse($impediment->isPast());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that is_past returns false for active impediment.
     */
    public function test_is_past_returns_false_for_active_impediment(): void
    {
        $testTime = Carbon::parse('2038-07-01 11:00:00');
        $start = $testTime->copy()->subHour();
        $end = $testTime->copy()->addHour();

        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        Carbon::setTestNow($testTime);
        $this->assertFalse($impediment->isPast());
        Carbon::setTestNow(); // Reset
    }

    /**
     * Test that impediment duration is calculated correctly.
     */
    public function test_impediment_duration_is_calculated_correctly(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Training',
                'start_datetime' => '2038-07-01 09:00:00',
                'end_datetime' => '2038-07-01 10:30:00', // 1.5 hours = 90 minutes
                'metadata' => null,
            ]);

        $this->assertEquals(90.0, $impediment->duration_minutes);
    }

    /**
     * Test that impediment correctly handles edge case overlaps.
     */
    public function test_overlaps_with_handles_edge_cases(): void
    {
        $impediment = $this->createImpedimentModelInstance([
            'start_datetime' => Carbon::parse('2038-07-01 10:00:00'),
            'end_datetime' => Carbon::parse('2038-07-01 12:00:00'),
        ]);

        // Exactly at start time (touches but doesn't overlap)
        $edgeStart = Carbon::parse('2038-07-01 12:00:00');
        $edgeEnd = Carbon::parse('2038-07-01 13:00:00');

        // Exactly at end time (touches but doesn't overlap)
        $edgeStart2 = Carbon::parse('2038-07-01 09:00:00');
        $edgeEnd2 = Carbon::parse('2038-07-01 10:00:00');

        $this->assertFalse($impediment->overlapsWith($edgeStart, $edgeEnd));
        $this->assertFalse($impediment->overlapsWith($edgeStart2, $edgeEnd2));
    }

    /**
     * Test that impediment duration is calculated correctly for exact duration.
     */
    public function test_duration_minutes_for_exact_hours(): void
    {
        $impediment = Impediment::for($this->schedulable)
            ->owner($this->availability)
            ->create([
                'reason' => 'Full Day',
                'start_datetime' => '2038-07-01 09:00:00',
                'end_datetime' => '2038-07-01 17:00:00', // 8 hours = 480 minutes
                'metadata' => null,
            ]);

        $this->assertEquals(480.0, $impediment->duration_minutes);
    }
}
