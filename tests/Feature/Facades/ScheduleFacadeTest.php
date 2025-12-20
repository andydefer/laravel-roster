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
    private Model $model;

    private Availability $availability;

    private Availability $trainingAvailability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model
        {
            protected $table = 'test_schedulables';
        };

        $this->model = $this->model::create();

        $testDate = Carbon::parse('2038-06-01');
        $dayOfWeek = strtolower($testDate->englishDayOfWeek);

        // Availability pour consultations
        $this->availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => [$dayOfWeek],
        ]);

        // Availability pour training
        $this->trainingAvailability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'training',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => [$dayOfWeek],
        ]);
    }

    public function test_facade_also_requires_availability(): void
    {
        $data = [
            'title' => 'Schedule via Facade sans Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        // La facade Schedule utilise le même service, donc même comportement
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        ScheduleFacade::for($this->model)->create($data);
    }

    public function test_facade_can_create_schedule(): void
    {
        $data = [
            'title' => 'Test Consultation',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ];

        // Utiliser la nouvelle API avec Availability explicite
        $schedule = ScheduleFacade::for($this->model)->create($this->availability, $data);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Test Consultation', $schedule->title);
        $this->assertSame($this->availability->id, $schedule->availability_id);
        $this->assertSame('consultation', $schedule->type);
    }

    public function test_facade_can_create_training_schedule(): void
    {
        $data = [
            'title' => 'Training Session',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
        ];

        // Utiliser l'availability de type training
        $schedule = ScheduleFacade::for($this->model)->create($this->trainingAvailability, $data);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Training Session', $schedule->title);
        $this->assertSame($this->trainingAvailability->id, $schedule->availability_id);
        $this->assertSame('training', $schedule->type);
    }

    public function test_facade_old_create_method_is_deprecated(): void
    {
        $data = [
            'title' => 'Test Consultation',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        // Tenter d'utiliser l'ancienne API
        ScheduleFacade::for($this->model)->create($data);
    }

    public function test_facade_can_find_schedule(): void
    {
        // Créer d'abord un schedule via la facade
        $schedule = ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        $found = ScheduleFacade::for($this->model)->find($schedule->id);

        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Test Schedule', $found->title);
    }

    public function test_facade_can_get_all_schedules(): void
    {
        // Créer des schedules via la facade
        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
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
        // Créer des schedules via la facade
        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Available Schedule',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Booked Schedule',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
            'status' => 'booked',
        ]);

        $availableSchedules = ScheduleFacade::for($this->model)
            ->whereStatus('available')
            ->get();

        $this->assertCount(1, $availableSchedules);
        $this->assertSame('Available Schedule', $availableSchedules->first()->title);
    }

    public function test_facade_can_filter_schedules_by_type(): void
    {
        // Créer des schedules de différents types via la facade
        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Consultation Schedule',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->model)->create($this->trainingAvailability, [
            'title' => 'Training Schedule',
            'start_datetime' => '2038-06-01 14:00:00',
            'end_datetime' => '2038-06-01 15:00:00',
            'status' => 'available',
        ]);

        $trainingSchedules = ScheduleFacade::for($this->model)
            ->whereType('training')
            ->get();

        $this->assertCount(1, $trainingSchedules);
        $this->assertSame('Training Schedule', $trainingSchedules->first()->title);
    }

    public function test_facade_can_check_time_slot_availability(): void
    {
        // Créer un schedule via la facade
        ScheduleFacade::for($this->model)->create($this->availability, [
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
            ScheduleFacade::for($this->model)->isTimeSlotAvailable($availableStart, $availableEnd, 'consultation')
        );

        $this->assertFalse(
            ScheduleFacade::for($this->model)->isTimeSlotAvailable($blockedStart, $blockedEnd, 'consultation')
        );
    }

    public function test_facade_can_find_next_available_slot(): void
    {
        // Créer un schedule via la facade
        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Booked',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'booked',
        ]);

        $nextSlot = ScheduleFacade::for($this->model)->findNextAvailableSlot(60, 'consultation');

        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);

        $this->assertSame('09:00', $nextSlot['start']->format('H:i'));
        $this->assertSame('10:00', $nextSlot['end']->format('H:i'));
    }

    public function test_facade_can_get_schedules_between_dates(): void
    {
        // Créer des schedules via la facade
        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-02 14:00:00',
            'end_datetime' => '2038-06-02 15:00:00',
            'status' => 'available',
        ]);

        $start = Carbon::parse('2038-06-01 00:00:00');
        $end = Carbon::parse('2038-06-01 23:59:59');

        $schedules = ScheduleFacade::for($this->model)->between($start, $end);

        $this->assertCount(1, $schedules);
        $this->assertSame('Schedule 1', $schedules->first()->title);
    }

    public function test_facade_can_delete_schedule(): void
    {
        // Créer un schedule via la facade
        $schedule = ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Schedule to delete',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
        ]);

        // Vérifier qu'il existe
        $found = ScheduleFacade::for($this->model)->find($schedule->id);
        $this->assertInstanceOf(\Roster\Models\Schedule::class, $found);

        // Le supprimer
        $deleted = ScheduleFacade::for($this->model)->delete($schedule->id);
        $this->assertTrue($deleted);

        // Vérifier qu'il n'existe plus
        $foundAfterDelete = ScheduleFacade::for($this->model)->find($schedule->id);
        $this->assertNotInstanceOf(\Roster\Models\Schedule::class, $foundAfterDelete);
    }

    public function test_facade_can_update_schedule(): void
    {
        // Créer un schedule via la facade
        $schedule = ScheduleFacade::for($this->model)->create($this->availability, [
            'title' => 'Original Title',
            'start_datetime' => '2038-06-01 10:00:00',
            'end_datetime' => '2038-06-01 11:00:00',
            'status' => 'available',
            'description' => 'Original description',
        ]);

        // Mettre à jour
        $updated = ScheduleFacade::for($this->model)->update($schedule->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        // Vérifier les changements
        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
    }
}
