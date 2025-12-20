<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Facades\Schedule as ScheduleFacade;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Tests\TestCase;

final class ScheduleFacadeTest extends TestCase
{
    private Model $testModel;

    private Availability $consultationAvailability;

    private Availability $trainingAvailability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testModel = new class extends Model
        {
            protected $table = 'test_schedulables';
        };

        $this->testModel = $this->testModel::create();

        $testDate = Carbon::parse('2038-06-01');
        $dayOfWeek = strtolower($testDate->englishDayOfWeek);

        $this->consultationAvailability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => [$dayOfWeek],
        ]);

        $this->trainingAvailability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'training',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => [$dayOfWeek],
        ]);
    }

    /**
     * Test that the facade requires an Availability instance for creation.
     */
    public function test_facade_also_requires_availability(): void
    {
        $scheduleData = [
            'title' => 'Schedule via Facade sans Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        ScheduleFacade::for($this->testModel)->create($scheduleData);
    }

    /**
     * Test that the facade can create a consultation schedule.
     */
    public function test_facade_can_create_schedule(): void
    {
        $scheduleData = [
            'title' => 'Test Consultation',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ];

        $schedule = ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, $scheduleData);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Test Consultation', $schedule->title);
        $this->assertSame($this->consultationAvailability->id, $schedule->availability_id);
        $this->assertSame('consultation', $schedule->type);
    }

    /**
     * Test that the facade can create a training schedule.
     */
    public function test_facade_can_create_training_schedule(): void
    {
        $scheduleData = [
            'title' => 'Training Session',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
        ];

        $schedule = ScheduleFacade::for($this->testModel)->create($this->trainingAvailability, $scheduleData);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Training Session', $schedule->title);
        $this->assertSame($this->trainingAvailability->id, $schedule->availability_id);
        $this->assertSame('training', $schedule->type);
    }

    /**
     * Test that the old create method without availability is deprecated.
     */
    public function test_facade_old_create_method_is_deprecated(): void
    {
        $scheduleData = [
            'title' => 'Test Consultation',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        ScheduleFacade::for($this->testModel)->create($scheduleData);
    }

    /**
     * Test that the facade can find a schedule by ID.
     */
    public function test_facade_can_find_schedule(): void
    {
        $schedule = ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        $found = ScheduleFacade::for($this->testModel)->find($schedule->id);

        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Test Schedule', $found->title);
    }

    /**
     * Test that the facade can retrieve all schedules.
     */
    public function test_facade_can_get_all_schedules(): void
    {
        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
            'status' => 'booked',
        ]);

        /** @var Collection<int, Schedule> $schedules */
        $schedules = ScheduleFacade::for($this->testModel)->all();

        $this->assertCount(2, $schedules);
        $this->assertSame('Schedule 1', $schedules[0]->title);
        $this->assertSame('Schedule 2', $schedules[1]->title);
    }

    /**
     * Test that the facade can filter schedules by status.
     */
    public function test_facade_can_filter_schedules(): void
    {
        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Available Schedule',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Booked Schedule',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
            'status' => 'booked',
        ]);

        $availableSchedules = ScheduleFacade::for($this->testModel)
            ->whereStatus('available')
            ->get();

        $this->assertCount(1, $availableSchedules);
        $this->assertSame('Available Schedule', $availableSchedules->first()->title);
    }

    /**
     * Test that the facade can filter schedules by type.
     */
    public function test_facade_can_filter_schedules_by_type(): void
    {
        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Consultation Schedule',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->testModel)->create($this->trainingAvailability, [
            'title' => 'Training Schedule',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
            'status' => 'available',
        ]);

        $trainingSchedules = ScheduleFacade::for($this->testModel)
            ->whereType('training')
            ->get();

        $this->assertCount(1, $trainingSchedules);
        $this->assertSame('Training Schedule', $trainingSchedules->first()->title);
    }

    /**
     * Test that the facade can check time slot availability.
     */
    public function test_facade_can_check_time_slot_availability(): void
    {
        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Blocked',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'booked',
        ]);

        $availableStart = Carbon::parse('2038-06-01 09:00:00');
        $availableEnd = Carbon::parse('2038-06-01 09:30:00');

        $blockedStart = Carbon::parse('2038-06-01 10:30:00');
        $blockedEnd = Carbon::parse('2038-06-01 11:00:00');

        $this->assertTrue(
            ScheduleFacade::for($this->testModel)->isTimeSlotAvailable($availableStart, $availableEnd, 'consultation')
        );

        $this->assertFalse(
            ScheduleFacade::for($this->testModel)->isTimeSlotAvailable($blockedStart, $blockedEnd, 'consultation')
        );
    }

    /**
     * Test that the facade can find the next available time slot.
     */
    public function test_facade_can_find_next_available_slot(): void
    {
        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Booked',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'booked',
        ]);

        $nextSlot = ScheduleFacade::for($this->testModel)->findNextAvailableSlot(60, 'consultation');

        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);

        $this->assertSame('09:00', $nextSlot['start']->format('H:i'));
        $this->assertSame('10:00', $nextSlot['end']->format('H:i'));
    }

    /**
     * Test that the facade can retrieve schedules between dates.
     */
    public function test_facade_can_get_schedules_between_dates(): void
    {
        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-02 14:00:00',
            'end_datetime' => '2038-06-02 15:00:00',
            'status' => 'available',
        ]);

        $start = Carbon::parse('2038-06-01 00:00:00');
        $end = Carbon::parse('2038-06-01 23:59:59');

        $schedules = ScheduleFacade::for($this->testModel)->between($start, $end);

        $this->assertCount(1, $schedules);
        $this->assertSame('Schedule 1', $schedules->first()->title);
    }

    /**
     * Test that the facade can delete a schedule.
     */
    public function test_facade_can_delete_schedule(): void
    {
        $schedule = ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Schedule to delete',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        $found = ScheduleFacade::for($this->testModel)->find($schedule->id);
        $this->assertInstanceOf(Schedule::class, $found);

        $deleted = ScheduleFacade::for($this->testModel)->delete($schedule->id);
        $this->assertTrue($deleted);

        $foundAfterDelete = ScheduleFacade::for($this->testModel)->find($schedule->id);
        $this->assertNotInstanceOf(\Roster\Models\Schedule::class, $foundAfterDelete);
    }

    /**
     * Test that the facade can update a schedule.
     */
    public function test_facade_can_update_schedule(): void
    {
        $schedule = ScheduleFacade::for($this->testModel)->create($this->consultationAvailability, [
            'title' => 'Original Title',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
            'description' => 'Original description',
        ]);

        $updated = ScheduleFacade::for($this->testModel)->update($schedule->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
    }
}
