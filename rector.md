# Rector Refactoring Report
*Generated: mer. 31 déc. 2025 19:25:54 WAT*


7 files with changes
====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/DebugRulesCommand.php:814

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


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTemporalCoherenceRule.php:144

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


3) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/Helpers/TimezoneHelperTest.php:14

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


4) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/RepositoryMutationTest.php:4

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


5) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php:530

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


6) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php:557

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


7) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidationContextTest.php:966

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


 [OK] 7 files would have been changed (dry-run) by Rector                                                               

