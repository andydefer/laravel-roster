<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Exception;
use Mockery;
use Mockery\MockInterface;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\TimeRangeRule;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class TimeRangeRuleTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService|MockInterface $availabilityService;
    private TimeRangeRule $rule;
    private Model|MockInterface $schedulable;

    /**
     * Set up the test environment with mocks and bindings.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->availabilityService = Mockery::mock(AvailabilityService::class);
        $this->schedulable = Mockery::mock(Model::class);

        $this->configureSchedulableMock();
        $this->configureServiceContainer();

        $this->rule = new TimeRangeRule();
    }

    /**
     * Test that validation passes when time range is within availability boundaries.
     */
    public function test_passes_when_time_range_is_valid_within_availability(): void
    {
        // Arrange: Time range completely within availability hours and validity period
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 09:00:00',
            endDateTime: '2024-01-01 17:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: No violations should be recorded for valid time range
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation fails when start time is before availability start.
     */
    public function test_fails_when_start_time_before_availability_start(): void
    {
        // Arrange: Start time earlier than availability's daily start time
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 07:00:00',
            endDateTime: '2024-01-01 10:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for start time before availability
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('start_datetime', $context->getViolations());
        $this->assertStringContainsString(
            'is before the availability start time',
            (string) $context->getViolations()['start_datetime']
        );
    }

    /**
     * Test that validation fails when end time is after availability end.
     */
    public function test_fails_when_end_time_after_availability_end(): void
    {
        // Arrange: End time later than availability's daily end time
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 16:00:00',
            endDateTime: '2024-01-01 19:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for end time after availability
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('end_datetime', $context->getViolations());
        $this->assertStringContainsString(
            'is after the availability end time',
            (string) $context->getViolations()['end_datetime']
        );
    }

    /**
     * Test that validation fails when event day is not allowed.
     */
    public function test_fails_when_day_not_allowed(): void
    {
        // Arrange: Event scheduled on a day not included in availability
        $context = $this->createValidationContext(
            startDateTime: '2024-01-02 09:00:00', // Tuesday (January 2, 2024 is Tuesday)
            endDateTime: '2024-01-02 17:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'], // Only Monday allowed
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for invalid day
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('start_datetime', $context->getViolations());
        $this->assertStringContainsString(
            'is not allowed',
            (string) $context->getViolations()['start_datetime']
        );
    }

    /**
     * Test that validation fails when start time is before validity period.
     */
    public function test_fails_when_start_before_validity(): void
    {
        // Arrange: Event scheduled before availability validity period starts
        $context = $this->createValidationContext(
            startDateTime: '2023-12-31 09:00:00',
            endDateTime: '2023-12-31 17:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['sunday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for start before validity period
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('start_datetime', $context->getViolations());
    }

    /**
     * Test that validation fails when end time is after validity period.
     */
    public function test_fails_when_end_after_validity(): void
    {
        // Arrange: Event scheduled after availability validity period ends
        $context = $this->createValidationContext(
            startDateTime: '2025-01-01 09:00:00',
            endDateTime: '2025-01-01 17:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['wednesday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for end after validity period
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('end_datetime', $context->getViolations());
    }

    /**
     * Test that validation fails when end time is before start time.
     */
    public function test_fails_when_end_before_start(): void
    {
        // Arrange: Event with end time chronologically before start time
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 12:00:00',
            endDateTime: '2024-01-01 08:00:00',
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')->never();

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for invalid time sequence
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('end_datetime', $context->getViolations());
    }

    /**
     * Test that validation passes for impediment entity type.
     */
    public function test_passes_for_impediment_entity(): void
    {
        // Arrange: Impediment entity with valid time range
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: [
                'availability_id' => 123,
                'start_datetime' => '2024-01-01 10:00:00',
                'end_datetime' => '2024-01-01 14:00:00',
            ],
            model: $this->schedulable
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => '2024-01-01',
            'validity_end' => '2024-12-31',
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule for impediment
        $this->rule->validate($context);

        // Assert: No violations for valid impediment time range
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation skips when availability is not found.
     */
    public function test_skips_validation_when_availability_not_found(): void
    {
        // Arrange: Non-existent availability ID
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 09:00:00',
            endDateTime: '2024-01-01 17:00:00',
            availabilityId: 999
        );

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: No violations when availability doesn't exist
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation handles malformed datetime strings gracefully.
     */
    public function test_handles_malformed_datetime_strings(): void
    {
        // Arrange: Invalid datetime format in input data
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 123,
                'start_datetime' => 'invalid-datetime',
                'end_datetime' => '2024-01-01 17:00:00',
            ],
            model: $this->schedulable
        );

        $this->availabilityService->shouldReceive('find')->never();

        // Act: Execute the time range validation rule with invalid data
        try {
            $this->rule->validate($context);
        } catch (Exception $exception) {
            // Expected exception from invalid datetime parsing
        }

        // Assert: No violations recorded for malformed datetime
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation passes when availability has no validity period.
     */
    public function test_passes_when_availability_has_no_validity_period(): void
    {
        // Arrange: Availability without validity period constraints
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 09:00:00',
            endDateTime: '2024-01-01 17:00:00',
            availabilityId: 123
        );

        $availability = $this->createAvailability([
            'daily_start' => '08:00:00',
            'daily_end' => '18:00:00',
            'days' => ['monday'],
            'validity_start' => null,
            'validity_end' => null,
        ]);

        $this->availabilityService->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($availability);

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: No violations for time range when no validity period
        $this->assertFalse($context->hasViolations());
    }

    /**
     * Test that validation fails when event spans across midnight.
     */
    public function test_fails_when_event_spans_midnight(): void
    {
        // Arrange: Event starting on one day and ending on the next
        $context = $this->createValidationContext(
            startDateTime: '2024-01-01 22:00:00',
            endDateTime: '2024-01-02 01:00:00',
            availabilityId: 123
        );

        $this->availabilityService->shouldReceive('find')->never();

        // Act: Execute the time range validation rule
        $this->rule->validate($context);

        // Assert: Violation should be recorded for multi-day event
        $this->assertTrue($context->hasViolations());
        $this->assertArrayHasKey('end_datetime', $context->getViolations());
        $this->assertStringContainsString(
            'Events cannot span across multiple days',
            (string) $context->getViolations()['end_datetime']
        );
    }

    /**
     * Configure the schedulable mock with required methods.
     */
    private function configureSchedulableMock(): void
    {
        $this->schedulable->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);

        $this->schedulable->shouldReceive('getMorphClass')
            ->andReturn('TestModel');
    }

    /**
     * Configure service container bindings for tests.
     */
    private function configureServiceContainer(): void
    {
        $this->app->singleton(AvailabilityService::class, function (): AvailabilityService|MockInterface {
            return $this->availabilityService;
        });

        $this->app->instance('roster.availability', $this->availabilityService);

        if (function_exists('availability_for') && method_exists($this->app, 'bind')) {
            $this->app->bind('availability.service', function (): MockInterface|AvailabilityService {
                return $this->availabilityService;
            });
        }
    }

    /**
     * Create an Availability model instance with test data.
     *
     * @param array{
     *     daily_start?: string,
     *     daily_end?: string,
     *     days?: string[],
     *     validity_start?: string|null,
     *     validity_end?: string|null
     * } $properties
     */
    private function createAvailability(array $properties): Availability
    {
        $availability = new Availability();

        $availability->daily_start = $properties['daily_start'] ?? '08:00:00';
        $availability->daily_end = $properties['daily_end'] ?? '18:00:00';
        $availability->days = $properties['days'] ?? ['monday'];
        $availability->validity_start = $properties['validity_start'] ?? '2024-01-01';
        $availability->validity_end = $properties['validity_end'] ?? '2024-12-31';

        return $availability;
    }

    /**
     * Create a ValidationContext with schedule entity type.
     */
    private function createValidationContext(string $startDateTime, string $endDateTime, ?int $availabilityId): ValidationContext
    {
        $data = [];

        if ($availabilityId !== null) {
            $data['availability_id'] = $availabilityId;
        }

        if ($startDateTime !== '') {
            $data['start_datetime'] = $startDateTime;
        }

        if ($endDateTime !== '') {
            $data['end_datetime'] = $endDateTime;
        }

        return new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: $data,
            model: $this->schedulable
        );
    }
}
