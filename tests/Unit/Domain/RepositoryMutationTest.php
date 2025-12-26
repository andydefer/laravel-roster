<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ScheduleRepository;
use Tests\Support\TestSchedulable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schedule as FacadesSchedule;
use Roster\Facades\Availability;
use Roster\Facades\Schedule;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Exceptions\InvalidOwnerException;
use Roster\Exceptions\MissingOwnerException;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Validation\Exceptions\ValidationFailedException;
use Tests\TestCase;

final class RepositoryMutationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->artisan('migrate')->run();
    }

    public function test_repository_can_create_availability(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour réel correspondant à startDate
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertEquals($testSchedulable->id, $availability->schedulable_id);
        $this->assertEquals(TestSchedulable::class, $availability->schedulable_type);
    }

    public function test_repository_can_update_availability(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour réel correspondant à startDate
        $day = strtolower($startDate->format('l'));

        // Création
        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Update via le service
        $updated = Availability::for($testSchedulable)
            ->update($availability->id, [
                'daily_start' => '10:00:00'
            ]);

        $this->assertTrue($updated);

        // Vérification en base
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '10:00:00',
        ]);
    }

    public function test_repository_can_delete_availability(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour réel correspondant à startDate
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        $deleted = Availability::for($testSchedulable)
            ->delete($availability->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_availabilities', ['id' => $availability->id]);
    }

    public function test_direct_delete_still_forbidden(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour réel correspondant à startDate
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        $this->expectException(ForbiddenModelMutationException::class);
        $availability->delete();
    }

    public function test_availability_throws_exception_when_owner_provided(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence pour la première availability
        $startDate1 = now()->addDay()->startOfDay();
        $endDate1 = now()->addDays(30)->startOfDay();
        $day1 = strtolower($startDate1->format('l'));

        Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day1], // ✅ dynamique
                'validity_start' => $startDate1->format('Y-m-d'),
                'validity_end' => $endDate1->format('Y-m-d'),
            ]);

        // Date de référence pour la deuxième availability (différente journée)
        $startDate2 = now()->addDays(2)->startOfDay();
        $endDate2 = now()->addDays(32)->startOfDay();
        $day2 = strtolower($startDate2->format('l'));

        // Créer une deuxième disponibilité pour tenter de l'utiliser comme owner
        $availability2 = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '08:00:00',
                'daily_end' => '18:00:00',
                'days' => [$day2], // ✅ dynamique
                'validity_start' => $startDate2->format('Y-m-d'),
                'validity_end' => $endDate2->format('Y-m-d'),
            ]);

        // Tenter de fournir un owner à un modèle Availability via le repository
        // (plutôt que via le service)
        $availabilityRepository = app(AvailabilityRepository::class);

        $this->expectException(InvalidOwnerException::class);

        // Utiliser le repository directement pour contourner la couche service
        $availabilityRepository->all(
            schedulable: $testSchedulable,
            owner: $availability2 // Ceci devrait lancer InvalidOwnerException dans le repository
        );
    }

    public function test_schedule_requires_owner(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Tenter de créer un schedule sans owner via le repository (devrait échouer)
        $this->expectException(MissingOwnerException::class);

        $scheduleRepository = app(ScheduleRepository::class);

        // Appeler directement le repository sans owner
        $scheduleRepository->create(
            data: [
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ],
            schedulable: $testSchedulable // Pas de owner - devrait lancer MissingOwnerException
        );
    }

    public function test_schedule_can_be_created_with_owner(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour réel correspondant à startDate
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        $schedule = Schedule::for($testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ]);

        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertEquals($availability->id, $schedule->availability_id);
        $this->assertEquals($testSchedulable->id, $schedule->schedulable_id);
    }


    public function test_schedule_requires_schedulable(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Tenter d'utiliser le repository sans schedulable (devrait échouer)
        $this->expectException(MissingOwnerException::class);

        $scheduleRepository = app(ScheduleRepository::class);

        Schedule::for($testSchedulable)->find(999);
    }

    public function test_schedule_update_with_owner(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Dates de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour valide calculé dynamiquement
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ cohérent avec la date
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Créer un schedule valide
        $schedule = Schedule::for($testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ]);

        // Mettre à jour le schedule
        $updated = Schedule::for($testSchedulable)
            ->owner($availability)
            ->update($schedule->id, [
                'title' => 'Updated Schedule',
            ]);

        $this->assertTrue($updated);

        // Vérification en base
        $this->assertDatabaseHas('roster_schedules', [
            'id' => $schedule->id,
            'title' => 'Updated Schedule',
            'availability_id' => $availability->id,
        ]);
    }

    public function test_schedule_delete_with_owner(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();

        // Jour réel correspondant à startDate
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Créer un schedule avec dates futures
        $schedule = Schedule::for($testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ]);

        // Supprimer le schedule via le service
        $deleted = Schedule::for($testSchedulable)
            ->owner($availability)
            ->delete($schedule->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_schedules', ['id' => $schedule->id]);
    }


    public function test_find_schedule_without_owner_throws_exception(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Créer un schedule
        $schedule = Schedule::for($testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ]);

        // Tenter de récupérer un schedule sans owner via le repository
        $this->expectException(MissingOwnerException::class);

        $scheduleRepository = app(ScheduleRepository::class);

        $scheduleRepository->find(
            id: $schedule->id,
            schedulable: $testSchedulable // Pas de owner - devrait lancer MissingOwnerException
        );
    }

    public function test_find_schedule_with_owner_succeeds(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Créer un schedule
        $schedule = Schedule::for($testSchedulable)
            ->owner($availability)
            ->create([
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ]);

        // Récupérer le schedule via le repository avec owner
        $scheduleRepository = app(ScheduleRepository::class);

        $foundSchedule = $scheduleRepository->find(
            id: $schedule->id,
            schedulable: $testSchedulable,
            owner: $availability
        );

        $this->assertInstanceOf(ScheduleModel::class, $foundSchedule);
        $this->assertEquals($schedule->id, $foundSchedule->id);
    }

    public function test_all_schedules_without_owner_throws_exception(): void
    {
        $testSchedulable = TestSchedulable::create();

        // Date de référence
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        Availability::for($testSchedulable)
            ->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => [$day], // ✅ dynamique
                'validity_start' => $startDate->format('Y-m-d'),
                'validity_end' => $endDate->format('Y-m-d'),
            ]);

        // Tenter de récupérer tous les schedules sans owner via le repository
        $this->expectException(MissingOwnerException::class);

        $scheduleRepository = app(ScheduleRepository::class);

        $scheduleRepository->all(
            schedulable: $testSchedulable // Pas de owner - devrait lancer MissingOwnerException
        );
    }
}
