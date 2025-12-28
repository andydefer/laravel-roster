<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Support\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Rules\DurationRule;
use Tests\TestCase;

/**
 * Unit tests for DurationRule validation logic.
 *
 * Tests minimum duration validation for availability, schedule, and impediment entities.
 */
final class DurationRuleTest extends TestCase
{
    private DurationRule $rule;

    /**
     * Set up the test case with a fresh rule instance.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new DurationRule();
    }

    /**
     * Configure minimum duration settings for different entity types.
     *
     * @param int $availabilityMinutes Minimum minutes for availability entities
     * @param int $scheduleMinutes Minimum minutes for schedule entities
     * @param int $impedimentMinutes Minimum minutes for impediment entities
     */
    private function configureMinimumDurations(
        int $availabilityMinutes = 15,
        int $scheduleMinutes = 15,
        int $impedimentMinutes = 5
    ): void {
        config([
            'roster.durations.minimum_availability_minutes' => $availabilityMinutes,
            'roster.durations.minimum_schedule_minutes' => $scheduleMinutes,
            'roster.durations.minimum_impediment_minutes' => $impedimentMinutes,
        ]);
    }

    /**
     * Test that validation passes when availability duration meets minimum requirement.
     */
    public function test_passes_when_availability_duration_meets_minimum(): void
    {
        // Arrange: Configure context with valid availability duration
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => '09:00:00',
            'end_time' => '10:00:00', // 60 minutes > 15 minimum
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations should be set
    }

    /**
     * Test that validation fails when availability duration is below minimum.
     */
    public function test_fails_when_availability_duration_below_minimum(): void
    {
        // Arrange: Configure context with insufficient availability duration
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => '09:00:00',
            'end_time' => '09:10:00', // 10 minutes < 15 minimum
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'duration',
                "Minimum duration of 15 minutes required for availability. Got 10 minutes"
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for insufficient duration
    }

    /**
     * Test that validation passes for CREATE when start/end times are missing.
     */
    public function test_passes_for_create_when_availability_times_missing(): void
    {
        // Arrange: Configure context with missing time fields for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'type' => 'office',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected when time fields are missing
    }

    /**
     * Test that validation passes for UPDATE when start/end times are not provided.
     */
    public function test_passes_for_update_when_availability_times_not_provided(): void
    {
        // Arrange: Configure context without time fields for update operation
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'type' => 'remote',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected when updating without time fields
    }

    /**
     * Test that validation passes when schedule duration meets minimum requirement.
     */
    public function test_passes_when_schedule_duration_meets_minimum(): void
    {
        // Arrange: Configure context with valid schedule duration
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $start = Carbon::now()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s');

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for valid schedule duration
    }

    /**
     * Test that validation fails when schedule duration is below minimum.
     */
    public function test_fails_when_schedule_duration_below_minimum(): void
    {
        // Arrange: Configure context with insufficient schedule duration
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $start = Carbon::now()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s');

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'duration',
                "Minimum duration of 15 minutes required for Schedule. Got 10 minutes"
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for insufficient schedule duration
    }

    /**
     * Test that validation passes when impediment duration meets minimum requirement.
     */
    public function test_passes_when_impediment_duration_meets_minimum(): void
    {
        // Arrange: Configure context with valid impediment duration
        $this->configureMinimumDurations(impedimentMinutes: 5);

        $start = Carbon::now()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s');

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for valid impediment duration
    }

