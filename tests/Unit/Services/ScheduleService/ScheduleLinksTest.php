<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ScheduleService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Roster\Enums\ScheduleStatus;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Tests for basic polymorphic link operations.
 */
final class ScheduleLinksTest extends TestCase
{
    use RefreshDatabase;

    /** @var Model The schedulable model used for testing */
    private Model $schedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = TestSchedulable::create();

        Config::set('roster.durations.default_slot_interval_minutes', 15);
        Config::set('roster.durations.max_search_period_days', 30);
    }

    /**
     * Test setting current schedule for link operations.
     */
    public function test_set_current_schedule_for_link_operations(): void
    {
        // Arrange: Create schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for linking',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Set current schedule
        $service = schedule_for($availability)->schedule($schedule);

        // Assert: Should return service instance with schedule set
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);

        // Note: The currentSchedule property is private, so we test through public API
        // Attempt to use a link operation which would fail without schedule
        $this->expectException(\RuntimeException::class);

        // Create new service without schedule set and try link operation
        $serviceWithoutSchedule = schedule_for($availability);
        $serviceWithoutSchedule->attach(TestSchedulable::create());
    }

    /**
     * Test attaching a model to schedule.
     */
    public function test_attach_model_to_schedule(): void
    {
        // Arrange: Create schedule and model to attach
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for linking',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $modelToAttach = TestSchedulable::create(['name' => 'Resource 1']);

        // Act: Attach model with metadata
        $service = schedule_for($availability)
            ->schedule($schedule)
            ->attach($modelToAttach, ['role' => 'participant', 'priority' => 'high']);

        // Assert: Should return service instance for chaining
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
    }

    /**
     * Test attaching multiple models to schedule.
     */
    public function test_attach_multiple_models_to_schedule(): void
    {
        // Arrange: Create schedule and models
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for multiple links',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $models = [
            TestSchedulable::create(['name' => 'Resource 1']),
            TestSchedulable::create(['name' => 'Resource 2']),
        ];

        // Act: Attach multiple models
        $service = schedule_for($availability)
            ->schedule($schedule)
            ->attachMany($models, ['type' => 'required']);

        // Assert: Should return service instance for chaining
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
    }

    /**
     * Test detaching a model from schedule.
     */
    public function test_detach_model_from_schedule(): void
    {
        // Arrange: Create schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for detach test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $modelToDetach = TestSchedulable::create(['name' => 'Resource to detach']);

        // Act: Detach model
        $service = schedule_for($availability)
            ->schedule($schedule)
            ->detach($modelToDetach);

        // Assert: Should return service instance
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
    }

    /**
     * Test checking if model is attached to schedule.
     */
    public function test_has_attached_model(): void
    {
        // Arrange: Create schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for attachment check',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $model = TestSchedulable::create(['name' => 'Test Model']);

        // Act: Check if model is attached (initially should be false)
        $hasAttached = schedule_for($availability)
            ->schedule($schedule)
            ->hasAttached($model);

        // Assert: Should return boolean
        $this->assertIsBool($hasAttached);
        $this->assertFalse($hasAttached);
    }

    /**
     * Test getting attached models from schedule.
     */
    public function test_get_attached_models(): void
    {
        // Arrange: Create schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for get attached test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Get attached models (empty initially)
        $attached = schedule_for($availability)
            ->schedule($schedule)
            ->getAttached();

        // Assert: Should return Collection
        $this->assertInstanceOf(Collection::class, $attached);
        $this->assertCount(0, $attached);
    }

    /**
     * Test synchronizing attached models.
     */
    public function test_sync_attached_models(): void
    {
        // Arrange: Create schedule and models
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for sync test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $models = [
            TestSchedulable::create(['name' => 'Resource 1']),
            TestSchedulable::create(['name' => 'Resource 2']),
        ];

        // Act: Synchronize models
        $service = schedule_for($availability)
            ->schedule($schedule)
            ->sync($models, ['batch' => 'initial']);

        // Assert: Should return service instance
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
    }

    /**
     * Test that link operations require current schedule to be set.
     */
    public function test_link_operations_require_current_schedule(): void
    {
        // Arrange: Create service without setting schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $model = TestSchedulable::create(['name' => 'Test Model']);

        // Assert: Should throw exception when no schedule is set
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No schedule set for link operations. Use schedule() method first.');

        // Act: Try to attach without setting schedule
        schedule_for($availability)->attach($model);
    }

    /**
     * Test fluent interface for link operations.
     */
    public function test_fluent_interface_for_link_operations(): void
    {
        // Arrange: Create schedule and models
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for fluent test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        $model1 = TestSchedulable::create(['name' => 'Resource 1']);
        $model2 = TestSchedulable::create(['name' => 'Resource 2']);

        // Act: Chain multiple link operations
        $service = schedule_for($availability)
            ->schedule($schedule)
            ->attach($model1, ['role' => 'primary'])
            ->attach($model2, ['role' => 'secondary'])
            ->detach($model1);

        // Assert: Should return service instance for chaining
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
    }

    /**
     * Test getting attached models by type.
     */
    public function test_get_attached_models_by_type(): void
    {
        // Arrange: Create schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for type filter test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Get attached models by type (empty initially)
        $attachedByType = schedule_for($availability)
            ->schedule($schedule)
            ->getAttachedByType(TestSchedulable::class);

        // Assert: Should return Collection
        $this->assertInstanceOf(Collection::class, $attachedByType);
        $this->assertCount(0, $attachedByType);
    }

    /**
     * Test detaching all models from schedule.
     */
    public function test_detach_all_models_from_schedule(): void
    {
        // Arrange: Create schedule
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Schedule for detach all test',
            'start_datetime' => '2038-01-04 10:00:00',
            'end_datetime' => '2038-01-04 11:00:00',
        ]);

        // Act: Detach all models
        $service = schedule_for($availability)
            ->schedule($schedule)
            ->detachAll();

        // Assert: Should return service instance
        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
    }
}
