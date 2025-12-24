# Rector Refactoring Report
*Generated: mer. 24 déc. 2025 05:29:33 WAT*


15 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php:18

    ---------- begin diff ----------
@@ @@

     protected $description = 'Manage Roster validation rules cache';

-    public function handle(RuleScanner $scanner): int
+    public function handle(RuleScanner $ruleScanner): int
     {
-        $generator = new RuleCacheGenerator($scanner);
+        $ruleCacheGenerator = new RuleCacheGenerator($ruleScanner);

         if ($this->option('clear')) {
-            return $this->clearCache($generator);
+            return $this->clearCache($ruleCacheGenerator);
         }

         if ($this->option('show')) {
-            return $this->showCache($generator);
+            return $this->showCache($ruleCacheGenerator);
         }

-        return $this->generateCache($generator);
+        return $this->generateCache($ruleCacheGenerator);
     }

     private function generateCache(RuleCacheGenerator $generator): int
@@ @@
         if ($generator->generate()) {
             $duration = round((microtime(true) - $start) * 1000, 2);
             $this->info("✅ Cache generated successfully at: " . $generator->getCachePath());
-            $this->info("⏱️  Duration: {$duration}ms");
+            $this->info(sprintf('⏱️  Duration: %sms', $duration));

             // Afficher des stats
             $this->showCacheStats($generator);
@@ @@

         while ($bytes >= 1024 && $i < count($units) - 1) {
             $bytes /= 1024;
-            $i++;
+            ++$i;
         }

         return round($bytes, 2) . ' ' . $units[$i];
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * PostIncDecToPreIncDecRector
 * RenameParamToMatchTypeRector
 * RenameVariableToMatchNewTypeRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\DTOs;

+use ValueError;
 use Illuminate\Support\Carbon;
 use Roster\Enums\ScheduleStatus;
 use Roster\Models\Schedule;
@@ @@
         if (is_string($status)) {
             try {
                 $status = ScheduleStatus::from($status);
-            } catch (\ValueError $e) {
+            } catch (ValueError $e) {
                 // Si la valeur n'est pas valide, utiliser la valeur par défaut
                 $status = ScheduleStatus::AVAILABLE;
             }
@@ @@
             'status' => $status,
             'schedulable_id' => $this->schedulableId,
             'schedulable_type' => $this->schedulableType,
-        ], static fn($value) => $value !== null);
+        ], static fn(int|string|array|null $value): bool => $value !== null);
     }

     public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
    ----------- end diff -----------

Applied rules:
 * AddArrowFunctionReturnTypeRector
 * AddArrayFunctionClosureParamTypeRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Support/TestSchedulable.php:10

    ---------- begin diff ----------