    /**
     * Test that validation fails when impediment duration is below minimum.
     */
    public function test_fails_when_impediment_duration_below_minimum(): void
    {
        // Arrange: Configure context with insufficient impediment duration
        $this->configureMinimumDurations(impedimentMinutes: 5);

        $start = Carbon::now()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addMinutes(3)->format('Y-m-d H:i:s');

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::IMPEDIMENT);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'duration',
                "Minimum duration of 5 minutes required for Impediment. Got 3 minutes"
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for insufficient impediment duration
    }

    /**
     * Test that validation fails for invalid time format (availability).
     */
    public function test_fails_for_invalid_availability_time_format(): void
    {
        // Arrange: Configure context with invalid time format for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => 'invalid-time',
            'end_time' => '09:30:00',
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'time_format',
                $this->stringContains('Invalid time format:')
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid time format
    }

    /**
     * Test that validation fails for invalid datetime format (schedule).
     */
    public function test_fails_for_invalid_schedule_datetime_format(): void
    {
        // Arrange: Configure context with invalid datetime format for schedule
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => 'invalid-datetime',
            'end_datetime' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'datetime_format',
                $this->stringContains('Invalid datetime format:')
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for invalid datetime format
    }

    /**
     * Test that validation handles partial update for availability.
     */
    public function test_handles_partial_update_for_availability(): void
    {
        // Arrange: Configure context with partial update for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'end_time' => '10:30:00',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for partial update
    }

    /**
     * Test that validation handles partial update for schedule.
     */
    public function test_handles_partial_update_for_schedule(): void
    {
        // Arrange: Configure context with partial update for schedule
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'end_datetime' => Carbon::now()->addHours(1)->format('Y-m-d H:i:s'),
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for partial update
    }

    /**
     * Test that validation passes for UPDATE with both times provided.
     */
    public function test_passes_for_update_with_both_availability_times(): void
    {
        // Arrange: Configure context with complete update for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for valid update duration
    }

    /**
     * Test that validation passes for UPDATE with both datetimes provided.
     */
    public function test_passes_for_update_with_both_schedule_datetimes(): void
    {
        // Arrange: Configure context with complete update for schedule
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $start = Carbon::now()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s');

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for valid update duration
    }

    /**
     * Test that validation handles exact minimum duration.
     */
    public function test_passes_for_exact_minimum_duration(): void
    {
        // Arrange: Configure context with exact minimum duration for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for exact minimum duration
    }

    /**
     * Test that validation uses correct display names in error messages.
     */
    public function test_uses_correct_display_names_in_error_messages(): void
    {
        // Arrange: Configure context with insufficient schedule duration
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $start = Carbon::now()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addMinutes(10)->format('Y-m-d H:i:s');

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => $start,
            'end_datetime' => $end,
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'duration',
                "Minimum duration of 15 minutes required for Schedule. Got 10 minutes"
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Error message should contain correct entity display name
    }

    /**
     * Test that validation handles edge case with 1 minute difference.
     */
    public function test_handles_one_minute_difference(): void
    {
        // Arrange: Configure context with minimal duration for availability
        $this->configureMinimumDurations(availabilityMinutes: 1);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => '09:00:00',
            'end_time' => '09:01:00',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for exact minimal duration
    }

    /**
     * Test that validation fails for zero duration.
     */
    public function test_fails_for_zero_duration(): void
    {
        // Arrange: Configure context with zero duration for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => '09:00:00',
            'end_time' => '09:00:00',
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'duration',
                "Minimum duration of 15 minutes required for availability. Got 0 minutes"
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for zero duration
    }

    /**
     * Test that validation handles end time before start time.
     */
    public function test_handles_end_time_before_start_time(): void
    {
        // Arrange: Configure context with negative duration for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::CREATE);
        $context->method('safeData')->willReturn([
            'start_time' => '10:00:00',
            'end_time' => '09:00:00',
        ]);

        $context->expects($this->once())
            ->method('setViolation')
            ->with(
                'duration',
                "Minimum duration of 15 minutes required for availability. Got -60 minutes"
            );

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: Violation should be set for negative duration
    }

    /**
     * Test that validation works for UPDATE with only start_time provided.
     */
    public function test_handles_update_with_only_start_time(): void
    {
        // Arrange: Configure context with partial update for availability
        $this->configureMinimumDurations(availabilityMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::AVAILABILITY);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'start_time' => '08:00:00',
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for partial update
    }

    /**
     * Test that validation works for UPDATE with only start_datetime provided.
     */
    public function test_handles_update_with_only_start_datetime(): void
    {
        // Arrange: Configure context with partial update for schedule
        $this->configureMinimumDurations(scheduleMinutes: 15);

        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn(EntityType::SCHEDULE);
        $context->method('getOperation')->willReturn(OperationType::UPDATE);
        $context->method('safeData')->willReturn([
            'start_datetime' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute validation
        $this->rule->validate($context);

        // Assert: No violations expected for partial update
    }

    /**
     * Create a mock validation context with specific configuration.
     *
     * @param EntityType $entityType
     * @param OperationType $operationType
     * @param array<string, mixed> $data
     * @return MockObject&ValidationContextInterface
     */
    private function createValidationContext(
        EntityType $entityType,
        OperationType $operationType,
        array $data = []
    ): MockObject {
        $context = $this->createMock(ValidationContextInterface::class);
        $context->method('getEntityType')->willReturn($entityType);
        $context->method('getOperation')->willReturn($operationType);
        $context->method('safeData')->willReturn($data);

        return $context;
    }
}
