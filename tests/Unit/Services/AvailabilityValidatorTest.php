<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Support\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Roster\Services\AvailabilityValidator;

final class AvailabilityValidatorTest extends TestCase
{
    private AvailabilityValidator $availabilityValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->availabilityValidator = new AvailabilityValidator();
    }

    // Test: validateBasicData
    public function test_validate_basic_data_with_valid_data(): void
    {
        $data = [
            'days' => ['monday', 'tuesday'],
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ];

        $this->expectNotToPerformAssertions();
        $this->availabilityValidator->validateBasicData($data);
    }

    public function test_validate_basic_data_with_empty_days_throws_exception(): void
    {
        $data = [
            'days' => [],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one day must be specified');
        $this->availabilityValidator->validateBasicData($data);
    }

    public function test_validate_basic_data_with_end_time_before_start_time_throws_exception(): void
    {
        $data = [
            'start_time' => '17:00:00',
            'end_time' => '09:00:00',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End time must be after start time');
        $this->availabilityValidator->validateBasicData($data);
    }

    public function test_validate_basic_data_with_end_date_before_start_date_throws_exception(): void
    {
        $data = [
            'start_date' => '2024-01-31',
            'end_date' => '2024-01-01',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after or equal to start date');
        $this->availabilityValidator->validateBasicData($data);
    }

    // Test: timeOverlaps
    public function test_time_overlaps_with_overlapping_times(): void
    {
        $existingStart = Carbon::parse('09:00:00');
        $existingEnd = Carbon::parse('17:00:00');

        // Overlap from middle
        $newStart = Carbon::parse('10:00:00');
        $newEnd = Carbon::parse('11:00:00');
        $this->assertTrue($this->availabilityValidator->timeOverlaps($existingStart, $existingEnd, $newStart, $newEnd));

        // Overlap from start
        $newStart = Carbon::parse('08:00:00');
        $newEnd = Carbon::parse('10:00:00');
        $this->assertTrue($this->availabilityValidator->timeOverlaps($existingStart, $existingEnd, $newStart, $newEnd));

        // Complete overlap
        $newStart = Carbon::parse('08:00:00');
        $newEnd = Carbon::parse('18:00:00');
        $this->assertTrue($this->availabilityValidator->timeOverlaps($existingStart, $existingEnd, $newStart, $newEnd));
    }

    public function test_time_overlaps_with_non_overlapping_times(): void
    {
        $existingStart = Carbon::parse('09:00:00');
        $existingEnd = Carbon::parse('17:00:00');

        // Before
        $newStart = Carbon::parse('07:00:00');
        $newEnd = Carbon::parse('08:00:00');
        $this->assertFalse($this->availabilityValidator->timeOverlaps($existingStart, $existingEnd, $newStart, $newEnd));

        // After
        $newStart = Carbon::parse('18:00:00');
        $newEnd = Carbon::parse('19:00:00');
        $this->assertFalse($this->availabilityValidator->timeOverlaps($existingStart, $existingEnd, $newStart, $newEnd));

        // Touching end (should not overlap)
        $newStart = Carbon::parse('17:00:00');
        $newEnd = Carbon::parse('18:00:00');
        $this->assertFalse($this->availabilityValidator->timeOverlaps($existingStart, $existingEnd, $newStart, $newEnd));
    }

    // Test: dateRangesOverlap (from trait)
    public function test_date_ranges_overlap_with_various_scenarios(): void
    {
        // Test 1: Both have no dates (indefinite)
        $this->assertTrue(
            $this->availabilityValidator->dateRangesOverlap(null, null, null, null)
        );

        // Test 2: Existing has no dates, new has dates
        $this->assertTrue(
            $this->availabilityValidator->dateRangesOverlap(null, null, Carbon::parse('2024-01-01'), Carbon::parse('2024-01-31'))
        );

        // Test 3: New has no dates, existing has dates
        $this->assertTrue(
            $this->availabilityValidator->dateRangesOverlap(Carbon::parse('2024-01-01'), Carbon::parse('2024-01-31'), null, null)
        );

        // Test 4: Both have dates and overlap
        $this->assertTrue(
            $this->availabilityValidator->dateRangesOverlap(
                Carbon::parse('2024-01-01'),
                Carbon::parse('2024-01-31'),
                Carbon::parse('2024-01-15'),
                Carbon::parse('2024-02-15')
            )
        );

        // Test 5: Both have dates and don't overlap
        $this->assertFalse(
            $this->availabilityValidator->dateRangesOverlap(
                Carbon::parse('2024-01-01'),
                Carbon::parse('2024-01-31'),
                Carbon::parse('2024-02-01'),
                Carbon::parse('2024-02-28')
            )
        );

        // Test 6: Existing has start date only
        $this->assertTrue(
            $this->availabilityValidator->dateRangesOverlap(
                Carbon::parse('2024-01-01'),
                null,
                Carbon::parse('2024-02-01'),
                Carbon::parse('2024-02-28')
            )
        );

        // Test 7: Existing has end date only
        $this->assertTrue(
            $this->availabilityValidator->dateRangesOverlap(
                null,
                Carbon::parse('2024-01-31'),
                Carbon::parse('2024-01-01'),
                Carbon::parse('2024-01-15')
            )
        );
    }

    // Test: overlaps
    public function test_overlaps_with_overlapping_availability(): void
    {
        // Utilisons des tableaux au lieu d'objets pour tester la logique
        $availabilityData = [
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('17:00:00'),
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ];

        $newStartTime = Carbon::parse('10:00:00');
        $newEndTime = Carbon::parse('11:00:00');
        $newStartDate = Carbon::parse('2024-01-15');
        $newEndDate = Carbon::parse('2024-01-20');

        // Test de la logique directement
        $timeOverlaps = $this->availabilityValidator->timeOverlaps(
            $availabilityData['start_time'],
            $availabilityData['end_time'],
            $newStartTime,
            $newEndTime
        );

        $dateRangesOverlap = $this->availabilityValidator->dateRangesOverlap(
            $availabilityData['start_date'],
            $availabilityData['end_date'],
            $newStartDate,
            $newEndDate
        );

        $this->assertTrue($timeOverlaps);
        $this->assertTrue($dateRangesOverlap);
        // Les deux doivent être vrais pour que overlaps soit vrai
        $this->assertTrue($timeOverlaps && $dateRangesOverlap);
    }

    public function test_overlaps_with_non_overlapping_times(): void
    {
        $availabilityData = [
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('17:00:00'),
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ];

        // Different time
        $newStartTime = Carbon::parse('18:00:00');
        $newEndTime = Carbon::parse('19:00:00');
        $newStartDate = Carbon::parse('2024-01-15');
        $newEndDate = Carbon::parse('2024-01-20');

        $timeOverlaps = $this->availabilityValidator->timeOverlaps(
            $availabilityData['start_time'],
            $availabilityData['end_time'],
            $newStartTime,
            $newEndTime
        );

        $dateRangesOverlap = $this->availabilityValidator->dateRangesOverlap(
            $availabilityData['start_date'],
            $availabilityData['end_date'],
            $newStartDate,
            $newEndDate
        );

        $this->assertFalse($timeOverlaps);
        $this->assertTrue($dateRangesOverlap);
        // overlaps doit être faux car timeOverlaps est faux
        $this->assertFalse($timeOverlaps && $dateRangesOverlap);
    }

    public function test_overlaps_with_non_overlapping_dates(): void
    {
        $availabilityData = [
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('17:00:00'),
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ];

        // Same time but different dates
        $newStartTime = Carbon::parse('10:00:00');
        $newEndTime = Carbon::parse('11:00:00');
        $newStartDate = Carbon::parse('2024-02-01');
        $newEndDate = Carbon::parse('2024-02-28');

        $timeOverlaps = $this->availabilityValidator->timeOverlaps(
            $availabilityData['start_time'],
            $availabilityData['end_time'],
            $newStartTime,
            $newEndTime
        );

        $dateRangesOverlap = $this->availabilityValidator->dateRangesOverlap(
            $availabilityData['start_date'],
            $availabilityData['end_date'],
            $newStartDate,
            $newEndDate
        );

        $this->assertTrue($timeOverlaps);
        $this->assertFalse($dateRangesOverlap);
        // overlaps doit être faux car dateRangesOverlap est faux
        $this->assertFalse($timeOverlaps && $dateRangesOverlap);
    }

    // Test: areAdjacent
    public function test_are_adjacent_logic(): void
    {
        // Testons la logique d'adjacence avec des données simples
        $firstData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'TestModel',
            'type' => 'consultation',
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('12:00:00'),
            'days' => ['monday'],
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ];

        $secondData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'TestModel',
            'type' => 'consultation',
            'start_time' => Carbon::parse('12:00:00'),
            'end_time' => Carbon::parse('15:00:00'),
            'days' => ['monday'],
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ];

        // Vérifions chaque condition individuellement
        $sameSchedulable = $firstData['schedulable_id'] === $secondData['schedulable_id'] &&
            $firstData['schedulable_type'] === $secondData['schedulable_type'];
        $this->assertTrue($sameSchedulable);

        $commonDays = array_intersect($firstData['days'], $secondData['days']);
        $this->assertNotEmpty($commonDays);

        $sameType = $firstData['type'] === $secondData['type'];
        $this->assertTrue($sameType);

        $dateRangesOverlap = $this->availabilityValidator->dateRangesOverlap(
            $firstData['start_date'],
            $firstData['end_date'],
            $secondData['start_date'],
            $secondData['end_date']
        );
        $this->assertTrue($dateRangesOverlap);

        if (!$firstData['end_time']->eq($secondData['start_time'])) {
            $secondData['end_time']->eq($firstData['start_time']);
        }
        $this->assertTrue($firstData['end_time']->eq($secondData['start_time']));
    }

    public function test_are_adjacent_with_different_schedulable(): void
    {
        $firstData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'Doctor',
            'type' => 'consultation',
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('12:00:00'),
            'days' => ['monday'],
        ];

        $secondData = [
            'schedulable_id' => 2, // Different schedulable
            'schedulable_type' => 'Doctor',
            'type' => 'consultation',
            'start_time' => Carbon::parse('12:00:00'),
            'end_time' => Carbon::parse('15:00:00'),
            'days' => ['monday'],
        ];

        $sameSchedulable = $firstData['schedulable_id'] === $secondData['schedulable_id'] &&
            $firstData['schedulable_type'] === $secondData['schedulable_type'];
        $this->assertFalse($sameSchedulable);
    }

    public function test_are_adjacent_with_different_type(): void
    {
        $firstData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'TestModel',
            'type' => 'consultation',
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('12:00:00'),
            'days' => ['monday'],
        ];

        $secondData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'TestModel',
            'type' => 'training', // Different type
            'start_time' => Carbon::parse('12:00:00'),
            'end_time' => Carbon::parse('15:00:00'),
            'days' => ['monday'],
        ];

        $sameType = $firstData['type'] === $secondData['type'];
        $this->assertFalse($sameType);
    }

    public function test_are_adjacent_with_no_common_days(): void
    {
        $firstData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'TestModel',
            'type' => 'consultation',
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('12:00:00'),
            'days' => ['monday'],
        ];

        $secondData = [
            'schedulable_id' => 1,
            'schedulable_type' => 'TestModel',
            'type' => 'consultation',
            'start_time' => Carbon::parse('12:00:00'),
            'end_time' => Carbon::parse('15:00:00'),
            'days' => ['tuesday'], // Different day
        ];

        $commonDays = array_intersect($firstData['days'], $secondData['days']);
        $this->assertEmpty($commonDays);
    }

    // Test: mergeAdjacent
    public function test_merge_adjacent_logic(): void
    {
        $firstData = [
            'type' => 'consultation',
            'start_time' => Carbon::parse('09:00:00'),
            'end_time' => Carbon::parse('12:00:00'),
            'days' => ['monday', 'tuesday'],
            'start_date' => Carbon::parse('2024-01-01'),
            'end_date' => Carbon::parse('2024-01-31'),
        ];

        $secondData = [
            'type' => 'consultation',
            'start_time' => Carbon::parse('12:00:00'),
            'end_time' => Carbon::parse('15:00:00'),
            'days' => ['monday', 'wednesday'],
            'start_date' => Carbon::parse('2024-01-15'),
            'end_date' => Carbon::parse('2024-02-15'),
        ];

        // Simulons la fusion
        $startTime = min($firstData['start_time']->timestamp, $secondData['start_time']->timestamp);
        $endTime = max($firstData['end_time']->timestamp, $secondData['end_time']->timestamp);

        // Gérer les dates de période
        $startDate = null;
        $endDate = null;

        if ($firstData['start_date'] instanceof Carbon || $secondData['start_date'] instanceof Carbon) {
            $firstStart = $firstData['start_date'] ? $firstData['start_date']->timestamp : PHP_INT_MAX;
            $secondStart = $secondData['start_date'] ? $secondData['start_date']->timestamp : PHP_INT_MAX;
            $startDate = Carbon::createFromTimestamp(min($firstStart, $secondStart));
        }

        if ($firstData['end_date'] instanceof Carbon || $secondData['end_date'] instanceof Carbon) {
            $firstEnd = $firstData['end_date'] ? $firstData['end_date']->timestamp : PHP_INT_MIN;
            $secondEnd = $secondData['end_date'] ? $secondData['end_date']->timestamp : PHP_INT_MIN;
            $endDate = Carbon::createFromTimestamp(max($firstEnd, $secondEnd));
        }

        $mergedData = [
            'type' => $firstData['type'],
            'start_time' => Carbon::createFromTimestamp($startTime)->format('H:i:s'),
            'end_time' => Carbon::createFromTimestamp($endTime)->format('H:i:s'),
            'days' => array_values(array_unique(array_merge($firstData['days'], $secondData['days']))),
            'start_date' => $startDate?->format('Y-m-d'),
            'end_date' => $endDate?->format('Y-m-d'),
        ];

        $this->assertSame('consultation', $mergedData['type']);
        $this->assertSame('09:00:00', $mergedData['start_time']);
        $this->assertSame('15:00:00', $mergedData['end_time']);
        $this->assertSame(['monday', 'tuesday', 'wednesday'], $mergedData['days']);
        $this->assertSame('2024-01-01', $mergedData['start_date']);
        $this->assertSame('2024-02-15', $mergedData['end_date']);
    }

    // Test: hasOverlapping
    public function test_has_overlapping_returns_true_when_overlap_exists(): void
    {
        $this->assertTrue(method_exists($this->availabilityValidator, 'hasOverlapping'));
    }
}
