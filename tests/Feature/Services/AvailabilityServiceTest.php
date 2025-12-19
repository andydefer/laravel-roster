<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Tests\TestCase;

final class AvailabilityServiceTest extends TestCase
{
    private AvailabilityService $availabilityService;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model {
            protected $table = 'test_schedulables';
        };

        $this->model = $this->model::create();

        // Utiliser le conteneur Laravel pour obtenir le service
        $this->availabilityService = app(AvailabilityService::class);
        $this->availabilityService->for($this->model);
    }

    public function test_create_availability_successfully(): void
    {
        $data = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday'],
        ];

        $availability = $this->availabilityService->create($data);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame($this->model->id, $availability->schedulable_id);
        $this->assertSame(get_class($this->model), $availability->schedulable_type);
        $this->assertSame('consultation', $availability->type);
        $this->assertSame(['monday', 'tuesday'], $availability->days);
        $this->assertDatabaseHas('roster_availabilities', [
            'schedulable_id' => $this->model->id,
            'type' => 'consultation',
        ]);
    }

    public function test_create_availability_with_date_ranges(): void
    {
        $data = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ];

        $availability = $this->availabilityService->create($data);

        $this->assertSame('2038-06-01', $availability->start_date->format('Y-m-d'));
        $this->assertSame('2038-06-30', $availability->end_date->format('Y-m-d'));
    }

    public function test_create_availability_with_overlap_throws_exception(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'days' => ['monday'],
        ]);
    }

    public function test_update_availability_successfully(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $updated = $this->availabilityService->update($availability->id, [
            'type' => 'training',
            'end_time' => '13:00:00',
        ]);

        $this->assertTrue($updated);

        $availability->refresh();
        $this->assertSame('training', $availability->type);
        $this->assertSame('13:00:00', $availability->end_time->format('H:i:s'));
    }

    public function test_delete_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $deleted = $this->availabilityService->delete($availability->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_availabilities', ['id' => $availability->id]);
    }

    public function test_find_availability_by_id(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $found = $this->availabilityService->find($availability->id);

        $this->assertInstanceOf(Availability::class, $found);
        $this->assertSame($availability->id, $found->id);
    }

    public function test_is_available_at_method(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $monday2038 = Carbon::parse('2038-06-07');

        $availableTime = $monday2038->copy()->setTime(10, 0, 0);
        $this->assertTrue($this->availabilityService->isAvailableAt($availableTime));

        $unavailableTime = $monday2038->copy()->setTime(8, 0, 0);
        $this->assertFalse($this->availabilityService->isAvailableAt($unavailableTime));

        $wrongDay = Carbon::parse('2038-06-08 10:00:00');
        $this->assertFalse($this->availabilityService->isAvailableAt($wrongDay));
    }

    public function test_available_slots_method(): void
    {
        $monday2038 = Carbon::parse('2038-06-07');

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $startDate = $monday2038->copy();
        $endDate = $monday2038->copy()->addDay();

        $slots = $this->availabilityService->findSlotsInPeriod($startDate, $endDate, 60, 60);

        $this->assertIsArray($slots);
        $this->assertCount(3, $slots);

        $this->assertSame('2038-06-07 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));
        $this->assertSame('consultation', $slots[0]['type']);
    }
}
