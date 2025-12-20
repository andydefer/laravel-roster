<?php

declare(strict_types=1);

namespace Tests\Feature\Facades;

use PHPUnit\Framework\Attributes\CoversClass;
use Roster\Services\ImpedimentService;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Exceptions\OverlappingImpedimentException;
use Roster\Exceptions\ScheduleImpedimentOverlapException;
use Roster\Exceptions\TimeRangeValidationException;
use Roster\Exceptions\ValidationException;
use Roster\Facades\Impediment as ImpedimentFacade;
use Roster\Facades\Schedule;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Tests\TestCase;

/**
 * Tests for the Impediment facade functionality.
 */
#[CoversClass(ImpedimentFacade::class)]
final class ImpedimentFacadeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The schedulable model instance used for testing.
     */
    private Model $schedulableModel;

    /**
     * June availability for testing.
     */
    private Availability $juneAvailability;

    /**
     * July availability for testing.
     */
    private Availability $julyAvailability;

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

        $this->schedulableModel->id = 1;
        $this->schedulableModel->save();

        $this->juneAvailability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $this->julyAvailability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['tuesday'],
            'start_date' => '2038-07-01',
            'end_date' => '2038-07-31',
        ]);
    }

    /**
     * Test that impediment creation fails when overlapping with existing impediment.
     */
    public function test_impediment_creation_fails_when_overlapping_with_existing_impediment(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'First impediment',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $this->expectException(OverlappingImpedimentException::class);
        $this->expectExceptionMessage('Time slot overlaps with an existing impediment');

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Overlapping impediment',
                'start_datetime' => '2038-06-07 10:30:00',
                'end_datetime' => '2038-06-07 11:30:00',
            ]
        );
    }

    /**
     * Test that impediment creation fails when overlapping with existing schedule.
     */
    public function test_impediment_creation_fails_when_overlapping_with_existing_schedule(): void
    {
        Schedule::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'title' => 'Client meeting',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $this->expectException(ScheduleImpedimentOverlapException::class);
        $this->expectExceptionMessage('Cannot schedule when impediment exists, or create impediment that overlaps with schedule');

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Impediment overlapping schedule',
                'start_datetime' => '2038-06-07 10:30:00',
                'end_datetime' => '2038-06-07 11:30:00',
            ]
        );
    }

    /**
     * Test that non-overlapping impediment can be created successfully.
     */
    public function test_non_overlapping_impediment_can_be_created_successfully(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'First impediment',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Non-overlapping impediment',
                'start_datetime' => '2038-06-07 11:30:00',
                'end_datetime' => '2038-06-07 12:30:00',
            ]
        );

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Non-overlapping impediment', $impediment->reason);
    }

    /**
     * Test that impediment update fails when it would cause overlap with existing impediment.
     */
    public function test_impediment_update_fails_when_overlapping_with_existing_impediment(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'First impediment',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Second impediment',
                'start_datetime' => '2038-06-07 12:00:00',
                'end_datetime' => '2038-06-07 13:00:00',
            ]
        );

        $this->expectException(ValidationException::class);

        ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'start_datetime' => '2038-06-07 10:30:00',
                'end_datetime' => '2038-06-07 11:30:00',
            ]
        );
    }

    /**
     * Test that impediment update fails when it would cause overlap with existing schedule.
     */
    public function test_impediment_update_fails_when_overlapping_with_existing_schedule(): void
    {
        Schedule::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'title' => 'Client meeting',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Impediment to update',
                'start_datetime' => '2038-06-07 12:00:00',
                'end_datetime' => '2038-06-07 13:00:00',
            ]
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot create impediment that overlaps with existing schedule');

        ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'start_datetime' => '2038-06-07 10:30:00',
                'end_datetime' => '2038-06-07 11:30:00',
            ]
        );
    }

    /**
     * Test that impediment update succeeds when changing non-overlapping attributes.
     */
    public function test_impediment_update_succeeds_when_changing_non_overlapping_attributes(): void
    {
        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Initial reason',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
                'metadata' => ['priority' => 'low'],
            ]
        );

        $updateResult = ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'reason' => 'Updated reason',
                'metadata' => ['priority' => 'high'],
            ]
        );

        $this->assertTrue($updateResult);

        $impediment->refresh();
        $this->assertSame('Updated reason', $impediment->reason);
        $this->assertSame(['priority' => 'high'], $impediment->metadata);
        $this->assertSame('2038-06-07 10:00:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 11:00:00', $impediment->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test that impediment update can move to different time slot if no overlaps.
     */
    public function test_impediment_update_succeeds_when_moving_to_non_overlapping_slot(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'First impediment',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Second impediment',
                'start_datetime' => '2038-06-07 12:00:00',
                'end_datetime' => '2038-06-07 13:00:00',
            ]
        );

        $updateResult = ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'start_datetime' => '2038-06-07 14:00:00',
                'end_datetime' => '2038-06-07 15:00:00',
            ]
        );

        $this->assertTrue($updateResult);

        $impediment->refresh();
        $this->assertSame('2038-06-07 14:00:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 15:00:00', $impediment->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test that impediment update fails when moving to overlap with itself (excluding self).
     */
    public function test_impediment_update_should_exclude_self_from_overlap_check(): void
    {
        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Impediment to update',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $updateResult = ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'start_datetime' => '2038-06-07 10:15:00',
                'end_datetime' => '2038-06-07 11:15:00',
            ]
        );

        $this->assertTrue($updateResult, 'Should allow update when only overlapping with itself (excluded)');

        $impediment->refresh();
        $this->assertSame('2038-06-07 10:15:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
        $this->assertSame('2038-06-07 11:15:00', $impediment->end_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test that impediment creation fails when time slot is partially outside availability.
     */
    public function test_impediment_creation_fails_when_outside_availability_hours(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Late night work',
                'start_datetime' => '2038-06-07 18:00:00',
                'end_datetime' => '2038-06-07 19:00:00',
            ]
        );
    }

    /**
     * Test that impediment creation fails when on wrong day of week.
     */
    public function test_impediment_creation_fails_when_on_wrong_day_of_week(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No matching availability found');

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Tuesday meeting',
                'start_datetime' => '2038-06-08 10:00:00',
                'end_datetime' => '2038-06-08 11:00:00',
            ]
        );
    }

    /**
     * Test that impediment creation fails when dates are reversed (end before start).
     */
    public function test_impediment_creation_fails_when_end_before_start(): void
    {
        $this->expectException(TimeRangeValidationException::class);

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Reversed times',
                'start_datetime' => '2038-06-07 11:00:00',
                'end_datetime' => '2038-06-07 10:00:00',
            ]
        );
    }

    /**
     * Test that impediment creation fails when duration is too short.
     */
    public function test_impediment_creation_fails_when_duration_too_short(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('must be at least');

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Too short',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 10:01:00',
            ]
        );
    }

    /**
     * Test multiple impediments can coexist in different time slots.
     */
    public function test_multiple_non_overlapping_impediments_can_coexist(): void
    {
        $impediment1 = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Morning meeting',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $impediment2 = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Lunch meeting',
                'start_datetime' => '2038-06-07 11:30:00',
                'end_datetime' => '2038-06-07 12:30:00',
            ]
        );

        $impediment3 = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Afternoon workshop',
                'start_datetime' => '2038-06-07 14:00:00',
                'end_datetime' => '2038-06-07 15:00:00',
            ]
        );

        $this->assertInstanceOf(Impediment::class, $impediment1);
        $this->assertInstanceOf(Impediment::class, $impediment2);
        $this->assertInstanceOf(Impediment::class, $impediment3);

        $allImpediments = ImpedimentFacade::for($this->schedulableModel)->all();
        $this->assertCount(3, $allImpediments);
    }

    /**
     * Test impediment update that would move to different availability with no overlaps.
     */
    public function test_impediment_update_can_move_to_different_availability_if_no_overlaps(): void
    {
        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'June impediment',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $updateResult = ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'start_datetime' => '2038-07-06 10:00:00',
                'end_datetime' => '2038-07-06 11:00:00',
            ]
        );

        $this->assertTrue($updateResult);

        $impediment->refresh();
        $this->assertSame($this->julyAvailability->id, $impediment->availability_id);
        $this->assertSame('2038-07-06 10:00:00', $impediment->start_datetime->format('Y-m-d H:i:s'));
    }

    /**
     * Test impediment update fails when moving to different availability that has overlaps.
     */
    public function test_impediment_update_fails_when_moving_to_availability_with_existing_impediment(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->julyAvailability,
            [
                'reason' => 'Existing July impediment',
                'start_datetime' => '2038-07-06 10:00:00',
                'end_datetime' => '2038-07-06 11:00:00',
            ]
        );

        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'June impediment to move',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $this->expectException(ValidationException::class);

        ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'start_datetime' => '2038-07-06 10:30:00',
                'end_datetime' => '2038-07-06 11:30:00',
            ]
        );
    }

    /**
     * Test creating a new impediment through the facade.
     */
    public function test_facade_can_create_impediment(): void
    {
        $impedimentData = [
            'reason' => 'Out of office',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $impediment = ImpedimentFacade::for($this->schedulableModel)
            ->create($this->juneAvailability, $impedimentData);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('Out of office', $impediment->reason);
        $this->assertSame($this->juneAvailability->id, $impediment->availability_id);
    }

    /**
     * Test creating an impediment with a different availability.
     */
    public function test_facade_can_create_impediment_with_different_availability(): void
    {
        $impedimentData = [
            'reason' => 'July holiday',
            'start_datetime' => '2038-07-06 10:00:00',
            'end_datetime' => '2038-07-06 11:00:00',
        ];

        $impediment = ImpedimentFacade::for($this->schedulableModel)
            ->create($this->julyAvailability, $impedimentData);

        $this->assertInstanceOf(Impediment::class, $impediment);
        $this->assertSame('July holiday', $impediment->reason);
        $this->assertSame($this->julyAvailability->id, $impediment->availability_id);
    }

    /**
     * Test that the old create method without availability is deprecated.
     */
    public function test_facade_old_create_method_is_deprecated(): void
    {
        $impedimentData = [
            'reason' => 'Test',
            'start_datetime' => '2038-06-07 10:00:00',
            'end_datetime' => '2038-06-07 11:00:00',
        ];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead.'
        );

        ImpedimentFacade::for($this->schedulableModel)->create($impedimentData);
    }

    /**
     * Test finding an impediment by ID.
     */
    public function test_facade_can_find_impediment(): void
    {
        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Team meeting',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $foundImpediment = ImpedimentFacade::for($this->schedulableModel)->find($impediment->id);

        $this->assertInstanceOf(Impediment::class, $foundImpediment);
        $this->assertSame($impediment->id, $foundImpediment->id);
        $this->assertSame('Team meeting', $foundImpediment->reason);
    }

    /**
     * Test retrieving all impediments for the schedulable.
     */
    public function test_facade_can_get_all_impediments(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Morning briefing',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Client presentation',
                'start_datetime' => '2038-06-07 14:00:00',
                'end_datetime' => '2038-06-07 15:00:00',
            ]
        );

        /** @var Collection<int, Impediment> $impediments */
        $impediments = ImpedimentFacade::for($this->schedulableModel)->all();

        $this->assertCount(2, $impediments);
        $this->assertSame('Morning briefing', $impediments[0]->reason);
        $this->assertSame('Client presentation', $impediments[1]->reason);
    }

    /**
     * Test filtering impediments by date range.
     */
    public function test_facade_can_filter_impediments(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Morning sync',
                'start_datetime' => '2038-06-07 09:30:00',
                'end_datetime' => '2038-06-07 10:00:00',
            ]
        );

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Afternoon workshop',
                'start_datetime' => '2038-06-07 14:00:00',
                'end_datetime' => '2038-06-07 15:00:00',
            ]
        );

        $startDate = Carbon::parse('2038-06-07 12:00:00');
        $endDate = Carbon::parse('2038-06-07 16:00:00');

        $filteredImpediments = ImpedimentFacade::for($this->schedulableModel)
            ->whereStartDate($startDate)
            ->whereEndDate($endDate)
            ->get();

        $this->assertCount(1, $filteredImpediments);
        $this->assertSame('Afternoon workshop', $filteredImpediments->first()->reason);
    }

    /**
     * Test filtering impediments by reason.
     */
    public function test_facade_can_filter_by_reason(): void
    {
        $uniqueSchedulable = new class extends Model {
            protected $table = 'test_schedulables';

            public $timestamps = false;
        };
        $uniqueSchedulable->id = 999;
        $uniqueSchedulable->save();

        $uniqueAvailability = Availability::create([
            'schedulable_id' => $uniqueSchedulable->id,
            'schedulable_type' => get_class($uniqueSchedulable),
            'type' => 'consultation',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['monday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        ImpedimentFacade::for($uniqueSchedulable)->create(
            $uniqueAvailability,
            [
                'reason' => 'Doctor appointment only',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        ImpedimentFacade::for($uniqueSchedulable)->create(
            $uniqueAvailability,
            [
                'reason' => 'Team lunch meeting',
                'start_datetime' => '2038-06-07 12:00:00',
                'end_datetime' => '2038-06-07 13:00:00',
            ]
        );

        $filteredImpediments = ImpedimentFacade::for($uniqueSchedulable)
            ->resetFilters()
            ->whereReason('Doctor appointment only')
            ->get();
        $this->assertCount(1, $filteredImpediments);
        $this->assertSame('Doctor appointment only', $filteredImpediments->first()->reason);
    }

    /**
     * Test checking if a time slot is blocked.
     */
    public function test_facade_can_check_if_time_slot_is_blocked(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Sprint planning',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $blockedStart = Carbon::parse('2038-06-07 10:30:00');
        $blockedEnd = Carbon::parse('2038-06-07 10:45:00');

        $availableStart = Carbon::parse('2038-06-07 11:30:00');
        $availableEnd = Carbon::parse('2038-06-07 12:00:00');

        $this->assertTrue(
            ImpedimentFacade::for($this->schedulableModel)
                ->isTimeSlotBlocked($blockedStart, $blockedEnd)
        );

        $this->assertFalse(
            ImpedimentFacade::for($this->schedulableModel)
                ->isTimeSlotBlocked($availableStart, $availableEnd)
        );
    }

    /**
     * Test getting impediments between two dates.
     */
    public function test_facade_can_get_impediments_between_dates(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'June meeting',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->julyAvailability,
            [
                'reason' => 'July conference',
                'start_datetime' => '2038-07-06 14:00:00',
                'end_datetime' => '2038-07-06 15:00:00',
            ]
        );

        $start = Carbon::parse('2038-06-07 00:00:00');
        $end = Carbon::parse('2038-06-07 23:59:59');

        $impediments = ImpedimentFacade::for($this->schedulableModel)
            ->between($start, $end);

        $this->assertCount(1, $impediments);
        $this->assertSame('June meeting', $impediments->first()->reason);
    }

    /**
     * Test deleting an impediment.
     */
    public function test_facade_can_delete_impediment(): void
    {
        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Temporary blockage',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
            ]
        );

        $initialFind = ImpedimentFacade::for($this->schedulableModel)->find($impediment->id);
        $this->assertInstanceOf(Impediment::class, $initialFind);

        $deletionResult = ImpedimentFacade::for($this->schedulableModel)
            ->delete($impediment->id);

        $this->assertTrue($deletionResult);

        $finalFind = ImpedimentFacade::for($this->schedulableModel)->find($impediment->id);
        $this->assertNotInstanceOf(\Roster\Models\Impediment::class, $finalFind);
    }

    /**
     * Test updating an impediment.
     */
    public function test_facade_can_update_impediment(): void
    {
        $impediment = ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Initial reason',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 11:00:00',
                'metadata' => ['priority' => 'low'],
            ]
        );

        $updateResult = ImpedimentFacade::for($this->schedulableModel)->update(
            $impediment->id,
            [
                'reason' => 'Updated reason',
                'metadata' => ['priority' => 'high'],
            ]
        );

        $this->assertTrue($updateResult);

        $impediment->refresh();
        $this->assertSame('Updated reason', $impediment->reason);
        $this->assertSame(['priority' => 'high'], $impediment->metadata);
    }

    /**
     * Test getting available time slots considering impediments.
     */
    public function test_facade_can_get_available_time_slots(): void
    {
        ImpedimentFacade::for($this->schedulableModel)->create(
            $this->juneAvailability,
            [
                'reason' => 'Blocked period',
                'start_datetime' => '2038-06-07 10:00:00',
                'end_datetime' => '2038-06-07 12:00:00',
            ]
        );

        $start = Carbon::parse('2038-06-07 09:00:00');
        $end = Carbon::parse('2038-06-07 17:00:00');

        $availableSlots = ImpedimentFacade::for($this->schedulableModel)
            ->getAvailableTimeSlots($start, $end);

        $this->assertInstanceOf(Collection::class, $availableSlots);
    }

    /**
     * Test filtering impediments by availability ID.
     */
    public function test_facade_can_filter_by_availability_id(): void
    {
        $anotherAvailability = Availability::create([
            'schedulable_id' => $this->schedulableModel->id,
            'schedulable_type' => get_class($this->schedulableModel),
            'type' => 'training',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'days' => ['wednesday'],
            'start_date' => '2038-06-01',
            'end_date' => '2038-06-30',
        ]);

        $impediments = ImpedimentFacade::for($this->schedulableModel)
            ->resetFilters()
            ->whereAvailabilityId($anotherAvailability->id)
            ->get();

        $this->assertCount(0, $impediments);

        ImpedimentFacade::for($this->schedulableModel)->create(
            $anotherAvailability,
            [
                'reason' => 'Training impediment',
                'start_datetime' => '2038-06-09 10:00:00',
                'end_datetime' => '2038-06-09 11:00:00',
            ]
        );

        $impediments = ImpedimentFacade::for($this->schedulableModel)
            ->resetFilters()
            ->whereAvailabilityId($anotherAvailability->id)
            ->get();

        $this->assertCount(1, $impediments);
        $this->assertSame('Training impediment', $impediments->first()->reason);
        $this->assertSame($anotherAvailability->id, $impediments->first()->availability_id);
    }

    /**
     * Test resetting filters.
     */
    public function test_facade_can_reset_filters(): void
    {
        $service = ImpedimentFacade::for($this->schedulableModel)
            ->whereReason('test')
            ->resetFilters();

        $this->assertInstanceOf(ImpedimentService::class, $service);
    }

    /**
     * Test getting the current schedulable model.
     */
    public function test_facade_can_get_schedulable(): void
    {
        $impedimentService = ImpedimentFacade::for($this->schedulableModel);
        $schedulable = $impedimentService->getSchedulable();

        $this->assertInstanceOf(Model::class, $schedulable);
        $this->assertSame($this->schedulableModel->id, $schedulable->id);
    }
}
