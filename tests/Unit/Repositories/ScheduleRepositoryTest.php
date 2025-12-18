<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Roster\Models\Schedule;
use Roster\Repositories\ScheduleRepository;

final class ScheduleRepositoryTest extends TestCase
{
    private ScheduleRepository $scheduleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduleRepository = new ScheduleRepository();
    }

    public function test_find_for_time_slot_returns_collection(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');

        $mockCollection = new Collection();

        Schedule::shouldReceive('where')
            ->with('availability_id', $availabilityId)
            ->once()
            ->andReturnSelf();

        Schedule::shouldReceive('where')
            ->with('start_datetime', '<', $end)
            ->once()
            ->andReturnSelf();

        Schedule::shouldReceive('where')
            ->with('end_datetime', '>', $start)
            ->once()
            ->andReturnSelf();

        Schedule::shouldReceive('orderBy')
            ->with('start_datetime')
            ->once()
            ->andReturnSelf();

        Schedule::shouldReceive('get')
            ->once()
            ->andReturn($mockCollection);

        $result = $this->scheduleRepository->findForTimeSlot($availabilityId, $start, $end);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($mockCollection, $result);
    }

    public function test_has_overlapping_schedule_without_exclude_id(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');

        $mockBuilder = Mockery::mock(Builder::class);

        Schedule::shouldReceive('where')
            ->with('availability_id', $availabilityId)
            ->once()
            ->andReturn($mockBuilder);

        $mockBuilder->shouldReceive('where')
            ->with('start_datetime', '<', $end)
            ->once()
            ->andReturnSelf();

        $mockBuilder->shouldReceive('where')
            ->with('end_datetime', '>', $start)
            ->once()
            ->andReturnSelf();

        $mockBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $result = $this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end);

        $this->assertTrue($result);
    }

    public function test_has_overlapping_schedule_with_exclude_id(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');
        $excludeId = 99;

        $mockBuilder = Mockery::mock(Builder::class);

        Schedule::shouldReceive('where')
            ->with('availability_id', $availabilityId)
            ->once()
            ->andReturn($mockBuilder);

        $mockBuilder->shouldReceive('where')
            ->with('start_datetime', '<', $end)
            ->once()
            ->andReturnSelf();

        $mockBuilder->shouldReceive('where')
            ->with('end_datetime', '>', $start)
            ->once()
            ->andReturnSelf();

        $mockBuilder->shouldReceive('where')
            ->with('id', '!=', $excludeId)
            ->once()
            ->andReturnSelf();

        $mockBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $result = $this->scheduleRepository->hasOverlappingSchedule($availabilityId, $start, $end, $excludeId);

        $this->assertFalse($result);
    }

    public function test_find_overlapping_schedules(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');
        $excludeId = 99;

        $mockBuilder = Mockery::mock(Builder::class);
        $mockCollection = new Collection();

        Schedule::shouldReceive('where')
            ->with('availability_id', $availabilityId)
            ->once()
            ->andReturn($mockBuilder);

        $mockBuilder->shouldReceive('where')
            ->once()
            ->with(Mockery::type('Closure'))
            ->andReturnSelf();

        $mockBuilder->shouldReceive('where')
            ->with('id', '!=', $excludeId)
            ->once()
            ->andReturnSelf();

        $mockBuilder->shouldReceive('get')
            ->once()
            ->andReturn($mockCollection);

        $result = $this->scheduleRepository->findOverlappingSchedules($availabilityId, $start, $end, $excludeId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($mockCollection, $result);
    }
}
