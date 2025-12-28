<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /**
     * Schedulable model instance for testing.
     */
    private Model $schedulableModel;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->schedulableModel = TestSchedulable::create();
    }

    /**
     * Test repository can create availability through service.
     */
    public function test_repository_can_create_availability(): void
    {
        // Arrange: Prepare availability data
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

        // Act: Create availability through service
        $availability = availability_for($this->schedulableModel)->create($availabilityData);

        // Assert: Availability created with correct relationships
        $this->assertInstanceOf(AvailabilityModel::class, $availability);
        $this->assertSame($this->schedulableModel->id, $availability->schedulable_id);
        $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
    }

    /**
     * Test repository can update availability through service.
     */
    public function test_repository_can_update_availability(): void
    {
        // Arrange: Create availability
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        // Act: Update availability through service
        $updated = availability_for($this->schedulableModel)->update($availability->id, [
            'daily_start' => '10:00:00',
        ]);

        // Assert: Update successful and database reflects changes
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
        // Arrange: Create availability
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        // Act: Delete availability through service
        $deleted = availability_for($this->schedulableModel)->delete($availability->id);

        // Assert: Soft delete successful
        $this->assertTrue($deleted);
        $this->assertSoftDeleted('roster_availabilities', [
            'id' => $availability->id,
        ]);

        // Verify record exists in trash
        $trashed = AvailabilityModel::withTrashed()->find($availability->id);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }

    /**
     * Test direct model deletion is still forbidden.
     */
    public function test_direct_delete_still_forbidden(): void
    {
        // Arrange: Create availability
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        // Act & Assert: Direct model deletion should fail
        $this->expectException(ForbiddenModelMutationException::class);
        $availability->delete();
    }

    /**
     * Test availability repository throws exception when owner is provided.
     */
    public function test_availability_throws_exception_when_owner_provided(): void
    {
        // Arrange: Create multiple availabilities
        $firstStartDate = now()->addDay()->startOfDay();
        $firstEndDate = now()->addDays(30)->startOfDay();
        $firstDay = strtolower($firstStartDate->format('l'));

        availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$firstDay],
            'validity_start' => $firstStartDate->format('Y-m-d'),
            'validity_end' => $firstEndDate->format('Y-m-d'),
        ]);

        $secondStartDate = now()->addDays(2)->startOfDay();
        $secondEndDate = now()->addDays(32)->startOfDay();
        $secondDay = strtolower($secondStartDate->format('l'));

        $secondAvailability = availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => [$secondDay],
            'validity_start' => $secondStartDate->format('Y-m-d'),
            'validity_end' => $secondEndDate->format('Y-m-d'),
        ]);

        $availabilityRepository = app(AvailabilityRepository::class);

        // Act & Assert: Availability repository should reject owner parameter
        $this->expectException(InvalidOwnerException::class);
        $availabilityRepository->all(
            schedulable: $this->schedulableModel,
            owner: $secondAvailability
        );
    }

    /**
     * Test schedule repository requires owner parameter.
     */
    public function test_schedule_requires_owner(): void
    {
        // Arrange: Create availability
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $scheduleRepository = app(ScheduleRepository::class);

        // Act & Assert: Schedule creation without owner should fail
        $this->expectException(MissingOwnerException::class);
        $scheduleRepository->create(
            data: [
                'title' => 'Test Schedule',
                'start_datetime' => $startDate->copy()->setTime(10, 0),
                'end_datetime' => $startDate->copy()->setTime(11, 0),
            ],
            schedulable: $this->schedulableModel
        );
    }

    /**
     * Test schedule can be created with owner through service.
     */
    public function test_schedule_can_be_created_with_owner(): void
    {
        // Arrange: Create availability and schedule data
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
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

        // Act: Create schedule through service
        $schedule = schedule_for($availability)->create($scheduleData);

        // Assert: Schedule created with correct relationships
        $this->assertInstanceOf(ScheduleModel::class, $schedule);
        $this->assertSame($availability->id, $schedule->availability_id);
        $this->assertSame($this->schedulableModel->id, $schedule->schedulable_id);
    }

    /**
     * Test schedule update with owner through service.
     */
    public function test_schedule_update_with_owner(): void
    {
        // Arrange: Create availability and schedule
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
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

        // Act: Update schedule through service
        $updated = schedule_for($availability)->update($schedule->id, [
            'title' => 'Updated Schedule',
        ]);

        // Assert: Update successful and database reflects changes
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
        // Arrange: Create availability and schedule
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
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

        // Act: Delete schedule through service
        $deleted = schedule_for($availability)->delete($schedule->id);

        // Assert: Soft delete successful
        $this->assertTrue($deleted);
        $this->assertSoftDeleted('roster_schedules', [
            'id' => $schedule->id,
        ]);

        // Verify record exists in trash
        $trashed = ScheduleModel::withTrashed()->find($schedule->id);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }

    /**
     * Test finding schedule without owner throws exception.
     */
    public function test_find_schedule_without_owner_throws_exception(): void
    {
        // Arrange: Create availability and schedule
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
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

        // Act & Assert: Schedule find without owner should fail
        $this->expectException(MissingOwnerException::class);
        $scheduleRepository->find(
            id: $schedule->id,
            schedulable: $this->schedulableModel
        );
    }

    /**
     * Test finding schedule with owner succeeds.
     */
    public function test_find_schedule_with_owner_succeeds(): void
    {
        // Arrange: Create availability and schedule
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        $availability = availability_for($this->schedulableModel)->create([
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

        // Act: Find schedule with owner through repository
        $foundSchedule = $scheduleRepository->find(
            id: $schedule->id,
            schedulable: $this->schedulableModel,
            owner: $availability
        );

        // Assert: Schedule found successfully
        $this->assertInstanceOf(ScheduleModel::class, $foundSchedule);
        $this->assertSame($schedule->id, $foundSchedule->id);
    }

    /**
     * Test retrieving all schedules without owner throws exception.
     */
    public function test_all_schedules_without_owner_throws_exception(): void
    {
        // Arrange: Create availability
        $startDate = now()->addDay()->startOfDay();
        $endDate = now()->addDays(30)->startOfDay();
        $day = strtolower($startDate->format('l'));

        availability_for($this->schedulableModel)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => [$day],
            'validity_start' => $startDate->format('Y-m-d'),
            'validity_end' => $endDate->format('Y-m-d'),
        ]);

        $scheduleRepository = app(ScheduleRepository::class);

        // Act & Assert: Schedule retrieval without owner should fail
        $this->expectException(MissingOwnerException::class);
        $scheduleRepository->all(schedulable: $this->schedulableModel);
    }
}
