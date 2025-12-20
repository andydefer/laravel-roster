<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Facades\Impediment as ImpedimentFacade;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Tests\TestCase;

final class ImpedimentFacadeTest extends TestCase
{
    use RefreshDatabase;

    private Model $model;

    private Availability $availability;

    private Availability $julyAvailability;

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

        $this->julyAvailability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['tuesday'],
            'start_date' => '2038-07-01',
            'end_date' => '2038-07-31',
        ]);
    }

    public function test_facade_can_create_impediment(): void
    {
        $data = [
            'reason' => 'Out of office',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin 2038
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        // Utiliser la nouvelle API avec Availability explicite
        $impediment = ImpedimentFacade::for($this->model)->create($this->availability, $data);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Out of office', $impediment->reason);
        $this->assertSame($this->availability->id, $impediment->availability_id);
    }

    public function test_facade_can_create_impediment_with_different_availability(): void
    {
        $data = [
            'reason' => 'July holiday',
            'start_datetime' => '2038-07-06 10:00:00', // Mardi 6 juillet 2038
            'end_datetime' => '2038-07-06 11:00:00',
        ];

        // Utiliser la juillet availability
        $impediment = ImpedimentFacade::for($this->model)->create($this->julyAvailability, $data);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('July holiday', $impediment->reason);
        $this->assertSame($this->julyAvailability->id, $impediment->availability_id);
    }

    public function test_facade_old_create_method_is_deprecated(): void
    {
        $data = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        // Tenter d'utiliser l'ancienne API
        ImpedimentFacade::for($this->model)->create($data);
    }

    public function test_facade_can_find_impediment(): void
    {
        // Créer d'abord un impediment via la facade
        $impediment = ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $found = ImpedimentFacade::for($this->model)->find($impediment->id);

        $this->assertInstanceOf(Impediment::class, $found);
        $this->assertSame($impediment->id, $found->id);
        $this->assertSame('Test', $found->reason);
    }

    public function test_facade_can_get_all_impediments(): void
    {
        // Créer des impediments via la facade
        ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        /** @var Collection<int, \Roster\Models\Impediment> $impediments */
        $impediments = ImpedimentFacade::for($this->model)->all();

        $this->assertCount(2, $impediments);
        $this->assertSame('Impediment 1', $impediments[0]->reason);
        $this->assertSame('Impediment 2', $impediments[1]->reason);
    }

    public function test_facade_can_filter_impediments(): void
    {
        // Créer des impediments via la facade
        ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Morning meeting',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Afternoon training',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        $startDate = Carbon::parse('2038-06-07 12:00:00');
        $endDate = Carbon::parse('2038-06-07 16:00:00');

        $filtered = ImpedimentFacade::for($this->model)
            ->whereStartDate($startDate)
            ->whereEndDate($endDate)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertSame('Afternoon training', $filtered->first()->reason);
    }

    public function test_facade_can_check_if_time_slot_is_blocked(): void
    {
        // Créer un impediment via la facade
        ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Meeting',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $blockedStart = Carbon::parse('2038-06-07 10:30:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:45:00');

        $availableStart = Carbon::parse('2038-06-07 11:30:00');
        $availableEnd = Carbon::parse('2038-06-07 12:00:00');

        $this->assertTrue(
            ImpedimentFacade::for($this->model)->isTimeSlotBlocked($blockedStart, $blockedEnd)
        );

        $this->assertFalse(
            ImpedimentFacade::for($this->model)->isTimeSlotBlocked($availableStart, $availableEnd)
        );
    }

    public function test_facade_can_get_impediments_between_dates(): void
    {
        // Créer des impediments via la facade
        ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        ImpedimentFacade::for($this->model)->create($this->julyAvailability, [
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-07-06 14:00:00',
            'end_datetime' => '2038-07-06 15:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        $impediments = ImpedimentFacade::for($this->model)->between($start, $end);

        $this->assertCount(1, $impediments);
        $this->assertSame('Impediment 1', $impediments->first()->reason);
    }

    public function test_facade_can_delete_impediment(): void
    {
        // Créer un impediment via la facade
        $impediment = ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Test to delete',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        // Vérifier qu'il existe
        $found = ImpedimentFacade::for($this->model)->find($impediment->id);
        $this->assertInstanceOf(\Roster\Models\Impediment::class, $found);

        // Le supprimer
        $deleted = ImpedimentFacade::for($this->model)->delete($impediment->id);
        $this->assertTrue($deleted);

        // Vérifier qu'il n'existe plus
        $foundAfterDelete = ImpedimentFacade::for($this->model)->find($impediment->id);
        $this->assertNotInstanceOf(\Roster\Models\Impediment::class, $foundAfterDelete);
    }

    public function test_facade_can_update_impediment(): void
    {
        // Créer un impediment via la facade
        $impediment = ImpedimentFacade::for($this->model)->create($this->availability, [
            'reason' => 'Original reason',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => ['notes' => 'Original notes'],
        ]);

        // Mettre à jour
        $updated = ImpedimentFacade::for($this->model)->update($impediment->id, [
            'reason' => 'Updated reason',
            'metadata' => ['notes' => 'Updated notes'],
        ]);

        $this->assertTrue($updated);

        // Vérifier les changements
        $impediment->refresh();
        $this->assertSame('Updated reason', $impediment->reason);
        $this->assertSame(['notes' => 'Updated notes'], $impediment->metadata);
    }
}
