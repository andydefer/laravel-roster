<?php

declare(strict_types=1);

// ==== tests/Unit/Services/ImpedimentServiceTest.php ====

namespace Roster\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Services\ImpedimentService;
use Roster\Tests\TestCase;

final class ImpedimentServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImpedimentService $impedimentService;

    private TestSchedulable $testSchedulable;

    /**
     * Dates fixes pour juin 2027
     */
    private Carbon $mondayJune7;   // Mardi 8 juin 2027

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_schedulables', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->impedimentService = new ImpedimentService;
        $this->testSchedulable = TestSchedulable::create(['name' => 'Test Schedulable']);

        // Dates fixes de juin 2027
        $this->mondayJune7 = Carbon::create(2027, 6, 7); // Lundi
        Carbon::create(2027, 6, 8); // Mardi
    }

    public function test_create_impediment_with_valid_data(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $impediment = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Maladie',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'metadata' => ['note' => 'Rendez-vous annulé'],
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertEquals('Maladie', $impediment->reason);
        $this->assertEquals('2027-06-07 09:00:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertEquals($availability->id, $impediment->availability_id);
        $this->assertEquals(['note' => 'Rendez-vous annulé'], $impediment->metadata);
    }

    public function test_create_impediment_prevents_overlapping_impediments(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un premier impediment
        $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Pause déjeuner',
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 0),
        ]);

        // Essaie de créer un deuxième impediment qui chevauche
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This time slot overlaps with an existing impediment');

        $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Réunion',
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 30),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 30),
        ]);
    }

    public function test_create_impediment_allows_non_overlapping_impediments(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un premier impediment
        $firstImpediment = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Pause déjeuner',
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 0),
        ]);

        // Créer un deuxième impediment qui ne chevauche pas
        $secondImpediment = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Réunion',
            'start_datetime' => $this->mondayJune7->copy()->setTime(14, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(15, 0),
        ]);

        $this->assertInstanceOf(Impediment::class, $firstImpediment);
        $this->assertInstanceOf(Impediment::class, $secondImpediment);
        $this->assertCount(2, Impediment::all());
    }

    public function test_create_impediment_deletes_overlapping_schedules(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un schedule à 9h-9h30
        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(9, 30),
            'status' => 'available',
        ]);

        // Créer un impediment à 9h30-10h30 (NE chevauche PAS, commence exactement à la fin)
        $impediment = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Réunion',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 30),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 30),
        ]);

        // Le schedule devrait toujours exister (pas de chevauchement)
        $this->assertDatabaseHas('schedules', ['id' => $schedule->id]);
        $this->assertDatabaseHas('impediments', ['id' => $impediment->id]);
    }

    public function test_create_impediment_fails_when_end_before_start(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End datetime must be after start datetime');

        $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Test',
            'start_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(9, 0), // End before start
        ]);
    }

    public function test_update_impediment_prevents_overlapping_with_other_impediments(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer deux impediments non chevauchants
        $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Pause déjeuner',
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 0),
        ]);

        $impediment2 = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Réunion',
            'start_datetime' => $this->mondayJune7->copy()->setTime(14, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(15, 0),
        ]);

        // Essaie de mettre à jour le deuxième pour qu'il chevauche le premier
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This time slot overlaps with another impediment');

        $this->impedimentService->for($this->testSchedulable)->update($impediment2->id, [
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 30),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 30),
        ]);
    }

    public function test_update_impediment_allows_valid_time_change(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $impediment = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Pause déjeuner',
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 0),
        ]);

        // Mettre à jour avec un horaire non chevauchant
        $result = $this->impedimentService->for($this->testSchedulable)->update($impediment->id, [
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 30),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 30),
            'reason' => 'Pause déjeuner prolongée',
        ]);

        $this->assertTrue($result);
        $impediment->refresh();
        $this->assertEquals('Pause déjeuner prolongée', $impediment->reason);
        $this->assertEquals('2027-06-07 12:30:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
    }

    public function test_is_time_slot_blocked_returns_true_with_impediment(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Pause',
            'start_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(13, 0),
        ]);

        $start = $this->mondayJune7->copy()->setTime(12, 30);
        $end = $this->mondayJune7->copy()->setTime(13, 30);

        $isBlocked = $this->impedimentService->for($this->testSchedulable)
            ->isTimeSlotBlocked($start, $end, 'consultation');

        $this->assertTrue($isBlocked);
    }

    public function test_is_time_slot_blocked_returns_false_without_impediment(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $start = $this->mondayJune7->copy()->setTime(14, 0);
        $end = $this->mondayJune7->copy()->setTime(15, 0);

        $isBlocked = $this->impedimentService->for($this->testSchedulable)
            ->isTimeSlotBlocked($start, $end, 'consultation');

        $this->assertFalse($isBlocked);
    }

    public function test_get_available_time_slots_returns_correct_slots(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        // Créer un impediment de 10h à 12h
        Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Réunion',
            'start_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
        ]);

        // Créer un impediment de 14h à 15h
        Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Pause',
            'start_datetime' => $this->mondayJune7->copy()->setTime(14, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(15, 0),
        ]);

        $start = $this->mondayJune7->copy()->setTime(9, 0);
        $end = $this->mondayJune7->copy()->setTime(16, 0);

        $availableSlots = $this->impedimentService->for($this->testSchedulable)
            ->getAvailableTimeSlots($start, $end, 'consultation');

        $this->assertCount(3, $availableSlots);

        // Premier créneau : 9h-10h
        $this->assertEquals('2027-06-07 09:00:00', $availableSlots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2027-06-07 10:00:00', $availableSlots[0]['end']->format('Y-m-d H:i:s'));

        // Deuxième créneau : 12h-14h
        $this->assertEquals('2027-06-07 12:00:00', $availableSlots[1]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2027-06-07 14:00:00', $availableSlots[1]['end']->format('Y-m-d H:i:s'));

        // Troisième créneau : 15h-16h
        $this->assertEquals('2027-06-07 15:00:00', $availableSlots[2]['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2027-06-07 16:00:00', $availableSlots[2]['end']->format('Y-m-d H:i:s'));
    }

    public function test_get_impediments_between_dates(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $nextMondayJune14 = Carbon::create(2027, 6, 14); // Lundi suivant

        Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Impediment 1',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
        ]);

        Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Impediment 2',
            'start_datetime' => $nextMondayJune14->copy()->setTime(9, 0),
            'end_datetime' => $nextMondayJune14->copy()->setTime(10, 0),
        ]);

        $startDate = Carbon::create(2027, 6, 1);
        $endDate = Carbon::create(2027, 6, 10);

        $impediments = $this->impedimentService->for($this->testSchedulable)
            ->between($startDate, $endDate);

        $this->assertCount(1, $impediments);
        $this->assertEquals('Impediment 1', $impediments->first()->reason);
    }

    public function test_filter_impediments_by_type(): void
    {
        $availability1 = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $availability2 = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'training',
            'start_time' => '18:00',
            'end_time' => '20:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        Impediment::create([
            'availability_id' => $availability1->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Maladie consultation',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(12, 0),
        ]);

        Impediment::create([
            'availability_id' => $availability2->id,
            'schedulable_type' =>  TestSchedulable::class,
            'schedulable_id' =>  $this->testSchedulable->id,
            'reason' => 'Blessure training',
            'start_datetime' => $this->mondayJune7->copy()->setTime(18, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(20, 0),
        ]);

        $consultationImpediments = $this->impedimentService->for($this->testSchedulable)
            ->whereType('consultation')
            ->get();

        $this->assertCount(1, $consultationImpediments);
        $this->assertEquals('Maladie consultation', $consultationImpediments->first()->reason);
    }

    public function test_create_fails_without_schedulable(): void
    {
        $this->expectException(MissingSchedulableException::class);
        $this->expectExceptionMessage('No schedulable specified. Use the for() method before executing the query.');

        $this->impedimentService->create([
            'reason' => 'Test',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
        ]);
    }

    public function test_delete_impediment(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2027-06-01',
            'end_date' => '2027-06-30',
        ]);

        $impediment = $this->impedimentService->for($this->testSchedulable)->create([
            'reason' => 'Test',
            'start_datetime' => $this->mondayJune7->copy()->setTime(9, 0),
            'end_datetime' => $this->mondayJune7->copy()->setTime(10, 0),
        ]);

        $result = $this->impedimentService->for($this->testSchedulable)->delete($impediment->id);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('impediments', ['id' => $impediment->id]);
    }

    public function test_delete_non_existent_impediment_returns_false(): void
    {
        $result = $this->impedimentService->for($this->testSchedulable)->delete(999);
        $this->assertFalse($result);
    }
}
