<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use Roster\Repositories\AvailabilityRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Services\AvailabilityValidator;
use Roster\Services\Core\ValidationService;
use Tests\TestCase;

final class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availabilityService;

    private Model $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $this->model->id = 1;
        $this->model->save();

        $validationService = new ValidationService();
        $availabilityValidator = new AvailabilityValidator();

        $this->availabilityService = new AvailabilityService($availabilityValidator, $validationService, app(AvailabilityRepository::class));
        $this->availabilityService->for($this->model);
    }

    public function test_create_availability_successfully(): void
    {
        $data = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday'],
        ];

        $availability = $this->availabilityService->create($data);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame($this->model->id, $availability->schedulable_id);
        $this->assertSame(get_class($this->model), $availability->schedulable_type);
        $this->assertSame('consultation', $availability->type);
        $this->assertSame(['monday', 'tuesday'], $availability->days);
        $this->assertDatabaseHas('availabilities', [
            'schedulable_id' => $this->model->id,
            'type' => 'consultation',
        ]);
    }

    public function test_create_availability_with_date_ranges(): void
    {
        $data = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ];

        $availability = $this->availabilityService->create($data);

        $this->assertSame('2024-01-01', $availability->start_date->format('Y-m-d'));
        $this->assertSame('2024-01-31', $availability->end_date->format('Y-m-d'));
    }

    public function test_create_availability_with_overlap_throws_exception(): void
    {
        // Create first availability
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        // Try to create overlapping availability
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'days' => ['monday'],
        ]);
    }

    public function test_update_availability_successfully(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $updated = $this->availabilityService->update($availability->id, [
            'type' => 'training',
            'end_time' => '13:00:00',
        ]);

        $this->assertTrue($updated);

        $availability->refresh();
        $this->assertSame('training', $availability->type);
        $this->assertSame('13:00:00', $availability->end_time->format('H:i:s'));
    }

    public function test_delete_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $deleted = $this->availabilityService->delete($availability->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('availabilities', ['id' => $availability->id]);
    }

    public function test_find_availability_by_id(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $found = $this->availabilityService->find($availability->id);

        $this->assertInstanceOf(Availability::class, $found);
        $this->assertSame($availability->id, $found->id);
    }

    public function test_is_available_at_method(): void
    {
        // Create availability for Monday 9-12
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        // Test available time (Monday at 10:00)
        $availableTime = Carbon::parse('2024-01-01 10:00:00'); // This is a Monday
        $this->assertTrue($this->availabilityService->isAvailableAt($availableTime));

        // Test unavailable time (Monday at 8:00)
        $unavailableTime = Carbon::parse('2024-01-01 08:00:00');
        $this->assertFalse($this->availabilityService->isAvailableAt($unavailableTime));

        // Test wrong day (Tuesday at 10:00)
        $wrongDay = Carbon::parse('2024-01-02 10:00:00'); // This is a Tuesday
        $this->assertFalse($this->availabilityService->isAvailableAt($wrongDay));
    }

    public function test_next_available_slot(): void
    {
        // Create availability for weekdays 9-17
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);

        // Start from a Monday at 8:00 (before availability starts)
        $fromDate = Carbon::parse('2024-01-01 08:00:00'); // Monday

        $nextSlot = $this->availabilityService->nextAvailableSlot($fromDate, 60);

        $this->assertInstanceOf(Carbon::class, $nextSlot);
        $this->assertSame('2024-01-01 09:00:00', $nextSlot->format('Y-m-d H:i:s'));
    }

    public function test_available_slots_method(): void
    {
        // Create availability for Monday 9-12
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $startDate = Carbon::parse('2024-01-01'); // Monday
        $endDate = Carbon::parse('2024-01-01');

        $slots = $this->availabilityService->availableSlots($startDate, $endDate, 60, 60);

        $this->assertIsArray($slots);
        $this->assertCount(3, $slots); // 9-10, 10-11, 11-12

        $this->assertSame('2024-01-01 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));
        $this->assertSame('consultation', $slots[0]['type']);
    }
}
