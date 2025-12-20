<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Models\Availability;
use Roster\Repositories\AvailabilityRepository;
use Tests\TestCase;

/**
 * Integration tests for the AvailabilityRepository.
 */
#[CoversClass(AvailabilityRepository::class)]
final class RepositoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityRepository $availabilityRepository;

    private Model $testModel;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestModel();
        $this->initializeAvailabilityRepository();
    }

    /**
     * Test finding availability for a specific time slot.
     */
    public function test_find_for_time_slot_integration(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $startTime = Carbon::parse('2024-01-01 10:00:00');
        $endTime = Carbon::parse('2024-01-01 11:00:00');

        $foundAvailability = $this->availabilityRepository->findForTimeSlot($this->testModel, $startTime, $endTime);

        $this->assertInstanceOf(Availability::class, $foundAvailability);
        $this->assertSame($availability->id, $foundAvailability->id);

        $foundWithType = $this->availabilityRepository->findForTimeSlot(
            $this->testModel,
            $startTime,
            $endTime,
            'consultation'
        );
        $this->assertSame($availability->id, $foundWithType->id);

        $notFoundWithType = $this->availabilityRepository->findForTimeSlot(
            $this->testModel,
            $startTime,
            $endTime,
            'training'
        );
        $this->assertNotInstanceOf(Availability::class, $notFoundWithType);
    }

    /**
     * Test retrieving availabilities for a specific date.
     */
    public function test_get_for_date_integration(): void
    {
        $this->createTestAvailabilities();

        $date = Carbon::parse('2024-01-01');

        $availabilities = $this->availabilityRepository->getForDate($this->testModel, $date);

        $this->assertCount(2, $availabilities);
        $this->assertSame('consultation', $availabilities[0]->type);
        $this->assertSame('training', $availabilities[1]->type);
    }

    /**
     * Test checking availability at a specific datetime.
     */
    public function test_is_available_at_integration(): void
    {
        Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $availableTime = Carbon::parse('2024-01-01 10:00:00');
        $this->assertTrue($this->availabilityRepository->isAvailableAt($this->testModel, $availableTime));

        $unavailableDay = Carbon::parse('2024-01-02 10:00:00');
        $this->assertFalse($this->availabilityRepository->isAvailableAt($this->testModel, $unavailableDay));

        $unavailableTime = Carbon::parse('2024-01-01 08:00:00');
        $this->assertFalse($this->availabilityRepository->isAvailableAt($this->testModel, $unavailableTime));
    }

    /**
     * Test finding overlapping availabilities.
     */
    public function test_find_overlapping_integration(): void
    {
        Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $overlappingAvailability = [
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'days' => ['monday'],
        ];

        $overlapping = $this->availabilityRepository->findOverlapping($this->testModel, $overlappingAvailability);

        $this->assertCount(1, $overlapping);
        $this->assertSame('consultation', $overlapping->first()->type);

        $nonOverlappingAvailability = [
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'days' => ['wednesday'],
        ];

        $nonOverlapping = $this->availabilityRepository->findOverlapping($this->testModel, $nonOverlappingAvailability);

        $this->assertCount(0, $nonOverlapping);
    }

    /**
     * Test time range overlap detection.
     */
    public function test_time_ranges_overlap_integration(): void
    {
        $existingStart = Carbon::parse('09:00:00');
        $existingEnd = Carbon::parse('12:00:00');

        $this->assertTrue($this->availabilityRepository->doTimeRangesOverlap(
            $existingStart,
            $existingEnd,
            Carbon::parse('10:00:00'),
            Carbon::parse('11:00:00')
        ));

        $this->assertFalse($this->availabilityRepository->doTimeRangesOverlap(
            $existingStart,
            $existingEnd,
            Carbon::parse('08:00:00'),
            Carbon::parse('08:30:00')
        ));

        $this->assertFalse($this->availabilityRepository->doTimeRangesOverlap(
            $existingStart,
            $existingEnd,
            Carbon::parse('12:30:00'),
            Carbon::parse('13:00:00')
        ));
    }

    /**
     * Test date range overlap detection.
     */
    public function test_date_ranges_overlap_integration(): void
    {
        $this->assertTrue($this->availabilityRepository->dateRangesOverlap(
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-31'),
            Carbon::parse('2024-01-15'),
            Carbon::parse('2024-02-15')
        ));

        $this->assertFalse($this->availabilityRepository->dateRangesOverlap(
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-31'),
            Carbon::parse('2024-02-01'),
            Carbon::parse('2024-02-28')
        ));

        $this->assertTrue($this->availabilityRepository->dateRangesOverlap(
            null,
            null,
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-31')
        ));
    }

    /**
     * Create a test model for scheduling.
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
     * Initialize the availability repository with its dependencies.
     */
    private function initializeAvailabilityRepository(): void
    {
        $validationService = app(ValidationServiceInterface::class);
        $this->availabilityRepository = new AvailabilityRepository($validationService);
    }

    /**
     * Create test availabilities for date-based tests.
     */
    private function createTestAvailabilities(): void
    {
        Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->testModel->id,
            'schedulable_type' => get_class($this->testModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['tuesday'],
        ]);
    }
}
