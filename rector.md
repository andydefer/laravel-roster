# Rector Refactoring Report
*Generated: sam. 03 janv. 2026 05:10:22 WAT*


19 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ImpedimentServiceTest.php:293

    ---------- begin diff ----------
@@ @@
         $found = impediment_for($this->testAvailability)->find($impediment->id);

         // Assert: Verify impediment not found in wrong context
-        $this->assertNull($found);
+        $this->assertNotInstanceOf(Model::class, $found);
     }

     /**
@@ @@
             'end_datetime' => '2038-01-04 10:00:00',
         ]);

-        $impediment2 = impediment_for($this->testAvailability)->create([
+        impediment_for($this->testAvailability)->create([
             'reason' => 'Afternoon meeting',
             'start_datetime' => '2038-01-04 14:00:00',
             'end_datetime' => '2038-01-04 15:00:00',
@@ @@
         $result = impediment_for($this->testAvailability)->first();

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(Model::class, $result);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleServiceTest.php:505

    ---------- begin diff ----------
@@ @@
         $found = schedule_for($availability1)->find($scheduleForSchedulable2->id);

         // Assert: Should return null for cross-schedulable access
-        $this->assertNull($found);
+        $this->assertNotInstanceOf(Model::class, $found);
     }

     /**
@@ @@
             'end_datetime' => '2038-01-04 11:00:00',
         ]);

-        $schedule2 = schedule_for($availability)->create([
+        schedule_for($availability)->create([
             'title' => 'Second meeting',
             'start_datetime' => '2038-01-04 12:00:00',
             'end_datetime' => '2038-01-04 13:00:00',
@@ @@
         $first = schedule_for($availability)->first();

         // Assert: Should return null
-        $this->assertNull($first);
+        $this->assertNotInstanceOf(Model::class, $first);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php:530

    ---------- begin diff ----------
@@ @@
             public $daily_start;

             public $daily_end;
-
-            public function __construct()
-            {
-                parent::__construct();
-            }
         };

         $entity->validity_start = Carbon::parse($validityStart);
    ----------- end diff -----------

Applied rules:
 * RemoveParentDelegatingConstructorRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php:557

    ---------- begin diff ----------
@@ @@
             public $validity_start;

             public $validity_end;
-
-            public function __construct()
-            {
-                parent::__construct();
-            }
         };

         if ($validityStart !== null) {
    ----------- end diff -----------

Applied rules:
 * RemoveParentDelegatingConstructorRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/FutureDateRuleTest.php:667

    ---------- begin diff ----------
@@ @@
      * @param OperationType $operationType The operation type
      * @param bool $hasStartDatetime Whether start_datetime field is present
      * @param string|null $startDatetime The start datetime value
-     *
-     * @return MockObject&ValidationContextInterface
      */
     private function createScheduleImpedimentContext(
         EntityType $entityType,
@@ @@
      * @param string|null $validityStart The validity start date value
      * @param bool $hasDailyStart Whether daily_start field is present
      * @param string|null $dailyStart The daily start time value
-     *
-     * @return MockObject&ValidationContextInterface
      */
     private function createAvailabilityContext(
         OperationType $operationType,
@@ @@
      *
      * @param bool $shouldValidateFutureDates Whether future date validation should be enabled
      * @param bool $allowPastDates Whether past dates are allowed
-     *
-     * @return MockObject&FutureDateRule
      */
     private function createMockWithConfigMethods(
         bool $shouldValidateFutureDates,
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidationContextTest.php:966

    ---------- begin diff ----------
@@ @@

         $this->assertContainsOnlyInstancesOf(ViolationData::class, $violations);

-        $this->assertEquals('field1', $violations[0]->getField());
-        $this->assertEquals('field2', $violations[1]->getField());
+        $this->assertSame('field1', $violations[0]->getField());
+        $this->assertSame('field2', $violations[1]->getField());
     }

     /**
    ----------- end diff -----------

Applied rules:
 * AssertEqualsToSameRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/DebugRulesCommand.php:814

    ---------- begin diff ----------
@@ @@
     /**
      * Sort scanned rules by priority in descending order.
      *
-     * @param array $scannedRules Scanned rules to sort
+     * @param array<string, ValidationRule> $scannedRules Scanned rules to sort
      * @return array Sorted rules
      */
     private function sortScannedRulesByPriority(array $scannedRules): array
    ----------- end diff -----------

Applied rules:
 * ClassMethodArrayDocblockParamFromLocalCallsRector


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/ServiceInterface.php:82

    ---------- begin diff ----------
@@ @@
     ): LengthAwarePaginator;

     /* ========= Data & Filter Management ========= */
-
     /**
      * Replace all filters.
      *
      * @param array<string, mixed> $filters New filters
-     * @return static
      */
     public function setFilters(array $filters): static;

     /**
      * Clear all filters.
-     *
-     * @return static
      */
     public function resetFilters(): static;

@@ @@
      *
      * @param string $key Filter key
      * @param mixed $value Filter value
-     * @return static
      */
     public function setFilter(string $key, mixed $value): static;

@@ @@

     /**
      * Clear all contextual data (filters, data, schedulable).
-     *
-     * @return static
      */
     public function clear(): static;
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php:235

    ---------- begin diff ----------
@@ @@
     public static function getDaysOfWeek(): array
     {
         $daysOfWeek = [];
-        for ($i = 0; $i < 7; $i++) {
+        for ($i = 0; $i < 7; ++$i) {
             $daysOfWeek[] = strtolower(Carbon::now()->startOfWeek()->addDays($i)->format('l'));
         }
    ----------- end diff -----------

Applied rules:
 * PostIncDecToPreIncDecRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:269

    ---------- begin diff ----------
@@ @@
     }

     /* ========= Filter Management ========= */
-
     /**
      * Sets a filter value.
      *
      * @param string $key Filter key
      * @param mixed $value Filter value
-     * @return static
      */
     public function setFilter(string $key, mixed $value): static
     {
@@ @@

     /**
      * Clears all active filters.
-     *
-     * @return static
      */
     public function clearFilters(): static
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php:6

    ---------- begin diff ----------
@@ @@

 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
-use Roster\Domain\Helpers\TimeSlotHelper;
-use Roster\DTOs\AvailabilityData;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Models\Availability;
@@ @@
 {
     /**
      * Entity awaiting deletion (for cascade operations).
-     *
-     * @var Availability|null
      */
     protected ?Availability $pendingDeletion = null;

@@ @@
      * Triggers a warning if invalid days were detected and warnings are enabled.
      *
      * @param array<int, string> $invalidDays Days outside the validity period
-     * @return void
      */
     private function triggerInvalidDaysWarningIfNeeded(array $invalidDays): void
     {
-        if (!empty($invalidDays) && config('roster.reconciliation_warning')) {
+        if ($invalidDays !== [] && config('roster.reconciliation_warning')) {
             trigger_error(
                 sprintf(
                     'The following days were outside the validity period and have been removed: %s',
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * RemoveUselessReturnTagRector
 * RemoveUselessVarTagRector


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:125

    ---------- begin diff ----------
@@ @@
         $this->requireContext();
         $existingEntity = $this->find($id);

-        if ($existingEntity === null) {
+        if (!$existingEntity instanceof Model) {
             throw $this->createEntityNotFoundValidationException(OperationType::UPDATE);
         }

@@ @@
         $this->requireContext();
         $entity = $this->find($id);

-        if ($entity === null) {
+        if (!$entity instanceof Model) {
             throw $this->createEntityNotFoundValidationException(OperationType::DELETE);
         }

@@ @@
     }

     /* ========= Data & Filter Management ========= */
-
     /**
      * Replace all filters.
      *
      * @param array<string, mixed> $filters New filters
-     * @return static
      */
     public function setFilters(array $filters): static
     {
@@ @@

     /**
      * Clear all filters.
-     *
-     * @return static
      */
     public function resetFilters(): static
     {
@@ @@
      *
      * @param string $key Filter key
      * @param mixed $value Filter value
-     * @return static
      */
     public function setFilter(string $key, mixed $value): static
     {
@@ @@

     /**
      * Clear all contextual data (filters, data, schedulable).
-     *
-     * @return static
      */
     public function clear(): static
     {
@@ @@
     /**
      * Gets the current repository based on service type.
      *
-     * @return AvailabilityRepositoryInterface|ScheduleRepositoryInterface|ImpedimentRepositoryInterface
      * @throws LogicException If service type not recognized
      */
     protected function getCurrentRepository(): AvailabilityRepositoryInterface|ScheduleRepositoryInterface|ImpedimentRepositoryInterface
@@ @@
      * Prepares data for entity update.
      *
      * @param array<string, mixed> $data
-     * @param Model $existingEntity
      * @return array<string, mixed>
      */
     private function prepareUpdateData(array $data, Model $existingEntity): array
@@ @@
     /**
      * Prepares data for entity deletion.
      *
-     * @param int $id
-     * @param Model $entity
      * @return array<string, mixed>
      */
     private function prepareDeleteData(int $id, Model $entity): array
@@ @@

     /**
      * Creates a validation exception for non-existent entity.
-     *
-     * @param OperationType $operationType
-     * @return ValidationFailedException
      */
     private function createEntityNotFoundValidationException(OperationType $operationType): ValidationFailedException
     {
@@ @@
      * Sets operation data.
      *
      * @param array<string, mixed> $data Data to set
-     * @return static
      */
     public function setData(array $data): static
     {
@@ @@
      * Sets the schedulable entity.
      *
      * @param Model $model Schedulable entity
-     * @return static
      */
     public function setSchedulable(Model $model): static
     {
@@ @@
     }

     /* ========= Magic Methods ========= */
-
     /**
      * Intercepts dynamic whereXyz method calls.
      *
      * @param string $method Method name
      * @param array<int, mixed> $arguments Method arguments
-     * @return static
      * @throws BadMethodCallException If method not supported
      */
     public function __call(string $method, array $arguments): static
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysInPeriodRule.php:19

    ---------- begin diff ----------
@@ @@
 {
     /**
      * Validates that provided days are within the new validity period.
-     *
-     * @param ValidationContextInterface $validationContext
      */
     public function validate(ValidationContextInterface $validationContext): void
     {
@@ @@

     /**
      * Sets a validation violation for invalid days.
+     * @param string[] $periodDays
      */
     private function setDaysViolation(
         ValidationContextInterface $validationContext,
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTemporalCoherenceRule.php:144

    ---------- begin diff ----------
@@ @@
     /**
      * Check if update contains changes that require validation.
      *
-     * @param array $updateData Normalized update data
+     * @param array<string, mixed[]|string|null> $updateData Normalized update data
      * @return bool True if validation is needed
      */
     private function hasRelevantChanges(array $updateData): bool
     {
-        return array_filter($updateData, fn($value): bool => $value !== null) !== [];
+        return array_filter($updateData, fn(array|string|null $value): bool => $value !== null) !== [];
     }

     /**
    ----------- end diff -----------

Applied rules:
 * ClassMethodArrayDocblockParamFromLocalCallsRector
 * AddArrayFunctionClosureParamTypeRector


15) /home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php:43

    ---------- begin diff ----------
@@ @@
     /**
      * Returns all days occurring within a date period, in standard week order.
      *
-     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $startDate
-     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $endDate
      * @return array<string> Unique lowercase day names within the period, sorted Monday → Sunday
      */
     function roster_days_in_period(
@@ @@

             $weekOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

-            usort($days, function ($a, $b) use ($weekOrder) {
-                return array_search($a, $weekOrder) <=> array_search($b, $weekOrder);
+            usort($days, function ($a, $b) use ($weekOrder): int {
+                return array_search($a, $weekOrder, true) <=> array_search($b, $weekOrder, true);
             });

             return $days;
    ----------- end diff -----------

Applied rules:
 * StrictArraySearchRector
 * RemoveUselessParamTagRector
 * ClosureReturnTypeRector


16) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php:237

    ---------- begin diff ----------
@@ @@
         ]);

         $wasWarned = false;
-        set_error_handler(function ($errno, $errstr) use (&$wasWarned) {
+        set_error_handler(function ($errno, $errstr) use (&$wasWarned): true {
             if ($errno === E_USER_WARNING && str_contains($errstr, 'outside the validity period')) {
                 $wasWarned = true;
             }
+
             return true; // continue execution
         });
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector
 * ClosureReturnTypeRector


17) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/Helpers/TimezoneHelperTest.php:14

    ---------- begin diff ----------
@@ @@
 /**
  * Test suite for TimezoneHelper functionality.
  */
-#[CoversClass(\Roster\Domain\Helpers\TimezoneHelper::class)]
+#[CoversClass(TimezoneHelper::class)]
 final class TimezoneHelperTest extends TestCase
 {
     /**
    ----------- end diff -----------

Applied rules:


18) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/RepositoryMutationTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Domain;

+use Illuminate\Support\Carbon;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Roster\Exceptions\ForbiddenModelMutationException;
@@ @@
         // Verify record exists in trash
         $trashed = ScheduleModel::withTrashed()->find($schedule->id);
         $this->assertNotNull($trashed);
-        $this->assertNotNull($trashed->deleted_at);
+        $this->assertInstanceOf(Carbon::class, $trashed->deleted_at);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * AssertEmptyNullableObjectToAssertInstanceofRector


19) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/AvailabilityServiceTest.php:194

    ---------- begin diff ----------
@@ @@
         $result = availability_for($this->schedulable)->find($availabilityId);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(Model::class, $result);
     }

     /**
@@ @@
             days: ['monday']
         );

-        $availability2 = $this->createTestAvailability(
+        $this->createTestAvailability(
             type: 'training',
             dailyStart: '14:00:00',
             dailyEnd: '17:00:00',
@@ @@
         $result = availability_for($this->schedulable)->first();

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(Model::class, $result);
     }

     /**
@@ @@
      * @param string $validityStart The validity start date
      * @param string $validityEnd The validity end date
      *
-     * @return array The availability data array
+     * @return array<string, string|mixed[]> The availability data array
      */
     private function createValidAvailabilityData(
         string $type = 'consultation',
@@ @@
      * @param string $type The availability type
      * @param string $dailyStart The daily start time
      * @param string $dailyEnd The daily end time
-     * @param array $days The days of week
+     * @param string[] $days The days of week
      * @param string $validityStart The validity start date
      * @param string $validityEnd The validity end date
      *
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * DocblockReturnArrayFromDirectArrayInstanceRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


 [OK] 19 files would have been changed (dry-run) by Rector                                                              

