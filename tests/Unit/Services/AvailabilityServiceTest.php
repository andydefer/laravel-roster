<?php

declare(strict_types=1);

namespace Roster\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Services\AvailabilityValidator;
use Roster\Tests\TestCase;

final class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilityService;

    private TestSchedulable $testSchedulable;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer une table factice pour notre modèle de test
        Schema::create('test_schedulables', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $availabilityValidator = new AvailabilityValidator;
        $this->availabilityService = new AvailabilityService($availabilityValidator);
        $this->testSchedulable = TestSchedulable::create(['name' => 'Test Schedulable']);
    }

    public function test_for_method_returns_availability_service_instance(): void
    {
        $availabilityService = $this->availabilityService->for($this->testSchedulable);

        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
        $this->assertSame($this->testSchedulable, $availabilityService->getSchedulable());
    }

    public function test_create_availability_with_valid_data(): void
    {
        $availability = $this->availabilityService->for($this->testSchedulable)->create([
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
        $this->assertEquals($this->testSchedulable->id, $availability->schedulable_id);
        $this->assertEquals(TestSchedulable::class, $availability->schedulable_type);
    }

    public function test_create_availability_without_optional_dates(): void
    {
        $availability = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertNull($availability->start_date);
        $this->assertNull($availability->end_date);
    }

    public function test_create_availability_throws_exception_for_invalid_time_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End time must be after start time');

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '18:00',
            'end_time' => '09:00',
            'days' => ['monday'],
        ]);
    }

    public function test_create_availability_throws_exception_for_empty_days(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one day must be specified');

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => [],
        ]);
    }

    public function test_update_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $updated = $this->availabilityService->for($this->testSchedulable)->update($availability->id, [
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

    public function test_update_availability_not_found(): void
    {
        $result = $this->availabilityService->for($this->testSchedulable)->update(999, [
            'start_time' => '10:00',
        ]);

        $this->assertFalse($result);
    }

    public function test_update_availability_with_invalid_data(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->availabilityService->for($this->testSchedulable)->update($availability->id, [
            'start_time' => '20:00',
            'end_time' => '18:00',
        ]);
    }

    public function test_delete_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $result = $this->availabilityService->for($this->testSchedulable)->delete($availability->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('availabilities', ['id' => $availability->id]);
    }

    public function test_delete_availability_not_found(): void
    {
        $result = $this->availabilityService->for($this->testSchedulable)->delete(999);

        $this->assertFalse($result);
    }

    public function test_find_availability_by_id(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $found = $this->availabilityService->for($this->testSchedulable)->find($availability->id);

        $this->assertInstanceOf(Availability::class, $found);
        $this->assertEquals($availability->id, $found->id);
    }

    public function test_find_availability_not_found(): void
    {
        $found = $this->availabilityService->for($this->testSchedulable)->find(999);

        $this->assertNotInstanceOf(Availability::class, $found);
    }

    public function test_get_all_availabilities(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['tuesday'],
        ]);

        $availabilities = $this->availabilityService->for($this->testSchedulable)->all();

        $this->assertCount(2, $availabilities);
        $this->assertInstanceOf(Availability::class, $availabilities->first());
    }

    public function test_get_availabilities_by_type(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['tuesday'],
        ]);

        $availabilities = $this->availabilityService->for($this->testSchedulable)->whereType('consultation')->get();

        $this->assertCount(1, $availabilities);
        $this->assertEquals('consultation', $availabilities->first()->type);
    }

    public function test_get_availabilities_for_specific_day(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'wednesday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'meeting',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['tuesday', 'thursday'],
        ]);

        $availabilities = $this->availabilityService->for($this->testSchedulable)->whereDay('monday')->get();

        $this->assertCount(1, $availabilities);
        $this->assertContains('monday', $availabilities->first()->days);
    }

    public function test_check_availability_for_specific_datetime(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
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

        $result = $this->availabilityService->for($this->testSchedulable)->isAvailableAt($datetime);

        $this->assertTrue($result);
    }

    public function test_check_availability_for_invalid_datetime(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        // Test avec un datetime invalide (mardi 2024-06-11 10:00)
        $datetime = Carbon::create(2024, 6, 11, 10, 0, 0); // Un mardi

        $result = $this->availabilityService->for($this->testSchedulable)->isAvailableAt($datetime);

        $this->assertFalse($result);
    }

    public function test_check_availability_with_time_outside_range(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        // Test avec un datetime valide pour le jour mais hors horaire
        $datetime = Carbon::create(2024, 6, 10, 8, 0, 0); // Un lundi à 8h

        $result = $this->availabilityService->for($this->testSchedulable)->isAvailableAt($datetime);

        $this->assertFalse($result);
    }

    public function test_get_next_available_slot(): void
    {
        Availability::create([
            'schedulable_id' => $this->testSchedulable->id,
            'schedulable_type' => TestSchedulable::class,
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday', 'wednesday', 'friday'],
        ]);

        // Test un mardi, le prochain créneau devrait être mercredi
        $fromDate = Carbon::create(2024, 6, 11, 0, 0, 0); // Mardi

        $nextSlot = $this->availabilityService->for($this->testSchedulable)->nextAvailableSlot($fromDate);

        $this->assertInstanceOf(Carbon::class, $nextSlot);
        $this->assertSame('2024-06-12 09:00:00', $nextSlot->format('Y-m-d H:i:s')); // Mercredi suivant
    }

    public function test_create_availability_with_overlap_throws_exception(): void
    {
        // Créer une première disponibilité
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Tentative de création d'une disponibilité qui chevauche
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
        ]);
    }

    public function test_create_availability_without_overlap_succeeds(): void
    {
        // Créer une première disponibilité
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une deuxième disponibilité qui ne chevauche pas
        $availability = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals('14:00', $availability->start_time->format('H:i'));
        $this->assertEquals('17:00', $availability->end_time->format('H:i'));
    }

    public function test_create_availability_with_overlap_on_different_days_succeeds(): void
    {
        // Créer une disponibilité le lundi
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une disponibilité avec mêmes horaires mais le mardi (pas de chevauchement)
        $availability = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['tuesday'],
        ]);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertEquals(['tuesday'], $availability->days);
    }

    public function test_update_availability_with_overlap_throws_exception(): void
    {
        // Créer deux disponibilités
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // Tentative de mise à jour pour chevaucher la première
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->availabilityService->for($this->testSchedulable)->update($availability2->id, [
            'start_time' => '10:00',
            'end_time' => '12:00',
            'days' => ['monday'], // Ajouter les jours pour la vérification
        ]);
    }

    public function test_update_availability_without_overlap_succeeds(): void
    {
        // Créer deux disponibilités
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'days' => ['monday'],
        ]);

        $availability2 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // Mise à jour qui ne chevauche pas
        $result = $this->availabilityService->for($this->testSchedulable)->update($availability2->id, [
            'start_time' => '16:00',
            'end_time' => '18:00',
            'days' => ['monday'], // Ajouter les jours pour la vérification
        ]);

        $this->assertTrue($result);

        $availability2->refresh();
        $this->assertEquals('16:00', $availability2->start_time->format('H:i'));
        $this->assertEquals('18:00', $availability2->end_time->format('H:i'));
    }

    public function test_has_overlapping_method_returns_true_when_overlap_exists(): void
    {
        // Créer une disponibilité
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Vérifier le chevauchement
        $hasOverlap = $this->availabilityService->for($this->testSchedulable)->hasOverlapping([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
        ]);

        $this->assertTrue($hasOverlap);
    }

    public function test_has_overlapping_method_returns_false_when_no_overlap(): void
    {
        // Créer une disponibilité
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Vérifier l'absence de chevauchement
        $hasOverlap = $this->availabilityService->for($this->testSchedulable)->hasOverlapping([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        $this->assertFalse($hasOverlap);
    }

    public function test_find_overlapping_returns_collection_of_overlapping_availabilities(): void
    {
        // Créer plusieurs disponibilités
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'days' => ['monday'],
        ]);

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // availability3 va fusionner avec availability2 car elles sont adjacentes
        $availability3 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '16:00',
            'end_time' => '18:00',
            'days' => ['monday'],
        ]);

        // availability2 a été fusionnée et supprimée
        // Nous avons maintenant availability1 (09:00-11:00) et availability3 (14:00-18:00)

        // Trouver les chevauchements avec une nouvelle disponibilité
        $overlapping = $this->availabilityService->for($this->testSchedulable)->findOverlapping([
            'type' => 'consultation',
            'start_time' => '15:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);

        // Doit trouver availability3 (14:00-18:00) seulement
        $this->assertCount(1, $overlapping);
        $this->assertTrue($overlapping->contains($availability3));
    }

    public function test_date_range_overlap_validation(): void
    {
        // Disponibilité avec dates spécifiques
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // Tentative de création avec dates qui chevauchent
        $this->expectException(InvalidArgumentException::class);

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
            'start_date' => '2024-04-01',
            'end_date' => '2024-08-31',
        ]);
    }

    public function test_no_date_range_overlap_when_dates_dont_intersect(): void
    {
        // Disponibilité avec dates spécifiques
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-03-31',
        ]);

        // Création avec dates qui ne chevauchent pas
        $availability = $this->availabilityService->for($this->testSchedulable)->create([
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

    public function test_auto_merge_adjacent_availabilities(): void
    {
        // Créer une première disponibilité
        $availability1 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une deuxième disponibilité adjacente
        // Elle devrait fusionner avec la première
        $availability2 = $this->availabilityService->for($this->testSchedulable)->create([
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

    public function test_find_adjacent_availabilities(): void
    {
        // Créer plusieurs disponibilités
        $availability1 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'days' => ['monday'],
        ]);

        // availability2 existe maintenant de 14:00 à 16:00
        // availability3 va fusionner avec availability2
        $availability3 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '16:00', // Adjacent à la fin de availability2
            'end_time' => '18:00',
            'days' => ['monday'],
        ]);

        // availability2 a été fusionnée et supprimée
        // Nous avons maintenant availability1 (09:00-12:00) et availability3 (14:00-18:00)

        // Trouver les adjacentes à une nouvelle disponibilité de 12:00 à 14:00
        $adjacent = $this->availabilityService->for($this->testSchedulable)->findAdjacentAvailabilities([
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

    public function test_different_types_dont_overlap(): void
    {
        // Créer une disponibilité de type 'consultation'
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Tentative de création d'une disponibilité de type 'meeting' aux mêmes horaires
        // (devrait échouer car les chevauchements sont interdits quel que soit le type)
        $this->expectException(InvalidArgumentException::class);

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'meeting',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);
    }

    public function test_adjacent_availabilities_with_different_types_not_merged(): void
    {
        // Créer une disponibilité de type 'consultation'
        $availability1 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une disponibilité adjacente de type différent
        $availability2 = $this->availabilityService->for($this->testSchedulable)->create([
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

    public function test_adjacent_availabilities_with_different_days_not_merged(): void
    {
        // Créer une disponibilité le lundi
        $availability1 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Créer une disponibilité adjacente le mardi
        $availability2 = $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '12:00',
            'end_time' => '15:00',
            'days' => ['tuesday'], // Jour différent
        ]);

        // Les deux devraient exister séparément
        $this->assertDatabaseHas('availabilities', ['id' => $availability1->id]);
        $this->assertDatabaseHas('availabilities', ['id' => $availability2->id]);
    }

    public function test_create_availability_throws_exception_when_end_time_before_start_time(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End time must be after start time');

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '20:00',
            'end_time' => '19:00',
            'days' => ['monday'],
        ]);
    }

    public function test_overlap_with_unlimited_date_range(): void
    {
        // Créer une disponibilité sans dates limites
        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'days' => ['monday'],
        ]);

        // Tentative de création avec dates spécifiques (devrait chevaucher car la première est illimitée)
        $this->expectException(InvalidArgumentException::class);

        $this->availabilityService->for($this->testSchedulable)->create([
            'type' => 'consultation',
            'start_time' => '10:00',
            'end_time' => '13:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
    }

    public function test_throws_exception_when_no_schedulable_specified(): void
    {
        $this->expectException(MissingSchedulableException::class);
        $this->expectExceptionMessage('No schedulable specified. Use the for() method before executing the query.');

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'days' => ['monday'],
        ]);
    }
}
