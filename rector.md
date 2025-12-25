# Rector Refactoring Report
*Generated: jeu. 25 déc. 2025 17:52:40 WAT*


18 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Contracts\Repository;

-use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use Roster\Contracts\CrudInterface;
 use Roster\Contracts\RepositoryInterface;
 use Roster\Models\Availability;
    ----------- end diff -----------

Applied rules:


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ImpedimentRepositoryInterface.php:7

    ---------- begin diff ----------
@@ @@
 use Roster\Models\Impediment;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use Roster\Contracts\CrudInterface;
 use Roster\Contracts\RepositoryInterface;

 /**
    ----------- end diff -----------

Applied rules:


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php:7

    ---------- begin diff ----------
@@ @@
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use Roster\Contracts\CrudInterface;
 use Roster\Contracts\RepositoryInterface;
 use Roster\Models\Schedule;
    ----------- end diff -----------

Applied rules:


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:56

    ---------- begin diff ----------
@@ @@
         'validity_end' => 'datetime',     // Changé de 'date' à 'datetime'
         'days' => 'array'
     ];
+
     /**
      * Get the schedulable resource that owns this availability.
      */
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:109

    ---------- begin diff ----------
@@ @@

         $model = $this->getModel();

-        $result = $model::query()
+
+        return $model::query()
             ->where('schedulable_id', $schedulable->id)
             ->where('schedulable_type', get_class($schedulable))
             // Si owner est défini, on filtre par availability_id
-            ->when($owner !== null, fn($query) => $query->where('availability_id', $owner->id))
+            ->when($owner instanceof Model, fn($query) => $query->where('availability_id', $owner->id))
             // Appliquer les filtres dynamiques
