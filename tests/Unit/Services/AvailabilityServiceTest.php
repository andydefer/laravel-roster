<?php

namespace Roster\Tests\Unit\Services;

use Roster\Services\AvailabilityService;
use Roster\Models\Availability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Roster\Tests\TestCase;

// Création d'un modèle factice pour les tests
class TestSchedulable extends Model
{
    protected $table = 'test_schedulables';

    protected $fillable = ['name'];
}

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AvailabilityService $service;
    protected TestSchedulable $schedulable;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer une table factice pour notre modèle de test
        Schema::create('test_schedulables', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->service = app(AvailabilityService::class);
        $this->schedulable = TestSchedulable::create(['name' => 'Test Schedulable']);
    }

    public function test_for_method_returns_availability_service_instance()
    {
        $result = $this->service->for($this->schedulable);

        $this->assertInstanceOf(AvailabilityService::class, $result);
        $this->assertSame($this->schedulable, $result->getSchedulable());
    }

    public function test_create_availability_with_valid_data()
    {
        $availability = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals('consultation', $availability->type);
        $this->assertEquals('09:00', $availability->start_time->format('H:i'));
        $this->assertEquals('17:00', $availability->end_time->format('H:i'));
        $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
        $this->assertEquals('2024-01-01', $availability->start_date->format('Y-m-d'));
        $this->assertEquals('2024-12-31', $availability->end_date->format('Y-m-d'));
        $this->assertEquals($this->schedulable->id, $availability->schedulable_id);
        $this->assertEquals(TestSchedulable::class, $availability->schedulable_type);
    }

    public function test_create_availability_without_optional_dates()
    {
        $availability = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertNull($availability->start_date);
        $this->assertNull($availability->end_date);
    }

    public function test_create_availability_throws_exception_for_invalid_time_range()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End time must be after start time');

        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '18:00',
            'end_time' => '09:00',
            'days' => ['monday'],
        ]);
    }

    public function test_create_availability_throws_exception_for_empty_days()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one day must be specified');

        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => [],
        ]);
    }

    public function test_update_availability()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $updated = $this->service->for($this->schedulable)->update($availability->id, [
            'start_time' => '10:00',
            'end_time' => '18:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $this->assertTrue($updated);
        $availability->refresh();
        $this->assertEquals('10:00', $availability->start_time->format('H:i'));
        $this->assertEquals('18:00', $availability->end_time->format('H:i'));
        $this->assertEquals(['monday', 'tuesday'], $availability->days);
    }

    public function test_update_availability_not_found()
    {
        $result = $this->service->for($this->schedulable)->update(999, [
            'start_time' => '10:00',
        ]);

        $this->assertFalse($result);
    }

    public function test_update_availability_with_invalid_data()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->for($this->schedulable)->update($availability->id, [
            'start_time' => '20:00',
            'end_time' => '18:00',
        ]);
    }

    public function test_delete_availability()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $result = $this->service->for($this->schedulable)->delete($availability->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('availabilities', ['id' => $availability->id]);
    }

    public function test_delete_availability_not_found()
    {
        $result = $this->service->for($this->schedulable)->delete(999);

        $this->assertFalse($result);
    }

    public function test_find_availability_by_id()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $found = $this->service->for($this->schedulable)->find($availability->id);

        $this->assertInstanceOf(Availability::class, $found);
        $this->assertEquals($availability->id, $found->id);
    }

    public function test_find_availability_not_found()
    {
        $found = $this->service->for($this->schedulable)->find(999);

        $this->assertNull($found);
    }

    public function test_get_all_availabilities()
    {
        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['tuesday'],
        ]);

        $availabilities = $this->service->for($this->schedulable)->all();

        $this->assertCount(2, $availabilities);
        $this->assertInstanceOf(Availability::class, $availabilities->first());
    }

    public function test_get_availabilities_by_type()
    {
        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['tuesday'],
        ]);

        $availabilities = $this->service->for($this->schedulable)->whereType('consultation')->get();

        $this->assertCount(1, $availabilities);
        $this->assertEquals('consultation', $availabilities->first()->type);
    }

    public function test_get_availabilities_for_specific_day()
    {
        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'wednesday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['tuesday', 'thursday'],
        ]);

        $availabilities = $this->service->for($this->schedulable)->whereDay('monday')->get();

        $this->assertCount(1, $availabilities);
        $this->assertTrue(in_array('monday', $availabilities->first()->days));
    }

    public function test_check_availability_for_specific_datetime()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        // Test avec un datetime valide (lundi 2024-06-10 10:00)
        $datetime = Carbon::create(2024, 6, 10, 10, 0, 0); // Un lundi

        $result = $this->service->for($this->schedulable)->isAvailableAt($datetime);

        $this->assertTrue($result);
    }

    public function test_check_availability_for_invalid_datetime()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        // Test avec un datetime invalide (mardi 2024-06-11 10:00)
        $datetime = Carbon::create(2024, 6, 11, 10, 0, 0); // Un mardi

        $result = $this->service->for($this->schedulable)->isAvailableAt($datetime);

        $this->assertFalse($result);
    }

    public function test_check_availability_with_time_outside_range()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        // Test avec un datetime valide pour le jour mais hors horaire
        $datetime = Carbon::create(2024, 6, 10, 8, 0, 0); // Un lundi à 8h

        $result = $this->service->for($this->schedulable)->isAvailableAt($datetime);

        $this->assertFalse($result);
    }

    public function test_get_next_available_slot()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'wednesday', 'friday'],
        ]);

        // Test un mardi, le prochain créneau devrait être mercredi
        $fromDate = Carbon::create(2024, 6, 11, 0, 0, 0); // Mardi

        $nextSlot = $this->service->for($this->schedulable)->nextAvailableSlot($fromDate);

        $this->assertInstanceOf(Carbon::class, $nextSlot);
        $this->assertEquals('2024-06-12 09:00:00', $nextSlot->format('Y-m-d H:i:s')); // Mercredi suivant
    }

    public function test_get_available_slots_with_default_interval()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday', 'wednesday'],
        ]);

        $startDate = Carbon::create(2024, 6, 10, 0, 0, 0); // Lundi
        $endDate = Carbon::create(2024, 6, 19, 23, 59, 59); // Mercredi semaine suivante

        // Avec intervalle de 30 minutes par défaut, on aura plusieurs créneaux par jour
        // De 9h à 12h avec durée de 60 min = 3 créneaux possibles (9h-10h, 9h30-10h30, 10h-11h, 10h30-11h30, 11h-12h)
        // En réalité avec l'algorithme actuel : 9h-10h, 9h30-10h30, 10h-11h, 10h30-11h30, 11h-12h = 5 créneaux
        // Sur 2 jours = 10 créneaux, sur 4 jours = 20 créneaux
        $slots = $this->service->for($this->schedulable)->availableSlots($startDate, $endDate, 60); // intervalle par défaut 30 min

        $this->assertIsArray($slots);

        // Avec intervalle de 30 min et durée de 60 min, de 9h à 12h :
        // Créneaux possibles : 9h-10h, 9h30-10h30, 10h-11h, 10h30-11h30, 11h-12h = 5 créneaux par jour
        // Sur 4 jours (2 lundis + 2 mercredis) = 20 créneaux
        $this->assertCount(20, $slots);

        // Vérifier le premier créneau
        $slot = $slots[0];
        $this->assertArrayHasKey('start', $slot);
        $this->assertArrayHasKey('end', $slot);
        $this->assertArrayHasKey('type', $slot);
        $this->assertArrayHasKey('availability_id', $slot);
        $this->assertInstanceOf(Carbon::class, $slot['start']);
        $this->assertInstanceOf(Carbon::class, $slot['end']);
        $this->assertEquals('09:00', $slot['start']->format('H:i'));
        $this->assertEquals('10:00', $slot['end']->format('H:i'));
    }

    public function test_get_available_slots_with_custom_interval()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday', 'wednesday'],
        ]);

        $startDate = Carbon::create(2024, 6, 10, 0, 0, 0); // Lundi
        $endDate = Carbon::create(2024, 6, 19, 23, 59, 59); // Mercredi semaine suivante

        // Avec intervalle de 120 min et durée de 60 min
        // De 9:00 à 12:00 = 3 heures
        // Créneaux : 9:00-10:00, 11:00-12:00 = 2 créneaux par jour
        // Sur 4 jours = 8 créneaux
        $slots = $this->service->for($this->schedulable)->availableSlots($startDate, $endDate, 60, 120);

        $this->assertIsArray($slots);

        // Correction : 4 jours × 2 créneaux = 8 créneaux
        $this->assertCount(8, $slots);

        // Vérifier les heures des créneaux
        $expectedTimes = ['09:00', '11:00']; // Heures de début attendues

        foreach ($slots as $index => $slot) {
            $this->assertArrayHasKey('start', $slot);
            $this->assertArrayHasKey('end', $slot);

            $hourStart = $slot['start']->format('H:i');
            $hourEnd = $slot['end']->format('H:i');

            // Vérifier que l'heure de début est soit 9:00 soit 11:00
            $this->assertContains($hourStart, $expectedTimes);

            // Vérifier la durée
            $expectedEnd = Carbon::parse($hourStart)->addMinutes(60)->format('H:i');
            $this->assertEquals($expectedEnd, $hourEnd);
        }
    }

    public function test_get_available_slots_for_date_range()
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday', 'wednesday'],
        ]);

        $startDate = Carbon::create(2024, 6, 10, 0, 0, 0); // Lundi
        $endDate = Carbon::create(2024, 6, 19, 23, 59, 59); // Mercredi semaine suivante

        // Intervalle égal à la durée (60 min)
        // De 9:00 à 12:00 = 3 heures
        // Créneaux : 9:00-10:00, 10:00-11:00, 11:00-12:00 = 3 créneaux par jour
        // Sur 4 jours = 12 créneaux
        $slots = $this->service->for($this->schedulable)->availableSlots($startDate, $endDate, 60, 60);

        $this->assertIsArray($slots);

        // Correction : 4 jours × 3 créneaux = 12 créneaux
        $this->assertCount(12, $slots);

        // Vérifier le format et les heures
        $expectedTimes = ['09:00', '10:00', '11:00']; // Heures de début attendues

        foreach ($slots as $slot) {
            $this->assertArrayHasKey('start', $slot);
            $this->assertArrayHasKey('end', $slot);
            $this->assertArrayHasKey('type', $slot);
            $this->assertArrayHasKey('availability_id', $slot);
            $this->assertInstanceOf(Carbon::class, $slot['start']);
            $this->assertInstanceOf(Carbon::class, $slot['end']);
            $this->assertEquals('consultation', $slot['type']);
            $this->assertEquals($availability->id, $slot['availability_id']);

            // Vérifier que l'heure de début est l'une des heures attendues
            $hourStart = $slot['start']->format('H:i');
            $this->assertContains($hourStart, $expectedTimes);

            // Vérifier la durée
            $expectedEnd = Carbon::parse($hourStart)->addMinutes(60)->format('H:i');
            $this->assertEquals($expectedEnd, $slot['end']->format('H:i'));
        }

        // Compter les créneaux par jour
        $slotsByDay = [];
        foreach ($slots as $slot) {
            $date = $slot['start']->format('Y-m-d');
            if (!isset($slotsByDay[$date])) {
                $slotsByDay[$date] = 0;
            }
            $slotsByDay[$date]++;
        }

        // Vérifier qu'on a 3 créneaux par jour
        foreach ($slotsByDay as $date => $count) {
            $this->assertEquals(3, $count, "Date {$date} devrait avoir 3 créneaux, a {$count}");
        }
    }

    public function test_throws_exception_when_no_schedulable_specified()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No schedulable specified. Use for() method first.');

        $this->service->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);
    }
}
