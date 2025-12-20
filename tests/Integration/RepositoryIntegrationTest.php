<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Models\Availability;
use Roster\Repositories\AvailabilityRepository;
use Tests\TestCase;

final class RepositoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityRepository $availabilityRepository;

    private Model $model;

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

        $validationService = app(ValidationServiceInterface::class);
        $this->availabilityRepository = new AvailabilityRepository($validationService);
    }

    public function test_find_for_time_slot_integration(): void
    {
        $availability = Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');

        $found = $this->availabilityRepository->findForTimeSlot($this->model, $start, $end);

        $this->assertInstanceOf(Availability::class, $found);
        $this->assertSame($availability->id, $found->id);

        $foundWithType = $this->availabilityRepository->findForTimeSlot($this->model, $start, $end, 'consultation');
        $this->assertSame($availability->id, $foundWithType->id);

        $notFoundWithType = $this->availabilityRepository->findForTimeSlot($this->model, $start, $end, 'training');
        $this->assertNotInstanceOf(Availability::class, $notFoundWithType);
    }

    public function test_get_for_date_integration(): void
    {
        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'training',
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
        ]);

        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['tuesday'],
        ]);

        $date = Carbon::parse('2024-01-01');

        $availabilities = $this->availabilityRepository->getForDate($this->model, $date);

        $this->assertCount(2, $availabilities);
        $this->assertSame('consultation', $availabilities[0]->type);
        $this->assertSame('training', $availabilities[1]->type);
    }

    public function test_is_available_at_integration(): void
    {
        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday'],
        ]);

        $availableTime = Carbon::parse('2024-01-01 10:00:00');
        $this->assertTrue($this->availabilityRepository->isAvailableAt($this->model, $availableTime));

        $unavailableDay = Carbon::parse('2024-01-02 10:00:00');
        $this->assertFalse($this->availabilityRepository->isAvailableAt($this->model, $unavailableDay));

        $unavailableTime = Carbon::parse('2024-01-01 08:00:00');
        $this->assertFalse($this->availabilityRepository->isAvailableAt($this->model, $unavailableTime));
    }

    public function test_find_overlapping_integration(): void
    {
        Availability::create([
            'schedulable_id' => $this->model->id,
            'schedulable_type' => get_class($this->model),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'days' => ['monday', 'tuesday'],
        ]);

        $overlappingData = [
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'days' => ['monday'],
        ];

        $overlapping = $this->availabilityRepository->findOverlapping($this->model, $overlappingData);

        $this->assertCount(1, $overlapping);
        $this->assertSame('consultation', $overlapping->first()->type);

        $nonOverlappingData = [
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'days' => ['wednesday'],
        ];

        $nonOverlapping = $this->availabilityRepository->findOverlapping($this->model, $nonOverlappingData);

        $this->assertCount(0, $nonOverlapping);
    }

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
}
