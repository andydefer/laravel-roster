<?php

namespace Roster\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Services\AvailabilityValidator;
use Roster\Tests\TestCase;

// Création d'un modèle factice pour les tests
class TestSchedulable extends Model
{
    protected $table = 'test_schedulables';

    protected $fillable = ['name'];

    // Utiliser le trait Schedulable si nécessaire
    use \Roster\Traits\HasRoster;
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

        $validator = new AvailabilityValidator;
        $this->service = new AvailabilityService($validator);
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

    public function test_create_availability_with_overlap_throws_exception()
    {
        // Créer une première disponibilité
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Tentative de création d'une disponibilité qui chevauche
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
        ]);
    }

    public function test_create_availability_without_overlap_succeeds()
    {
        // Créer une première disponibilité
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une deuxième disponibilité qui ne chevauche pas
        $availability = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals('14:00', $availability->start_time->format('H:i'));
        $this->assertEquals('17:00', $availability->end_time->format('H:i'));
    }

    public function test_create_availability_with_overlap_on_different_days_succeeds()
    {
        // Créer une disponibilité le lundi
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une disponibilité avec mêmes horaires mais le mardi (pas de chevauchement)
        $availability = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['tuesday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals(['tuesday'], $availability->days);
    }

    public function test_update_availability_with_overlap_throws_exception()
    {
        // Créer deux disponibilités
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // Tentative de mise à jour pour chevaucher la première
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->service->for($this->schedulable)->update($availability2->id, [
            'start_time' => '10:00',
            'end_time' => '12:00',
            'days' => ['monday'], // Ajouter les jours pour la vérification
        ]);
    }

    public function test_update_availability_without_overlap_succeeds()
    {
        // Créer deux disponibilités
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // Mise à jour qui ne chevauche pas
        $result = $this->service->for($this->schedulable)->update($availability2->id, [
            'start_time' => '16:00',
            'end_time' => '18:00',
            'days' => ['monday'], // Ajouter les jours pour la vérification
        ]);

        $this->assertTrue($result);

        $availability2->refresh();
        $this->assertEquals('16:00', $availability2->start_time->format('H:i'));
        $this->assertEquals('18:00', $availability2->end_time->format('H:i'));
    }

    public function test_has_overlapping_method_returns_true_when_overlap_exists()
    {
        // Créer une disponibilité
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Vérifier le chevauchement
        $hasOverlap = $this->service->for($this->schedulable)->hasOverlapping([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
        ]);

        $this->assertTrue($hasOverlap);
    }

    public function test_has_overlapping_method_returns_false_when_no_overlap()
    {
        // Créer une disponibilité
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Vérifier l'absence de chevauchement
        $hasOverlap = $this->service->for($this->schedulable)->hasOverlapping([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        $this->assertFalse($hasOverlap);
    }

    public function test_find_overlapping_returns_collection_of_overlapping_availabilities()
    {
        // Créer plusieurs disponibilités
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // availability3 va fusionner avec availability2 car elles sont adjacentes
        $availability3 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '16:00',
            'end_time' => '18:00',
            'days' => ['monday'],
        ]);

        // availability2 a été fusionnée et supprimée
        // Nous avons maintenant availability1 (09:00-11:00) et availability3 (14:00-18:00)

        // Trouver les chevauchements avec une nouvelle disponibilité
        $overlapping = $this->service->for($this->schedulable)->findOverlapping([
            'type' => 'consultation',
            'start_time' => '15:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        // Doit trouver availability3 (14:00-18:00) seulement
        $this->assertCount(1, $overlapping);
        $this->assertTrue($overlapping->contains($availability3));
    }

    public function test_date_range_overlap_validation()
    {
        // Disponibilité avec dates spécifiques
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // Tentative de création avec dates qui chevauchent
        $this->expectException(\InvalidArgumentException::class);

        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
            'start_date' => '2024-04-01',
            'end_date' => '2024-08-31',
        ]);
    }

    public function test_no_date_range_overlap_when_dates_dont_intersect()
    {
        // Disponibilité avec dates spécifiques
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-03-31',
        ]);

        // Création avec dates qui ne chevauchent pas
        $availability = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals('2024-07-01', $availability->start_date->format('Y-m-d'));
    }

    public function test_auto_merge_adjacent_availabilities()
    {
        // Créer une première disponibilité
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une deuxième disponibilité adjacente
        // Elle devrait fusionner avec la première
        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '12:00', // Adjacent à la fin de la première
            'end_time' => '15:00',
            'days' => ['monday'],
        ]);

        // La première disponibilité devrait avoir été fusionnée et supprimée
        $this->assertDatabaseMissing('availabilities', ['id' => $availability1->id]);

        // La deuxième devrait avoir les horaires étendus
        $availability2->refresh();
        $this->assertEquals('09:00', $availability2->start_time->format('H:i'));
        $this->assertEquals('15:00', $availability2->end_time->format('H:i'));
    }

    public function test_find_adjacent_availabilities()
    {
        // Créer plusieurs disponibilités
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // availability2 existe maintenant de 14:00 à 16:00
        // availability3 va fusionner avec availability2
        $availability3 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '16:00', // Adjacent à la fin de availability2
            'end_time' => '18:00',
            'days' => ['monday'],
        ]);

        // availability2 a été fusionnée et supprimée
        // Nous avons maintenant availability1 (09:00-12:00) et availability3 (14:00-18:00)

        // Trouver les adjacentes à une nouvelle disponibilité de 12:00 à 14:00
        $adjacent = $this->service->for($this->schedulable)->findAdjacentAvailabilities([
            'type' => 'consultation',
            'start_time' => '12:00',
            'end_time' => '14:00',
            'days' => ['monday'],
        ]);

        // Doit trouver availability1 (09:00-12:00) et availability3 (14:00-18:00)
        $this->assertCount(2, $adjacent);
        $this->assertTrue($adjacent->contains($availability1));
        // availability2 n'existe plus, vérifions availability3
        $this->assertTrue($adjacent->contains($availability3));
    }

    public function test_different_types_dont_overlap()
    {
        // Créer une disponibilité de type 'consultation'
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Tentative de création d'une disponibilité de type 'meeting' aux mêmes horaires
        // (devrait échouer car les chevauchements sont interdits quel que soit le type)
        $this->expectException(\InvalidArgumentException::class);

        $this->service->for($this->schedulable)->create([
            'type' => 'meeting',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);
    }

    public function test_adjacent_availabilities_with_different_types_not_merged()
    {
        // Créer une disponibilité de type 'consultation'
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une disponibilité adjacente de type différent
        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'meeting', // Type différent
            'start_time' => '12:00',
            'end_time' => '15:00',
            'days' => ['monday'],
        ]);

        // Les deux devraient exister séparément (pas de fusion car types différents)
        $this->assertDatabaseHas('availabilities', ['id' => $availability1->id]);
        $this->assertDatabaseHas('availabilities', ['id' => $availability2->id]);

        // Vérifier qu'elles n'ont pas été fusionnées
        $availability2->refresh();
        $this->assertEquals('12:00', $availability2->start_time->format('H:i'));
        $this->assertEquals('15:00', $availability2->end_time->format('H:i'));
    }

    public function test_adjacent_availabilities_with_different_days_not_merged()
    {
        // Créer une disponibilité le lundi
        $availability1 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une disponibilité adjacente le mardi
        $availability2 = $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '12:00',
            'end_time' => '15:00',
            'days' => ['tuesday'], // Jour différent
        ]);

        // Les deux devraient exister séparément
        $this->assertDatabaseHas('availabilities', ['id' => $availability1->id]);
        $this->assertDatabaseHas('availabilities', ['id' => $availability2->id]);
    }

    public function test_create_availability_throws_exception_when_end_time_before_start_time()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End time must be after start time');

        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '20:00',
            'end_time' => '19:00',
            'days' => ['monday'],
        ]);
    }

    public function test_overlap_with_unlimited_date_range()
    {
        // Créer une disponibilité sans dates limites
        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Tentative de création avec dates spécifiques (devrait chevaucher car la première est illimitée)
        $this->expectException(\InvalidArgumentException::class);

        $this->service->for($this->schedulable)->create([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
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
