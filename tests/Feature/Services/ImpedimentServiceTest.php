<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\OverlappingImpedimentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Services\ImpedimentService;
use Tests\TestCase;

final class ImpedimentServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImpedimentService $impedimentService;

    private Model $model;

    private Availability $availability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $this->model->id = 1;
        $this->model->save();

        $this->availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $this->impedimentService = app(ImpedimentService::class);
        $this->impedimentService->for($this->model);
    }

    public function test_create_impediment_successfully(): void
    {
        $data = [
            'reason' => 'Out of office',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => ['notes' => 'Doctor appointment'],
        ];

        $impediment = $this->impedimentService->create($data);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Out of office', $impediment->reason);
        $this->assertSame($this->model->id, $impediment->schedulable_id);
        $this->assertSame(get_class($this->model), $impediment->schedulable_type);
        $this->assertSame($this->availability->id, $impediment->availability_id);
        $this->assertDatabaseHas('impediments', [
            'reason' => 'Out of office',
            'availability_id' => $this->availability->id,
        ]);
    }

    public function test_create_impediment_with_no_matching_availability_throws_exception(): void
    {
        $data = [
            'reason' => 'Test',
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->impedimentService->create($data);
    }

    public function test_create_overlapping_impediment_throws_exception(): void
    {
        $this->impedimentService->create([
            'reason' => 'First impediment',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->expectException(OverlappingImpedimentException::class);

        $this->impedimentService->create([
            'reason' => 'Overlapping impediment',
            'start_datetime' => '2038-06-07 10:30:00',
            'end_datetime' => '2038-06-07 11:30:00',
        ]);
    }

    public function test_update_impediment_successfully(): void
    {
        $impediment = Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Original reason',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
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

    public function test_update_impediment_with_time_change(): void
    {
        $impediment = Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
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

    public function test_delete_impediment(): void
    {
        $impediment = Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $deleted = $this->impedimentService->delete($impediment->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('impediments', ['id' => $impediment->id]);
    }

    public function test_find_impediment_by_id(): void
    {
        $impediment = Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $found = $this->impedimentService->find($impediment->id);

        $this->assertInstanceOf(Impediment::class, $found);
        $this->assertSame($impediment->id, $found->id);
        $this->assertSame('Test', $found->reason);
    }

    public function test_is_time_slot_blocked(): void
    {
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
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

    public function test_get_available_time_slots_with_impediments(): void
    {
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
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

    public function test_between_method_returns_impediments_in_period(): void
    {
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 3',
            'start_datetime' => '2038-06-08 10:00:00',
            'end_datetime' => '2038-06-08 11:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        /** @var Collection<Impediment> $impediments */
        $impediments = $this->impedimentService->between($start, $end);

        $this->assertCount(2, $impediments);
        $this->assertSame('Impediment 1', $impediments[0]->reason);
        $this->assertSame('Impediment 2', $impediments[1]->reason);
    }
}