@@ @@
 class TestSchedulable extends Model
 {
     use HasRoster;
+
     protected $table = 'test_schedulables';

     public $timestamps = false;
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php:39

    ---------- begin diff ----------
@@ @@

             $currentEntity = $validationContext->getCurrentEntity();

-
-            if ($currentEntity) {
-            }
-
             $excludeId = $currentEntity ? ($currentEntity->id ?? null) : null;


@@ @@
             // Vérifiez d'abord SANS exclusion pour voir ce qui existe
             $allOverlapping = $scheduleRepository->findOverlappingSchedules($availabilityId, $start, $end);
             if ($allOverlapping->count() > 0) {
-                foreach ($allOverlapping as $schedule) {
-                }
             }

             // Puis vérifiez AVEC exclusion
@@ @@
             if ($excludeId) {
                 $overlappingExcludingSelf = $scheduleRepository->findOverlappingSchedules($availabilityId, $start, $end, $excludeId);
                 if ($overlappingExcludingSelf->count() > 0) {
-                    foreach ($overlappingExcludingSelf as $schedule) {
-                    }
                 }
             }
    ----------- end diff -----------

Applied rules:
 * RemoveDeadIfForeachForRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php:7

    ---------- begin diff ----------
@@ @@
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use Illuminate\Support\Facades\Log;
 use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
 use Roster\Models\Impediment;
    ----------- end diff -----------

Applied rules:


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php:7

    ---------- begin diff ----------
@@ @@
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use Illuminate\Support\Facades\Log;
 use Roster\Contracts\Repository\ScheduleRepositoryInterface;
 use Roster\Models\Schedule;

@@ @@
         if ($excludeId) {
             $query->where('id', '!=', $excludeId);
         }
-
         // Log pour déboguer
-        $sql = $query->toSql();
-        $bindings = $query->getBindings();
+        $query->toSql();
+        $query->getBindings();



-        $result = $query->exists();
-
-
-
-        return $result;
+        return $query->exists();
     }

     /**
    ----------- end diff -----------

Applied rules:
 * SimplifyUselessVariableRector
 * RemoveUnusedVariableAssignRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster;

+use Roster\Commands\CacheRulesCommand;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Filesystem\Filesystem;
 use Illuminate\Support\ServiceProvider;
@@ @@

         if ($this->app->runningInConsole()) {
             $this->commands([
-                \Roster\Commands\CacheRulesCommand::class,
+                CacheRulesCommand::class,
             ]);
         }
     }
    ----------- end diff -----------

Applied rules:


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:120

    ---------- begin diff ----------
@@ @@
     public function update(int $id, array $data): bool
     {

-        // Supprime les clés spécifiées si elles existent
-        $data = array_diff_key($data, array_flip(['schedulable_id', 'schedulable_type', 'availability_id']));
         return true;
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:168

    ---------- begin diff ----------
@@ @@
                 EntityType::IMPEDIMENT
             );
         }
+
         // Préparer les données de validation avec les infos schedulable
         $deleteData = [
             'id' => $id,
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

-use Roster\Models\Impediment;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
         $slotStart = $availabilityStart->copy();

         // Si un searchStart est spécifié et qu'il est dans la même journée
-        if ($searchStart !== null && $searchStart->isSameDay($day)) {
+        if ($searchStart instanceof Carbon && $searchStart->isSameDay($day)) {
             // Si searchStart est avant availabilityStart, commencer à availabilityStart
             if ($searchStart->lt($availabilityStart)) {
                 $slotStart = $availabilityStart->copy();
@@ @@
         // Trier les impediments par start_datetime
         $sortedImpediments = $impediments->sortBy('start_datetime');

-        /** @var Impediment $impediment */
-        foreach ($sortedImpediments as $impediment) {
-            $impStart = $impediment->start_datetime;
-            $impEnd = $impediment->end_datetime;
+        foreach ($sortedImpediments as $sortedImpediment) {
+            $impStart = $sortedImpediment->start_datetime;
+            $impEnd = $sortedImpediment->end_datetime;

             // S'il y a un espace avant l'impediment
             if ($impStart->gt($currentTime)) {
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector
 * RemoveNonExistingVarAnnotationRector
 * RenameForeachValueVariableToMatchExprVariableRector


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Cache/RuleCacheGenerator.php:5

    ---------- begin diff ----------
@@ @@

 namespace Roster\Validation\Cache;

+use Carbon\Carbon;
+use Roster\Enums\EntityType;
+use Roster\Enums\OperationType;
 use Roster\Validation\RuleScanner;
 use Roster\Validation\Attributes\ValidationRule;
-use ReflectionClass;

 class RuleCacheGenerator
 {
@@ @@

         // Vérifier si le fichier a été généré il y a moins de X heures
         $maxAge = config('roster-validation.cache_max_age_hours', 24);
-        return (time() - filemtime($this->cachePath)) < ($maxAge * 3600);
+        return (Carbon::now()
+            ->getTimestamp() - filemtime($this->cachePath)) < ($maxAge * 3600);
     }

     public function clear(): bool
@@ @@

     private function buildCacheFile(array $rules): string
     {
-        $timestamp = date('Y-m-d H:i:s');
+        $timestamp = Carbon::now()
+            ->format('Y-m-d H:i:s');
         $rulesCount = count($rules);

         $content = <<<PHP
@@ @@
             $content .= $this->buildRuleEntry($className, $attribute);
         }

-        $content .= "];\n";
-
-        return $content;
+        return $content . "];\n";
     }

-    private function buildRuleEntry(string $className, ValidationRule $attribute): string
+    private function buildRuleEntry(string $className, ValidationRule $validationRule): string
     {
-        $entities = array_map(fn($e) => $e->value, $attribute->entities);
-        $operations = array_map(fn($o) => $o->value, $attribute->operations);
+        $entities = array_map(fn(EntityType $entityType) => $entityType->value, $validationRule->entities);
+        $operations = array_map(fn(OperationType $operationType) => $operationType->value, $validationRule->operations);

         $indent = '    ';
         $entry = $indent . "'" . addslashes($className) . "' => [\n";
-        $entry .= $indent . $indent . "'priority' => " . $attribute->priority . ",\n";
-        $entry .= $indent . $indent . "'entities' => [" . implode(', ', array_map(fn($e) => "'$e'", $entities)) . "],\n";
-        $entry .= $indent . $indent . "'operations' => [" . implode(', ', array_map(fn($o) => "'$o'", $operations)) . "],\n";
+        $entry .= $indent . $indent . "'priority' => " . $validationRule->priority . ",\n";
+        $entry .= $indent . $indent . "'entities' => [" . implode(', ', array_map(fn(string $e): string => sprintf("'%s'", $e), $entities)) . "],\n";
+        $entry .= $indent . $indent . "'operations' => [" . implode(', ', array_map(fn(string $o): string => sprintf("'%s'", $o), $operations)) . "],\n";
         $entry .= $indent . $indent . "'class' => '" . addslashes($className) . "',\n";
-        $entry .= $indent . "],\n";

-        return $entry;
+        return $entry . ($indent . "],\n");
     }
 }
    ----------- end diff -----------

Applied rules:
 * DateFuncCallToCarbonRector
 * TimeFuncCallToCarbonRector
 * SimplifyUselessVariableRector
 * EncapsedStringsToSprintfRector
 * RenameParamToMatchTypeRector
 * AddArrowFunctionReturnTypeRector
 * AddParamStringTypeFromSprintfUseRector
 * AddArrayFunctionClosureParamTypeRector


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Validation;

+use RuntimeException;
+use Roster\Enums\EntityType;
+use Roster\Enums\OperationType;
 use Illuminate\Support\Facades\Log;
 use ReflectionClass;
 use Throwable;
@@ @@
      */
     private array $ruleDirectories;

-    private bool $withCache;
-
     private ?array $cachedRules = null;

     private bool $useCacheFile;
+
     private ?string $cacheFile;

     public function __construct(
@@ @@

         return $this->doScan();
     }
+
     private function shouldUseCache(): bool
     {
         if (!$this->cacheFile || !file_exists($this->cacheFile)) {
@@ @@
         }

         // En développement, vérifier si le cache est frais
-        $cacheGenerator = new RuleCacheGenerator($this);
-        return $cacheGenerator->isCacheFresh();
+        $ruleCacheGenerator = new RuleCacheGenerator($this);
+        return $ruleCacheGenerator->isCacheFresh();
     }

     private function loadFromCache(): array
@@ @@

             // Valider la structure du cache
             if (!is_array($rules)) {
-                throw new \RuntimeException('Invalid cache file structure');
+                throw new RuntimeException('Invalid cache file structure');
             }

             // Convertir les données en objets ValidationRule
             $result = [];
             foreach ($rules as $className => $data) {
-                $result[$className] = new Attributes\ValidationRule(
+                $result[$className] = new ValidationRule(
                     priority: $data['priority'],
                     entities: array_map(
-                        fn($e) => \Roster\Enums\EntityType::from($e),
+                        fn($e) => EntityType::from($e),
                         $data['entities']
                     ),
                     operations: array_map(
-                        fn($o) => \Roster\Enums\OperationType::from($o),
+                        fn($o) => OperationType::from($o),
                         $data['operations']
                     )
                 );
@@ @@
             }

             return $result;
-        } catch (\Throwable $e) {
+        } catch (Throwable $throwable) {
             // Si le cache est corrompu, régénérer
             Log::warning('Roster rule cache corrupted, regenerating', [
                 'file' => $this->cacheFile,
-                'error' => $e->getMessage()
+                'error' => $throwable->getMessage()
             ]);

             return $this->regenerateCache();
@@ @@
     private function regenerateCache(): array
     {
         $rules = $this->doScan();
-        $cacheGenerator = new RuleCacheGenerator($this);
-        $cacheGenerator->generate();
+        $ruleCacheGenerator = new RuleCacheGenerator($this);
+        $ruleCacheGenerator->generate();

         return $rules;
     }
    ----------- end diff -----------

Applied rules:
 * CatchExceptionNameMatchingTypeRector
 * NewlineBetweenClassLikeStmtsRector
 * RemoveUnusedPrivatePropertyRector
 * RenameVariableToMatchNewTypeRector
 * DocblockVarFromParamDocblockInConstructorRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/ScheduleTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Unit\Models;

-use Illuminate\Support\Carbon;
-use Rector\Carbon\NodeFactory\CarbonCallFactory;
-use Roster\Models\Availability;
-use Tests\Support\TestSchedulable;
 use Tests\TestCase;

 final class ScheduleTest extends TestCase
@@ @@
     {
         parent::setUp();
     }
+
     /**
      * Test basic data validation with valid input.
      */
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ImpedimentServiceTest.php:188

    ---------- begin diff ----------
@@ @@
         $this->assertEquals(Carbon::parse('2038-01-04 13:00:00'), $impediment->start_datetime);
         $this->assertEquals(Carbon::parse('2038-01-04 15:00:00'), $impediment->end_datetime);
     }
+
     public function test_update_impediment_throws_exception_when_not_found(): void
     {
         // Assert
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


15) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleServiceTest.php:7

    ---------- begin diff ----------
@@ @@
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\Config;
-use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
-use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
-use Roster\Contracts\Repository\ScheduleRepositoryInterface;
-use Roster\Contracts\Validation\ValidatorInterface;
-use Roster\DTOs\ScheduleData;
-use Roster\Enums\EntityType;
-use Roster\Enums\OperationType;
 use Roster\Enums\ScheduleStatus;
 use Roster\Models\Availability;
 use Roster\Models\Impediment;
@@ @@
 final class ScheduleServiceTest extends TestCase
 {
     private ScheduleService $scheduleService;
+
     private TestSchedulable $testSchedulable;
+
     private Availability $testAvailability;
+
     private array $baseScheduleData;

     protected function setUp(): void
@@ @@
         ]);


