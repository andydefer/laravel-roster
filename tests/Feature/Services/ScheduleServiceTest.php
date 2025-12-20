<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Services\ScheduleService;
use Tests\TestCase;

/**
 * Test suite for ScheduleService functionality
 */
final class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleService $scheduleService;

    private Model $testModel;

    private Availability $consultationAvailability;

    private Availability $trainingAvailability;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestModel();
        $this->createAvailabilities();
        $this->initializeScheduleService();
    }

    /**
     * Creates a test model instance for testing
     */
    private function createTestModel(): void
    {
        $this->testModel = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        $this->testModel->id = 1;
        $this->testModel->save();
    }

    /**
     * Creates test availability records
     */
    private function createAvailabilities(): void
    {
        $this->consultationAvailability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        $this->trainingAvailability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'training',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);
    }

    /**
     * Initializes the schedule service for testing
     */
    private function initializeScheduleService(): void
    {
        $this->scheduleService = app(ScheduleService::class);
        $this->scheduleService->for($this->testModel);
    }

    /**
     * Tests successful schedule creation
     */
    public function test_create_schedule_successfully(): void
    {
        $scheduleData = [
            'title' => 'Test Consultation',
            'description' => 'Test description',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
            'metadata' => ['notes' => 'Test notes'],
        ];

        $schedule = $this->scheduleService->create($this->consultationAvailability, $scheduleData);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Test Consultation', $schedule->title);
        $this->assertSame($this->consultationAvailability->id, $schedule->availability_id);

        $this->assertDatabaseHas('roster_schedules', [
            'title' => 'Test Consultation',
            'availability_id' => $this->consultationAvailability->id,
        ]);
    }

    /**
     * Tests that availability is required for schedule creation workflow
     */
    public function test_availability_is_required_for_schedule_creation_workflow(): void
    {
        $scheduleWithAvailability = $this->scheduleService->create($this->consultationAvailability, [
            'title' => 'Schedule avec Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ]);

        $this->assertNotNull($scheduleWithAvailability->availability_id);
        $this->assertSame($this->consultationAvailability->id, $scheduleWithAvailability->availability_id);

        $this->expectException(BadMethodCallException::class);
        $this->scheduleService->create([
            'title' => 'Schedule sans Availability',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ]);
    }

    /**
     * Tests that schedule type filters availability correctly
     */
    public function test_create_schedule_with_type_filters_availability(): void
    {
        $scheduleData = [
            'title' => 'Training Session',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
        ];

        $schedule = $this->scheduleService->create($this->trainingAvailability, $scheduleData);

        $this->assertSame($this->trainingAvailability->id, $schedule->availability_id);
        $this->assertSame('training', $schedule->type);
    }

    /**
     * Tests that schedule creation with invalid availability throws exception
     */
    public function test_create_schedule_with_invalid_availability_throws_exception(): void
    {
        $otherModel = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $otherModel->id = 2;
        $otherModel->save();

        $scheduleData = [
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided availability does not belong to this schedulable');

        $this->scheduleService->for($otherModel);
        $this->scheduleService->create($this->consultationAvailability, $scheduleData);
    }

    /**
     * Tests that schedule cannot be created without availability parameter
     */
    public function test_cannot_create_schedule_without_availability(): void
    {
        $scheduleData = [
            'title' => 'Schedule sans Availability',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        $this->scheduleService->create($scheduleData);
    }

    /**
     * Tests that null availability parameter throws exception
     */
    public function test_cannot_create_schedule_with_null_availability(): void
    {
        $scheduleData = [
            'title' => 'Schedule avec Availability null',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid arguments for create method');

        $this->scheduleService->create(null, $scheduleData);
    }

    /**
     * Tests that invalid availability type parameter throws exception
     */
    public function test_cannot_create_schedule_with_invalid_availability_type(): void
    {
        $scheduleData = [
            'title' => "Schedule avec mauvais type d'Availability",
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid arguments for create method');

        $this->scheduleService->create(['type' => 'consultation'], $scheduleData);
    }

    /**
     * Tests database constraint for availability_id field
     */
    public function test_schedule_requires_availability_id_in_database(): void
    {
        $scheduleData = [
            'title' => 'Schedule sans availability_id',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ];

        try {
            $schedule = Schedule::create($scheduleData);
            $this->assertNull($schedule->availability_id, "Le schedule ne devrait pas avoir d'availability_id");
        } catch (Exception $exception) {
            $this->assertStringContainsString('availability_id', $exception->getMessage());
        }
    }

    /**
     * Tests that schedule title can be different from availability type
     */
    public function test_schedule_title_can_be_different_from_availability(): void
    {
        $scheduleData = [
            'title' => 'Réunion de projet - Sprint 15',
            'description' => 'Discussion sur les objectifs du sprint',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:30:00',
            'status' => 'booked',
            'metadata' => [
                'project' => 'Système de réservation',
                'attendees' => ['Alice', 'Bob', 'Charlie'],
            ],
        ];

        $schedule = $this->scheduleService->create($this->consultationAvailability, $scheduleData);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('Réunion de projet - Sprint 15', $schedule->title);
        $this->assertNotSame($this->consultationAvailability->type, $schedule->title);
        $this->assertSame('consultation', $schedule->type);
    }

    /**
     * Tests successful schedule update
     */
    public function test_update_schedule_successfully(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Original Title',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $updated = $this->scheduleService->update($schedule->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($updated);

        $schedule->refresh();
        $this->assertSame('Updated Title', $schedule->title);
        $this->assertSame('Updated description', $schedule->description);
    }

    /**
     * Tests successful schedule deletion
     */
    public function test_delete_schedule(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $deleted = $this->scheduleService->delete($schedule->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Tests schedule retrieval by ID
     */
    public function test_find_schedule_by_id(): void
    {
        $schedule = Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        $found = $this->scheduleService->find($schedule->id);

        $this->assertInstanceOf(Schedule::class, $found);
        $this->assertSame($schedule->id, $found->id);
        $this->assertSame('Test Schedule', $found->title);
    }

    /**
     * Tests time slot availability checking
     */
    public function test_is_time_slot_available(): void
    {
        Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Blocked Schedule',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $availableStart = Carbon::parse('2038-06-07 09:00:00');
        $availableEnd = Carbon::parse('2038-06-07 09:30:00');
        $this->assertTrue($this->scheduleService->isTimeSlotAvailable($availableStart, $availableEnd, 'consultation'));

        $blockedStart = Carbon::parse('2038-06-07 10:00:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:30:00');
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable($blockedStart, $blockedEnd, 'consultation'));

        $overlapStart = Carbon::parse('2038-06-07 10:30:00');
        $overlapEnd = Carbon::parse('2038-06-07 11:30:00');
        $this->assertFalse($this->scheduleService->isTimeSlotAvailable($overlapStart, $overlapEnd, 'consultation'));
    }

    /**
     * Tests next available slot finding
     */
    public function test_find_next_available_slot(): void
    {
        Carbon::setTestNow('2038-06-06 08:00:00');

        Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Blocked',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'booked',
        ]);

        $nextSlot = $this->scheduleService->findNextAvailableSlot(60, 'consultation');

        $this->assertIsArray($nextSlot);
        $this->assertArrayHasKey('start', $nextSlot);
        $this->assertArrayHasKey('end', $nextSlot);
        $this->assertSame('2038-06-07 09:00:00', $nextSlot['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $nextSlot['end']->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    /**
     * Tests schedule retrieval within specific time period
     */
    public function test_between_method_returns_schedules_in_period(): void
    {
        Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Schedule 1',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Schedule 2',
            'start_datetime' => '2038-06-07 14:00:00',
            'end_datetime' => '2038-06-07 15:00:00',
            'status' => 'available',
        ]);

        Schedule::create([
            'availability_id' => $this->consultationAvailability->id,
            'title' => 'Schedule 3',
            'start_datetime' => '2038-06-14 10:00:00',
            'end_datetime' => '2038-06-14 11:00:00',
            'status' => 'available',
        ]);

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        /** @var Collection<Schedule> $schedules */
        $schedules = $this->scheduleService->between($start, $end);

        $this->assertCount(2, $schedules);
        $this->assertSame('Schedule 1', $schedules[0]->title);
        $this->assertSame('Schedule 2', $schedules[1]->title);
    }

    /**
     * Tests that old deprecated create method throws exception
     */
    public function test_old_create_method_is_deprecated(): void
    {
        $scheduleData = [
            'title' => 'Test Consultation',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.');

        $this->scheduleService->create($scheduleData);
    }
}
