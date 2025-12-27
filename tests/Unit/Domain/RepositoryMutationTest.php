<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Exceptions\InvalidOwnerException;
use Roster\Exceptions\MissingOwnerException;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Schedule as ScheduleModel;
use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ScheduleRepository;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Test suite for repository mutation protection system.
 *
 * Validates that repositories properly enforce mutation context,
 * owner requirements, and schedulable relationships.
 */
final class RepositoryMutationTest extends TestCase
{
    use RefreshDatabase;

    private Model $testSchedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Test repository can create availability through service.
     */
    public function test_repository_can_create_availability(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availabilityData = [
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ];

        // Act
        $availability = availability_for($this->testSchedulable)->create($availabilityData);

        // Assert
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
    }

    /**
     * Test repository can update availability through service.
     */
    public function test_repository_can_update_availability(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        // Act
        $updated = availability_for($this->testSchedulable)->update($availability->id, [
            'daily_start' => '10:00:00',
        ]);

        // Assert
        $this->assertTrue($updated);
        $this->assertDatabaseHas('roster_availabilities', [
            'id' => $availability->id,
            'daily_start' => '10:00:00',
        ]);
    }

    /**
     * Test repository can delete availability through service.
     */
    public function test_repository_can_delete_availability(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        // Act
        $deleted = availability_for($this->testSchedulable)->delete($availability->id);

        // Assert
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_availabilities', ['id' => $availability->id]);
    }

    /**
     * Test direct model deletion is still forbidden.
     */
    public function test_direct_delete_still_forbidden(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        // Assert
        $this->expectException(ForbiddenModelMutationException::class);

        // Act
        $availability->delete();
    }

    /**
     * Test availability repository throws exception when owner is provided.
     */
    public function test_availability_throws_exception_when_owner_provided(): void
    {
        // Arrange
        $startDate1 = now()->addDay()->startOfDay();
        $endDate1 = now()->addDays(30)->startOfDay();
        $day1 = strtolower($startDate1->format('l'));

        availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day1],
            'validity_start' => $startDate1->format('Y-m-d'),
            'validity_end' => $endDate1->format('Y-m-d'),
        ]);

        $startDate2 = now()->addDays(2)->startOfDay();
        $endDate2 = now()->addDays(32)->startOfDay();
        $day2 = strtolower($startDate2->format('l'));

        $availability2 = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => [$day2],
            'validity_start' => $startDate2->format('Y-m-d'),
            'validity_end' => $endDate2->format('Y-m-d'),
        ]);

        $availabilityRepository = app(AvailabilityRepository::class);

        // Assert
        $this->expectException(InvalidOwnerException::class);

        // Act
        $availabilityRepository->all(
            schedulable: $this->testSchedulable,
            owner: $availability2
        );
    }

    /**
     * Test schedule repository requires owner parameter.
     */
    public function test_schedule_requires_owner(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $scheduleRepository = app(ScheduleRepository::class);

        // Assert
        $this->expectException(MissingOwnerException::class);

        // Act
        $scheduleRepository->create(
            data: [
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ],
            schedulable: $this->testSchedulable
        );
    }

    /**
     * Test schedule can be created with owner through service.
     */
    public function test_schedule_can_be_created_with_owner(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $scheduleData = [
            'title' => 'Test Schedule',
            'start_datetime' => $startDate->copy()->setTime(10, 0),
            'end_datetime' => $startDate->copy()->setTime(11, 0),
        ];

        // Act
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertSame($availability->id, $schedule->availability_id);
        $this->assertSame($this->testSchedulable->id, $schedule->schedulable_id);
    }

    /**
     * Test schedule update with owner through service.
     */
    public function test_schedule_update_with_owner(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => $startDate->copy()->setTime(10, 0),
            'end_datetime' => $startDate->copy()->setTime(11, 0),
        ]);

        // Act
        $updated = schedule_for($availability)->update($schedule->id, [
            'title' => 'Updated Schedule',
        ]);

        // Assert
        $this->assertTrue($updated);
        $this->assertDatabaseHas('roster_schedules', [
            'id' => $schedule->id,
            'title' => 'Updated Schedule',
            'availability_id' => $availability->id,
        ]);
    }

    /**
     * Test schedule deletion with owner through service.
     */
    public function test_schedule_delete_with_owner(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => $startDate->copy()->setTime(10, 0),
            'end_datetime' => $startDate->copy()->setTime(11, 0),
        ]);

        // Act
        $deleted = schedule_for($availability)->delete($schedule->id);

        // Assert
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test finding schedule without owner throws exception.
     */
    public function test_find_schedule_without_owner_throws_exception(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => $startDate->copy()->setTime(10, 0),
            'end_datetime' => $startDate->copy()->setTime(11, 0),
        ]);

        $scheduleRepository = app(ScheduleRepository::class);

        // Assert
        $this->expectException(MissingOwnerException::class);

        // Act
        $scheduleRepository->find(
            id: $schedule->id,
            schedulable: $this->testSchedulable
        );
    }

    /**
     * Test finding schedule with owner succeeds.
     */
    public function test_find_schedule_with_owner_succeeds(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Test Schedule',
            'start_datetime' => $startDate->copy()->setTime(10, 0),
            'end_datetime' => $startDate->copy()->setTime(11, 0),
        ]);

        $scheduleRepository = app(ScheduleRepository::class);

        // Act
        $foundSchedule = $scheduleRepository->find(
            id: $schedule->id,
            schedulable: $this->testSchedulable,
            owner: $availability
        );

        // Assert
        $this->assertInstanceOf(ScheduleModel::class, $foundSchedule);
        $this->assertSame($schedule->id, $foundSchedule->id);
    }

    /**
     * Test retrieving all schedules without owner throws exception.
     */
    public function test_all_schedules_without_owner_throws_exception(): void
    {
        // Arrange
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        availability_for($this->testSchedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $scheduleRepository = app(ScheduleRepository::class);

        // Assert
        $this->expectException(MissingOwnerException::class);

        // Act
        $scheduleRepository->all(schedulable: $this->testSchedulable);
    }
}
