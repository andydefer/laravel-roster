<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use Roster\Services\AvailabilityService;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Facades\Availability as AvailabilityFacade;
use Roster\Models\Availability;
use Tests\TestCase;

final class AvailabilityFacadeTest extends TestCase
{
    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        // Créer un enregistrement dans la table
        $this->model = $this->model::create();
    }

    public function test_facade_returns_availability_service(): void
    {
        $availabilityService = AvailabilityFacade::for($this->model);

        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
        $this->assertSame($this->model->id, $availabilityService->getSchedulable()->id);
    }

    public function test_facade_can_create_availability(): void
    {
        $data = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ];

        $availability = AvailabilityFacade::for($this->model)->create($data);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame('consultation', $availability->type);
        $this->assertDatabaseHas('roster_availabilities', [
            'schedulable_id' => $this->model->id,
            'type' => 'consultation',
        ]);
    }

    public function test_facade_can_find_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $found = AvailabilityFacade::for($this->model)->find($availability->id);

        $this->assertInstanceOf(Availability::class, $found);
        $this->assertSame($availability->id, $found->id);
    }

    public function test_facade_can_get_all_availabilities(): void
    {
        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'days' => ['monday'],
        ]);

        /** @var Collection<int, \Roster\Models\Availability> $availabilities */
        $availabilities = AvailabilityFacade::for($this->model)->all();

        $this->assertCount(2, $availabilities);
        $this->assertSame('consultation', $availabilities[0]->type);
        $this->assertSame('training', $availabilities[1]->type);
    }

    public function test_facade_can_filter_by_type(): void
    {
        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'days' => ['monday'],
        ]);

        $availabilities = AvailabilityFacade::for($this->model)
            ->whereType('consultation')
            ->get();

        $this->assertCount(1, $availabilities);
        $this->assertSame('consultation', $availabilities->first()->type);
    }

    public function test_facade_can_check_availability_at_time(): void
    {
        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $availableTime = Carbon::parse('2038-06-07 10:00:00');
        $unavailableTime = Carbon::parse('2038-06-07 08:00:00');

        $this->assertTrue(
            AvailabilityFacade::for($this->model)->isAvailableAt($availableTime)
        );

        $this->assertFalse(
            AvailabilityFacade::for($this->model)->isAvailableAt($unavailableTime)
        );
    }
}