-        $schedule2 = $this->scheduleService->create($this->testAvailability, [
+        $this->scheduleService->create($this->testAvailability, [
             'title' => 'Schedule 2',
             'start_datetime' => '2038-01-04 12:00:00',
             'end_datetime' => '2038-01-04 13:00:00',
@@ @@
         // Assert
         $this->assertNotNull($startOnly);
         $this->assertInstanceOf(Carbon::class, $startOnly);
-        $this->assertEquals('2038-01-04 09:00:00', $startOnly->format('Y-m-d H:i:s'));
+        $this->assertSame('2038-01-04 09:00:00', $startOnly->format('Y-m-d H:i:s'));
     }

     public function test_find_next_slot_respects_availability_hours(): void
@@ @@
     public function test_find_next_slot_returns_null_when_no_availability(): void
     {
         // Arrange - Créer une disponibilité uniquement pour lundi
-        $limitedAvailability = Availability::create([
+        Availability::create([
             'schedulable_id' => $this->testSchedulable->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'limited',
@@ @@
                 $this->assertTrue(
                     $slotEnd->lte(Carbon::parse('2038-01-04 10:00:00')) ||
                         $slotStart->gte(Carbon::parse('2038-01-04 12:00:00')),
-                    "Slot {$slotStart->format('H:i')}-{$slotEnd->format('H:i')} should not overlap 10:00-12:00"
+                    sprintf('Slot %s-%s should not overlap 10:00-12:00', $slotStart->format('H:i'), $slotEnd->format('H:i'))
                 );
             }
         }
@@ @@
     public function test_concurrent_schedule_creation_prevents_double_booking(): void
     {
         // Arrange - Créer un schedule
-        $schedule1 = $this->scheduleService->create($this->testAvailability, [
+        $this->scheduleService->create($this->testAvailability, [
             'title' => 'Premier',
             'start_datetime' => '2038-01-04 10:00:00',
             'end_datetime' => '2038-01-04 11:00:00',
@@ @@
     public function test_schedule_exact_boundary_not_overlap(): void
     {
         // Arrange - Créer un schedule
-        $schedule1 = $this->scheduleService->create($this->testAvailability, [
+        $this->scheduleService->create($this->testAvailability, [
             'title' => 'Premier',
             'start_datetime' => '2038-01-04 10:00:00',
             'end_datetime' => '2038-01-04 11:00:00',
@@ @@
         ]);

         // 2. Créer un autre schedule
-        $otherSchedule = $this->scheduleService->create($this->testAvailability, [
+        $this->scheduleService->create($this->testAvailability, [
             'title' => 'Autre',
             'start_datetime' => '2038-01-04 14:00:00',
             'end_datetime' => '2038-01-04 15:00:00',
@@ @@
         ]);

         // 1. Créer un schedule le matin (première disponibilité)
-        $morningSchedule = $this->scheduleService->create($this->testAvailability, [
+        $this->scheduleService->create($this->testAvailability, [
             'title' => 'Matin',
             'start_datetime' => '2038-01-04 10:00:00',
             'end_datetime' => '2038-01-04 11:00:00',
@@ @@
         ]);

         // 2. Créer un schedule l'après-midi (deuxième disponibilité)
-        $afternoonSchedule = $this->scheduleService->create($afternoonAvailability, [
+        $this->scheduleService->create($afternoonAvailability, [
             'title' => 'Après-midi',
             'start_datetime' => '2038-01-04 14:00:00',
             'end_datetime' => '2038-01-04 15:00:00',
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * EncapsedStringsToSprintfRector
 * RemoveUnusedVariableAssignRector
 * AssertEqualsToSameRector


 [OK] 15 files would have been changed (dry-run) by Rector                                                              

