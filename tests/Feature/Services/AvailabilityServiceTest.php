<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Exceptions\ValidationException;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Tests\TestCase;

/**
 * Test suite for the AvailabilityService.
 */
#[CoversClass(AvailabilityService::class)]
final class AvailabilityServiceTest extends TestCase
{
    private AvailabilityService $availabilityService;

    private Model $schedulableModel;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulableModel = new class extends Model {
            protected $table = 'test_schedulables';
        };

        $this->schedulableModel = $this->schedulableModel::create();

        $this->availabilityService = app(AvailabilityService::class);
        $this->availabilityService->for($this->schedulableModel);
    }

    /**
     * Test creating an availability successfully.
     */
    public function test_create_availability_successfully(): void
    {
        $availabilityData = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday', 'tuesday'],
        ];

        $availability = $this->availabilityService->create($availabilityData);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame($this->schedulableModel->id, $availability->schedulable_id);
        $this->assertSame(get_class($this->schedulableModel), $availability->schedulable_type);
        $this->assertSame('consultation', $availability->type);
        $this->assertSame(['monday', 'tuesday'], $availability->days);

        $this->assertDatabaseHas('roster_availabilities', [
            'schedulable_id' => $this->schedulableModel->id,
            'type' => 'consultation',
        ]);
    }

    /**
     * Test creating an availability with date ranges.
     */
    public function test_create_availability_with_date_ranges(): void
    {
        $availabilityData = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ];

        $availability = $this->availabilityService->create($availabilityData);

        $this->assertSame('2038-06-01', $availability->start_date->format('Y-m-d'));
        $this->assertSame('2038-06-30', $availability->end_date->format('Y-m-d'));
    }

    /**
     * Test that creating overlapping availabilities throws an exception.
     */
    public function test_create_availability_with_overlap_throws_exception(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This availability overlaps with an existing one.');

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '10:00:00',
            'end_time' => '13:00:00',
            'days' => ['monday'],
        ]);
    }

    /**
     * Test updating an availability successfully.
     */
    public function test_update_availability_successfully(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
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

    /**
     * Test deleting an availability.
     */
    public function test_delete_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $deleted = $this->availabilityService->delete($availability->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('roster_availabilities', ['id' => $availability->id]);
    }

    /**
     * Test finding an availability by ID.
     */
    public function test_find_availability_by_id(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $foundAvailability = $this->availabilityService->find($availability->id);

        $this->assertInstanceOf(Availability::class, $foundAvailability);
        $this->assertSame($availability->id, $foundAvailability->id);
    }

    /**
     * Test checking availability at a specific time.
     */
    public function test_is_available_at_method(): void
    {
        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $mondayDate = Carbon::parse('2038-06-07');

        $availableTime = $mondayDate->copy()->setTime(10, 0, 0);
        $this->assertTrue($this->availabilityService->isAvailableAt($availableTime));

        $unavailableTime = $mondayDate->copy()->setTime(8, 0, 0);
        $this->assertFalse($this->availabilityService->isAvailableAt($unavailableTime));

        $wrongDay = Carbon::parse('2038-06-08 10:00:00');
        $this->assertFalse($this->availabilityService->isAvailableAt($wrongDay));
    }

    /**
     * Test finding available slots in a period.
     */
    public function test_available_slots_method(): void
    {
        $mondayDate = Carbon::parse('2038-06-07');

        $this->availabilityService->create([
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $startDate = $mondayDate->copy();
        $endDate = $mondayDate->copy()->addDay();

        $slots = $this->availabilityService->findSlotsInPeriod($startDate, $endDate, 60, 60);

        $this->assertIsArray($slots);
        $this->assertCount(3, $slots);

        $this->assertSame('2038-06-07 09:00:00', $slots[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 10:00:00', $slots[0]['end']->format('Y-m-d H:i:s'));
        $this->assertSame('consultation', $slots[0]['type']);
    }
}
