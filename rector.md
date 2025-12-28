# Rector Refactoring Report
*Generated: dim. 28 déc. 2025 13:41:10 WAT*


19 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/SchedulableValidationRuleTest.php:688

    ---------- begin diff ----------
@@ @@

     /**
      * Create a mock model stub with specified ID.
-     *
-     * @param int $id
-     * @return Model
      */
     private function createModelStub(int $id): Model
     {
@@ @@
     /**
      * Create a validation context mock for CREATE operation.
      *
-     * @param EntityType $entityType
-     * @param Model|null $schedulable
      * @param array<string, mixed> $data
      * @return MockObject&ValidationContextInterface
      */
@@ @@
     /**
      * Create a validation context mock for UPDATE operation.
      *
-     * @param EntityType $entityType
-     * @param Model $schedulable
      * @param array<string, mixed> $data
      * @return MockObject&ValidationContextInterface
      */
@@ @@
     /**
      * Create a validation context mock for DELETE operation.
      *
-     * @param EntityType $entityType
-     * @param Model $schedulable
      * @return MockObject&ValidationContextInterface
      */
     private function createContextForDeleteOperation(
@@ @@
      * Configure context get method for schedulable fields.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param mixed $schedulableId
-     * @param string|null $schedulableType
      */
     private function configureContextGetMethod(
         MockObject $context,
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/TimeRangeRuleTest.php:22

    ---------- begin diff ----------
@@ @@
     use RefreshDatabase;

     private AvailabilityService|MockInterface $availabilityService;
+
     private TimeRangeRule $rule;
+
     private Model|MockInterface $schedulable;

     /**
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/TimeSlotDateTimeRuleTest.php:23

    ---------- begin diff ----------
@@ @@
 final class TimeSlotDateTimeRuleTest extends TestCase
 {
     private TimeSlotDateTimeRule $rule;
+
     private Model|MockInterface $schedulable;

     /**
@@ @@
      * @param string|null $startDatetime The start datetime string or null if not provided
      * @param string|null $endDatetime The end datetime string or null if not provided
      * @param object|null $currentEntity The current entity for UPDATE operations
-     *
-     * @return ValidationContext
      */
     private function createValidationContext(
         OperationType $operationType,
@@ @@
      *
      * @param string|null $startDatetime The start datetime string or null
      * @param string|null $endDatetime The end datetime string or null
-     *
-     * @return object
      */
     private function createMockEntity(?string $startDatetime, ?string $endDatetime): object
     {
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RemoveUselessReturnTagRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidationContextTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Context;

-use Exception;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Facades\Schema;
@@ @@
     use RefreshDatabase;

     private TestSchedulable $schedulable;
+
     private Availability $availability;

     /**
@@ @@
         $this->createTestTable('test_entity_for_update');
         $currentEntity = new class extends Model {
             protected $table = 'test_entity_for_update';
+
             protected $guarded = [];
+
             public $timestamps = false;

             protected $attributes = [
@@ @@
     public function test_resolves_owner_from_current_entity_with_availability_method(): void
     {
         // Arrange: Create entity with availability relationship
-        $this->createTestTable('test_entity_with_availability', function ($table) {
+        $this->createTestTable('test_entity_with_availability', function ($table): void {
             $table->foreignId('availability_id')->nullable()->constrained('roster_availabilities')->nullOnDelete();
         });

         $currentEntity = new class extends Model {
             protected $table = 'test_entity_with_availability';
+
             protected $guarded = [];
+
             public $timestamps = false;

             public function availability()
@@ @@
         $this->createTestTable('test_partial_updates');
         $currentEntity = new class extends Model {
             protected $table = 'test_partial_updates';
+
             protected $guarded = [];
+
             public $timestamps = false;

             protected $attributes = [
@@ @@
     private function createTestTable(string $tableName, ?callable $callback = null): void
     {
         if (!Schema::hasTable($tableName)) {
-            Schema::create($tableName, function ($table) use ($callback) {
+            Schema::create($tableName, function ($table) use ($callback): void {
                 $table->id();
                 $table->string('name')->nullable();
                 $table->integer('count')->nullable();

-                if ($callback) {
+                if ($callback !== null) {
                     $callback($table);
                 }
             });
@@ @@
     {
         $entity = new class extends Model {
             protected $guarded = [];
+
             public $timestamps = false;
         };
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * NullableCompareToNullRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidatorTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation;

+use ReflectionClass;
 use Exception;
 use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
 use PHPUnit\Framework\MockObject\MockObject;
@@ @@
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Validation\RuleScanner;
-use Roster\Validation\ValidationResult;
 use Roster\Validation\Validator;
 use Tests\TestCase;

@@ @@
         // Assert: Verify both rules are registered by class name
         $this->assertTrue($validator->hasRule(TestRule1::class));
         $this->assertTrue($validator->hasRule(TestRule2::class));
-        $this->assertEquals(2, $validator->getRuleCount());
+        $this->assertSame(2, $validator->getRuleCount());
     }

     /**
@@ @@

         // Assert: Verify rule is registered and accessible by class name
         $this->assertTrue($this->validator->hasRule(CustomRule::class));
-        $this->assertEquals(1, $this->validator->getRuleCount());
+        $this->assertSame(1, $this->validator->getRuleCount());
     }

     /**
@@ @@
             {
                 return 'LowPriorityRule';
             }
+
             public function getPriority(): int
             {
                 return 10;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'MediumPriorityRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'HighPriorityRule';
             }
+
             public function getPriority(): int
             {
                 return 100;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'PassingRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'BaseRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('base', 'Base rule violation');
@@ @@
             {
                 return 'AdditionalRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::UPDATE && $entity === EntityType::IMPEDIMENT;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('additional', 'Additional rule violation');
@@ @@
             {
                 return 'ExceptionRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::SCHEDULE;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 throw new Exception('Rule processing failed');
@@ @@
             {
                 return 'AvailabilityRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $entity === EntityType::AVAILABILITY &&
                     ($operation === OperationType::CREATE || $operation === OperationType::UPDATE);
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'ScheduleRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $entity === EntityType::SCHEDULE && $operation === OperationType::CREATE;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'TestRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'Rule1';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'Rule2';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'Rule3';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
         // Assert: Verify all rules are returned
         $this->assertCount(3, $allRules);

-        $ruleNames = array_map(fn($rule) => $rule->getName(), $allRules);
+        $ruleNames = array_map(fn(RuleInterface $rule): string => $rule->getName(), $allRules);
         $this->assertContains('Rule1', $ruleNames);
         $this->assertContains('Rule2', $ruleNames);
         $this->assertContains('Rule3', $ruleNames);
@@ @@
             {
                 return 'Rule1';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('rule1', 'Violation from Rule1');
@@ @@
             {
                 return 'Rule2';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('rule2', 'Violation from Rule2');
@@ @@
             {
                 return 'Rule3';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return $operation === OperationType::CREATE && $entity === EntityType::AVAILABILITY;
             }
+
             public function validate(ValidationContextInterface $context): void
             {
                 $context->setViolation('rule3', 'Violation from Rule3');
@@ @@
     public function test_generates_correct_cache_keys_for_rule_indexing(): void
     {
         // Arrange: Use reflection to access private method
-        $reflection = new \ReflectionClass($this->validator);
+        $reflection = new ReflectionClass($this->validator);
         $method = $reflection->getMethod('createCacheKey');
         $method->setAccessible(true);

@@ @@
             {
                 return 'InvalidAttributeRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return false;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'CountRule1';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
             {
                 return 'CountRule2';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

         // Act: Register rules and check counts
-        $this->assertEquals(0, $this->validator->getRuleCount());
+        $this->assertSame(0, $this->validator->getRuleCount());

         $this->validator->registerRule($rule1);
-        $this->assertEquals(1, $this->validator->getRuleCount());
+        $this->assertSame(1, $this->validator->getRuleCount());

         $this->validator->registerRule($rule2);
-        $this->assertEquals(2, $this->validator->getRuleCount());
+        $this->assertSame(2, $this->validator->getRuleCount());

         // Register same instance again (should increase count - duplicates are allowed)
         $this->validator->registerRule($rule1);
-        $this->assertEquals(3, $this->validator->getRuleCount());
+        $this->assertSame(3, $this->validator->getRuleCount());
     }

     /**
@@ @@
             {
                 return 'DuplicateRule';
             }
+
             public function getPriority(): int
             {
                 return 50;
             }
+
             public function supports(OperationType $operation, EntityType $entity): bool
             {
                 return true;
             }
+
             public function validate(ValidationContextInterface $context): void {}
         };

@@ @@
         $this->validator->registerRule($rule);

         // Assert: All registrations are counted
-        $this->assertEquals(3, $this->validator->getRuleCount());
+        $this->assertSame(3, $this->validator->getRuleCount());
         $this->assertTrue($this->validator->hasRule(get_class($rule)));
     }
 }
@@ @@
     {
         return 'TestRule1';
     }
+
     public function getPriority(): int
     {
         return 50;
     }
+
     public function supports(OperationType $operation, EntityType $entity): bool
     {
         return true;
     }
+
     public function validate(ValidationContextInterface $context): void {}
 }

@@ @@
     {
         return 'TestRule2';
     }
+
     public function getPriority(): int
     {
         return 50;
     }
+
     public function supports(OperationType $operation, EntityType $entity): bool
     {
         return true;
     }
+
     public function validate(ValidationContextInterface $context): void {}
 }

@@ @@
     {
         return 'CustomRule';
     }
+
     public function getPriority(): int
     {
         return 50;
     }
+
     public function supports(OperationType $operation, EntityType $entity): bool
     {
         return true;
     }
+
     public function validate(ValidationContextInterface $context): void {}
 }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * AssertEqualsToSameRector
 * AddArrowFunctionReturnTypeRector
 * AddArrayFunctionClosureParamTypeRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Integration/CompleteRosterIntegrationTest.php:954

    ---------- begin diff ----------
@@ @@
         foreach ($createdSchedules as $createdSchedule) {
             $scheduleAvailability = $availabilityByScheduleId[$createdSchedule->id] ?? null;

-            if ($scheduleAvailability) {
+            if ($scheduleAvailability instanceof AvailabilityModel) {
                 try {
                     schedule_for($scheduleAvailability)->delete($createdSchedule->id);
                 } catch (Exception $e) {
@@ @@
     /**
      * Create multiple impediments for a given availability.
      *
-     * @param AvailabilityModel $availability
      * @return array<int, ImpedimentModel>
      */
     private function createImpedimentsForAvailability(AvailabilityModel $availability): array
@@ @@
     /**
      * Create multiple schedules for a given availability.
      *
-     * @param AvailabilityModel $availability
      * @param array<int, AvailabilityModel> $availabilityByScheduleId
      * @return array<int, ScheduleModel>
      */
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * NullableCompareToNullRector
 * RemoveUselessParamTagRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Integration/Database/AvailabilityIntegrationTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Integration\Database;

-use PHPUnit\Framework\Attributes\Group;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Collection;
 use Roster\Models\Availability as AvailabilityModel;
    ----------- end diff -----------

Applied rules:


8) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/MutationContextAllowsMutationTest.php:47

    ---------- begin diff ----------
@@ @@
         ];

         // Act: Create availability within mutation context
-        $availability = RosterMutationContext::allow(function () use ($testData) {
+        $availability = RosterMutationContext::allow(function () use ($testData): Availability {
             return $this->createAvailability($testData);
         });

@@ @@
     public function test_update_inside_context_is_allowed(): void
     {
         // Arrange: Create initial availability
-        $availability = RosterMutationContext::allow(function () {
+        $availability = RosterMutationContext::allow(function (): Availability {
             return $this->createAvailability([
                 'schedulable_id' => 1,
                 'schedulable_type' => TestSchedulable::class,
@@ @@
     public function test_delete_inside_context_is_allowed(): void
     {
         // Arrange: Create availability
-        $availability = RosterMutationContext::allow(function () {
+        $availability = RosterMutationContext::allow(function (): Availability {
             return $this->createAvailability([
                 'schedulable_id' => 1,
                 'schedulable_type' => TestSchedulable::class,
@@ @@
      * Create an availability with the given data.
      *
      * @param array<string, mixed> $data
-     * @return Availability
      */
     private function createAvailability(array $data): Availability
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector
 * ClosureReturnTypeRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/HelpersTest.php:336

    ---------- begin diff ----------
@@ @@

     /**
      * Create a mock schedulable model for testing.
-     *
-     * @return Model
      */
     private function createMockSchedulable(): Model
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/ScheduleTest.php:155

    ---------- begin diff ----------
@@ @@
             // Act: Create schedule with specific status
             $schedule = schedule_for($this->testAvailability)->create([
                 'title' => 'Test Schedule',
-                'start_datetime' => "2038-07-01 {$startTimes[$index]}:00",
-                'end_datetime'   => "2038-07-01 {$endTimes[$index]}:00",
+                'start_datetime' => sprintf('2038-07-01 %s:00', $startTimes[$index]),
+                'end_datetime'   => sprintf('2038-07-01 %s:00', $endTimes[$index]),
                 'status' => ScheduleStatus::from($testCase['input']),
                 'metadata' => null,
             ]);
@@ @@
         $schedule = $this->createScheduleModelInstance();

         // Act: Soft delete schedule inside allowed mutation context
-        RosterMutationContext::allow(function () use ($schedule) {
+        RosterMutationContext::allow(function () use ($schedule): void {
             $schedule->delete();
         });

@@ @@
         ]);

         // Restore the schedule inside allowed mutation context
-        RosterMutationContext::allow(function () use ($schedule) {
+        RosterMutationContext::allow(function () use ($schedule): void {
             $schedule->restore();
         });

@@ @@
             // Act: Create schedule with specific status
             $schedule = schedule_for($this->testAvailability)->create([
                 'title' => $testCase['title'],
-                'start_datetime' => "2038-07-01 {$startTimes[$index]}:00",
-                'end_datetime' => "2038-07-01 {$endTimes[$index]}:00",
+                'start_datetime' => sprintf('2038-07-01 %s:00', $startTimes[$index]),
+                'end_datetime' => sprintf('2038-07-01 %s:00', $endTimes[$index]),
                 'status' => $testCase['status'],
                 'metadata' => null,
             ]);
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


11) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php:95

    ---------- begin diff ----------
@@ @@
             ->willReturnCallback(function (string $field, string $message) use (&$violationCount): void {
                 ++$violationCount;
                 if ($violationCount === 1) {
-                    $this->assertEquals('daily_time_range', $field);
-                    $this->assertEquals('End time must be after start time', $message);
+                    $this->assertSame('daily_time_range', $field);
+                    $this->assertSame('End time must be after start time', $message);
                 } else {
-                    $this->assertEquals('min_duration', $field);
-                    $this->assertEquals('Minimum duration must be at least 15 minutes', $message);
+                    $this->assertSame('min_duration', $field);
+                    $this->assertSame('Minimum duration must be at least 15 minutes', $message);
                 }
             });

@@ @@
     /**
      * Create a validation context mock for UPDATE operation.
      *
-     * @param Model|null $existingEntity
      * @return MockObject&ValidationContextInterface
      */
     private function createValidationContextWithUpdateOperation(?Model $existingEntity): MockObject
@@ @@
      * Configure context with all data fields.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param string $validityStart
-     * @param string $validityEnd
-     * @param string $dailyStart
-     * @param string $dailyEnd
      */
     private function configureContextWithData(
         MockObject $context,
@@ @@
      *
      * @param MockObject&ValidationContextInterface $context
      * @param array<int, string> $fieldsToProvide
-     * @param string|null $validityStart
-     * @param string|null $validityEnd
-     * @param string|null $dailyStart
-     * @param string|null $dailyEnd
      */
     private function configureContextWithPartialData(
         MockObject $context,
@@ @@
     /**
      * Create a stub entity with the given date and time values.
      *
-     * @param string $validityStart
-     * @param string $validityEnd
-     * @param string $dailyStart
-     * @param string $dailyEnd
      *
-     * @return Model
      */
     private function createEntityStub(
         string $validityStart,
@@ @@
     ): Model {
         $entity = new class extends Model {
             public $validity_start;
+
             public $validity_end;
+
             public $daily_start;
+
             public $daily_end;

             public function __construct()
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector
 * AssertEqualsToSameRector


12) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php:129

    ---------- begin diff ----------
@@ @@
         $context->expects($this->exactly(2))
             ->method('setViolation')
             ->willReturnCallback(function (string $field, string $message) use (&$violationCount): void {
-                $this->assertEquals('days', $field);
+                $this->assertSame('days', $field);
                 $this->assertStringContainsString('is not within the validity period', $message);
                 ++$violationCount;
             });
@@ @@
         $this->rule->validate($context);

         // Assert: Two violations should be recorded for two out-of-period days
-        $this->assertEquals(2, $violationCount);
+        $this->assertSame(2, $violationCount);
     }

     /**
@@ @@
             ->method('setViolation')
             ->willReturnCallback(function (string $field, string $message) use (&$capturedMessages): void {
                 $capturedMessages[] = $message;
-                $this->assertEquals('days', $field);
+                $this->assertSame('days', $field);
                 $this->assertStringContainsString('is not within the validity period', $message);
             });

@@ @@
     {
         $entity = new class extends Model {
             public $validity_start;
+
             public $validity_end;

             public function __construct()
             {
-                parent::__construct([]);
+                parent::__construct();
             }
         };
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RemoveArgumentFromDefaultParentCallRector
 * AssertEqualsToSameRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityOverlapRuleTest.php:569

    ---------- begin diff ----------
@@ @@
     {
         return new class {
             public $id = 123;
+
             public $daily_start = '09:00:00';
+
             public $daily_end = '17:00:00';
+
             public $days = ['monday', 'tuesday'];
+
             public $validity_start = '2038-01-01';
+
             public $validity_end = '2038-12-31';
+
             public $type = 'consultation';
         };
     }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityOwnershipRuleTest.php:146

    ---------- begin diff ----------
@@ @@
     {
         // Arrange: Create context with null schedulable
         $context = $this->createValidationContext(
-            operationType: OperationType::CREATE,
-            schedulable: null
+            operationType: OperationType::CREATE
         );

         $this->configureContextHasMethod($context, hasAvailabilityId: true);
@@ @@

         $context = $this->createValidationContext(
             operationType: OperationType::UPDATE,
-            schedulable: $schedulable,
-            currentEntity: null
+            schedulable: $schedulable
         );

         $this->configureContextHasMethod($context, hasAvailabilityId: false);
@@ @@

     /**
      * Create a schedulable model mock.
-     *
-     * @param int $id
-     * @return Model
      */
     private function createSchedulableMock(int $id): Model
     {
@@ @@

     /**
      * Create an availability model mock.
-     *
-     * @param int $id
-     * @param int $schedulableId
-     * @param string|null $schedulableClass
-     * @return Availability
      */
     private function createAvailabilityMock(int $id, int $schedulableId, ?string $schedulableClass = null): Availability
     {
@@ @@

     /**
      * Create a stub schedule entity.
-     *
-     * @param int|null $availabilityId
-     * @return object
      */
     private function createScheduleEntityStub(?int $availabilityId): object
     {
         return new class($availabilityId) {
+            /**
+             * @var int|null
+             */
             public $availability_id;

             public function __construct(?int $availabilityId)
@@ @@
     /**
      * Create a validation context mock with given parameters.
      *
-     * @param OperationType $operationType
-     * @param Model|null $schedulable
-     * @param object|null $currentEntity
-     * @param AvailabilityService|null $availabilityService
      * @return MockObject&ValidationContextInterface
      */
     private function createValidationContext(
@@ @@
         $context->method('getSchedulable')->willReturn($schedulable);
         $context->method('getCurrentEntity')->willReturn($currentEntity);

-        if ($availabilityService !== null) {
+        if ($availabilityService instanceof AvailabilityService) {
             $context->method('getAvailabilityService')->willReturn($availabilityService);
         }

@@ @@
      * Configure the has() method on the validation context.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param bool $hasAvailabilityId
      */
     private function configureContextHasMethod(MockObject $context, bool $hasAvailabilityId): void
     {
@@ @@
      * Configure the get() method on the validation context.
      *
      * @param MockObject&ValidationContextInterface $context
-     * @param int|null $availabilityId
      */
     private function configureContextGetMethod(MockObject $context, ?int $availabilityId): void
     {
@@ @@

     /**
      * Configure an availability service mock with find() method expectation.
-     *
-     * @param int $availabilityId
-     * @param Availability|null $returnValue
-     * @return AvailabilityService
      */
     private function configureAvailabilityServiceWithFind(int $availabilityId, ?Availability $returnValue): AvailabilityService
     {
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector
 * RemoveNullArgOnNullDefaultParamRector
 * TypedPropertyFromStrictConstructorRector


15) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityRulesTest.php:21

    ---------- begin diff ----------
@@ @@
 final class AvailabilityRulesTest extends TestCase
 {
     private RequiredFieldsRule $requiredFieldsRule;
+
     private AvailabilityOverlapRule $availabilityOverlapRule;
+
     private TestSchedulable $testSchedulable;

     /**
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


16) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/DateRangeRulesTest.php:24

    ---------- begin diff ----------
@@ @@
 final class DateRangeRulesTest extends TestCase
 {
     private AvailabilityDateRangeRule $availabilityDateRangeRule;
+
     private TimeSlotDateTimeRule $timeSlotDateTimeRule;
+
     private TestSchedulable $testSchedulable;

     /**
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


17) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/DurationRuleTest.php:5

    ---------- begin diff ----------
@@ @@
 namespace Tests\Unit\Validation\Rules;

 use Illuminate\Support\Carbon;
-use PHPUnit\Framework\MockObject\MockObject;
 use Roster\Contracts\Validation\ValidationContextInterface;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
@@ @@
         $this->rule->validate($context);

         // Assert: No violations expected for partial update
-    }
-
-    /**
-     * Create a mock validation context with specific configuration.
-     *
-     * @param EntityType $entityType
-     * @param OperationType $operationType
-     * @param array<string, mixed> $data
-     * @return MockObject&ValidationContextInterface
-     */
-    private function createValidationContext(
-        EntityType $entityType,
-        OperationType $operationType,
-        array $data = []
-    ): MockObject {
-        $context = $this->createMock(ValidationContextInterface::class);
-        $context->method('getEntityType')->willReturn($entityType);
-        $context->method('getOperation')->willReturn($operationType);
-        $context->method('safeData')->willReturn($data);
-
-        return $context;
     }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedPrivateMethodRector


18) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php:27

    ---------- begin diff ----------
@@ @@
     use RefreshDatabase;

     private Model $schedulable;
+
     private ImpedimentScheduleDaysCoherenceRule $rule;

     /**
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


19) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/RequiredFieldsRuleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Validation\Rules;

-use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
 use PHPUnit\Framework\MockObject\MockObject;
 use Roster\Contracts\Validation\ValidationContextInterface;
 use Roster\Enums\EntityType;
@@ @@
         $expectedFields = ['daily_start', 'daily_end', 'days', 'validity_start', 'validity_end'];
         foreach ($expectedFields as $field) {
             $this->assertArrayHasKey($field, $violations);
-            $this->assertSame("Field '{$field}' is required", $violations[$field]);
+            $this->assertSame(sprintf("Field '%s' is required", $field), $violations[$field]);
         }
     }
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector


 [OK] 19 files would have been changed (dry-run) by Rector                                                              

