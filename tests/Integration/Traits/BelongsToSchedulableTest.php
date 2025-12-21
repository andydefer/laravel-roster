<?php

declare(strict_types=1);

namespace Tests\Integration\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Roster\Exceptions\MissingSchedulableException;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Tests\TestCase;

/**
 * Tests for BelongsToSchedulable trait validation.
 */
final class BelongsToSchedulableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test model instance.
     */
    private Model $model;

    /**
     * {@inheritdoc}
     */
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
    }

    /**
     * Test that schedule creation fails without schedulable_id.
     */
    public function test_schedule_creation_fails_without_schedulable_id(): void
    {
        $availability = $this->createTestAvailability();

        $this->expectException(MissingSchedulableException::class);
        $this->expectExceptionMessage('Schedule must have a schedulable owner. Set schedulable_id and schedulable_type.');

        Schedule::create([
            'availability_id' => $availability->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);
    }

    /**
     * Test that schedule creation fails without schedulable_type.
     */
    public function test_schedule_creation_fails_without_schedulable_type(): void
    {
        $availability = $this->createTestAvailability();

        $this->expectException(MissingSchedulableException::class);
        $this->expectExceptionMessage('Schedule must have a schedulable owner. Set schedulable_id and schedulable_type.');

        Schedule::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);
    }

    /**
     * Test that schedule creation fails with empty schedulable_type.
     */
    public function test_schedule_creation_fails_with_empty_schedulable_type(): void
    {
        $availability = $this->createTestAvailability();

        $this->expectException(MissingSchedulableException::class);
        $this->expectExceptionMessage('Schedule must have a schedulable owner. Set schedulable_id and schedulable_type.');

        Schedule::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => '',
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);
    }

    /**
     * Test that schedule creation succeeds with proper schedulable fields.
     */
    public function test_schedule_creation_succeeds_with_proper_schedulable_fields(): void
    {
        $availability = $this->createTestAvailability();

        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'title' => 'Test Schedule',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame($this->model->id, $schedule->schedulable_id);
        $this->assertSame(get_class($this->model), $schedule->schedulable_type);
    }

    /**
     * Test that impediment creation fails without schedulable fields.
     */
    public function test_impediment_creation_fails_without_schedulable_fields(): void
    {
        $availability = $this->createTestAvailability();

        $this->expectException(MissingSchedulableException::class);
        $this->expectExceptionMessage('Impediment must have a schedulable owner. Set schedulable_id and schedulable_type.');

        Impediment::create([
            'availability_id' => $availability->id,
            'reason' => 'Test',
            'start_datetime' => '2024-01-01 11:00:00',
            'end_datetime' => '2024-01-01 12:00:00',
        ]);
    }

    /**
     * Test that impediment creation succeeds with proper schedulable fields.
     */
    public function test_impediment_creation_succeeds_with_proper_schedulable_fields(): void
    {
        $availability = $this->createTestAvailability();

        $impediment = Impediment::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'reason' => 'Test',
            'start_datetime' => '2024-01-01 11:00:00',
            'end_datetime' => '2024-01-01 12:00:00',
        ]);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame($this->model->id, $impediment->schedulable_id);
        $this->assertSame(get_class($this->model), $impediment->schedulable_type);
    }

    /**
     * Test that schedule update still requires schedulable fields on creation.
     */
    public function test_schedule_update_still_requires_schedulable_fields(): void
    {
        $availability = $this->createTestAvailability();

        $schedule = Schedule::create([
            'availability_id' => $availability->id,
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'title' => 'Original Title',
            'start_datetime' => '2024-01-01 10:00:00',
            'end_datetime' => '2024-01-01 11:00:00',
            'status' => 'available',
        ]);

        $schedule->update(['title' => 'Updated Title']);
        $schedule->refresh();

        $this->assertSame('Updated Title', $schedule->title);
    }

    /**
     * Test that global scope filters by schedulable.
     */
    public function test_global_scope_filters_by_schedulable(): void
    {
        $model = $this->createSecondSchedulable();

        $availabilityForFirst = $this->createTestAvailability();
        $availabilityForSecond = $this->createAvailabilityForSchedulable($model);

        $firstSchedulableAvailabilities = Availability::forSchedulable($this->model)->get();
        $secondSchedulableAvailabilities = Availability::forSchedulable($model)->get();

        $this->assertCount(1, $firstSchedulableAvailabilities);
        $this->assertSame($availabilityForFirst->id, $firstSchedulableAvailabilities->first()->id);

        $this->assertCount(1, $secondSchedulableAvailabilities);
        $this->assertSame($availabilityForSecond->id, $secondSchedulableAvailabilities->first()->id);
    }

    /**
     * Create a test availability for the primary test schedulable.
     */
    private function createTestAvailability(): Availability
    {
        return Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);
    }

    /**
     * Create a second schedulable for comparison tests.
     */
    private function createSecondSchedulable(): Model
    {
        $schedulable = new class extends Model
        {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        $schedulable->id = 2;
        $schedulable->save();

        return $schedulable;
    }

    /**
     * Create an availability for a specific schedulable.
     */
    private function createAvailabilityForSchedulable(Model $model): Availability
    {
        return Availability::create([
            'schedulable_id' => $model->id,
            'schedulable_type' => get_class($model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);
    }
}
