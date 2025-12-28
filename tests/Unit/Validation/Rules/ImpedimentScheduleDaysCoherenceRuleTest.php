<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Roster\Contracts\Validation\ValidationContextInterface;
use Roster\Services\AvailabilityService;
use Roster\Validation\Exceptions\ValidationFailedException;
use Roster\Validation\Rules\ImpedimentScheduleDaysCoherenceRule;
use Tests\TestCase;
use Tests\Support\TestSchedulable;

/**
 * Test suite for ImpedimentScheduleDaysCoherenceRule validation rule.
 *
 * Validates that impediments and schedules can only be created on days
 * allowed by their parent availability.
 */
#[AllowMockObjectsWithoutExpectations]
final class ImpedimentScheduleDaysCoherenceRuleTest extends TestCase
{
    use RefreshDatabase;

    private Model $schedulable;
    private ImpedimentScheduleDaysCoherenceRule $rule;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->schedulable = TestSchedulable::create();
        $this->rule = new ImpedimentScheduleDaysCoherenceRule();
    }

    /**
     * Test that validation passes when required datetimes are missing.
     */
    public function test_passes_when_required_datetimes_missing(): void
    {
        // Arrange: Context without start_datetime and end_datetime fields
        $context = $this->createMock(ValidationContextInterface::class);
        $this->configureContextWithMissingDatetimes($context);

        $context->expects($this->never())->method('get');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur when required fields are missing
    }

    /**
     * Test that validation passes when end datetime is before start datetime.
     */
    public function test_passes_when_end_before_start(): void
    {
        // Arrange: Context with invalid datetime order (end before start)
        $context = $this->createMock(ValidationContextInterface::class);
        $this->configureContextWithInvalidDatetimeOrder($context);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur for invalid datetime order (handled elsewhere)
    }

    /**
     * Test that validation passes when datetime parsing fails.
     */
    public function test_passes_when_datetime_parsing_fails(): void
    {
        // Arrange: Context with invalid datetime format
        $context = $this->createMock(ValidationContextInterface::class);
        $this->configureContextWithInvalidDatetimeFormat($context);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur for invalid datetime format (handled elsewhere)
    }

    /**
     * Test that validation passes when availability_id is missing.
     */
    public function test_passes_when_availability_id_missing(): void
    {
        // Arrange: Context without availability_id field
        $context = $this->createMock(ValidationContextInterface::class);
        $this->configureContextWithoutAvailabilityId($context);

        $context->expects($this->never())->method('getAvailabilityService');
        $context->expects($this->never())->method('setViolation');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur without availability_id
    }

    /**
     * Test that validation passes when availability service is not available.
     */
    public function test_passes_when_availability_service_unavailable(): void
    {
        // Arrange: Context with availability_id but unavailable service
        $availabilityService = $this->createUnavailableAvailabilityService();
        $context = $this->createMock(ValidationContextInterface::class);

        $this->configureContextWithAvailabilityId($context);
        $context->method('getAvailabilityService')->willReturn($availabilityService);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur with unavailable service
    }

    /**
     * Test that validation passes when availability is not found.
     */
    public function test_passes_when_availability_not_found(): void
    {
        // Arrange: Context with non-existent availability_id
        $availabilityService = $this->createMock(AvailabilityService::class);
        $availabilityService->method('find')->willReturn(null);

        $context = $this->createMock(ValidationContextInterface::class);
        $this->configureContextWithAvailabilityId($context, 999);
        $context->method('getAvailabilityService')->willReturn($availabilityService);

        $context->expects($this->never())->method('setViolation');

        // Act: Execute the validation rule
        $this->rule->validate($context);

        // Assert: No validation should occur for non-existent availability
    }

    /**
     * Test that validation passes for schedule on allowed day.
     */
    public function test_passes_for_schedule_on_allowed_day(): void
    {
        // Arrange: Create availability with Tuesday and Thursday allowed days
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Create schedule on Thursday (allowed day)
        $schedule = schedule_for($availability)->create([
            'title' => 'Thursday schedule',
            'start_datetime' => '2038-01-07 10:00:00', // Thursday
            'end_datetime' => '2038-01-07 11:00:00',
        ]);

        // Assert: Schedule should be created successfully on allowed day
        $this->assertNotNull($schedule);
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test that validation fails for schedule spanning multiple unauthorized days.
     */
    public function test_fails_for_schedule_spanning_multiple_unauthorized_days(): void
    {
        // Arrange: Create availability with only Tuesday and Thursday allowed
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/failed for Schedule.*not allowed because this availability only permits/"
        );

        // Act: Attempt to create schedule spanning Saturday to Monday (unauthorized days)
        schedule_for($availability)->create([
            'title' => 'Weekend schedule',
            'start_datetime' => '2038-01-02 10:00:00', // Saturday
            'end_datetime' => '2038-01-04 11:00:00', // Monday (3 days)
        ]);

        // Assert: Exception should be thrown for unauthorized days
    }

    /**
     * Test that validation fails for impediment spanning multiple unauthorized days.
     */
    public function test_fails_for_impediment_spanning_multiple_unauthorized_days(): void
    {
        // Arrange: Create availability with Monday, Wednesday, Friday allowed
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/failed for Impediment.*not allowed because this availability only permits/"
        );

        // Act: Attempt to create impediment spanning Saturday to Monday
        impediment_for($availability)->create([
            'reason' => 'Weekend impediment',
            'start_datetime' => '2038-01-09 10:00:00', // Saturday
            'end_datetime' => '2038-01-11 11:00:00', // Monday
        ]);

        // Assert: Exception should be thrown for impediment on unauthorized days
    }

    /**
     * Test that validation passes for single-day schedule on allowed day.
     */
    public function test_passes_for_single_day_schedule(): void
    {
        // Arrange: Create availability with Monday, Wednesday, Friday allowed
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Create single-day schedule on Monday
        $schedule = schedule_for($availability)->create([
            'title' => 'Monday schedule',
            'start_datetime' => '2038-01-04 10:00:00', // Monday
            'end_datetime' => '2038-01-04 16:00:00',
        ]);

        // Assert: Schedule should be created successfully on allowed single day
        $this->assertNotNull($schedule);
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test that validation fails for schedule outside validity period.
     */
    public function test_fails_for_schedule_outside_validity_period(): void
    {
        // Arrange: Create availability with validity ending January 15
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-15 23:59:59',
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/failed for Schedule.*selected end datetime.*is after the availability end datetime/"
        );

        // Act: Attempt to create schedule after validity period ends
        schedule_for($availability)->create([
            'title' => 'Post-validity schedule',
            'start_datetime' => '2038-01-20 10:00:00', // After validity_end
            'end_datetime' => '2038-01-20 11:00:00',
        ]);

        // Assert: Exception should be thrown for schedule outside validity period
    }

    /**
     * Test that validation passes for schedule within validity period.
     */
    public function test_passes_for_schedule_within_validity_period(): void
    {
        // Arrange: Create availability with two-week validity
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-15',
        ]);

        // Act: Create schedule during validity period
        $schedule = schedule_for($availability)->create([
            'title' => 'Mid-validity schedule',
            'start_datetime' => '2038-01-08 10:00:00', // Friday
            'end_datetime' => '2038-01-08 11:00:00',
        ]);

        // Assert: Schedule should be created successfully within validity period
        $this->assertNotNull($schedule);
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test that validation works for UPDATE operation.
     */
    public function test_works_for_update_operation(): void
    {
        // Arrange: Create availability and initial schedule on Tuesday
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Tuesday schedule',
            'start_datetime' => '2038-01-05 10:00:00', // Tuesday
            'end_datetime' => '2038-01-05 11:00:00',
        ]);

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessageMatches(
            "/Update validation failed for Schedule: start_datetime.*not allowed/"
        );

        // Act: Attempt to update schedule to Wednesday (not allowed day)
        schedule_for($availability)->update($schedule->id, [
            'title' => 'Updated schedule',
            'start_datetime' => '2038-01-06 10:00:00', // Wednesday - not allowed
            'end_datetime' => '2038-01-06 11:00:00',
        ]);

        // Assert: Exception should be thrown for update to unauthorized day
    }

    /**
     * Test that validation passes for UPDATE to allowed day.
     */
    public function test_passes_for_update_to_allowed_day(): void
    {
        // Arrange: Create availability and initial schedule on Tuesday
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $schedule = schedule_for($availability)->create([
            'title' => 'Tuesday schedule',
            'start_datetime' => '2038-01-05 10:00:00', // Tuesday
            'end_datetime' => '2038-01-05 11:00:00',
        ]);

        // Act: Update schedule to Thursday (allowed day)
        $result = schedule_for($availability)->update($schedule->id, [
            'title' => 'Updated to Thursday',
            'start_datetime' => '2038-01-07 10:00:00', // Thursday - allowed
            'end_datetime' => '2038-01-07 11:00:00',
        ]);

        // Assert: Update should succeed when moving to allowed day
        $this->assertTrue($result);
        $this->assertDatabaseHas('roster_schedules', [
            'id' => $schedule->id,
            'title' => 'Updated to Thursday',
        ]);
    }

    /**
     * Test that validation works with availability having long validity.
     */
    public function test_works_with_long_validity(): void
    {
        // Arrange: Create availability with 11-month validity
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-11-31', // Long validity
        ]);

        // Act: Create schedule far in the future but within validity
        $schedule = schedule_for($availability)->create([
            'title' => 'Future schedule',
            'start_datetime' => '2038-11-10 10:00:00',
            'end_datetime' => '2038-11-10 11:00:00',
        ]);

        // Assert: Schedule should be created successfully with long validity
        $this->assertNotNull($schedule);
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test that validation works with partial validity period.
     */
    public function test_works_with_partial_validity_period(): void
    {
        // Arrange: Create availability with 10-day validity
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-10', // Only 10 days
        ]);

        $this->expectException(ValidationFailedException::class);

        // Act: Attempt to create schedule after validity period ends
        schedule_for($availability)->create([
            'title' => 'Post-period schedule',
            'start_datetime' => '2038-01-15 10:00:00', // After validity_end
            'end_datetime' => '2038-01-15 11:00:00',
        ]);

        // Assert: Exception should be thrown for schedule outside validity period
    }

    /**
     * Test that validation fails for multi-day schedule with mixed authorization.
     */
    public function test_fails_for_multi_day_schedule_with_mixed_authorization(): void
    {
        // Arrange: Create availability with Monday, Wednesday, Friday allowed
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'wednesday', 'friday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        $this->expectException(ValidationFailedException::class);

        // Act: Attempt to create schedule spanning Monday (allowed), Tuesday (not allowed), Wednesday (allowed)
        schedule_for($availability)->create([
            'title' => 'Mixed week schedule',
            'start_datetime' => '2038-01-04 10:00:00', // Monday
            'end_datetime' => '2038-01-06 11:00:00', // Wednesday (3 days)
        ]);

        // Assert: Exception should be thrown for schedule including unauthorized day
    }

    /**
     * Test that validation handles edge case with same start and end datetime.
     */
    public function test_passes_for_same_start_and_end_datetime(): void
    {
        // Arrange: Create availability with Tuesday and Thursday allowed
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['tuesday', 'thursday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Create schedule with 30-minute duration (minimum required)
        $schedule = schedule_for($availability)->create([
            'title' => 'Short duration schedule',
            'start_datetime' => '2038-01-05 10:00:00', // Tuesday
            'end_datetime' => '2038-01-05 10:30:00', // 30 minutes later
        ]);

        // Assert: Schedule should be created successfully with valid duration
        $this->assertNotNull($schedule);
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Test that validation works with all days allowed.
     */
    public function test_passes_with_all_days_allowed(): void
    {
        // Arrange: Create availability allowing all days of the week
        $availability = availability_for($this->schedulable)->create([
            'type' => 'consultation',
            'daily_start' => '09:00:00',
            'daily_end' => '17:00:00',
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'validity_start' => '2038-01-01',
            'validity_end' => '2038-01-31',
        ]);

        // Act: Create schedule on Saturday
        $schedule = schedule_for($availability)->create([
            'title' => 'Weekend schedule',
            'start_datetime' => '2038-01-09 10:00:00', // Saturday
            'end_datetime' => '2038-01-09 11:00:00',
        ]);

        // Assert: Schedule should be created successfully when all days are allowed
        $this->assertNotNull($schedule);
        $this->assertDatabaseHas('roster_schedules', ['id' => $schedule->id]);
    }

    /**
     * Configure context with missing datetime fields.
     */
    private function configureContextWithMissingDatetimes(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'start_datetime' => false,
                'end_datetime' => false,
                default => false,
            }
        );
    }

    /**
     * Configure context with invalid datetime order.
     */
    private function configureContextWithInvalidDatetimeOrder(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'start_datetime' => true,
                'end_datetime' => true,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'start_datetime' => '2038-01-02 10:00:00',
                'end_datetime' => '2038-01-01 10:00:00', // End before start
                default => null,
            }
        );
    }

    /**
     * Configure context with invalid datetime format.
     */
    private function configureContextWithInvalidDatetimeFormat(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'start_datetime' => true,
                'end_datetime' => true,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'start_datetime' => 'invalid-datetime',
                'end_datetime' => '2038-01-01 10:00:00',
                default => null,
            }
        );
    }

    /**
     * Configure context without availability_id field.
     */
    private function configureContextWithoutAvailabilityId(MockObject $context): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'start_datetime' => true,
                'end_datetime' => true,
                'availability_id' => false,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'start_datetime' => '2038-01-01 10:00:00',
                'end_datetime' => '2038-01-01 11:00:00',
                'availability_id' => null,
                default => null,
            }
        );
    }

    /**
     * Configure context with availability_id.
     */
    private function configureContextWithAvailabilityId(MockObject $context, int $availabilityId = 1): void
    {
        $context->method('has')->willReturnCallback(
            fn(string $key): bool => match ($key) {
                'start_datetime' => true,
                'end_datetime' => true,
                'availability_id' => true,
                default => false,
            }
        );

        $context->method('get')->willReturnCallback(
            fn(string $key): mixed => match ($key) {
                'start_datetime' => '2038-01-01 10:00:00',
                'end_datetime' => '2038-01-01 11:00:00',
                'availability_id' => $availabilityId,
                default => null,
            }
        );
    }

    /**
     * Create an unavailable availability service mock.
     */
    private function createUnavailableAvailabilityService(): MockObject&AvailabilityService
    {
        $service = $this->createMock(AvailabilityService::class);
        $service->method('find')->willReturn(null);
        $service->method('getSchedulable')->willReturn(null);
        $service->method('getData')->willReturn([]);
        $service->method('getFilters')->willReturn([]);

        return $service;
    }
}
