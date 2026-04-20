<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Config\RosterConfig;
use Roster\Services\ScheduleService;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Unit tests for ScheduleService duration validation.
 *
 * Tests that the service properly validates minimum duration requirements
 * and prevents infinite loops with invalid durations.
 */
final class ScheduleServiceDurationValidationTest extends TestCase
{
    private TestSchedulable $schedulable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schedulable = TestSchedulable::create(['name' => 'Test Entity']);
    }

    /**
     * Helper to get ScheduleService instance through the helper.
     */
    private function getScheduleService(): ScheduleService
    {
        // Arrange : Create a dummy availability first to have a valid context
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-12-31',
        ]);

        // Return the service through the helper
        return schedule_for($availability);
    }

    /**
     * Test that findNextSlot throws exception when duration is invalid (<= 0).
     */
    public function test_find_next_slot_throws_exception_when_duration_is_zero_or_negative(): void
    {
        // Arrange : Get service and define invalid durations
        $service = $this->getScheduleService();
        $invalidDurations = [0, -1, -5, -10];

        // Act & Assert : Each invalid duration should throw an exception
        foreach ($invalidDurations as $duration) {
            try {
                $service->findNextSlot(
                    durationMinutes: $duration,
                    startFrom: Carbon::now(),
                    endBefore: Carbon::now()->addDays(7)
                );
                $this->fail("Duration {$duration} should have thrown an exception");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('Duration minutes must be greater than 0', $e->getMessage());
            }
        }
    }

    /**
     * Test that findNextSlot throws exception when duration is below minimum (1-9 minutes).
     */
    public function test_find_next_slot_throws_exception_when_duration_below_minimum(): void
    {
        // Arrange : Get service and define durations below minimum
        $service = $this->getScheduleService();
        $invalidDurations = range(1, 9);

        // Act & Assert : Each duration below 10 minutes should be rejected
        foreach ($invalidDurations as $duration) {
            try {
                $service->findNextSlot(
                    durationMinutes: $duration,
                    startFrom: Carbon::now(),
                    endBefore: Carbon::now()->addDays(7)
                );
                $this->fail("Duration {$duration} minutes should have been rejected");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString(
                    'Duration minutes must be at least ' . RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES . ' minutes',
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Test that findNextSlot accepts duration exactly 10 minutes or greater.
     */
    public function test_find_next_slot_accepts_valid_durations(): void
    {
        // Arrange : Get service and define valid durations
        $service = $this->getScheduleService();
        $validDurations = [10, 15, 30, 45, 60, 90, 120, 180, 240, 480, 720];

        // Act & Assert : Each valid duration should be accepted
        foreach ($validDurations as $duration) {
            try {
                $result = $service->findNextSlot(
                    durationMinutes: $duration,
                    startFrom: Carbon::now(),
                    endBefore: Carbon::now()->addDays(7)
                );
                $this->assertTrue($result === null || is_array($result), "Duration {$duration} should be accepted");
            } catch (\InvalidArgumentException $e) {
                $this->fail("Duration {$duration} minutes should be valid, got: " . $e->getMessage());
            }
        }
    }

    /**
     * Test that findAvailableSlots throws exception when duration is invalid (<= 0).
     */
    public function test_find_available_slots_throws_exception_when_duration_is_zero_or_negative(): void
    {
        // Arrange : Get service and define invalid durations
        $service = $this->getScheduleService();
        $invalidDurations = [0, -1, -5, -10];

        // Act & Assert : Each invalid duration should throw an exception
        foreach ($invalidDurations as $duration) {
            try {
                $service->findAvailableSlots(
                    startDate: Carbon::now(),
                    endDate: Carbon::now()->addDays(7),
                    durationMinutes: $duration
                );
                $this->fail("Duration {$duration} should have thrown an exception");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('Duration minutes must be greater than 0', $e->getMessage());
            }
        }
    }

    /**
     * Test that findAvailableSlots throws exception when duration is below minimum (1-9 minutes).
     */
    public function test_find_available_slots_throws_exception_when_duration_below_minimum(): void
    {
        // Arrange : Get service and define durations below minimum
        $service = $this->getScheduleService();
        $invalidDurations = range(1, 9);

        // Act & Assert : Each duration below 10 minutes should be rejected
        foreach ($invalidDurations as $duration) {
            try {
                $service->findAvailableSlots(
                    startDate: Carbon::now(),
                    endDate: Carbon::now()->addDays(7),
                    durationMinutes: $duration
                );
                $this->fail("Duration {$duration} minutes should have been rejected");
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString(
                    'Duration minutes must be at least ' . RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES . ' minutes',
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Test that findAvailableSlots accepts duration exactly 10 minutes or greater.
     */
    public function test_find_available_slots_accepts_valid_durations(): void
    {
        // Arrange : Get service and define valid durations
        $service = $this->getScheduleService();
        $validDurations = [10, 15, 30, 45, 60, 90, 120];

        // Act & Assert : Each valid duration should be accepted
        foreach ($validDurations as $duration) {
            try {
                $result = $service->findAvailableSlots(
                    startDate: Carbon::now(),
                    endDate: Carbon::now()->addDays(7),
                    durationMinutes: $duration
                );
                $this->assertInstanceOf(Collection::class, $result);
            } catch (\InvalidArgumentException $e) {
                $this->fail("Duration {$duration} minutes should be valid, got: " . $e->getMessage());
            }
        }
    }

    /**
     * Test that the absolute minimum duration constant is 10.
     */
    public function test_absolute_min_duration_constant_is_10(): void
    {
        // Assert : The constant should be 10
        $this->assertEquals(10, RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES);
    }

    /**
     * Test that the max iterations constant is 10000.
     */
    public function test_max_iterations_constant_is_10000(): void
    {
        // Assert : The constant should be 10000
        $this->assertEquals(10000, RosterConfig::MAX_ITERATIONS);
    }

    /**
     * Test that validation message is clear and contains the expected values.
     */
    public function test_validation_message_is_clear(): void
    {
        // Arrange : Get service and define invalid duration
        $service = $this->getScheduleService();

        // Act : Attempt to use invalid duration
        try {
            $service->findNextSlot(durationMinutes: 7);
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            // Assert : Exception message contains expected values
            $this->assertStringContainsString((string) RosterConfig::ABSOLUTE_MIN_DURATION_MINUTES, $e->getMessage());
            $this->assertStringContainsString('7', $e->getMessage());
            $this->assertStringContainsString('performance reasons', $e->getMessage());
        }
    }
}
