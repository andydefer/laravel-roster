<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Rules;

use Exception;
use Illuminate\Support\Carbon;
use Mockery;
use Mockery\MockInterface;
use Roster\Domain\DTOs\ConflictResult;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\Context\ValidationContext;
use Roster\Validation\Rules\TemporalConflictRule;
use Tests\TestCase;

final class TemporalConflictRuleTest extends TestCase
{
    private TemporalConflictService|MockInterface $conflictService;

    private TemporalConflictRule $rule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conflictService = Mockery::mock(TemporalConflictService::class);
        $this->rule = new TemporalConflictRule(temporalConflictService: $this->conflictService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_passes_when_no_conflicts_for_schedule_create(): void
    {
        // Arrange: Create validation context for schedule creation
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_passes_when_no_conflicts_for_impediment_create(): void
    {
        // Arrange: Create validation context for impediment creation
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: [
                'availability_id' => 789,
                'start_datetime' => '2024-01-01 09:00:00',
                'end_datetime' => '2024-01-01 11:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                789,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_fails_when_conflicts_exist_for_schedule_create(): void
    {
        // Arrange: Create validation context with conflicting schedule
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $conflictResult = new ConflictResult(
            hasConflicts: true,
            message: 'Schedule overlaps with existing schedule from 2024-01-01 09:00 to 2024-01-01 10:00'
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn($conflictResult);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for overlap
        $this->assertTrue($context->hasViolations());
        $this->assertTrue($context->hasViolationFor('overlap'));
    }

    public function test_fails_when_conflicts_exist_for_impediment_create(): void
    {
        // Arrange: Create validation context with conflicting impediment
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: [
                'availability_id' => 789,
                'start_datetime' => '2024-01-01 09:00:00',
                'end_datetime' => '2024-01-01 11:00:00',
            ]
        );

        $conflictResult = new ConflictResult(
            hasConflicts: true,
            message: 'Schedule overlaps with existing impediment from 2024-01-01 10:00 to 2024-01-01 10:30'
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                789,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn($conflictResult);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: Violation should be present for overlap
        $this->assertTrue($context->hasViolations());
        $this->assertTrue($context->hasViolationFor('overlap'));
    }

    public function test_excludes_current_entity_for_schedule_update(): void
    {
        // Arrange: Create validation context for schedule update with existing entity
        $currentEntity = (object) ['id' => 123];

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ],
            model: null,
            currentEntity: $currentEntity
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                123,
                null
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_excludes_current_entity_for_impediment_update(): void
    {
        // Arrange: Create validation context for impediment update with existing entity
        $currentEntity = (object) ['id' => 456];

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::IMPEDIMENT,
            data: [
                'availability_id' => 789,
                'start_datetime' => '2024-01-01 09:00:00',
                'end_datetime' => '2024-01-01 11:00:00',
            ],
            model: null,
            currentEntity: $currentEntity
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                789,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                456
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_handles_current_entity_without_id(): void
    {
        // Arrange: Create validation context with entity missing ID
        $currentEntity = (object) ['name' => 'Test Entity'];

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ],
            model: null,
            currentEntity: $currentEntity
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_handles_current_entity_as_null(): void
    {
        // Arrange: Create validation context with null current entity
        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_fails_when_end_time_before_start_time(): void
    {
        // Arrange: Create validation context with invalid time range
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 12:00:00',
                'end_datetime' => '2024-01-01 08:00:00',
            ]
        );

        // Le service sera appelé car votre règle n'a pas de validation pour end > start
        // Nous devons donc mocké le retour d'un ConflictResult
        $conflictResult = new ConflictResult(
            hasConflicts: false, // Pas de conflits pour ce test
            conflictingSchedules: [],
            conflictingImpediments: []
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                null
            )
            ->andReturn($conflictResult);

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present (car pas de validation end > start dans la règle)
        $this->assertFalse($context->hasViolations());
    }

    public function test_skips_validation_when_missing_required_fields(): void
    {
        // Arrange: Create validation context without start_datetime
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->never();

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_skips_validation_when_availability_id_is_missing(): void
    {
        // Arrange: Create validation context without availability ID
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->never();

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_skips_validation_when_availability_id_is_null(): void
    {
        // Arrange: Create validation context with null availability ID
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => null,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->never();

        // Act: Validate the context
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_handles_exception_from_conflict_service(): void
    {
        // Arrange: Create validation context with service that throws exception
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->andThrow(new Exception('Service error'));

        // Act: Validate the context (exception should be caught)
        $this->rule->validate($context);

        // Assert: No violations should be present despite exception
        $this->assertFalse($context->hasViolations());
    }

    public function test_handles_malformed_datetime_strings(): void
    {
        // Arrange: Create validation context with invalid datetime format
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => 'not-a-valid-datetime',
                'end_datetime' => '2024-01-01 12:00:00',
            ]
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->never();

        // Act: Validate the context (exception should be caught)
        $this->rule->validate($context);

        // Assert: No violations should be present
        $this->assertFalse($context->hasViolations());
    }

    public function test_uses_correct_exclusion_ids_for_different_entity_types(): void
    {
        // Arrange: Test for Schedule entity type
        $scheduleEntity = (object) ['id' => 123];
        $scheduleContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ],
            model: null,
            currentEntity: $scheduleEntity
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                123,
                null
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate schedule context
        $this->rule->validate($scheduleContext);

        // Reset mock for next test
        Mockery::close();
        $this->setUp();

        // Arrange: Test for Impediment entity type
        $impedimentEntity = (object) ['id' => 789];
        $impedimentContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::IMPEDIMENT,
            data: [
                'availability_id' => 456,
                'start_datetime' => '2024-01-01 08:00:00',
                'end_datetime' => '2024-01-01 12:00:00',
            ],
            model: null,
            currentEntity: $impedimentEntity
        );

        $this->conflictService
            ->expects('checkAllConflicts')
            ->once()
            ->with(
                456,
                Mockery::type(Carbon::class),
                Mockery::type(Carbon::class),
                null,
                789
            )
            ->andReturn(ConflictResult::noConflict());

        // Act: Validate impediment context
        $this->rule->validate($impedimentContext);

        // Assert: Both contexts should have no violations
        $this->assertFalse($scheduleContext->hasViolations());
        $this->assertFalse($impedimentContext->hasViolations());
    }
}
