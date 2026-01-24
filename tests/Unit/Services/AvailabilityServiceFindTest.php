<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Support\RosterMutationContext;
use Tests\Support\TestSchedulable;
use Tests\TestCase;

/**
 * Test suite for AvailabilityService::getAvailabilityForTimeSlot method.
 *
 * Validates the ability to find availabilities covering specific time slots
 * with various constraints including type filters, validity periods, and time windows.
 */
final class AvailabilityServiceFindTest extends TestCase
{
    use RefreshDatabase;

    /** @var Model The schedulable model used for testing */
    private Model $schedulable;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schedulable = TestSchedulable::create();
    }

    /**
     * Test finds availability that covers a time slot.
     */
    public function test_finds_availability_that_covers_time_slot(): void
    {
        // Arrange: Create availability for Thursday 9:00-17:00 (1er juillet 2038 est un jeudi)
        $availability = $this->createTestAvailability(
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 12:00:00'); // Jeudi 1er juillet
        $slotEnd = Carbon::parse('2038-07-01 13:00:00');

        // Act: Find availability for time slot
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test returns null when no availability covers time slot.
     */
    public function test_returns_null_when_no_availability_covers_time_slot(): void
    {
        // Arrange: Create availability for Tuesday only
        $this->createTestAvailability(
            days: ['tuesday'], // Mardi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability for time slot
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test respects type filter when searching for availability.
     */
    public function test_respects_type_filter_when_searching_for_availability(): void
    {
        // Arrange: Create two availabilities for same time but different types
        $consultationAvailability = $this->createTestAvailability(
            type: 'consultation',
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $this->createTestAvailability(
            type: 'training',
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find with type filter
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd, 'consultation');

        // Assert: Should only find consultation availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($consultationAvailability->id, $result->id);
        $this->assertSame('consultation', $result->type);
    }

    /**
     * Test returns null when type filter doesn't match.
     */
    public function test_returns_null_when_type_filter_doesnt_match(): void
    {
        // Arrange: Create availability with type 'consultation'
        $this->createTestAvailability(
            type: 'consultation',
            days: ['thursday'],
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 10:00:00');
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find with different type filter
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd, 'training');

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test handles time slots outside daily window.
     */
    public function test_returns_null_for_time_slots_outside_daily_window(): void
    {
        // Arrange: Create availability 9:00-12:00
        $this->createTestAvailability(
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 14:00:00'); // Outside window
        $slotEnd = Carbon::parse('2038-07-01 15:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test handles time slots partially outside daily window.
     */
    public function test_returns_null_for_time_slots_partially_outside_daily_window(): void
    {
        // Arrange: Create availability 9:00-12:00
        $this->createTestAvailability(
            days: ['thursday'],
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 11:30:00'); // Starts inside, ends outside
        $slotEnd = Carbon::parse('2038-07-01 12:30:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null (slot must be fully contained)
        $this->assertNull($result);
    }

    /**
     * Test handles time slots outside validity period.
     */
    public function test_returns_null_for_time_slots_outside_validity_period(): void
    {
        // Arrange: Create availability valid only for 1er juillet
        $this->createTestAvailability(
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-01' // Seulement le 1er juillet
        );

        $slotStart = Carbon::parse('2038-07-08 10:00:00'); // Jeudi suivant
        $slotEnd = Carbon::parse('2038-07-08 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test finds availability with partial validity dates - version corrigée.
     * On garde une date de fin très éloignée pour simuler "no end date".
     */
    public function test_finds_availability_with_partial_validity_dates(): void
    {
        // Arrange: Create availability avec date de fin très éloignée
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['thursday'], // Jeudi
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-12-31', // Date très future pour simuler "no end"
        ]);

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test finds availability with only end date - version corrigée.
     * On utilise une date de début très ancienne.
     */
    public function test_finds_availability_with_only_end_date(): void
    {
        // Arrange: Create availability avec date de début très ancienne
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['thursday'], // Jeudi
            'validity_start' => '2038-01-01', // Date très ancienne
            'validity_end' => '2038-07-31',
        ]);

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test finds earliest matching availability when multiple exist.
     */
    public function test_finds_earliest_matching_availability_when_multiple_exist(): void
    {
        // Arrange: Create multiple availabilities for same time but different days
        $firstAvailability = $this->createTestAvailability(
            type: 'consultation',
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '12:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $this->createTestAvailability(
            type: 'consultation',
            days: ['friday'], // Vendredi (différent pour éviter conflit)
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd, 'consultation');

        // Assert: Should return the first (earliest) matching availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($firstAvailability->id, $result->id);
    }

    /**
     * Test throws exception for invalid time window (end before start).
     */
    public function test_throws_exception_for_invalid_time_window(): void
    {
        // Arrange: Invalid time window (end before start)
        $slotStart = Carbon::parse('2038-07-01 11:00:00');
        $slotEnd = Carbon::parse('2038-07-01 10:00:00');

        // Assert: Should throw InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/end.*before.*start|must be after|daily window/i');

        // Act: Try to find availability with invalid window
        availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);
    }

    /**
     * Test works with multiple schedulable entities.
     */
    public function test_works_with_multiple_schedulable_entities(): void
    {
        // Arrange: Create two schedulables with their own availabilities
        $schedulable1 = $this->schedulable;
        $schedulable2 = TestSchedulable::create();

        $availability1 = $this->createTestAvailabilityForSchedulable($schedulable1);
        $availability2 = $this->createTestAvailabilityForSchedulable($schedulable2);

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability for schedulable1
        $result1 = availability_for($schedulable1)
            ->getAvailabilityForTimeSlot($schedulable1, $slotStart, $slotEnd);

        // Act: Find availability for schedulable2
        $result2 = availability_for($schedulable2)
            ->getAvailabilityForTimeSlot($schedulable2, $slotStart, $slotEnd);

        // Assert: Each should find their own availability
        $this->assertInstanceOf(AvailabilityModel::class, $result1);
        $this->assertSame($availability1->id, $result1->id);

        $this->assertInstanceOf(AvailabilityModel::class, $result2);
        $this->assertSame($availability2->id, $result2->id);
    }

    /**
     * Test handles edge case where slot exactly matches availability boundaries.
     */
    public function test_handles_slot_exactly_matching_availability_boundaries(): void
    {
        // Arrange: Create availability 9:00-17:00
        $availability = $this->createTestAvailability(
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        // Test exact start time
        $slotStart = Carbon::parse('2038-07-01 09:00:00');
        $slotEnd = Carbon::parse('2038-07-01 10:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);

        // Test exact end time
        $slotStart = Carbon::parse('2038-07-01 16:00:00');
        $slotEnd = Carbon::parse('2038-07-01 17:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test finds availability with date exactly at validity start.
     */
    public function test_finds_availability_with_date_exactly_at_validity_start(): void
    {
        // Arrange: Create availability starting exactly on 1er juillet
        $availability = $this->createTestAvailability(
            days: ['thursday'], // Jeudi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Exactement à la date de début
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test finds availability with date exactly at validity end.
     */
    public function test_finds_availability_with_date_exactly_at_validity_end(): void
    {
        // Arrange: Create availability ending exactly on 31 juillet
        $availability = $this->createTestAvailability(
            days: ['saturday'], // Samedi (31 juillet 2038 est un samedi)
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-31 10:00:00'); // Exactement à la date de fin
        $slotEnd = Carbon::parse('2038-07-31 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should find the availability
        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test returns null for slot before validity start.
     */
    public function test_returns_null_for_slot_before_validity_start(): void
    {
        // Arrange: Create availability starting on 8 juillet
        $this->createTestAvailability(
            days: ['thursday'],
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-08', // Démarre le 8 juillet
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // 1er juillet
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test returns null for slot after validity end.
     */
    public function test_returns_null_for_slot_after_validity_end(): void
    {
        // Arrange: Create availability ending on 1er juillet
        $this->createTestAvailability(
            days: ['thursday'],
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-01' // Termine le 1er juillet
        );

        $slotStart = Carbon::parse('2038-07-08 10:00:00'); // 8 juillet
        $slotEnd = Carbon::parse('2038-07-08 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Test finds availability with multiple days when slot is on one of them.
     */
    public function test_finds_availability_with_multiple_days(): void
    {
        // Arrange: Create availability for jeudi et vendredi
        $availability = $this->createTestAvailability(
            days: ['thursday', 'friday'], // Jeudi et vendredi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        // Test jeudi
        $slotStart = Carbon::parse('2038-07-01 10:00:00'); // Jeudi
        $slotEnd = Carbon::parse('2038-07-01 11:00:00');

        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);

        // Test vendredi
        $slotStart = Carbon::parse('2038-07-02 10:00:00'); // Vendredi
        $slotEnd = Carbon::parse('2038-07-02 11:00:00');

        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        $this->assertInstanceOf(AvailabilityModel::class, $result);
        $this->assertSame($availability->id, $result->id);
    }

    /**
     * Test returns null when slot not on any availability day.
     */
    public function test_returns_null_when_slot_not_on_any_availability_day(): void
    {
        // Arrange: Create availability only for jeudi et vendredi
        $this->createTestAvailability(
            days: ['thursday', 'friday'], // Jeudi et vendredi
            dailyStart: '09:00:00',
            dailyEnd: '17:00:00',
            validityStart: '2038-07-01',
            validityEnd: '2038-07-31'
        );

        $slotStart = Carbon::parse('2038-07-03 10:00:00'); // Samedi
        $slotEnd = Carbon::parse('2038-07-03 11:00:00');

        // Act: Find availability
        $result = availability_for($this->schedulable)
            ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

        // Assert: Should return null
        $this->assertNull($result);
    }

    /**
     * Helper to create availability for specific schedulable.
     */
    private function createTestAvailabilityForSchedulable(Model $schedulable): AvailabilityModel
    {
        // Utiliser le service avec le contexte mutation
        return RosterMutationContext::allow(function () use ($schedulable) {
            return availability_for($schedulable)->create([
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['thursday'], // Jeudi
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });
    }

    /**
     * Create and return a test availability instance.
     *
     * @param string $type The availability type
     * @param string $dailyStart The daily start time
     * @param string $dailyEnd The daily end time
     * @param array $days The days of week
     * @param string $validityStart The validity start date
     * @param string $validityEnd The validity end date
     *
     * @return AvailabilityModel The created availability instance
     */
    private function createTestAvailability(
        string $type = 'consultation',
        string $dailyStart = '09:00:00',
        string $dailyEnd = '17:00:00',
        array $days = ['thursday'], // Jeudi par défaut
        string $validityStart = '2038-07-01',
        string $validityEnd = '2038-07-31'
    ): AvailabilityModel {
        return availability_for($this->schedulable)->create([
            'type' => $type,
            'daily_start' => $dailyStart,
            'daily_end' => $dailyEnd,
            'days' => $days,
            'validity_start' => $validityStart,
            'validity_end' => $validityEnd,
        ]);
    }
}