-            ->when(!empty($filters), function ($query) use ($filters, $model) {
+            ->when($filters !== [], function ($query) use ($filters): void {
                 foreach ($filters as $field => $value) {
                     $lowerField = strtolower($field);
                     if (str_contains($lowerField, 'start')) {
@@ @@
                 }
             })
             ->get();
-
-
-        return $result;
     }
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * SimplifyUselessVariableRector
 * FlipTypeControlToUseExclusiveTypeRector
 * RemoveUnusedClosureVariableUseRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php:7

    ---------- begin diff ----------
@@ @@
 use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
 use Roster\Contracts\Repository\ScheduleRepositoryInterface;
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
-use Illuminate\Database\Eloquent\Builder;
-use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\DB;
-use Illuminate\Support\Facades\Log;
 use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
-use Roster\Contracts\RosterDataInterface;
 use Roster\Contracts\Validation\ValidatorInterface;
 use Roster\DTOs\AvailabilityData;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
-use Roster\Exceptions\MergeConflictException;
 use Roster\Models\Availability;
 use Roster\Services\Core\AbstractValidatingService;
 use Roster\Validation\Exceptions\ValidationFailedException;
    ----------- end diff -----------

Applied rules:


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services\Core;

-use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
-use Illuminate\Support\Collection;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Exceptions\InvalidServiceContextException;
    ----------- end diff -----------

Applied rules:


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services\Core;

-use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Facades\Config;

 /**
    ----------- end diff -----------

Applied rules:


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services\Core;

+use BadMethodCallException;
 use LogicException;
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 use Illuminate\Database\Eloquent\Model;
-use Roster\Contracts\CrudInterface;
 use Roster\Contracts\EntityServiceInterface;
 use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
 use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
@@ @@
      *   $service->whereReason('holiday');
      *
      * @param string $method Nom de la méthode appelée
-     * @param array $arguments Arguments passés à la méthode
+     * @param array<int, mixed> $arguments Arguments passés à la méthode
      * @return $this
      *
-     * @throws \BadMethodCallException Si la méthode ne correspond pas au pattern whereXyz
+     * @throws BadMethodCallException Si la méthode ne correspond pas au pattern whereXyz
      */
     public function __call(string $method, array $arguments): self
     {
         // Vérifie si la méthode commence par "where" (insensible à la casse)
-        if (str_starts_with($method, 'where') && !empty($arguments)) {
+        if (str_starts_with($method, 'where') && $arguments !== []) {
             // Extrait le nom du champ à partir du nom de la méthode
             // whereType => type, whereReason => reason
             $field = lcfirst(substr($method, 5)); // enlève 'where' et passe la première lettre en minuscule
@@ @@
             return $this;
         }

-        throw new \BadMethodCallException(sprintf(
+        throw new BadMethodCallException(sprintf(
             'Call to undefined method %s::%s()',
             static::class,
             $method
@@ @@
         return $this;
     }

-    public function setFilter($key, $value): self
+    public function setFilter(string $key, $value): self
     {
         $this->filters[$key] = $value;
         return $this;
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * AddParamArrayDocblockFromDimFetchAccessRector
 * AddParamFromDimFetchKeyUseRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractValidatingService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services\Core;

-use Illuminate\Database\Eloquent\Model;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
-use Roster\Exceptions\InvalidServiceContextException;
 use Roster\Validation\Context\ValidationContext;
 use Roster\Validation\Exceptions\ValidationFailedException;
    ----------- end diff -----------

Applied rules:


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:5

    ---------- begin diff ----------
@@ @@
 namespace Roster\Services;

 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
-use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
    ----------- end diff -----------

Applied rules:


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOverlapRule.php:80

    ---------- begin diff ----------
@@ @@
                 $firstOverlap = $overlapping->first();
                 $validationContext->setViolation(
                     'overlap',
-                    "Availability overlaps with an existing availability {#$firstOverlap->id} -> type : {$firstOverlap->type} {$firstOverlap->validity_start} - {$firstOverlap->validity_end} for {$firstOverlap->daily_start}- {$firstOverlap->daily_end} "
+                    sprintf('Availability overlaps with an existing availability {#%s} -> type : %s %s - %s for %s- %s ', $firstOverlap->id, $firstOverlap->type, $firstOverlap->validity_start, $firstOverlap->validity_end, $firstOverlap->daily_start, $firstOverlap->daily_end)
                 );
             }
         } catch (Exception $exception) {
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTimeRangeRule.php:48

    ---------- begin diff ----------
@@ @@
             // La validation de format est gérée par d'autres règles
         }
     }
+
     private function validateTimeRange(
         ValidationContextInterface $validationContext,
         Availability $availability,
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Integration/Traits/BelongsToSchedulableTest.php:11

    ---------- begin diff ----------
@@ @@
 use Roster\Facades\Availability;
 use Roster\Facades\Schedule;
 use Roster\Facades\Impediment;
-use Roster\Models\Schedule as ModelsSchedule;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;

@@ @@
     public function test_schedule_creation_fails_without_schedulable_context(): void
     {
         // Create an availability first
-        $availability = Availability::for($this->testSchedulable)->create([
+        Availability::for($this->testSchedulable)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_impediment_creation_fails_without_proper_context(): void
     {
         // Create an availability
-        $availability = Availability::for($this->testSchedulable)->create([
+        Availability::for($this->testSchedulable)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@

         // Should NOT find the schedule when using a different schedulable
         $notFoundSchedule = Schedule::for($this->secondSchedulable)->find($schedule->id);
-        $this->assertNull($notFoundSchedule);
+        $this->assertNotInstanceOf(\Roster\Models\Schedule::class, $notFoundSchedule);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


15) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/AvailabilityServiceTest.php:6

    ---------- begin diff ----------
@@ @@

 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Roster\Facades\Availability as AvailabilityFacade;
-use Roster\Facades\Schedule as ScheduleFacade;
 use Roster\Models\Availability;
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\Support\TestSchedulable;
@@ @@
         $result = AvailabilityFacade::for($this->testSchedulable)->find($availabilityId);

         // Assert
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(\Roster\Models\Availability::class, $result);
     }

     public function test_can_get_all_availabilities_with_filters(): void
@@ @@
     public function test_sets_and_gets_filters_correctly(): void
     {
         // Arrange
-        $service = AvailabilityFacade::for($this->testSchedulable);
+        $availabilityService = AvailabilityFacade::for($this->testSchedulable);
         $filters = [
             'type' => 'consultation',
             'day' => 'monday',
@@ @@
         ];

         // Act
-        $service->setFilters($filters);
-        $result = $service->getFilters();
+        $availabilityService->setFilters($filters);
+        $result = $availabilityService->getFilters();

         // Assert
         $this->assertSame($filters, $result);
@@ @@
     public function test_can_reset_filters(): void
     {
         // Arrange
-        $service = AvailabilityFacade::for($this->testSchedulable);
-        $service->setFilters(['type' => 'consultation']);
-        $service->setFilter('day', 'monday');
+        $availabilityService = AvailabilityFacade::for($this->testSchedulable);
+        $availabilityService->setFilters(['type' => 'consultation']);
+        $availabilityService->setFilter('day', 'monday');

         // Act
-        $service->resetFilters();
-        $filters = $service->getFilters();
+        $availabilityService->resetFilters();
+
+        $filters = $availabilityService->getFilters();

         // Assert
         $this->assertEmpty($filters);
    ----------- end diff -----------

Applied rules:
 * NewlineBeforeNewAssignSetRector
 * RenameVariableToMatchMethodCallReturnTypeRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


16) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ImpedimentServiceTest.php:14

    ---------- begin diff ----------
@@ @@
 use Roster\Facades\Schedule;
 use Roster\Models\Availability as AvailabilityModel;
 use Roster\Models\Impediment as ImpedimentModel;
-use Roster\Models\Schedule as ScheduleModel;
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\TestCase;
 use Tests\Support\TestSchedulable;
@@ @@
     use RefreshDatabase;

     private TestSchedulable $testSchedulable;
-    private AvailabilityModel $testAvailability;

+    private AvailabilityModel $availabilityModel;
+
     protected function setUp(): void
     {
         parent::setUp();
@@ @@
         Config::set('roster.durations.max_search_period_days', 30);

         // Créer la disponibilité UNIQUEMENT via la facade
-        $this->testAvailability = Availability::for($this->testSchedulable)->create([
+        $this->availabilityModel = Availability::for($this->testSchedulable)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     {
         // Act
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Maintenance système',
                 'start_datetime' => '2038-01-04 10:00:00', // Lundi
@@ @@
         // Assert
         $this->assertInstanceOf(ImpedimentModel::class, $impediment);
         $this->assertEquals('Maintenance système', $impediment->reason);
-        $this->assertEquals($this->testAvailability->id, $impediment->availability_id);
+        $this->assertEquals($this->availabilityModel->id, $impediment->availability_id);
         $this->assertEquals($this->testSchedulable->id, $impediment->schedulable_id);
         $this->assertEquals(['priority' => 'high'], $impediment->metadata);
     }
@@ @@
     {
         // Act
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Formation',
                 'start_datetime' => '2038-01-05 14:00:00', // Mardi
@@ @@

         // Act
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test invalide',
                 'start_datetime' => '2038-01-04 12:00:00',
@@ @@

         // Act - 5 minutes seulement
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test trop court',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
     {
         // Arrange
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Raison originale',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act
         $result = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->update($impediment->id, [
                 'reason' => 'Nouvelle raison',
                 'metadata' => ['updated' => true],
@@ @@
     {
         // Arrange
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Original',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act
         $result = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->update($impediment->id, [
                 'start_datetime' => '2038-01-04 13:00:00',
                 'end_datetime' => '2038-01-04 15:00:00',
@@ @@

         // Act
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->update(999999, ['reason' => 'test']);
     }

@@ @@
     {
         // Arrange
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'À supprimer',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act
         $result = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->delete($impediment->id);

         // Assert
@@ @@

         // Act
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->delete(999999);
     }

@@ @@
     {
         // Arrange
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test find',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act
         $found = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->find($impediment->id);

         // Assert
@@ @@

         // Act - Essayer de trouver avec le mauvais schedulable
         $found = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->find($impediment->id);

         // Assert - Ne devrait pas trouver
-        $this->assertNull($found);
+        $this->assertNotInstanceOf(\Roster\Models\Impediment::class, $found);
     }

     public function test_get_all_impediments(): void
@@ @@
     {
         // Arrange
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Impediment 1',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Impediment 2',
                 'start_datetime' => '2038-01-05 14:00:00',
@@ @@

         // Act
         $result = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->all();

         // Assert
@@ @@
     {
         // Arrange
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Janvier',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Février',
                 'start_datetime' => '2038-02-04 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Janvier tardif',
                 'start_datetime' => '2038-01-25 10:00:00',
@@ @@

         // Act - Filtrer pour janvier seulement
         $result = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->setFilter('start_datetime', '2038-01-01')
             ->setFilter('end_datetime', '2038-01-31')
             ->all();
@@ @@
     {
         // Arrange
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Maintenance système',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Formation sécurité',
                 'start_datetime' => '2038-01-05 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Maintenance réseau',
                 'start_datetime' => '2038-01-06 10:00:00',
@@ @@

         // Act - Filtrer par raison contenant "Maintenance"
         $result = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->setFilter('reason', 'Maintenance')
             ->all();

@@ @@
     {
         // Arrange - Créer un schedule
         Schedule::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'title' => 'Réunion existante',
                 'start_datetime' => '2038-01-04 11:00:00',
@@ @@

         // Act - Vérifier chevauchement
         $wouldOverlap = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->wouldOverlapWithSchedule(
-                $this->testAvailability->id,
+                $this->availabilityModel->id,
                 Carbon::parse('2038-01-04 10:00:00'),
                 Carbon::parse('2038-01-04 12:00:00')
             );
@@ @@
     {
         // Arrange - Créer un schedule
         Schedule::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'title' => 'Réunion existante',
                 'start_datetime' => '2038-01-04 11:00:00',
@@ @@

         // Act - Vérifier pas de chevauchement
         $wouldOverlap = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->wouldOverlapWithSchedule(
-                $this->testAvailability->id,
+                $this->availabilityModel->id,
                 Carbon::parse('2038-01-04 14:00:00'),
                 Carbon::parse('2038-01-04 15:00:00')
             );
@@ @@
     {
         // Arrange - Créer un impediment existant
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Existant',
                 'start_datetime' => '2038-01-04 11:00:00',
@@ @@

         // Act - Vérifier avec exclusion
         $wouldOverlap = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->wouldOverlapWithSchedule(
-                $this->testAvailability->id,
+                $this->availabilityModel->id,
                 Carbon::parse('2038-01-04 12:00:00'), // Chevauche l'impediment existant
                 Carbon::parse('2038-01-04 14:00:00'),
                 $impediment->id // Exclure cet impediment
@@ @@
     {
         // Arrange - Créer un impediment existant
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Impediment existant',
                 'start_datetime' => '2038-01-04 11:00:00',
@@ @@

         // Act - Vérifier chevauchement
         $wouldOverlap = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->wouldOverlapWithOtherImpediment(
-                $this->testAvailability->id,
+                $this->availabilityModel->id,
                 Carbon::parse('2038-01-04 10:00:00'),
                 Carbon::parse('2038-01-04 12:00:00')
             );
@@ @@
     {
         // Arrange - Créer un impediment existant
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Impediment existant',
                 'start_datetime' => '2038-01-04 11:00:00',
@@ @@

         // Act - Vérifier pas de chevauchement
         $wouldOverlap = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->wouldOverlapWithOtherImpediment(
-                $this->testAvailability->id,
+                $this->availabilityModel->id,
                 Carbon::parse('2038-01-04 14:00:00'),
                 Carbon::parse('2038-01-04 15:00:00')
             );
@@ @@
     {
         // Arrange - Créer un impediment
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test block',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act - Vérifier créneau chevauchant
         $isBlocked = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->isTimeSlotBlocked(
                 Carbon::parse('2038-01-04 11:00:00'),
                 Carbon::parse('2038-01-04 13:00:00')
@@ @@
     {
         // Arrange - Créer un impediment
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test block',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act - Vérifier créneau non chevauchant
         $isBlocked = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->isTimeSlotBlocked(
                 Carbon::parse('2038-01-04 14:00:00'),
                 Carbon::parse('2038-01-04 15:00:00')
@@ @@

         // Act - Vérifier avec type 'consultation' (différent de 'emergency')
         $isBlocked = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->isTimeSlotBlocked(
                 Carbon::parse('2038-01-04 20:00:00'),
                 Carbon::parse('2038-01-04 20:30:00'),
@@ @@
     {
         // Arrange - Créer un impediment
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Meeting',
                 'start_datetime' => '2038-01-04 10:00:00', // Lundi
@@ @@

         // Act
         $slots = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->getAvailableTimeSlots(
                 Carbon::parse('2038-01-04 09:00:00'),
                 Carbon::parse('2038-01-04 17:00:00')
@@ @@
     {
         // Arrange - Bloquer toute la journée
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Full day meeting',
                 'start_datetime' => '2038-01-04 09:00:00',
@@ @@

         // Act
         $slots = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->getAvailableTimeSlots(
                 Carbon::parse('2038-01-04 09:00:00'),
                 Carbon::parse('2038-01-04 17:00:00')
@@ @@
     {
         // Arrange - Créer un impediment
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Rendez-vous médical',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act - Essayer de créer un schedule qui chevauche l'impediment
         Schedule::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'title' => 'Nouveau rendez-vous',
                 'start_datetime' => '2038-01-04 11:00:00', // Chevauche l'impediment
@@ @@
     {
         // Arrange - Créer plusieurs impediments
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Réunion matin',
                 'start_datetime' => '2038-01-04 09:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Pause déjeuner',
                 'start_datetime' => '2038-01-04 12:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Formation',
                 'start_datetime' => '2038-01-04 15:00:00',
@@ @@

         // Act
         $allImpediments = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->all();

         // Assert
@@ @@

         // Act
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Maintenance complexe',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
     {
         // Arrange & Act
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test duration',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
     {
         // Arrange
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Exact boundary',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
     public function test_concurrent_impediment_creation_prevents_overlap(): void
     {
         // Arrange - Créer un premier impediment
-        $imp1 = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+        Impediment::for($this->testSchedulable)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Premier impediment',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@


         // Act - Essayer de créer un deuxième qui chevauche
-        $imp2 = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+        Impediment::for($this->testSchedulable)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Deuxième impediment',
                 'start_datetime' => '2038-01-04 11:00:00', // Chevauche
@@ @@
     {
         // Arrange
         $impediment1 = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Premier impediment',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@

         // Act - Créer un deuxième qui commence exactement à la fin du premier
         $impediment2 = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Deuxième impediment',
                 'start_datetime' => '2038-01-04 11:00:00', // Exactement à la fin du premier
@@ @@
     {
         // Arrange - Créer des impediments adjacents
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Impediment 1',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Impediment 2',
                 'start_datetime' => '2038-01-04 11:00:00', // Exactement à la fin du premier
@@ @@

         // Act
         $slots = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->getAvailableTimeSlots(
                 Carbon::parse('2038-01-04 09:00:00'),
                 Carbon::parse('2038-01-04 17:00:00')
@@ @@
     {
         // Arrange - Créer plusieurs impediments qui couvrent toute la journée
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Réunion matin',
                 'start_datetime' => '2038-01-04 09:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Pause déjeuner',
                 'start_datetime' => '2038-01-04 12:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Réunion après-midi',
                 'start_datetime' => '2038-01-04 13:00:00',
@@ @@

         // Act - Vérifier qu'aucun créneau n'est disponible
         $isBlocked = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->isTimeSlotBlocked(
                 Carbon::parse('2038-01-04 10:00:00'),
                 Carbon::parse('2038-01-04 11:00:00')
@@ @@

         // Act - Chercher créneaux disponibles
         $slots = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->getAvailableTimeSlots(
                 Carbon::parse('2038-01-04 09:00:00'),
                 Carbon::parse('2038-01-04 17:00:00')
@@ @@

         // Act
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Maintenance urgente',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
     public function test_paginate_impediments(): void
     {
         // Arrange - Créer 25 impediments sur des jours permis (lundi à vendredi)
-        $startDate = \Illuminate\Support\Carbon::parse('2038-01-04'); // lundi
-        for ($i = 0; $i < 25; $i++) {
+        $startDate = Carbon::parse('2038-01-04'); // lundi
+        for ($i = 0; $i < 25; ++$i) {
             // Calcul du jour suivant valide
             $date = $startDate->copy()->addWeeks(intdiv($i, 5))->addDays($i % 5);

             Impediment::for($this->testSchedulable)
-                ->owner($this->testAvailability)
+                ->owner($this->availabilityModel)
                 ->create([
                     'reason' => "Impediment " . ($i + 1),
                     'start_datetime' => $date->setTime(10, 0, 0)->toDateTimeString(),
@@ @@
         }

         // Act - Paginer
-        $paginator = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+        $lengthAwarePaginator = Impediment::for($this->testSchedulable)
+            ->owner($this->availabilityModel)
             ->paginate(10);

         // Assert
-        $this->assertEquals(25, $paginator->total());
-        $this->assertEquals(10, $paginator->perPage());
-        $this->assertEquals(3, $paginator->lastPage());
-        $this->assertCount(10, $paginator->items());
+        $this->assertEquals(25, $lengthAwarePaginator->total());
+        $this->assertEquals(10, $lengthAwarePaginator->perPage());
+        $this->assertEquals(3, $lengthAwarePaginator->lastPage());
+        $this->assertCount(10, $lengthAwarePaginator->items());
     }


@@ @@
     {
         // Arrange
         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test 1',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
             ]);

         Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test 2',
                 'start_datetime' => '2038-01-05 10:00:00',
@@ @@

         // Act - Appliquer un filtre, puis réinitialiser
         $filtered = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->setFilter('start_date', '2038-01-05')
             ->resetFilters()
             ->all();
@@ @@
     {
         // Arrange
         $impediment = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability)
+            ->owner($this->availabilityModel)
             ->create([
                 'reason' => 'Test clear',
                 'start_datetime' => '2038-01-04 10:00:00',
@@ @@
             ]);

         // Act - Utiliser clear() sur le service
-        $service = Impediment::for($this->testSchedulable)
-            ->owner($this->testAvailability);
+        $impedimentService = Impediment::for($this->testSchedulable)
+            ->owner($this->availabilityModel);

-        $service->clear();
+        $impedimentService->clear();

         // Assert - Le service devrait être vide mais l'impediment existe toujours
-        $this->assertEmpty($service->getFilters());
-        $this->assertEmpty($service->getData());
+        $this->assertEmpty($impedimentService->getFilters());
+        $this->assertEmpty($impedimentService->getData());

         // L'impediment devrait toujours exister en base
         $this->assertNotNull(ImpedimentModel::find($impediment->id));
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * PostIncDecToPreIncDecRector
 * RemoveUnusedVariableAssignRector
 * RenameVariableToMatchMethodCallReturnTypeRector
 * RenamePropertyToMatchTypeRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


17) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleServiceTest.php:12

    ---------- begin diff ----------
@@ @@
 use Roster\Facades\Availability;
 use Roster\Facades\Impediment;
 use Roster\Facades\Schedule;
-use Roster\Models\Availability as AvailabilityModel;
 use Roster\Models\Schedule as ScheduleModel;
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\TestCase;
@@ @@
     use RefreshDatabase;

     private TestSchedulable $testSchedulable;
-    private AvailabilityModel $availability;

     protected function setUp(): void
     {
@@ @@
             ->find($scheduleForSchedulable2->id);

         // Assert - Ne devrait pas trouver car ce schedule n'appartient pas au bon schedulable
-        $this->assertNull($found);
+        $this->assertNotInstanceOf(\Roster\Models\Schedule::class, $found);
     }

     public function test_all_schedules(): void
@@ @@
         ]);

         // Créer un schedule
-        $schedule1 = Schedule::for($this->testSchedulable)
+        Schedule::for($this->testSchedulable)
             ->owner($availability)
             ->create([
                 'title' => 'Première réunion',
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * RenamePropertyToMatchTypeRector
 * NarrowUnusedSetUpDefinedPropertyRector
 * AssertEmptyNullableObjectToAssertInstanceofRector


18) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/DateRangeRulesTest.php:13

    ---------- begin diff ----------
@@ @@
 use Roster\Validation\Rules\TimeSlotDateTimeRule;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;
-use Roster\Facades\Availability as AvailabilityFacade;
 use Roster\Support\RosterMutationContext;

 final class DateRangeRulesTest extends TestCase
 {
     private AvailabilityDateRangeRule $availabilityDateRangeRule;
+
     private TimeSlotDateTimeRule $timeSlotDateTimeRule;
+
     private TestSchedulable $testSchedulable;

     protected function setUp(): void
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector


 [OK] 18 files would have been changed (dry-run) by Rector                                                              

