# Rector Refactoring Report
*Generated: ven. 26 déc. 2025 22:27:12 WAT*


9 files with changes
====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php:15

    ---------- begin diff ----------
@@ @@
     /**
      * Get a query builder for availabilities of a schedulable resource.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param string|null $type Optional availability type filter
      * @return Builder<Availability> Query builder for availabilities
      */
-    public function findForSchedulable(Model $schedulable, ?string $type = null): Builder;
+    public function findForSchedulable(Model $model, ?string $type = null): Builder;

     /**
      * Get all availabilities for a schedulable resource within a date range.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $start Start date of the range
      * @param Carbon $end End date of the range
      * @param string|null $type Optional availability type filter
      * @return Collection<int, Availability> Collection of availabilities
      */
-    public function getForDateRange(Model $schedulable, Carbon $start, Carbon $end, ?string $type = null): Collection;
+    public function getForDateRange(Model $model, Carbon $start, Carbon $end, ?string $type = null): Collection;

     /**
      * Find a specific availability that covers a time slot.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $start Start time of the slot
      * @param Carbon $end End time of the slot
      * @param string|null $type Optional availability type filter
      * @return Availability|null The availability covering the slot, or null if not found
      */
-    public function getAvailabilityForTimeSlot(Model $schedulable, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
+    public function getAvailabilityForTimeSlot(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;

     /**
      * Get all availabilities for a specific date.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $date The date to check
      * @param string|null $type Optional availability type filter
      * @return Collection<int, Availability> Collection of availabilities for the date
      */
-    public function getForDate(Model $schedulable, Carbon $date, ?string $type = null): Collection;
+    public function getForDate(Model $model, Carbon $date, ?string $type = null): Collection;

     /**
      * Check if an availability is valid for a specific date.
@@ @@
     /**
      * Find an availability for a time slot with conflict detection information.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $start Start time of the slot
      * @param Carbon $end End time of the slot
      * @param string|null $type Optional availability type filter
      * @return Availability|null The availability with conflict information, or null if not found
      */
-    public function findForTimeSlotWithConflictInfo(Model $schedulable, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
+    public function findForTimeSlotWithConflictInfo(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
 }
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ImpedimentRepositoryInterface.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Contracts\Repository;

+use Roster\Models\Impediment;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
      *
      * @param int $availabilityId The ID of the availability
      * @param Carbon $from Starting date for future impediments
-     * @return Collection<int, \Roster\Models\Impediment> Collection of future impediments
+     * @return Collection<int, Impediment> Collection of future impediments
      */
     public function getFutureImpediments(
         int $availabilityId,
    ----------- end diff -----------

Applied rules:


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Contracts\Repository;

+use Roster\Models\Schedule;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
      *
      * @param int $availabilityId The ID of the availability
      * @param Carbon $from Starting date for future schedules
-     * @return Collection<int, \Roster\Models\Schedule> Collection of future schedules
+     * @return Collection<int, Schedule> Collection of future schedules
      */
     public function getFutureSchedules(
         int $availabilityId,
@@ @@
      * @param Carbon $start Start date of the range
      * @param Carbon $end End date of the range
      * @param array<string, mixed> $filters Additional filters to apply
-     * @return Collection<int, \Roster\Models\Schedule> Collection of schedules
+     * @return Collection<int, Schedule> Collection of schedules
      */
     public function getForDateRange(
         int $schedulableId,
    ----------- end diff -----------

Applied rules:


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:59

    ---------- begin diff ----------
@@ @@

     /**
      * Get the schedulable resource that owns this availability.
-     *
-     * @return MorphTo
      */
     public function schedulable(): MorphTo
     {
@@ @@

     /**
      * Get the schedules associated with this availability.
-     *
-     * @return HasMany
      */
     public function schedules(): HasMany
     {
@@ @@

     /**
      * Get the impediments associated with this availability.
-     *
-     * @return HasMany
      */
     public function impediments(): HasMany
     {
@@ @@
         if ($this->validity_start && $start->lt($this->validity_start)) {
             return false;
         }
-
-        if ($this->validity_end && $end->gt($this->validity_end)) {
-            return false;
-        }
-
-        return true;
+        return !($this->validity_end && $end->gt($this->validity_end));
     }

     /**
@@ @@
         if ($this->validity_start && $date->lt($this->validity_start)) {
             return false;
         }
-
-        if ($this->validity_end && $date->gt($this->validity_end)) {
-            return false;
-        }
-
-        return true;
+        return !($this->validity_end && $date->gt($this->validity_end));
     }

     /**
    ----------- end diff -----------

Applied rules:
 * SimplifyIfReturnBoolRector
 * RemoveUselessReturnTagRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php:68

    ---------- begin diff ----------
@@ @@
      * Accessor and mutator for metadata attribute.
      *
      * Accepts either a JSON string or an array from the user.
-     *
-     * @return Attribute
      */
     protected function metadata(): Attribute
     {
@@ @@

     /**
      * Get the schedulable entity associated with this impediment.
-     *
-     * @return MorphTo
      */
     public function schedulable(): MorphTo
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Models;

+use Illuminate\Database\Eloquent\Relations\Relation;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\Relations\BelongsTo;
 use Illuminate\Support\Carbon;
@@ @@

     /**
      * Get the schedulable entity through the parent availability.
-     *
-     * @return \Illuminate\Database\Eloquent\Relations\Relation|null
      */
-    public function schedulable(): ?\Illuminate\Database\Eloquent\Relations\Relation
+    public function schedulable(): ?Relation
     {
         return $this->availability?->schedulable();
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/RepositoryMutationTest.php:8

    ---------- begin diff ----------
@@ @@
 use Roster\Repositories\ScheduleRepository;
 use Tests\Support\TestSchedulable;
 use Illuminate\Foundation\Testing\RefreshDatabase;
-use Illuminate\Support\Facades\Schedule as FacadesSchedule;
 use Roster\Facades\Availability;
 use Roster\Facades\Schedule;
 use Roster\Models\Availability as AvailabilityModel;
@@ @@
 use Roster\Exceptions\ForbiddenModelMutationException;
 use Roster\Exceptions\InvalidOwnerException;
 use Roster\Exceptions\MissingOwnerException;
-use Roster\Exceptions\MissingSchedulableException;
-use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\TestCase;

 final class RepositoryMutationTest extends TestCase
@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = Availability::for($testSchedulable)
+        Availability::for($testSchedulable)
             ->create([
                 'type' => 'consultation',
                 'daily_start' => '09:00:00',
@@ @@
         // Tenter d'utiliser le repository sans schedulable (devrait échouer)
         $this->expectException(MissingOwnerException::class);

-        $scheduleRepository = app(ScheduleRepository::class);
+        app(ScheduleRepository::class);

         Schedule::for($testSchedulable)->find(999);
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector


8) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/AvailabilityTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Models;

+use Illuminate\Database\Eloquent\Relations\HasMany;
 use Roster\Facades\Availability;
 use Roster\Models\Availability as AvailabilityModel;
 use Tests\Support\TestSchedulable;
@@ @@
     /**
      * Test schedulable instance.
      */
-    private TestSchedulable $schedulable;
+    private TestSchedulable $testSchedulable;

     /**
      * Set up the test environment.
@@ @@
     {
         parent::setUp();

-        $this->schedulable = TestSchedulable::create();
+        $this->testSchedulable = TestSchedulable::create();
     }

     /**
      * Helper method to create an availability instance.
+     * @param array<string, string[]>|array<string, string> $attributes
      */
     private function createAvailability(array $attributes = []): AvailabilityModel
     {
@@ @@
             'validity_end' => '2038-07-31 23:59:59',
         ];

-        return Availability::for($this->schedulable)
+        return Availability::for($this->testSchedulable)
             ->create(array_merge($defaultAttributes, $attributes));
     }

@@ @@
         ]);

         $this->assertInstanceOf(AvailabilityModel::class, $availability);
-        $this->assertSame($this->schedulable->id, $availability->schedulable_id);
+        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
         $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
         $this->assertEquals('training', $availability->type);
         $this->assertEquals(['monday', 'wednesday', 'friday'], $availability->days);
@@ @@
         ]);

         $this->assertIsArray($availability->days);
-        $this->assertEquals(['tuesday', 'thursday'], $availability->days);
+        $this->assertSame(['tuesday', 'thursday'], $availability->days);
     }

     /**
@@ @@
     {
         $availability = $this->createAvailability();

-        $this->assertEquals(480, $availability->getDailyDurationMinutes()); // 8 heures * 60 minutes
+        $this->assertSame(480, $availability->getDailyDurationMinutes()); // 8 heures * 60 minutes
     }

     /**
@@ @@
     {
         $availability = $this->createAvailability();

-        $this->assertEquals(30, $availability->getValidityDurationDays()); // 31 jours - 1
+        $this->assertSame(30, $availability->getValidityDurationDays()); // 31 jours - 1
     }

     /**
@@ @@
         // Pour tester cette méthode dans un contexte où les dates seraient null,
         // nous devons créer une instance sans passer par la validation
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->schedulable->id,
+            'schedulable_id' => $this->testSchedulable->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
     {
         // Création d'une instance sans passer par la validation pour tester la méthode
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->schedulable->id,
+            'schedulable_id' => $this->testSchedulable->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
     {
         // Création d'une instance sans passer par la validation
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->schedulable->id,
+            'schedulable_id' => $this->testSchedulable->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
     {
         // Création d'une instance sans passer par la validation
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->schedulable->id,
+            'schedulable_id' => $this->testSchedulable->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
         $availability = $this->createAvailability();

         $this->assertInstanceOf(TestSchedulable::class, $availability->schedulable);
-        $this->assertEquals($this->schedulable->id, $availability->schedulable->id);
+        $this->assertEquals($this->testSchedulable->id, $availability->schedulable->id);
     }

     /**
@@ @@
     {
         $availability = $this->createAvailability();

-        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $availability->schedules());
+        $this->assertInstanceOf(HasMany::class, $availability->schedules());
     }

     /**
@@ @@
     {
         $availability = $this->createAvailability();

-        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $availability->impediments());
+        $this->assertInstanceOf(HasMany::class, $availability->impediments());
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector
 * AssertEqualsToSameRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/ImpedimentTest.php:22

    ---------- begin diff ----------
@@ @@
     /**
      * Test schedulable instance.
      */
-    private TestSchedulable $schedulable;
+    private TestSchedulable $testSchedulable;

     /**
      * Test availability instance.
@@ @@
     {
         parent::setUp();

-        $this->schedulable = TestSchedulable::create();
+        $this->testSchedulable = TestSchedulable::create();
         $this->availability = $this->createAvailability();
     }

@@ @@
      */
     private function createAvailability(): Availability
     {
-        return \Roster\Facades\Availability::for($this->schedulable)
+        return \Roster\Facades\Availability::for($this->testSchedulable)
             ->create([
                 'type' => 'consultation',
                 'daily_start' => '09:00:00',
@@ @@

     /**
      * Helper method to create an impediment instance for testing model methods.
+     * @param array<string, Carbon> $attributes
      */
     private function createImpedimentModelInstance(array $attributes = []): ImpedimentModel
     {
         // Create via facade first to get valid instance
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Test Impediment',
@@ @@
             ]);

         // Then update with test attributes if needed
-        if (!empty($attributes)) {
-            foreach ($attributes as $key => $value) {
-                $impediment->$key = $value;
-            }
+        foreach ($attributes as $key => $value) {
+            $impediment->$key = $value;
         }

         return $impediment;
@@ @@
      */
     public function test_impediment_can_be_created_with_valid_attributes(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Vacation',
@@ @@
             ]);

         $this->assertInstanceOf(ImpedimentModel::class, $impediment);
-        $this->assertSame($this->schedulable->id, $impediment->schedulable_id);
+        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
         $this->assertSame(TestSchedulable::class, $impediment->schedulable_type);
         $this->assertSame($this->availability->id, $impediment->availability_id);
         $this->assertEquals('Vacation', $impediment->reason);
@@ @@
      */
     public function test_datetime_attributes_are_properly_cast(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Meeting',
@@ @@
      */
     public function test_metadata_is_properly_cast_to_array(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Emergency',
@@ @@
             ]);

         $this->assertIsArray($impediment->metadata);
-        $this->assertEquals(['type' => 'emergency', 'priority' => 'high'], $impediment->metadata);
+        $this->assertSame(['type' => 'emergency', 'priority' => 'high'], $impediment->metadata);
     }

     /**
@@ @@
         ));

         $this->assertIsArray($impediment->metadata);
-        $this->assertEquals(['note' => 'Test note', 'category' => 'technical'], $impediment->metadata);
+        $this->assertSame(['note' => 'Test note', 'category' => 'technical'], $impediment->metadata);
     }

     /**
@@ @@
      */
     public function test_metadata_returns_empty_array_when_null(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Test',
@@ @@
         $impediment = $this->createImpedimentModelInstance();

         $this->assertInstanceOf(TestSchedulable::class, $impediment->schedulable);
-        $this->assertEquals($this->schedulable->id, $impediment->schedulable->id);
+        $this->assertEquals($this->testSchedulable->id, $impediment->schedulable->id);
     }

     /**
@@ @@
      */
     public function test_duration_minutes_attribute_returns_correct_duration(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Long Meeting',
@@ @@
                 'metadata' => null,
             ]);

-        $this->assertEquals(150.0, $impediment->duration_minutes);
+        $this->assertEqualsWithDelta(150.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
     }

     /**
@@ @@
      */
     public function test_impediment_duration_is_calculated_correctly(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Training',
@@ @@
                 'metadata' => null,
             ]);

-        $this->assertEquals(90.0, $impediment->duration_minutes);
+        $this->assertEqualsWithDelta(90.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
     }

     /**
@@ @@
      */
     public function test_duration_minutes_for_exact_hours(): void
     {
-        $impediment = Impediment::for($this->schedulable)
+        $impediment = Impediment::for($this->testSchedulable)
             ->owner($this->availability)
             ->create([
                 'reason' => 'Full Day',
@@ @@
                 'metadata' => null,
             ]);

-        $this->assertEquals(480.0, $impediment->duration_minutes);
+        $this->assertEqualsWithDelta(480.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
     }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedNonEmptyArrayBeforeForeachRector
 * RenamePropertyToMatchTypeRector
 * AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector
 * AssertEqualsToSameRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


 [OK] 9 files would have been changed (dry-run) by Rector                                                               

