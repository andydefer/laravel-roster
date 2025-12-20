# Rector Refactoring Report
*Generated: sam. 20 déc. 2025 15:22:56 WAT*


3 files with changes
====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:105

    ---------- begin diff ----------
@@ @@

         $availability = $this->availabilityRepository->findById($availabilityId);

-        if (!$availability) {
+        if (!$availability instanceof Availability) {
             throw new ValidationException(ValidationType::INVALID_AVAILABILITY);
         }
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * NullableCompareToNullRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Facades/AvailabilityFacadeTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Feature\Facades;

+use PHPUnit\Framework\Attributes\CoversClass;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@

 /**
  * Tests for the Availability facade functionality.
- *
- * @covers \Roster\Facades\Availability
  */
+#[CoversClass(\Roster\Facades\Availability::class)]
 final class AvailabilityFacadeTest extends TestCase
 {
     /**
@@ @@

         $this->schedulableModel = new class extends Model {
             protected $table = 'test_schedulables';
+
             public $timestamps = false;
         };

@@ @@
      */
     public function test_facade_can_reset_filters(): void
     {
-        $service = AvailabilityFacade::for($this->schedulableModel)
+        $availabilityService = AvailabilityFacade::for($this->schedulableModel)
             ->whereType('consultation')
             ->resetFilters();

-        $this->assertInstanceOf(AvailabilityService::class, $service);
+        $this->assertInstanceOf(AvailabilityService::class, $availabilityService);
     }

     /**
@@ @@
      */
     public function test_facade_can_get_schedulable(): void
     {
-        $service = AvailabilityFacade::for($this->schedulableModel);
-        $schedulable = $service->getSchedulable();
+        $availabilityService = AvailabilityFacade::for($this->schedulableModel);
+        $schedulable = $availabilityService->getSchedulable();

         $this->assertInstanceOf(Model::class, $schedulable);
         $this->assertSame($this->schedulableModel->id, $schedulable->id);
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RenameVariableToMatchMethodCallReturnTypeRector
 * CoversAnnotationWithValueToAttributeRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Facades/ImpedimentFacadeTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Feature\Facades;

+use PHPUnit\Framework\Attributes\CoversClass;
+use Roster\Services\ImpedimentService;
 use BadMethodCallException;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
@@ @@

 /**
  * Tests for the Impediment facade functionality.
- *
- * @covers \Roster\Facades\Impediment
  */
+#[CoversClass(\Roster\Facades\Impediment::class)]
 final class ImpedimentFacadeTest extends TestCase
 {
     use RefreshDatabase;
@@ @@

         $this->schedulableModel = new class extends Model {
             protected $table = 'test_schedulables';
+
             public $timestamps = false;
         };

@@ @@
             ]
         );
     }
+
     /**
      * Test creating a new impediment through the facade.
      */
@@ @@
         // SOLUTION RADICALE : Utiliser un schedulable UNIQUE pour ce test
         $uniqueSchedulable = new class extends Model {
             protected $table = 'test_schedulables';
+
             public $timestamps = false;
         };
         $uniqueSchedulable->id = 999; // ID unique
@@ @@
         $this->assertTrue($deletionResult);

         $finalFind = ImpedimentFacade::for($this->schedulableModel)->find($impediment->id);
-        $this->assertNull($finalFind);
+        $this->assertNotInstanceOf(\Roster\Models\Impediment::class, $finalFind);
     }

     /**
@@ @@
         $this->assertSame('Training impediment', $impediments->first()->reason);
         $this->assertSame($anotherAvailability->id, $impediments->first()->availability_id);
     }
+
     /**
      * Test resetting filters.
      */
     public function test_facade_can_reset_filters(): void
     {
-        $service = ImpedimentFacade::for($this->schedulableModel)
+        $impedimentService = ImpedimentFacade::for($this->schedulableModel)
             ->whereReason('test')
             ->resetFilters();

-        $this->assertInstanceOf(\Roster\Services\ImpedimentService::class, $service);
+        $this->assertInstanceOf(ImpedimentService::class, $impedimentService);
     }

     /**
@@ @@
      */
     public function test_facade_can_get_schedulable(): void
     {
-        $service = ImpedimentFacade::for($this->schedulableModel);
-        $schedulable = $service->getSchedulable();
+        $impedimentService = ImpedimentFacade::for($this->schedulableModel);
+        $schedulable = $impedimentService->getSchedulable();

         $this->assertInstanceOf(Model::class, $schedulable);
         $this->assertSame($this->schedulableModel->id, $schedulable->id);
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RenameVariableToMatchMethodCallReturnTypeRector
 * CoversAnnotationWithValueToAttributeRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


 [OK] 3 files would have been changed (dry-run) by Rector                                                               

