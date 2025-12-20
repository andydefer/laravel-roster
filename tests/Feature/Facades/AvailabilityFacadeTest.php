<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Facades\Availability as AvailabilityFacade;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Tests\TestCase;

/**
 * Tests for the Availability facade functionality.
 */
#[CoversClass(AvailabilityFacade::class)]
final class AvailabilityFacadeTest extends TestCase
{
    /**
     * The schedulable model instance used for testing.
     */
    private Model $schedulableModel;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulableModel = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };

        $this->schedulableModel = $this->schedulableModel::create();
    }

    /**
     * Test that the facade returns the correct service instance.
     */
    public function test_facade_returns_availability_service(): void
    {
        $availabilityService = AvailabilityFacade::for($this->schedulableModel);

        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
        $this->assertSame($this->schedulableModel->id, $availabilityService->getSchedulable()->id);
    }

    /**
     * Test creating a new availability through the facade.
     */
    public function test_availability_can_be_created_through_facade(): void
    {
        $availabilityData = [
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ];

        $availability = AvailabilityFacade::for($this->schedulableModel)->create($availabilityData);

        $this->assertInstanceOf(Availability::class, $availability);
        $this->assertSame('consultation', $availability->type);

        $this->assertDatabaseHas('roster_availabilities', [
            'schedulable_id' => $this->schedulableModel->id,
            'type' => 'consultation',
        ]);
    }

    /**
     * Test finding an existing availability by ID.
     */
    public function test_existing_availability_can_be_found(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $foundAvailability = AvailabilityFacade::for($this->schedulableModel)->find($availability->id);

        $this->assertInstanceOf(Availability::class, $foundAvailability);
        $this->assertSame($availability->id, $foundAvailability->id);
    }

    /**
     * Test retrieving all availabilities for the schedulable.
     */
    public function test_all_availabilities_can_be_retrieved(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'days' => ['monday'],
        ]);

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = AvailabilityFacade::for($this->schedulableModel)->all();

        $this->assertCount(2, $availabilities);
        $this->assertSame('consultation', $availabilities[0]->type);
        $this->assertSame('training', $availabilities[1]->type);
    }

    /**
     * Test filtering availabilities by type.
     */
    public function test_facade_can_filter_by_type(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'days' => ['monday'],
        ]);

        $availabilities = AvailabilityFacade::for($this->schedulableModel)
            ->whereType('consultation')
            ->get();

        $this->assertCount(1, $availabilities);
        $this->assertSame('consultation', $availabilities->first()->type);
    }

    /**
     * Test filtering availabilities by day of week.
     */
    public function test_facade_can_filter_by_day(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'days' => ['tuesday'],
        ]);

        $availabilities = AvailabilityFacade::for($this->schedulableModel)
            ->filterByDay('monday')
            ->get();

        $this->assertCount(1, $availabilities);
        $this->assertSame(['monday'], $availabilities->first()->days);
    }

    /**
     * Test checking availability at a specific datetime.
     */
    public function test_availability_at_specific_time_can_be_checked(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ]);

        $availableTime = Carbon::parse('2038-06-07 10:00:00');
        $unavailableTime = Carbon::parse('2038-06-07 08:00:00');

        $this->assertTrue(
            AvailabilityFacade::for($this->schedulableModel)->isAvailableAt($availableTime)
        );

        $this->assertFalse(
            AvailabilityFacade::for($this->schedulableModel)->isAvailableAt($unavailableTime)
        );
    }

    /**
     * Test checking availability for a period.
     */
    public function test_facade_can_check_availability_for_period(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ]);

        $availableStart = Carbon::parse('2038-06-07 10:00:00');
        $availableEnd = Carbon::parse('2038-06-07 11:00:00');

        $unavailableStart = Carbon::parse('2038-06-07 08:00:00');
        $unavailableEnd = Carbon::parse('2038-06-07 09:00:00');

        $this->assertTrue(
            AvailabilityFacade::for($this->schedulableModel)
                ->isAvailableForPeriod($availableStart, $availableEnd)
        );

        $this->assertFalse(
            AvailabilityFacade::for($this->schedulableModel)
                ->isAvailableForPeriod($unavailableStart, $unavailableEnd)
        );
    }

    /**
     * Test finding overlapping availabilities.
     */
    public function test_facade_can_find_overlapping_availabilities(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ]);

        $checkData = [
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'days' => ['monday'],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ];

        $overlapping = AvailabilityFacade::for($this->schedulableModel)
            ->findOverlapping($checkData);

        $this->assertCount(1, $overlapping);
        $this->assertSame('consultation', $overlapping->first()->type);
    }

    /**
     * Test finding adjacent availabilities.
     */
    public function test_facade_can_find_adjacent_availabilities(): void
    {
        $commonDay = 'monday';
        $commonType = 'consultation';

        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => $commonType,
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => [$commonDay],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ]);

        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => $commonType,
            'start_time' => '12:00:00',
            'end_time' => '14:00:00',
            'days' => [$commonDay],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ]);

        $checkData = [
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'days' => [$commonDay],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
            'type' => $commonType,
        ];

        $adjacent = AvailabilityFacade::for($this->schedulableModel)
            ->findByType($checkData);

        $this->assertCount(1, $adjacent);
        $this->assertSame('12:00:00', $adjacent->first()->start_time->format('H:i:s'));
    }

    /**
     * Test finding available time slots in a period.
     */
    public function test_facade_can_find_slots_in_period(): void
    {
        Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-01-01',
            'end_date' => '2038-12-31',
        ]);

        $startDate = Carbon::parse('2038-06-07 00:00:00');
        $endDate = Carbon::parse('2038-06-07 23:59:59');

        $slots = AvailabilityFacade::for($this->schedulableModel)
            ->findSlotsInPeriod($startDate, $endDate, 60, 30);

        $this->assertIsArray($slots);
        $this->assertGreaterThan(0, count($slots));
    }

    /**
     * Test updating an existing availability.
     */
    public function test_facade_can_update_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $updateResult = AvailabilityFacade::for($this->schedulableModel)
            ->update($availability->id, ['type' => 'training']);

        $this->assertTrue($updateResult);

        $availability->refresh();
        $this->assertSame('training', $availability->type);
    }

    /**
     * Test deleting an availability.
     */
    public function test_facade_can_delete_availability(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $deleteResult = AvailabilityFacade::for($this->schedulableModel)
            ->delete($availability->id);

        $this->assertTrue($deleteResult);
        $this->assertDatabaseMissing('roster_availabilities', ['id' => $availability->id]);
    }

    /**
     * Test resetting filters.
     */
    public function test_facade_can_reset_filters(): void
    {
        $availabilityService = AvailabilityFacade::for($this->schedulableModel)
            ->whereType('consultation')
            ->resetFilters();

        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
    }

    /**
     * Test getting the current schedulable model.
     */
    public function test_facade_can_get_schedulable(): void
    {
        $availabilityService = AvailabilityFacade::for($this->schedulableModel);
        $schedulable = $availabilityService->getSchedulable();

        $this->assertInstanceOf(Model::class, $schedulable);
        $this->assertSame($this->schedulableModel->id, $schedulable->id);
    }
}
