<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\ScheduleService;
use Tests\TestCase;

final class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleService $scheduleService;

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

        // Create an availability
        $this->availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $this->scheduleService = app(ScheduleService::class);
        $this->scheduleService->for($this->model);
    }

    public function test_create_schedule_successfully(): void
    {
        // Utiliser une date future (2038-06-07 est un lundi)
        $data = [
            'title' => 'Test Consultation',
            'description' => 'Test description',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
            'metadata' => ['notes' => 'Test notes'],
        ];

        $schedule = $this->scheduleService->create($data);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Test Consultation', $schedule->title);
        $this->assertSame($this->availability->id, $schedule->availability_id);
        $this->assertDatabaseHas('schedules', [
            'title' => 'Test Consultation',
            'availability_id' => $this->availability->id,
        ]);
    }

    public function test_create_schedule_with_type_filters_availability(): void
    {
        // Create another availability with different type
        $trainingAvailability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'training',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $data = [
            'title' => 'Training Session',
            'start_datetime' => '2038-06-07 14:00:00', // Lundi
            'end_datetime' => '2038-06-07 15:00:00',
            'type' => 'training',
        ];

        $schedule = $this->scheduleService->create($data);

        $this->assertSame($trainingAvailability->id, $schedule->availability_id);
        $this->assertSame('training', $schedule->type);
    }

    public function test_create_schedule_with_no_matching_availability_throws_exception(): void
    {
        $data = [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-08 10:00:00', // Mardi (8 juin 2038), mais availability est seulement lundi
            'end_datetime' => '2038-06-08 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        $this->scheduleService->create($data);
    }

    public function test_update_schedule_successfully(): void
    {
        // Créer un schedule avec une date future pour l'update
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Original Title',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $updated = $this->scheduleService->update($schedule->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
    }

    public function test_delete_schedule(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $deleted = $this->scheduleService->delete($schedule->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_find_schedule_by_id(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $found = $this->scheduleService->find($schedule->id);

        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Test Schedule', $found->title);
    }

    public function test_is_time_slot_available(): void
    {
        // Create a schedule to block part of the availability
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Blocked Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        // Test available slot (before blocked time)
        $availableStart = Carbon::parse('2038-06-07 09:00:00');
        $availableEnd = Carbon::parse('2038-06-07 09:30:00');
        $this->assertTrue($this->scheduleService->isTimeSlotAvailable($availableStart, $availableEnd));

        // Test blocked slot
        $blockedStart = Carbon::parse('2038-06-07 10:00:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:30:00');
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable($blockedStart, $blockedEnd));

        // Test overlapping slot
        $overlapStart = Carbon::parse('2038-06-07 10:30:00');
        $overlapEnd = Carbon::parse('2038-06-07 11:30:00');
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable($overlapStart, $overlapEnd));
    }

    public function test_find_next_available_slot(): void
    {
        Carbon::setTestNow('2038-06-06 08:00:00');

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Blocked',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $nextSlot = $this->scheduleService->findNextAvailableSlot(60, 'consultation');

        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);
        $this->assertSame('2038-06-07 09:00:00', $nextSlot['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $nextSlot['end']->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    public function test_between_method_returns_schedules_in_period(): void
    {
        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->availability->id,
            'title' => 'Schedule 3',
            'start_datetime' => '2038-06-14 10:00:00',
            'end_datetime' => '2038-06-14 11:00:00',
            'status' => 'available',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        /** @var Collection<Schedule> $schedules */
        $schedules = $this->scheduleService->between($start, $end);

        $this->assertCount(2, $schedules);
        $this->assertSame('Schedule 1', $schedules[0]->title);
        $this->assertSame('Schedule 2', $schedules[1]->title);
    }
}
