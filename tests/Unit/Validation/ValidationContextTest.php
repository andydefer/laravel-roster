<?php

declare(strict_types=1);

namespace Tests\Unit\Validation\Context;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Tests\TestCase;
use Tests\Support\TestSchedulable;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Services\AvailabilityService;
use Roster\Support\RosterMutationContext;
use Roster\Contracts\Services\ServiceInterface;
use Roster\Validation\Context\ValidationContext;

/**
 * Test suite for ValidationContext functionality.
 *
 * Covers data access, validation violations, service resolution, and owner resolution logic.
 */
final class ValidationContextTest extends TestCase
{
    use RefreshDatabase;

    private TestSchedulable $schedulable;
    private Availability $availability;

    /**
     * Set up test environment with necessary database entities.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Arrange: Create test schedulable entity
        $this->schedulable = TestSchedulable::create();

        // Arrange: Create availability within mutation context
        $this->availability = RosterMutationContext::allow(function () {
            return Availability::create([
                'schedulable_id' => $this->schedulable->id,
                'schedulable_type' => TestSchedulable::class,
                'type' => 'consultation',
                'daily_start' => '09:00:00',
                'daily_end' => '17:00:00',
                'days' => json_encode(['monday', 'tuesday']),
                'validity_start' => '2025-01-01',
                'validity_end' => '2025-12-31',
            ]);
        });
    }

    /**
     * Clean up temporary tables after tests.
     */
    protected function tearDown(): void
    {
        // Drop any test tables that might have been created
        $tables = ['test_current_entities', 'test_entity_for_update', 'test_entity_with_availability', 'test_partial_updates'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        parent::tearDown();
    }

    /**
     * Test basic instantiation with minimal parameters.
     */
    public function test_can_be_instantiated_with_basic_parameters(): void
    {
        // Arrange: Create context with only required parameters
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: ['name' => 'Test']
        );

        // Assert: Verify basic properties are correctly set
        $this->assertSame(OperationType::CREATE, $context->getOperation());
        $this->assertSame(EntityType::SCHEDULE, $context->getEntityType());
        $this->assertNull($context->getCurrentEntity());
    }

    /**
     * Test instantiation with all possible parameters.
     */
    public function test_can_be_instantiated_with_all_parameters(): void
    {
        // Arrange: Create simple entity for testing
        $this->createTestTable('test_current_entities');
        $currentEntity = $this->createSimpleEntity('test_current_entities');

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::IMPEDIMENT,
            data: ['name' => 'Test'],
            model: $this->schedulable,
            currentEntity: $currentEntity
        );

