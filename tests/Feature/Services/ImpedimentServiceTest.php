<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\OverlappingImpedimentException;
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

        // Availability pour juin
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

        // Availability pour juillet (utilisée dans certains tests)
        $this->julyAvailability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['tuesday'], // Mardi 1er juillet 2038
            'start_date' => '2038-07-01',
            'end_date' => '2038-07-31',
        ]);

        $this->impedimentService = app(ImpedimentService::class);
        $this->impedimentService->for($this->model);
    }

    public function test_create_impediment_successfully(): void
    {
        $data = [
            'reason' => 'Out of office',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin 2038
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => ['notes' => 'Doctor appointment'],
        ];

        // Utiliser la nouvelle API avec Availability explicite
        $impediment = $this->impedimentService->create($this->availability, $data);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Out of office', $impediment->reason);
        $this->assertSame($this->model->id, $impediment->schedulable_id);
        $this->assertSame(get_class($this->model), $impediment->schedulable_type);
        $this->assertSame($this->availability->id, $impediment->availability_id);
        $this->assertDatabaseHas('roster_impediments', [
            'reason' => 'Out of office',
            'availability_id' => $this->availability->id,
        ]);
    }

    public function test_create_impediment_with_wrong_availability_throws_exception(): void
    {
        // Créer un autre modèle avec sa propre availability
        $otherModel = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $otherModel->id = 2;
        $otherModel->save();

        $otherAvailability = Availability::create([
            'schedulable_id' => $otherModel->id,
            'schedulable_type' => get_class($otherModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $data = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided availability does not belong to this schedulable');

        // Tenter d'utiliser une availability qui appartient à un autre modèle
        $this->impedimentService->create($otherAvailability, $data);
    }

    public function test_create_overlapping_impediment_throws_exception(): void
    {
        // Premier impediment
        $this->impedimentService->create($this->availability, [
            'reason' => 'First impediment',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->expectException(OverlappingImpedimentException::class);

        // Deuxième impediment qui chevauche
        $this->impedimentService->create($this->availability, [
            'reason' => 'Overlapping impediment',
            'start_datetime' => '2038-06-07 10:30:00',
            'end_datetime' => '2038-06-07 11:30:00',
        ]);
    }

    public function test_update_impediment_successfully(): void
    {
        // Créer d'abord un impediment
        $impediment = $this->impedimentService->create($this->availability, [
            'reason' => 'Original reason',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'metadata' => ['notes' => 'Original notes'],
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
        // Créer d'abord un impediment
        $impediment = $this->impedimentService->create($this->availability, [
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
        // Créer d'abord un impediment
        $impediment = $this->impedimentService->create($this->availability, [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $deleted = $this->impedimentService->delete($impediment->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_impediments', ['id' => $impediment->id]);
    }

    public function test_find_impediment_by_id(): void
    {
        // Créer d'abord un impediment
        $impediment = $this->impedimentService->create($this->availability, [
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
        // Créer un impediment
        $this->impedimentService->create($this->availability, [
            'reason' => 'Meeting',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        // Test slot bloqué (chevauchement partiel)
        $blockedStart = Carbon::parse('2038-06-07 10:30:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:45:00');
        $this->assertTrue($this->impedimentService->isTimeSlotBlocked($blockedStart, $blockedEnd));

        // Test slot disponible (après l'impediment)
        $availableStart = Carbon::parse('2038-06-07 11:30:00');
        $availableEnd = Carbon::parse('2038-06-07 12:00:00');
        $this->assertFalse($this->impedimentService->isTimeSlotBlocked($availableStart, $availableEnd));

        // Test avec type différent (devrait être disponible)
        $this->assertFalse($this->impedimentService->isTimeSlotBlocked($blockedStart, $blockedEnd, 'training'));
    }

    public function test_get_available_time_slots_with_impediments(): void
    {
        // Créer un impediment
        $this->impedimentService->create($this->availability, [
            'reason' => 'Blocked',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 09:00:00');
        $end = Carbon::parse('2038-06-07 12:00:00');

        $slots = $this->impedimentService->getAvailableTimeSlots($start, $end);

        $this->assertCount(2, $slots);

        // Slot avant l'impediment
        $this->assertSame('2038-06-07 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));

        // Slot après l'impediment
        $this->assertSame('2038-06-07 11:00:00', $slots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 12:00:00', $slots[1]['end']->format('Y-m-d H:i:s'));
    }

    public function test_between_method_returns_impediments_in_period(): void
    {
        // Créer plusieurs impediments
        $this->impedimentService->create($this->availability, [
            'reason' => 'Impediment 1',
            'start_datetime' => '2038-06-07 10:00:00', // Lundi 7 juin
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->impedimentService->create($this->availability, [
            'reason' => 'Impediment 2',
            'start_datetime' => '2038-06-07 14:00:00', // Lundi 7 juin
            'end_datetime' => '2038-06-07 15:00:00',
        ]);

        // CORRECTION : Utiliser un mardi pour l'impediment de juillet
        // Le 6 juillet 2038 est un mardi
        $this->impedimentService->create($this->julyAvailability, [
            'reason' => 'Impediment 3',
            'start_datetime' => '2038-07-06 10:00:00', // Mardi 6 juillet - CORRIGÉ
            'end_datetime' => '2038-07-06 11:00:00',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        /** @var Collection<Impediment> $impediments */
        $impediments = $this->impedimentService->between($start, $end);

        $this->assertCount(2, $impediments);
        $this->assertSame('Impediment 1', $impediments[0]->reason);
        $this->assertSame('Impediment 2', $impediments[1]->reason);
    }

    public function test_old_create_method_is_deprecated(): void
    {
        $data = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        // Tenter d'utiliser l'ancienne API
        $this->impedimentService->create($data);
    }
}
