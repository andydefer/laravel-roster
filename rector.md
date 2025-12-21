# Rector Refactoring Report
*Generated: dim. 21 déc. 2025 17:41:25 WAT*


3 files with changes
====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

-use BadMethodCallException;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use InvalidArgumentException;
 use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
 use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
 use Roster\Contracts\Repository\ScheduleRepositoryInterface;
    ----------- end diff -----------

Applied rules:


2) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Facades/ImpedimentFacadeTest.php:6

    ---------- begin diff ----------
@@ @@

 use PHPUnit\Framework\Attributes\CoversClass;
 use Roster\Services\ImpedimentService;
-use BadMethodCallException;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Carbon;
    ----------- end diff -----------

Applied rules:


3) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Services/ImpedimentServiceTest.php:447

    ---------- begin diff ----------
@@ @@

         try {
             $this->impedimentService->create($impedimentData);
-        } catch (BadMethodCallException $e) {
+        } catch (BadMethodCallException $badMethodCallException) {
             $this->assertStringContainsString(
                 'Method create(array $data) is deprecated. Use create(Availability $availability, array $data) instead',
-                $e->getMessage()
+                $badMethodCallException->getMessage()
             );

-            throw $e;
+            throw $badMethodCallException;
         }
     }
 }
    ----------- end diff -----------

Applied rules:
 * CatchExceptionNameMatchingTypeRector


 [OK] 3 files would have been changed (dry-run) by Rector                                                               

