<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;
use Roster\Models\Impediment;
use Roster\Repositories\ImpedimentRepository;

final class ImpedimentRepositoryTest extends TestCase
{
    private ImpedimentRepository $impedimentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->impedimentRepository = new ImpedimentRepository();
    }

    public function test_find_for_time_slot_returns_collection(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');

        $mockCollection = new Collection();

        Impediment::shouldReceive('where')
            ->with('availability_id', $availabilityId)
            ->once()
            ->andReturnSelf();

        Impediment::shouldReceive('where')
            ->with('start_datetime', '<', $end)
            ->once()
            ->andReturnSelf();

        Impediment::shouldReceive('where')
            ->with('end_datetime', '>', $start)
            ->once()
            ->andReturnSelf();

        Impediment::shouldReceive('orderBy')
            ->with('start_datetime')
            ->once()
            ->andReturnSelf();

        Impediment::shouldReceive('get')
            ->once()
            ->andReturn($mockCollection);

        $result = $this->impedimentRepository->findForTimeSlot($availabilityId, $start, $end);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($mockCollection, $result);
    }

    public function test_has_overlapping_impediment_without_exclude_id(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');

        $mockBuilder = Mockery::mock(Builder::class);

        Impediment::shouldReceive('where')
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

        $result = $this->impedimentRepository->hasOverlappingImpediment($availabilityId, $start, $end);

        $this->assertTrue($result);
    }

    public function test_has_overlapping_impediment_with_exclude_id(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');
        $excludeId = 99;

        $mockBuilder = Mockery::mock(Builder::class);

        Impediment::shouldReceive('where')
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

        $result = $this->impedimentRepository->hasOverlappingImpediment($availabilityId, $start, $end, $excludeId);

        $this->assertFalse($result);
    }

    public function test_find_overlapping_impediments(): void
    {
        $availabilityId = 1;
        $start = Carbon::parse('2024-01-01 10:00:00');
        $end = Carbon::parse('2024-01-01 11:00:00');
        $excludeId = 99;

        $mockBuilder = Mockery::mock(Builder::class);
        $mockCollection = new Collection();

        Impediment::shouldReceive('where')
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

        $result = $this->impedimentRepository->findOverlappingImpediments($availabilityId, $start, $end, $excludeId);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($mockCollection, $result);
    }
}
