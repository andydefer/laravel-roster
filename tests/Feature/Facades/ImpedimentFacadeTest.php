<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Facades\Impediment as ImpedimentFacade;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Tests\TestCase;

final class ImpedimentFacadeTest extends TestCase
{
    use RefreshDatabase;

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

        // Create an availability for testing
        $this->availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);
    }

    public function test_facade_can_create_impediment(): void
    {
        $data = [
            'reason' => 'Out of office',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ];

        $impediment = ImpedimentFacade::for($this->model)->create($data);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Out of office', $impediment->reason);
        $this->assertSame($this->availability->id, $impediment->availability_id);
    }

    public function test_facade_can_find_impediment(): void
    {
        $impediment = Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Test',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        $found = ImpedimentFacade::for($this->model)->find($impediment->id);

        $this->assertInstanceOf(Impediment::class, $found);
        $this->assertSame($impediment->id, $found->id);
        $this->assertSame('Test', $found->reason);
    }

    public function test_facade_can_get_all_impediments(): void
    {
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 1',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 2',
            'start_datetime' => '2024-01-01 14:00:00',
            'end_datetime' => '2024-01-01 15:00:00',
        ]);

        /** @var Collection<int, \Roster\Models\Impediment> $impediments */
        $impediments = ImpedimentFacade::for($this->model)->all();

        $this->assertCount(2, $impediments);
        $this->assertSame('Impediment 1', $impediments[0]->reason);
        $this->assertSame('Impediment 2', $impediments[1]->reason);
    }


    public function test_facade_can_filter_impediments(): void
    {
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Morning meeting',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Afternoon training',
            'start_datetime' => '2024-01-01 14:00:00',
            'end_datetime' => '2024-01-01 15:00:00',
        ]);

        // Filter by date range
        $startDate = Carbon::parse('2024-01-01 12:00:00');
        $endDate = Carbon::parse('2024-01-01 16:00:00');

        $filtered = ImpedimentFacade::for($this->model)
            ->whereStartDate($startDate)
            ->whereEndDate($endDate)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertSame('Afternoon training', $filtered->first()->reason);
    }

    public function test_facade_can_check_if_time_slot_is_blocked(): void
    {
        // Create an impediment from 10:00-11:00
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Meeting',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        $blockedStart = Carbon::parse('2024-01-01 10:30:00');
        $blockedEnd = Carbon::parse('2024-01-01 10:45:00');

        $availableStart = Carbon::parse('2024-01-01 11:30:00');
        $availableEnd = Carbon::parse('2024-01-01 12:00:00');

        $this->assertTrue(
            ImpedimentFacade::for($this->model)->isTimeSlotBlocked($blockedStart, $blockedEnd)
        );

        $this->assertFalse(
            ImpedimentFacade::for($this->model)->isTimeSlotBlocked($availableStart, $availableEnd)
        );
    }

    public function test_facade_can_get_impediments_between_dates(): void
    {
        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 1',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
        ]);

        Impediment::create([
            'availability_id' => $this->availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Impediment 2',
            'start_datetime' => '2024-01-02 14:00:00',
            'end_datetime' => '2024-01-02 15:00:00',
        ]);

        $start = Carbon::parse('2024-01-01 00:00:00');
        $end = Carbon::parse('2024-01-01 23:59:59');

        $impediments = ImpedimentFacade::for($this->model)->between($start, $end);

        $this->assertCount(1, $impediments);
        $this->assertSame('Impediment 1', $impediments->first()->reason);
    }
}
