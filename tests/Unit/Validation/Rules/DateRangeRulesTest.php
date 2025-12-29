<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use stdClass;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\AvailabilityDateRangeRule;
use Roster\Validation\Rules\TimeSlotDateTimeRule;
use Tests\Support\TestSchedulable;
use Tests\TestCase;
use Roster\Support\RosterMutationContext;

/**
 * Integration tests for date range and time slot validation rules.
 *
 * Tests the validation logic for availability date ranges and schedule time slots
 * across different operations (CREATE, UPDATE) and entity types.
 */
final class DateRangeRulesTest extends TestCase
{
    private AvailabilityDateRangeRule $availabilityDateRangeRule;
    private TimeSlotDateTimeRule $timeSlotDateTimeRule;
    private TestSchedulable $testSchedulable;

    /**
     * Set up test dependencies before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityDateRangeRule = new AvailabilityDateRangeRule();
        $this->timeSlotDateTimeRule = new TimeSlotDateTimeRule();
        $this->testSchedulable = TestSchedulable::create();
    }

    /**
     * Test successful validation of availability creation with valid date ranges.
     */
    public function test_availability_validate_create_success(): void
    {
        // Arrange: Prepare valid availability data for CREATE operation
        $data = [
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: No violations should be present
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test validation failure when availability end date is before start date.
     */
    public function test_availability_validate_create_fails_when_end_before_start(): void
    {
        // Arrange: Prepare invalid data with end date before start date
        $data = [
            'validity_start' => '2038-07-31',
            'validity_end' => '2038-07-01',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: Validation should fail with date range violation
        $this->assertTrue($validationContext->hasViolations());
        $this->assertTrue($validationContext->hasViolationFor('validity_date_range'));
    }

    /**
     * Test successful validation of partial date update for availability.
     */
    public function test_availability_validate_update_partial_date(): void
    {
        // Arrange: Create existing availability within mutation context
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Prepare update data with only end date changed
        $data = ['validity_end' => '2038-08-15'];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable,
            currentEntity: $availability
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: No violations for valid partial update
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test validation failure for invalid partial date update of availability.
     */
    public function test_availability_validate_update_partial_date_fails(): void
    {
        // Arrange: Create existing availability within mutation context
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Prepare invalid update data with end date before existing start date
        $data = ['validity_end' => '2038-06-30'];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable,
            currentEntity: $availability
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: Validation should fail with date range violation
        $this->assertTrue($validationContext->hasViolations());
        $this->assertTrue($validationContext->hasViolationFor('validity_date_range'));;
    }

    /**
     * Test that validation is skipped when no date fields are changed in update.
     */
    public function test_availability_validate_update_skip_when_no_dates_changed(): void
    {
        // Arrange: Create existing availability within mutation context
        $availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->testSchedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => ['monday'],
                'validity_start' => '2038-07-01',
                'validity_end' => '2038-07-31',
            ]);
        });

        // Prepare update data with only non-date fields changed
        $data = ['type' => 'training'];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable,
            currentEntity: $availability
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: No violations when date fields remain unchanged
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test validation failure when availability period exceeds maximum allowed days.
     */
    public function test_availability_validate_period_too_long(): void
    {
        // Arrange: Prepare data with period exceeding 365 days
        $data = [
            'validity_start' => '2038-01-01',
            'validity_end' => '2039-02-01', // 397 days
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: Validation should fail with maximum duration violation
        $this->assertTrue($validationContext->hasViolations());

        $this->assertTrue($validationContext->hasViolationFor('max_duration'));

        $violation = array_values(array_filter(
            $validationContext->getViolations(),
            fn($v) => $v->getField() === 'max_duration'
        ))[0] ?? null;

        $this->assertNotNull($violation);
        $this->assertStringContainsString('cannot exceed 365 days', $violation->getMessage());
    }

    /**
     * Test validation failure when daily time duration is too short.
     */
    public function test_availability_validate_time_too_short(): void
    {
        // Arrange: Prepare data with insufficient daily duration
        $data = [
            'validity_start' => '2038-07-01',
            'validity_end' => '2038-07-31',
            'daily_start' => '09:00:00',
            'daily_end' => '09:04:00', // 4 minutes
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute availability date range validation
        $this->availabilityDateRangeRule->validate($validationContext);

        // Assert: Validation should fail with minimum duration violation
        $this->assertTrue($validationContext->hasViolations());
        $this->assertTrue($validationContext->hasViolationFor('min_duration'));
    }

    /**
     * Test successful validation of schedule creation with valid time slots.
     */
    public function test_schedule_validate_create_success(): void
    {
        // Arrange: Prepare valid schedule data for CREATE operation
        $data = [
            'start_datetime' => '2038-07-01 10:00:00',
            'end_datetime' => '2038-07-01 11:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute time slot validation
        $this->timeSlotDateTimeRule->validate($validationContext);

        // Assert: No violations should be present
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test validation failure when schedule end datetime is before start datetime.
     */
    public function test_schedule_validate_create_fails(): void
    {
        // Arrange: Prepare invalid data with end datetime before start datetime
        $data = [
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 10:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute time slot validation
        $this->timeSlotDateTimeRule->validate($validationContext);

        // Assert: Validation should fail with datetime range violation
        $this->assertTrue($validationContext->hasViolations());
        $this->assertTrue($validationContext->hasViolationFor('datetime_range'));
    }

    /**
     * Test successful validation of impediment creation with valid time slots.
     */
    public function test_impediment_validate_create_success(): void
    {
        // Arrange: Prepare valid impediment data for CREATE operation
        $data = [
            'start_datetime' => '2038-07-01 11:00:00',
            'end_datetime' => '2038-07-01 12:00:00',
        ];

        $validationContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: $data,
            model: $this->testSchedulable
        );

        // Act: Execute time slot validation
        $this->timeSlotDateTimeRule->validate($validationContext);

        // Assert: No violations should be present
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test successful validation of partial datetime update for schedule.
     */
    public function test_schedule_validate_update_partial(): void
    {
        // Arrange: Create mock existing schedule entity
        $schedule = new stdClass();
        $schedule->start_datetime = '2038-07-01 10:00:00';
        $schedule->end_datetime = '2038-07-01 11:00:00';

        // Prepare update data with only end datetime changed
        $data = ['end_datetime' => '2038-07-01 12:00:00'];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->testSchedulable,
            currentEntity: $schedule
        );

        // Act: Execute time slot validation
        $this->timeSlotDateTimeRule->validate($validationContext);

        // Assert: No violations for valid partial update
        $this->assertFalse($validationContext->hasViolations());
    }

    /**
     * Test validation failure for invalid partial datetime update of schedule.
     */
    public function test_schedule_validate_update_partial_fails(): void
    {
        // Arrange: Create mock existing schedule entity
        $schedule = new stdClass();
        $schedule->start_datetime = '2038-07-01 10:00:00';
        $schedule->end_datetime = '2038-07-01 11:00:00';

        // Prepare invalid update data with start datetime after existing end datetime
        $data = ['start_datetime' => '2038-07-01 12:00:00'];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->testSchedulable,
            currentEntity: $schedule
        );

        // Act: Execute time slot validation
        $this->timeSlotDateTimeRule->validate($validationContext);

        // Assert: Validation should fail with datetime range violation
        $this->assertTrue($validationContext->hasViolations());
        $this->assertTrue($validationContext->hasViolationFor('datetime_range'));
    }

    /**
     * Test that validation is skipped when no datetime fields are changed in update.
     */
    public function test_schedule_validate_update_skip_when_no_datetimes_changed(): void
    {
        // Arrange: Create mock existing schedule entity
        $schedule = new stdClass();
        $schedule->start_datetime = '2038-07-01 10:00:00';
        $schedule->end_datetime = '2038-07-01 11:00:00';

        // Prepare update data with only non-datetime fields changed
        $data = ['title' => 'New Title'];

        $validationContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->testSchedulable,
            currentEntity: $schedule
        );

        // Act: Execute time slot validation
        $this->timeSlotDateTimeRule->validate($validationContext);

        // Assert: No violations when datetime fields remain unchanged
        $this->assertFalse($validationContext->hasViolations());
    }
}
