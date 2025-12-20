<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Tests\TestCase;

final class ModelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $this->model->id = 1;
        $this->model->save();
    }

    public function test_availability_relationships(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $this->assertInstanceOf(get_class($this->model), $availability->schedulable);
        $this->assertSame($this->model->id, $availability->schedulable->id);

        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        $this->assertCount(1, $availability->schedules);
        $this->assertSame($schedule->id, $availability->schedules->first()->id);

        $impediment = Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Test',
            'start_datetime' => '2024-01-01 11:00:00',
            'end_datetime' => '2024-01-01 12:00:00',
        ]);

        $this->assertCount(1, $availability->impediments);
        $this->assertSame($impediment->id, $availability->impediments->first()->id);
    }

    public function test_schedule_relationships_and_attributes(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        $this->assertInstanceOf(Availability::class, $schedule->availability);
        $this->assertSame($availability->id, $schedule->availability->id);

        $this->assertSame('consultation', $schedule->type);

        $schedulable = $schedule->schedulable();
        $this->assertNotNull($schedulable);
    }

    public function test_impediment_relationships(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $impediment = Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Test',
            'start_datetime' => '2024-01-01 11:00:00',
            'end_datetime' => '2024-01-01 12:00:00',
        ]);

        $this->assertInstanceOf(Availability::class, $impediment->availability);
        $this->assertSame($availability->id, $impediment->availability->id);

        $this->assertInstanceOf(get_class($this->model), $impediment->schedulable);
        $this->assertSame($this->model->id, $impediment->schedulable->id);
    }

    public function test_availability_methods(): void
    {
        $availability = new Availability([
            'days' => ['monday', 'tuesday'],
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('17:00:00'),
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ]);

        $validStart = Carbon::parse('2024-01-01 10:00:00');
        $validEnd = Carbon::parse('2024-01-01 11:00:00');
        $this->assertTrue($availability->isAvailableForSchedule($validStart, $validEnd));

        $wrongDayStart = Carbon::parse('2024-01-03 10:00:00');
        $wrongDayEnd = Carbon::parse('2024-01-03 11:00:00');
        $this->assertFalse($availability->isAvailableForSchedule($wrongDayStart, $wrongDayEnd));

        $earlyStart = Carbon::parse('2024-01-01 08:00:00');
        $earlyEnd = Carbon::parse('2024-01-01 08:30:00');
        $this->assertFalse($availability->isAvailableForSchedule($earlyStart, $earlyEnd));

        $outsideDateStart = Carbon::parse('2024-02-01 10:00:00');
        $outsideDateEnd = Carbon::parse('2024-02-01 11:00:00');
        $this->assertFalse($availability->isAvailableForSchedule($outsideDateStart, $outsideDateEnd));
    }

    public function test_schedule_methods(): void
    {
        $schedule = new Schedule([
            'start_datetime' => Carbon::parse('2024-01-01 10:00:00'),
            'end_datetime' => Carbon::parse('2024-01-01 11:00:00'),
        ]);

        $overlapStart = Carbon::parse('2024-01-01 10:30:00');
        $overlapEnd = Carbon::parse('2024-01-01 11:30:00');
        $this->assertTrue($schedule->overlapsWith($overlapStart, $overlapEnd));

        $nonOverlapStart = Carbon::parse('2024-01-01 11:30:00');
        $nonOverlapEnd = Carbon::parse('2024-01-01 12:00:00');
        $this->assertFalse($schedule->overlapsWith($nonOverlapStart, $nonOverlapEnd));

        $this->assertEqualsWithDelta(60.0, $schedule->getDurationMinutesAttribute(), PHP_FLOAT_EPSILON);
        $this->assertEquals(60, $schedule->getDurationMinutesAttribute());

        $now = Carbon::now();

        $activeSchedule = new Schedule([
            'start_datetime' => $now->copy()->subHour(),
            'end_datetime' => $now->copy()->addHour(),
        ]);
        $this->assertTrue($activeSchedule->isActive());
        $this->assertFalse($activeSchedule->isUpcoming());
        $this->assertFalse($activeSchedule->isPast());

        $upcomingSchedule = new Schedule([
            'start_datetime' => $now->copy()->addDay(),
            'end_datetime' => $now->copy()->addDay()->addHour(),
        ]);
        $this->assertFalse($upcomingSchedule->isActive());
        $this->assertTrue($upcomingSchedule->isUpcoming());
        $this->assertFalse($upcomingSchedule->isPast());
    }

    public function test_impediment_methods(): void
    {
        $impediment = new Impediment([
            'start_datetime' => Carbon::parse('2024-01-01 10:00:00'),
            'end_datetime' => Carbon::parse('2024-01-01 11:00:00'),
        ]);

        $overlapStart = Carbon::parse('2024-01-01 10:30:00');
        $overlapEnd = Carbon::parse('2024-01-01 11:30:00');
        $this->assertTrue($impediment->overlapsWith($overlapStart, $overlapEnd));

        $nonOverlapStart = Carbon::parse('2024-01-01 11:30:00');
        $nonOverlapEnd = Carbon::parse('2024-01-01 12:00:00');
        $this->assertFalse($impediment->overlapsWith($nonOverlapStart, $nonOverlapEnd));

        $this->assertEqualsWithDelta(60.0, $impediment->getDurationMinutesAttribute(), PHP_FLOAT_EPSILON);
        $this->assertEquals(60, $impediment->getDurationMinutesAttribute());

        $now = Carbon::now();

        $activeImpediment = new Impediment([
            'start_datetime' => $now->copy()->subHour(),
            'end_datetime' => $now->copy()->addHour(),
        ]);
        $this->assertTrue($activeImpediment->isActive());
        $this->assertFalse($activeImpediment->isUpcoming());
        $this->assertFalse($activeImpediment->isPast());

        $upcomingImpediment = new Impediment([
            'start_datetime' => $now->copy()->addDay(),
            'end_datetime' => $now->copy()->addDay()->addHour(),
        ]);
        $this->assertFalse($upcomingImpediment->isActive());
        $this->assertTrue($upcomingImpediment->isUpcoming());
        $this->assertFalse($upcomingImpediment->isPast());
    }
}
