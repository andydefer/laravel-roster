<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\OverlappingImpedimentException;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Services\ImpedimentService;
use Tests\TestCase;

/**
 * Test suite for ImpedimentService functionality
 */
final class ImpedimentServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImpedimentService $impedimentService;

    private Model $testModel;

    private Availability $juneAvailability;

    private Availability $julyAvailability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestModel();
        $this->createAvailabilities();
        $this->initializeImpedimentService();
    }

    /**
     * Creates a test model instance for testing
     */
    private function createTestModel(): void
    {
        $this->testModel = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        $this->testModel->id = 1;
        $this->testModel->save();
    }

    /**
     * Creates test availability records
     */
    private function createAvailabilities(): void
    {
        $this->juneAvailability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $this->julyAvailability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['tuesday'],
            'start_date' => '2038-07-01',
            'end_date' => '2038-07-31',
        ]);
    }

    /**
     * Initializes the impediment service for testing
     */
    private function initializeImpedimentService(): void
    {
        $this->impedimentService = app(ImpedimentService::class);
        $this->impedimentService->for($this->testModel);
    }

    /**
     * Tests successful impediment creation
     */
    public function test_create_impediment_successfully(): void
    {
        $impedimentData = [
            'reason' => 'Out of office',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => ['notes' => 'Doctor appointment'],
        ];

        $impediment = $this->impedimentService->create($this->juneAvailability, $impedimentData);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Out of office', $impediment->reason);
        $this->assertSame($this->testModel->id, $impediment->schedulable_id);
        $this->assertSame(get_class($this->testModel), $impediment->schedulable_type);
        $this->assertSame($this->juneAvailability->id, $impediment->availability_id);

        $this->assertDatabaseHas('roster_impediments', [
            'reason' => 'Out of office',
            'availability_id' => $this->juneAvailability->id,
        ]);
    }

    /**
     * Tests that using availability from another model throws exception
     */
    public function test_create_impediment_with_wrong_availability_throws_exception(): void
    {
        $otherModel = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $otherModel->id = 2;
        $otherModel->save();

        $otherAvailability = Availability::create([
            'schedulable_id' => $otherModel->id,
            'schedulable_type' => get_class($otherModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $impedimentData = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided availability does not belong to this schedulable');

        $this->impedimentService->create($otherAvailability, $impedimentData);
    }

    /**
     * Tests that overlapping impediments are prevented
     */
    public function test_create_overlapping_impediment_throws_exception(): void
    {
        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'First impediment',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->expectException(OverlappingImpedimentException::class);

        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Overlapping impediment',
            'start_datetime' => '2038-06-07 10:30:00',
            'end_datetime' => '2038-06-07 11:30:00',
        ]);
    }

    /**
     * Tests that impediments cannot be created outside availability days
     */
    public function test_create_impediment_outside_availability_days_throws_exception(): void
    {
        $impedimentData = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-08 10:00:00',
            'end_datetime' => '2038-06-08 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->impedimentService->create($this->juneAvailability, $impedimentData);
    }

    /**
     * Tests that impediments cannot be created outside availability time range
     */
    public function test_create_impediment_outside_availability_time_range_throws_exception(): void
    {
        $impedimentData = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 08:00:00',
            'end_datetime' => '2038-06-07 08:30:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->impedimentService->create($this->juneAvailability, $impedimentData);
    }

    /**
     * Tests impediment retrieval with filters
     */
    public function test_between_with_filters(): void
    {
        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Meeting consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->impedimentService->setFilters(['reason' => 'Meeting']);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        $impediments = $this->impedimentService->between($start, $end);

        $this->assertCount(1, $impediments);
        $this->assertSame('Meeting consultation', $impediments[0]->reason);
    }

    /**
     * Tests JSON metadata conversion to array
     */
    public function test_create_impediment_with_json_metadata_converts_to_array(): void
    {
        $impedimentData = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => '{"notes": "test", "priority": "high"}',
        ];

        $impediment = $this->impedimentService->create($this->juneAvailability, $impedimentData);

        $this->assertIsArray($impediment->metadata);
        $this->assertSame('test', $impediment->metadata['notes']);
        $this->assertSame('high', $impediment->metadata['priority']);
    }

    /**
     * Tests successful impediment update
     */
    public function test_update_impediment_successfully(): void
    {
        $impediment = $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Original reason',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => ['notes' => 'Original notes'],
        ]);

        $updated = $this->impedimentService->update($impediment->id, [
            'reason' => 'Updated reason',
            'metadata' => ['notes' => 'Updated notes'],
        ]);

        $this->assertTrue($updated);

        $impediment->refresh();
        $this->assertSame('Updated reason', $impediment->reason);
        $this->assertSame(['notes' => 'Updated notes'], $impediment->metadata);
    }

    /**
     * Tests updating impediment with time change
     */
    public function test_update_impediment_with_time_change(): void
    {
        $impediment = $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $updated = $this->impedimentService->update($impediment->id, [
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $this->assertTrue($updated);

        $impediment->refresh();
        $this->assertSame('2038-06-07 14:00:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 15:00:00', $impediment->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Tests successful impediment deletion
     */
    public function test_delete_impediment(): void
    {
        $impediment = $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $deleted = $this->impedimentService->delete($impediment->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_impediments', ['id' => $impediment->id]);
    }

    /**
     * Tests that deleting non-existent impediment returns false
     */
    public function test_delete_non_existent_impediment_returns_false(): void
    {
        $result = $this->impedimentService->delete(999);
        $this->assertFalse($result);
    }

    /**
     * Tests impediment retrieval by ID
     */
    public function test_find_impediment_by_id(): void
    {
        $impediment = $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $found = $this->impedimentService->find($impediment->id);

        $this->assertInstanceOf(Impediment::class, $found);
        $this->assertSame($impediment->id, $found->id);
        $this->assertSame('Test', $found->reason);
    }

    /**
     * Tests that finding non-existent impediment returns null
     */
    public function test_find_non_existent_impediment_returns_null(): void
    {
        $result = $this->impedimentService->find(999);
        $this->assertNotInstanceOf(Impediment::class, $result);
    }

    /**
     * Tests time slot blocking functionality
     */
    public function test_is_time_slot_blocked(): void
    {
        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Meeting',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $blockedStart = Carbon::parse('2038-06-07 10:30:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:45:00');
        $this->assertTrue($this->impedimentService->isTimeSlotBlocked($blockedStart, $blockedEnd));

        $availableStart = Carbon::parse('2038-06-07 11:30:00');
        $availableEnd = Carbon::parse('2038-06-07 12:00:00');
        $this->assertFalse($this->impedimentService->isTimeSlotBlocked($availableStart, $availableEnd));

        $this->assertFalse($this->impedimentService->isTimeSlotBlocked($blockedStart, $blockedEnd, 'training'));
    }

    /**
     * Tests available time slot calculation with impediments
     */
    public function test_get_available_time_slots_with_impediments(): void
    {
        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Blocked',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 09:00:00');
        $end = Carbon::parse('2038-06-07 12:00:00');

        $slots = $this->impedimentService->getAvailableTimeSlots($start, $end);

        $this->assertCount(2, $slots);

        $this->assertSame('2038-06-07 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));

        $this->assertSame('2038-06-07 11:00:00', $slots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 12:00:00', $slots[1]['end']->format('Y-m-d H:i:s'));
    }

    /**
     * Tests impediment retrieval within a specific time period
     */
    public function test_between_method_returns_impediments_in_period(): void
    {
        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->impedimentService->create($this->juneAvailability, [
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $this->impedimentService->create($this->julyAvailability, [
            'reason' => 'Impediment 3',
            'start_datetime' => '2038-07-06 10:00:00',
            'end_datetime' => '2038-07-06 11:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        /** @var Collection<Impediment> $impediments */
        $impediments = $this->impedimentService->between($start, $end);

        $this->assertCount(2, $impediments);
        $this->assertSame('Impediment 1', $impediments[0]->reason);
        $this->assertSame('Impediment 2', $impediments[1]->reason);
    }

    /**
     * Tests that old deprecated create method throws exception
     */
    public function test_old_create_method_is_deprecated(): void
    {
        $impedimentData = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        $this->impedimentService->create($impedimentData);
    }
}
