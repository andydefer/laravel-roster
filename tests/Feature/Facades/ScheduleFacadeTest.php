<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Facades\Schedule as ScheduleFacade;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Tests\TestCase;

final class ScheduleFacadeTest extends TestCase
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

    public function test_facade_can_create_schedule(): void
    {
        $data = [
            'title' => 'Test Consultation',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ];

        $schedule = ScheduleFacade::for($this->model)->create($data);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Test Consultation', $schedule->title);
        $this->assertSame($this->availability->id, $schedule->availability_id);
    }

    public function test_facade_can_find_schedule(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        $found = ScheduleFacade::for($this->model)->find($schedule->id);

        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertSame($schedule->id, $found->id);
    }

    public function test_facade_can_get_all_schedules(): void
    {
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 1',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 2',
            'start_datetime' => '2024-01-01 14:00:00',
            'end_datetime' => '2024-01-01 15:00:00',
            'status' => 'booked',
        ]);

        /** @var Collection<int, \Roster\Models\Schedule> $schedules */
        $schedules = ScheduleFacade::for($this->model)->all();

        $this->assertCount(2, $schedules);
        $this->assertSame('Schedule 1', $schedules[0]->title);
        $this->assertSame('Schedule 2', $schedules[1]->title);
    }


    public function test_facade_can_filter_schedules(): void
    {
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Available Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Booked Schedule',
            'start_datetime' => '2024-01-01 14:00:00',
            'end_datetime' => '2024-01-01 15:00:00',
            'status' => 'booked',
        ]);

        // Filter by status
        $availableSchedules = ScheduleFacade::for($this->model)
            ->whereStatus('available')
            ->get();

        $this->assertCount(1, $availableSchedules);
        $this->assertSame('Available Schedule', $availableSchedules->first()->title);

        // Filter by date range
        $startDate = Carbon::parse('2024-01-01 09:00:00');
        $endDate = Carbon::parse('2024-01-01 12:00:00');

        $dateFiltered = ScheduleFacade::for($this->model)
            ->whereStartDate($startDate)
            ->whereEndDate($endDate)
            ->get();

        $this->assertCount(1, $dateFiltered);
        $this->assertSame('Available Schedule', $dateFiltered->first()->title);
    }

    public function test_facade_can_check_time_slot_availability(): void
    {
        // Block 10:00-11:00 with a schedule
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Blocked',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'booked',
        ]);

        $availableStart = Carbon::parse('2024-01-01 09:00:00');
        $availableEnd = Carbon::parse('2024-01-01 09:30:00');

        $blockedStart = Carbon::parse('2024-01-01 10:30:00');
        $blockedEnd = Carbon::parse('2024-01-01 11:00:00');

        $this->assertTrue(
            ScheduleFacade::for($this->model)->isTimeSlotAvailable($availableStart, $availableEnd)
        );

        $this->assertFalse(
            ScheduleFacade::for($this->model)->isTimeSlotAvailable($blockedStart, $blockedEnd)
        );
    }

    public function test_facade_can_find_next_available_slot(): void
    {
        // Block 10:00-11:00
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Booked',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'booked',
        ]);

        $nextSlot = ScheduleFacade::for($this->model)->findNextAvailableSlot(60);

        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);
        $this->assertSame('2024-01-01 09:00:00', $nextSlot['start']->format('Y-m-d H:i:s'));
    }
}
