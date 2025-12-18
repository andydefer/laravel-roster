<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Core;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Services\Core\SlotFinderService;
use Roster\Services\Core\ValidationService;

final class SlotFinderServiceTest extends TestCase
{
    private SlotFinderService $slotFinderService;

    protected function setUp(): void
    {
        parent::setUp();
        $validationService = new ValidationService();
        $this->slotFinderService = new SlotFinderService($validationService);
    }

    public function test_get_available_slots_from_impediments_with_no_impediments(): void
    {
        $availability = $this->createMock(Availability::class);
        $start = Carbon::parse('2024-01-01 09:00:00');
        $end = Carbon::parse('2024-01-01 17:00:00');
        $impediments = new Collection();

        $result = $this->slotFinderService->getAvailableSlotsFromImpediments(
            $availability,
            $start,
            $end,
            $impediments
        );

        $this->assertCount(1, $result);
        $this->assertSame($start->format('Y-m-d H:i:s'), $result[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame($end->format('Y-m-d H:i:s'), $result[0]['end']->format('Y-m-d H:i:s'));
    }

    public function test_get_available_slots_from_impediments_with_single_impediment(): void
    {
        $availability = $this->createMock(Availability::class);
        $start = Carbon::parse('2024-01-01 09:00:00');
        $end = Carbon::parse('2024-01-01 17:00:00');

        $impediment = $this->createMock(Impediment::class);
        $impediment->method('__get')
            ->willReturnCallback(function ($property): ?Carbon {
                return match ($property) {
                    'start_datetime' => Carbon::parse('2024-01-01 12:00:00'),
                    'end_datetime' => Carbon::parse('2024-01-01 13:00:00'),
                    default => null,
                };
            });

        $impediments = new Collection([$impediment]);

        $result = $this->slotFinderService->getAvailableSlotsFromImpediments(
            $availability,
            $start,
            $end,
            $impediments
        );

        $this->assertCount(2, $result);

        // First slot: 09:00 - 12:00
        $this->assertSame('2024-01-01 09:00:00', $result[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 12:00:00', $result[0]['end']->format('Y-m-d H:i:s'));

        // Second slot: 13:00 - 17:00
        $this->assertSame('2024-01-01 13:00:00', $result[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 17:00:00', $result[1]['end']->format('Y-m-d H:i:s'));
    }

    public function test_get_available_slots_from_impediments_with_multiple_impediments(): void
    {
        $availability = $this->createMock(Availability::class);
        $start = Carbon::parse('2024-01-01 09:00:00');
        $end = Carbon::parse('2024-01-01 17:00:00');

        $impediment1 = $this->createMock(Impediment::class);
        $impediment1->method('__get')
            ->willReturnCallback(function ($property): ?Carbon {
                return match ($property) {
                    'start_datetime' => Carbon::parse('2024-01-01 10:00:00'),
                    'end_datetime' => Carbon::parse('2024-01-01 11:00:00'),
                    default => null,
                };
            });

        $impediment2 = $this->createMock(Impediment::class);
        $impediment2->method('__get')
            ->willReturnCallback(function ($property): ?Carbon {
                return match ($property) {
                    'start_datetime' => Carbon::parse('2024-01-01 13:00:00'),
                    'end_datetime' => Carbon::parse('2024-01-01 14:30:00'),
                    default => null,
                };
            });

        $impediments = new Collection([$impediment1, $impediment2]);

        $result = $this->slotFinderService->getAvailableSlotsFromImpediments(
            $availability,
            $start,
            $end,
            $impediments
        );

        $this->assertCount(3, $result);

        // Slot 1: 09:00 - 10:00
        $this->assertSame('2024-01-01 09:00:00', $result[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:00:00', $result[0]['end']->format('Y-m-d H:i:s'));

        // Slot 2: 11:00 - 13:00
        $this->assertSame('2024-01-01 11:00:00', $result[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 13:00:00', $result[1]['end']->format('Y-m-d H:i:s'));

        // Slot 3: 14:30 - 17:00
        $this->assertSame('2024-01-01 14:30:00', $result[2]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 17:00:00', $result[2]['end']->format('Y-m-d H:i:s'));
    }

    public function test_get_available_slots_from_impediments_with_overlapping_impediments(): void
    {
        $availability = $this->createMock(Availability::class);
        $start = Carbon::parse('2024-01-01 09:00:00');
        $end = Carbon::parse('2024-01-01 17:00:00');

        $impediment1 = $this->createMock(Impediment::class);
        $impediment1->method('__get')
            ->willReturnCallback(function ($property): ?Carbon {
                return match ($property) {
                    'start_datetime' => Carbon::parse('2024-01-01 10:00:00'),
                    'end_datetime' => Carbon::parse('2024-01-01 12:00:00'),
                    default => null,
                };
            });

        $impediment2 = $this->createMock(Impediment::class);
        $impediment2->method('__get')
            ->willReturnCallback(function ($property): ?Carbon {
                return match ($property) {
                    'start_datetime' => Carbon::parse('2024-01-01 11:30:00'),
                    'end_datetime' => Carbon::parse('2024-01-01 13:00:00'),
                    default => null,
                };
            });

        $impediments = new Collection([$impediment1, $impediment2]);

        $result = $this->slotFinderService->getAvailableSlotsFromImpediments(
            $availability,
            $start,
            $end,
            $impediments
        );

        $this->assertCount(2, $result);

        // Slot 1: 09:00 - 10:00
        $this->assertSame('2024-01-01 09:00:00', $result[0]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:00:00', $result[0]['end']->format('Y-m-d H:i:s'));

        // Slot 2: 13:00 - 17:00 (uses the later end time from impediment2)
        $this->assertSame('2024-01-01 13:00:00', $result[1]['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 17:00:00', $result[1]['end']->format('Y-m-d H:i:s'));
    }
}