        // Assert: Verify all properties are correctly set
        $this->assertSame(OperationType::UPDATE, $context->getOperation());
        $this->assertSame(EntityType::IMPEDIMENT, $context->getEntityType());
        $this->assertSame($this->schedulable, $context->getSchedulable());
        $this->assertSame($currentEntity, $context->getCurrentEntity());
    }

    /**
     * Test the has() method with various value types.
     */
    public function test_checks_if_key_exists_and_is_not_null(): void
    {
        // Arrange: Create context with various data types
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'name' => 'Test',
                'nullable' => null,
                'empty_string' => '',
                'zero' => 0,
                'false' => false
            ]
        );

        // Assert: Verify has() correctly identifies non-null values
        $this->assertTrue($context->has('name'));
        $this->assertFalse($context->has('nullable'));
        $this->assertTrue($context->has('empty_string'));
        $this->assertTrue($context->has('zero'));
        $this->assertTrue($context->has('false'));
        $this->assertFalse($context->has('nonexistent'));
    }

    /**
     * Test retrieval of safe data excluding null values.
     */
    public function test_gets_safe_data_without_null_values(): void
    {
        // Arrange: Create context with mixed data including null values
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'name' => 'Test',
                'description' => null,
                'active' => true,
                'optional' => null
            ]
        );

        // Act: Retrieve safe data (non-null values only)
        $safeData = $context->safeData();

        // Assert: Verify null values are excluded from safe data
        $this->assertArrayHasKey('name', $safeData);
        $this->assertArrayHasKey('active', $safeData);
        $this->assertArrayNotHasKey('description', $safeData);
        $this->assertArrayNotHasKey('optional', $safeData);
        $this->assertCount(2, $safeData);
    }

    /**
     * Test get() method for CREATE operation (data only).
     */
    public function test_gets_value_from_safe_data_for_create_operation(): void
    {
        // Arrange: Create context for CREATE operation
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'name' => 'Test Name',
                'description' => null,
                'count' => 5
            ]
        );

        // Assert: Verify get() returns correct values with defaults
        $this->assertEquals('Test Name', $context->get('name'));
        $this->assertEquals(5, $context->get('count'));
        $this->assertNull($context->get('description'));
        $this->assertNull($context->get('nonexistent'));
        $this->assertEquals('default', $context->get('nonexistent', 'default'));
    }

    /**
     * Test get() method for UPDATE operation with current entity.
     */
    public function test_gets_value_from_current_entity_for_update_operation(): void
    {
        // Arrange: Create current entity with test attributes
        $this->createTestTable('test_entity_for_update');
        $currentEntity = new class extends Model {
            protected $table = 'test_entity_for_update';
            protected $guarded = [];
            public $timestamps = false;

            protected $attributes = [
                'name' => 'Existing Name',
                'count' => 10,
                'custom_field' => 'custom value'
            ];
        };

        // Create instance without saving to avoid database interactions
        $entity = new $currentEntity();

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'description' => 'Updated Description'
            ],
            currentEntity: $entity
        );

        // Assert: Verify get() prioritizes context data, falls back to entity
        $this->assertEquals('Updated Description', $context->get('description'));
        $this->assertEquals('Existing Name', $context->get('name'));
        $this->assertEquals(10, $context->get('count'));
        $this->assertEquals('custom value', $context->get('custom_field'));
        $this->assertNull($context->get('nonexistent'));
    }

    /**
     * Test raw data access methods.
     */
    public function test_gets_raw_data_values(): void
    {
        // Arrange: Create context with raw data including null
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'name' => 'Test',
                'nullable' => null
            ]
        );

        // Assert: Verify raw access methods include null values
        $this->assertEquals('Test', $context->rawGet('name'));
        $this->assertNull($context->rawGet('nullable'));
        $this->assertNull($context->rawGet('nonexistent'));
        $this->assertEquals('default', $context->rawGet('nonexistent', 'default'));
    }

    /**
     * Test raw data existence checking.
     */
    public function test_checks_if_key_exists_in_raw_data(): void
    {
        // Arrange: Create context with null values
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'name' => 'Test',
                'nullable' => null
            ]
        );

        // Assert: Verify rawHas() includes null values
        $this->assertTrue($context->rawHas('name'));
        $this->assertTrue($context->rawHas('nullable'));
        $this->assertFalse($context->rawHas('nonexistent'));
    }

    /**
     * Test retrieval of complete raw data.
     */
    public function test_gets_all_raw_data(): void
    {
        // Arrange: Define complete test data
        $data = [
            'name' => 'Test',
            'description' => null,
            'active' => true
        ];

        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: $data
        );

        // Assert: Verify all data access methods return complete data
        $this->assertEquals($data, $context->getData());
        $this->assertEquals($data, $context->rawData());
    }

    /**
     * Test data modification via set() method.
     */
    public function test_sets_new_values_in_data(): void
    {
        // Arrange: Create initial context
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: ['name' => 'Original']
        );

        // Act: Modify existing and add new data
        $context->set('name', 'Updated');
        $context->set('new_field', 'Value');

        // Assert: Verify modifications are reflected
        $this->assertEquals('Updated', $context->get('name'));
        $this->assertEquals('Value', $context->get('new_field'));
    }

    /**
     * Test validation violation recording and retrieval.
     */
    public function test_records_validation_violations(): void
    {
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        // Act: Add violations
        $context->setViolation('name', 'Name is required');
        $context->setViolation('email', 'Email is invalid');

        $violations = $context->getViolations();

        // Convert the array of objects into an associative array by field
        $violationsByField = [];
        foreach ($violations as $violation) {
            $violationsByField[$violation->getField()] = $violation;
        }

        // Assert: Check that violations are properly recorded
        $this->assertArrayHasKey('name', $violationsByField);
        $this->assertArrayHasKey('email', $violationsByField);
        $this->assertStringContainsString('Name is required', $violationsByField['name']->getMessage());
        $this->assertStringContainsString('Email is invalid', $violationsByField['email']->getMessage());
    }

    /**
     * Test flag management functionality.
     */
    public function test_manages_flags(): void
    {
        // Arrange: Create context for flag testing
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        // Act & Assert: Test flag setting and retrieval
        $context->setFlag('skip_validation');
        $this->assertTrue($context->hasFlag('skip_validation'));
        $this->assertFalse($context->hasFlag('nonexistent_flag'));

        $this->assertTrue($context->getFlag('skip_validation'));
        $this->assertFalse($context->getFlag('nonexistent_flag'));
        $this->assertEquals('default', $context->getFlag('nonexistent_flag', 'default'));

        $context->setFlag('max_attempts', 3);
        $this->assertEquals(3, $context->getFlag('max_attempts'));
        $this->assertTrue($context->hasFlag('max_attempts'));

        $context->setFlag('enabled', false);
        $this->assertFalse($context->hasFlag('enabled'));
        $this->assertFalse($context->getFlag('enabled'));
    }

    /**
     * Test service resolution for availability entity with schedulable.
     */
    public function test_gets_current_service_for_availability_entity(): void
    {
        // Arrange: Create context for availability entity with schedulable
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: [],
            model: $this->schedulable
        );

        // Act: Retrieve current service
        $result = $context->getCurrentService();

        // Assert: Verify correct service type is returned
        $this->assertInstanceOf(ServiceInterface::class, $result);
    }

    /**
     * Test exception when getting service without schedulable.
     */
    public function test_throws_exception_when_getting_service_without_schedulable(): void
    {
        // Arrange: Create context without schedulable
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: []
        );

        // Assert: Expect exception when schedulable is missing
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get service: schedulable is not set in validation context');

        // Act: Attempt to get service without schedulable
        $context->getCurrentService();
    }

    /**
     * Test owner resolution from availability_id in data.
     */
    public function test_resolves_owner_from_availability_id(): void
    {
        // Arrange: Create context with availability_id in data
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: ['availability_id' => $this->availability->id],
            model: $this->schedulable
        );

        // Act: Access private resolveOwner method via reflection
        $owner = $this->invokePrivateMethod($context, 'resolveOwner');

        // Assert: Verify owner is correctly resolved from availability ID
        $this->assertInstanceOf(Availability::class, $owner);
        $this->assertEquals($this->availability->id, $owner->id);
    }

    /**
     * Test owner resolution from current entity with availability relationship.
     */
    public function test_resolves_owner_from_current_entity_with_availability_method(): void
    {
        // Arrange: Create entity with availability relationship
        $this->createTestTable('test_entity_with_availability', function ($table) {
            $table->foreignId('availability_id')->nullable()->constrained('roster_availabilities')->nullOnDelete();
        });

        $currentEntity = new class extends Model {
            protected $table = 'test_entity_with_availability';
            protected $guarded = [];
            public $timestamps = false;

            public function availability()
            {
                return $this->belongsTo(Availability::class, 'availability_id');
            }
        };

        $entity = new $currentEntity();
        $entity->availability_id = $this->availability->id;
        $entity->save();

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [],
            currentEntity: $entity
        );

        // Act: Resolve owner via private method
        $owner = $this->invokePrivateMethod($context, 'resolveOwner');

        // Assert: Verify owner is resolved from entity relationship
        $this->assertInstanceOf(Availability::class, $owner);
        $this->assertEquals($this->availability->id, $owner->id);
    }

    /**
     * Test owner resolution when current entity is itself an availability.
     */
    public function test_resolves_owner_from_current_entity_as_availability_model(): void
    {
        // Arrange: Create context with availability as current entity
        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [],
            currentEntity: $this->availability
        );

        // Act: Resolve owner via private method
        $owner = $this->invokePrivateMethod($context, 'resolveOwner');

        // Assert: Verify the same availability instance is returned
        $this->assertSame($this->availability, $owner);
    }

    /**
     * Test owner resolution from flag.
     */
    public function test_resolves_owner_from_flag(): void
    {
        // Arrange: Create context with availability stored in flag
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        $context->setFlag('availability', $this->availability);

        // Act: Resolve owner via private method
        $owner = $this->invokePrivateMethod($context, 'resolveOwner');

        // Assert: Verify owner is resolved from flag
        $this->assertSame($this->availability, $owner);
    }

    /**
     * Test owner resolution returns null when no owner can be resolved.
     */
    public function test_returns_null_when_no_owner_can_be_resolved(): void
    {
        // Arrange: Create context with no owner resolution sources
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        // Act: Resolve owner via private method
        $owner = $this->invokePrivateMethod($context, 'resolveOwner');

        // Assert: Verify null is returned when no owner sources exist
        $this->assertNull($owner);
    }

    /**
     * Test graceful handling of missing availability.
     */
    public function test_handles_exception_when_availability_not_found(): void
    {
        // Arrange: Create context with non-existent availability ID
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: ['availability_id' => 99999],
            model: $this->schedulable
        );

        // Act: Resolve owner via private method
        $owner = $this->invokePrivateMethod($context, 'resolveOwner');

        // Assert: Verify null is returned for non-existent availability
        $this->assertNull($owner);
    }

    /**
     * Test availability service retrieval with schedulable.
     */
    public function test_gets_availability_service(): void
    {
        // Arrange: Create context with schedulable
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [],
            model: $this->schedulable
        );

        // Act: Retrieve availability service
        $result = $context->getAvailabilityService();

        // Assert: Verify correct service type is returned
        $this->assertInstanceOf(AvailabilityService::class, $result);
    }

    /**
     * Test exception when getting availability service without schedulable.
     */
    public function test_throws_exception_when_getting_availability_service_without_schedulable(): void
    {
        // Arrange: Create context without schedulable
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        // Assert: Expect exception when schedulable is missing
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get Availability service: schedulable is not set in validation context');

        // Act: Attempt to get service without schedulable
        $context->getAvailabilityService();
    }

    /**
     * Test get() method with non-Model entities.
     */
    public function test_handles_edge_cases_in_get_method(): void
    {
        // Arrange: Create context with stdClass entity (non-Model)
        $nonModelEntity = new stdClass();
        $nonModelEntity->name = 'Test';
        $nonModelEntity->count = 5;

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [],
            currentEntity: $nonModelEntity
        );

        // Assert: Verify get() returns null for non-Model entity properties
        $this->assertNull($context->get('name'));
        $this->assertNull($context->get('count'));
    }

    /**
     * Test partial update handling with mixed data sources.
     */
    public function test_handles_partial_updates_correctly(): void
    {
        // Arrange: Create entity with initial values
        $this->createTestTable('test_partial_updates');
        $currentEntity = new class extends Model {
            protected $table = 'test_partial_updates';
            protected $guarded = [];
            public $timestamps = false;

            protected $attributes = [
                'name' => 'Existing',
                'description' => 'Existing Description',
                'count' => 5
            ];
        };

        // Create instance without saving (attributes are already set)
        $entity = new $currentEntity();

        $context = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: [
                'description' => 'Updated Description',
                'new_field' => 'New Value'
            ],
            currentEntity: $entity
        );

        // Assert: Verify get() prioritizes context data, falls back to entity
        $this->assertEquals('Updated Description', $context->get('description'));
        $this->assertEquals('Existing', $context->get('name'));
        $this->assertEquals(5, $context->get('count'));
        $this->assertEquals('New Value', $context->get('new_field'));
        $this->assertNull($context->get('nonexistent'));
    }

    /**
     * Test operation type enum handling.
     */
    public function test_handles_operation_type_enum_correctly(): void
    {
        // Arrange: Create contexts with different operation types
        $createContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        $updateContext = new ValidationContext(
            operationType: OperationType::UPDATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        // Assert: Verify operation types are correctly stored and retrieved
        $this->assertSame(OperationType::CREATE, $createContext->getOperation());
        $this->assertSame(OperationType::UPDATE, $updateContext->getOperation());
    }

    /**
     * Test entity type enum handling.
     */
    public function test_handles_entity_type_enum_correctly(): void
    {
        // Arrange: Create contexts with different entity types
        $availabilityContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY,
            data: []
        );

        $scheduleContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: []
        );

        $impedimentContext = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: []
        );

        // Assert: Verify entity types are correctly stored and retrieved
        $this->assertSame(EntityType::AVAILABILITY, $availabilityContext->getEntityType());
        $this->assertSame(EntityType::SCHEDULE, $scheduleContext->getEntityType());
        $this->assertSame(EntityType::IMPEDIMENT, $impedimentContext->getEntityType());
    }

    /**
     * Test schedule service building with resolved owner.
     */
    public function test_builds_schedule_service_with_resolved_owner(): void
    {
        // Arrange: Create context with availability_id for owner resolution
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: ['availability_id' => $this->availability->id],
            model: $this->schedulable
        );

        // Act: Retrieve current service
        $result = $context->getCurrentService();

        // Assert: Verify service is correctly built with resolved owner
        $this->assertInstanceOf(ServiceInterface::class, $result);
    }

    /**
     * Test exception when building schedule service without owner.
     */
    public function test_throws_exception_when_building_schedule_service_without_owner(): void
    {
        // Arrange: Create context without owner resolution sources
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::SCHEDULE,
            data: [],
            model: $this->schedulable
        );

        // Assert: Expect exception when owner is missing
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot get Schedule service: owner is required but not available in validation context');

        // Act: Attempt to get service without owner
        $context->getCurrentService();
    }

    /**
     * Test impediment service building with resolved owner.
     */
    public function test_builds_impediment_service_with_resolved_owner(): void
    {
        // Arrange: Create context for impediment entity with owner
        $context = new ValidationContext(
            operationType: OperationType::CREATE,
            entityType: EntityType::IMPEDIMENT,
            data: ['availability_id' => $this->availability->id],
            model: $this->schedulable
        );

        // Act: Retrieve current service
        $result = $context->getCurrentService();

        // Assert: Verify impediment service is correctly built
        $this->assertInstanceOf(ServiceInterface::class, $result);
    }

    /**
     * Create a test database table.
     */
    private function createTestTable(string $tableName, ?callable $callback = null): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function ($table) use ($callback) {
                $table->id();
                $table->string('name')->nullable();
                $table->integer('count')->nullable();

                if ($callback) {
                    $callback($table);
                }
            });
        }
    }

    /**
     * Create a simple entity instance.
     */
    private function createSimpleEntity(string $tableName): Model
    {
        $entity = new class extends Model {
            protected $guarded = [];
            public $timestamps = false;
        };

        $entity->setTable($tableName);
        $entity->save();

        return $entity;
    }

    /**
     * Invoke a private method on an object using reflection.
     */
    private function invokePrivateMethod(object $object, string $methodName): mixed
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($object);
    }
}
