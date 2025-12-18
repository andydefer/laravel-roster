<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use Roster\Services\Core\ValidationService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;
use Roster\Repositories\AvailabilityRepository;

final class AvailabilityRepositoryTest extends TestCase
{
    private AvailabilityRepository $availabilityRepository;

    protected function setUp(): void
    {
        parent::setUp();
        // On crée un vrai ValidationService, pas un mock
        $validationService = new ValidationService();
        $this->availabilityRepository = new AvailabilityRepository($validationService);
    }

    public function test_time_ranges_overlap_with_overlapping_ranges(): void
    {
        $existingStart = Carbon::parse('10:00:00');
        $existingEnd = Carbon::parse('12:00:00');

        // Overlapping from middle
        $newStart = Carbon::parse('11:00:00');
        $newEnd = Carbon::parse('13:00:00');

        $this->assertTrue(
            $this->availabilityRepository->timeRangesOverlap($existingStart, $existingEnd, $newStart, $newEnd)
        );

        // Overlapping from start
        $newStart = Carbon::parse('09:00:00');
        $newEnd = Carbon::parse('11:00:00');

        $this->assertTrue(
            $this->availabilityRepository->timeRangesOverlap($existingStart, $existingEnd, $newStart, $newEnd)
        );

        // Complete overlap
        $newStart = Carbon::parse('09:00:00');
        $newEnd = Carbon::parse('13:00:00');

        $this->assertTrue(
            $this->availabilityRepository->timeRangesOverlap($existingStart, $existingEnd, $newStart, $newEnd)
        );
    }

    public function test_time_ranges_overlap_with_non_overlapping_ranges(): void
    {
        $existingStart = Carbon::parse('10:00:00');
        $existingEnd = Carbon::parse('12:00:00');

        // Before
        $newStart = Carbon::parse('08:00:00');
        $newEnd = Carbon::parse('09:00:00');

        $this->assertFalse(
            $this->availabilityRepository->timeRangesOverlap($existingStart, $existingEnd, $newStart, $newEnd)
        );

        // After
        $newStart = Carbon::parse('13:00:00');
        $newEnd = Carbon::parse('14:00:00');

        $this->assertFalse(
            $this->availabilityRepository->timeRangesOverlap($existingStart, $existingEnd, $newStart, $newEnd)
        );

        // Touching end (should not overlap)
        $newStart = Carbon::parse('12:00:00');
        $newEnd = Carbon::parse('13:00:00');

        $this->assertFalse(
            $this->availabilityRepository->timeRangesOverlap($existingStart, $existingEnd, $newStart, $newEnd)
        );
    }

    public function test_date_ranges_overlap_with_various_scenarios(): void
    {
        // Test 1: Existing has no dates (indefinite)
        $this->assertTrue(
            $this->availabilityRepository->dateRangesOverlap(null, null, Carbon::parse('2024-01-01'), Carbon::parse('2024-01-31'))
        );

        // Test 2: New has no dates (indefinite)
        $this->assertTrue(
            $this->availabilityRepository->dateRangesOverlap(Carbon::parse('2024-01-01'), Carbon::parse('2024-01-31'), null, null)
        );

        // Test 3: Both have dates and overlap
        $this->assertTrue(
            $this->availabilityRepository->dateRangesOverlap(
                Carbon::parse('2024-01-01'),
                Carbon::parse('2024-01-31'),
                Carbon::parse('2024-01-15'),
                Carbon::parse('2024-02-15')
            )
        );

        // Test 4: Both have dates and don't overlap
        $this->assertFalse(
            $this->availabilityRepository->dateRangesOverlap(
                Carbon::parse('2024-01-01'),
                Carbon::parse('2024-01-31'),
                Carbon::parse('2024-02-01'),
                Carbon::parse('2024-02-28')
            )
        );

        // Test 5: Existing has start date only, new has dates
        $this->assertTrue(
            $this->availabilityRepository->dateRangesOverlap(
                Carbon::parse('2024-01-01'),
                null,
                Carbon::parse('2024-02-01'),
                Carbon::parse('2024-02-28')
            )
        );

        // Test 6: Existing has end date only, new has dates
        $this->assertTrue(
            $this->availabilityRepository->dateRangesOverlap(
                null,
                Carbon::parse('2024-01-31'),
                Carbon::parse('2024-01-01'),
                Carbon::parse('2024-01-15')
            )
        );
    }
}
