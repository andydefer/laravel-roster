# Rector Refactoring Report
*Generated: sam. 20 déc. 2025 17:35:24 WAT*


11 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:56

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
         if ($this->start_date && $start->lt($this->start_date)) {
             return false;
         }
-
-        if ($this->end_date && $end->gt($this->end_date)) {
-            return false;
-        }
-
-        return true;
+        return !($this->end_date && $end->gt($this->end_date));
     }
 }
    ----------- end diff -----------

Applied rules:
 * SimplifyIfReturnBoolRector
 * RemoveUselessReturnTagRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Models;

+use Illuminate\Database\Eloquent\Relations\MorphTo;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\Relations\BelongsTo;
 use Illuminate\Support\Carbon;
@@ @@
     /**
      * Get the schedulable entity associated with this impediment.
      *
-     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
+     * @return MorphTo
      */
     public function schedulable()
     {
    ----------- end diff -----------

Applied rules:


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php:4

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
      *
-     * @return \Illuminate\Database\Eloquent\Relations\Relation|null
+     * @return Relation|null
      */
     public function schedulable()
     {
    ----------- end diff -----------

Applied rules:


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Repositories;

+use InvalidArgumentException;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
@@ @@
     /**
      * Get availabilities for a specific date range.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $start Start of date range
      * @param Carbon $end End of date range
      * @param string|null $type Optional availability type filter
@@ @@
      * @return Collection<int, Availability> Collection of availabilities within the date range
      */
     public function getForDateRange(
-        Model $schedulable,
+        Model $model,
         Carbon $start,
         Carbon $end,
         ?string $type = null
     ): Collection {
-        $builder = $this->buildBaseQuery($schedulable)
+        $builder = $this->buildBaseQuery($model)
             ->where(function ($query) use ($end): void {
                 $query->whereNull('start_date')
                     ->orWhere('start_date', '<=', $end);
@@ @@
     /**
      * Find availability for a time slot with conflict information.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $start Start of time slot
      * @param Carbon $end End of time slot
      * @param string|null $type Optional availability type filter
      * @return Availability|null The matching availability with conflict info or null
-     * @throws \InvalidArgumentException If the time range is invalid
+     * @throws InvalidArgumentException If the time range is invalid
      */
     public function findForTimeSlotWithConflictInfo(
-        Model $schedulable,
+        Model $model,
         Carbon $start,
         Carbon $end,
         ?string $type = null
@@ @@
     ): ?Availability {
         $this->validationService->validateTimeRange($start, $end);

-        return Availability::where('schedulable_id', $schedulable->id)
-            ->where('schedulable_type', get_class($schedulable))
+        return Availability::where('schedulable_id', $model->id)
+            ->where('schedulable_type', get_class($model))
             ->when($type, function ($query) use ($type): void {
                 $query->where('type', $type);
             })
@@ @@
     /**
      * Find availability for a specific time slot.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $start Start of time slot
      * @param Carbon $end End of time slot
      * @param string|null $type Optional availability type filter
      * @return Availability|null The matching availability or null
-     * @throws \InvalidArgumentException If the time range is invalid
+     * @throws InvalidArgumentException If the time range is invalid
      */
     public function findForTimeSlot(
-        Model $schedulable,
+        Model $model,
         Carbon $start,
         Carbon $end,
         ?string $type = null
@@ @@
     ): ?Availability {
         $this->validationService->validateTimeRange($start, $end);

-        $builder = $this->buildBaseQuery($schedulable);
+        $builder = $this->buildBaseQuery($model);

         if ($type) {
             $builder->where('type', $type);
@@ @@
     /**
      * Get availabilities for a specific date.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $date The date to check
      * @param string|null $type Optional availability type filter
      * @return Collection<int, Availability> Collection of availabilities for the date
      */
     public function getForDate(
-        Model $schedulable,
+        Model $model,
         Carbon $date,
         ?string $type = null
     ): Collection {
-        $builder = $this->buildBaseQuery($schedulable)
+        $builder = $this->buildBaseQuery($model)
             ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

         if ($type) {
@@ @@
     /**
      * Get all availabilities for a schedulable entity.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param string|null $type Optional availability type filter
      * @param string|null $day Optional day filter
      * @return Collection<int, Availability> Collection of availabilities
      */
     public function getAllForSchedulable(
-        Model $schedulable,
+        Model $model,
         ?string $type = null,
         ?string $day = null
     ): Collection {
-        $builder = $this->buildBaseQuery($schedulable)
+        $builder = $this->buildBaseQuery($model)
             ->with(['schedules', 'impediments']);

         if ($type) {
@@ @@
     /**
      * Check if schedulable is available at specific datetime.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $datetime The datetime to check
      * @return bool True if available at the given datetime
      */
-    public function isAvailableAt(Model $schedulable, Carbon $datetime): bool
+    public function isAvailableAt(Model $model, Carbon $datetime): bool
     {
         $dayOfWeek = strtolower($datetime->englishDayOfWeek);
         $time = $datetime->format('H:i:s');

-        $builder = $this->buildBaseQuery($schedulable)
+        $builder = $this->buildBaseQuery($model)
             ->whereJsonContains('days', $dayOfWeek)
             ->where('start_time', '<=', $time)
             ->where('end_time', '>=', $time);
@@ @@
     /**
      * Find overlapping availabilities.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param array<string, mixed> $data The availability data to check
      * @param int|null $exceptId ID to exclude from search
      * @return Collection<int, Availability> Collection of overlapping availabilities
-     * @throws \InvalidArgumentException If time range is invalid
+     * @throws InvalidArgumentException If time range is invalid
      */
     public function findOverlapping(
-        Model $schedulable,
+        Model $model,
         array $data,
         ?int $exceptId = null
     ): Collection {
@@ @@
         $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
         $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

-        $builder = $this->buildBaseQuery($schedulable);
+        $builder = $this->buildBaseQuery($model);

         if ($exceptId !== null) {
             $builder->where('id', '!=', $exceptId);
@@ @@
     /**
      * Find related availabilities based on search criteria.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param array<string, mixed> $data Search criteria
      * @return Collection<int, Availability> Collection of related availabilities
      */
-    public function findByType(Model $schedulable, array $data): Collection
+    public function findByType(Model $model, array $data): Collection
     {
         $type = $data['type'] ?? null;

-        $builder = $this->buildBaseQuery($schedulable);
+        $builder = $this->buildBaseQuery($model);

         if ($type !== null) {
             $builder->where('type', $type);
@@ @@
     /**
      * Build filtered query for availabilities.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param array<string, mixed> $filters Filters to apply
      * @return Builder Eloquent query builder
      */
-    public function buildQueryWithFilters(Model $schedulable, array $filters = []): Builder
+    public function buildQueryWithFilters(Model $model, array $filters = []): Builder
     {
-        $builder = $this->buildBaseQuery($schedulable);
+        $builder = $this->buildBaseQuery($model);

         match (true) {
             isset($filters['type']) && isset($filters['day']) =>
@@ @@
     /**
      * Load availabilities with pre-loaded schedule and impediment conflicts.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $start Start of the date range
      * @param Carbon $end End of the date range
      * @param string|null $type Optional availability type filter
@@ @@
      * @return Collection<int, Availability> Collection of availabilities with conflict info
      */
     public function getAvailabilitiesWithConflictInfo(
-        Model $schedulable,
+        Model $model,
         Carbon $start,
         Carbon $end,
         ?string $type = null
     ): Collection {
-        $availabilities = $this->getForDateRange($schedulable, $start, $end, $type);
+        $availabilities = $this->getForDateRange($model, $start, $end, $type);

         return $availabilities->load(['schedules', 'impediments']);
     }
@@ @@
     /**
      * Build base query for availabilities of a schedulable entity.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @return Builder Base query builder
      */
-    private function buildBaseQuery(Model $schedulable): Builder
+    private function buildBaseQuery(Model $model): Builder
     {
-        return Availability::where('schedulable_id', $schedulable->id)
-            ->where('schedulable_type', get_class($schedulable));
+        return Availability::where('schedulable_id', $model->id)
+            ->where('schedulable_type', get_class($model));
     }

     /**
@@ @@
      */
     private function applyDayFilters(Builder $builder, array $days): void
     {
-        if (empty($days)) {
+        if ($days === []) {
             return;
         }

@@ @@
     /**
      * Apply date filter to relation query using strategy pattern.
      *
-     * @param Builder $query Relation query builder
+     * @param Builder $builder Relation query builder
      * @param Carbon|null $startDate Optional start date
      * @param Carbon|null $endDate Optional end date
      */
-    private function applyRelationDateFilter(Builder $query, ?Carbon $startDate, ?Carbon $endDate): void
+    private function applyRelationDateFilter(Builder $builder, ?Carbon $startDate, ?Carbon $endDate): void
     {
         match (true) {
             $startDate instanceof Carbon && $endDate instanceof Carbon =>
-            $query->where(function ($q) use ($startDate, $endDate): void {
+            $builder->where(function ($q) use ($startDate, $endDate): void {
                 $q->whereBetween('start_datetime', [$startDate, $endDate])
                     ->orWhereBetween('end_datetime', [$startDate, $endDate])
                     ->orWhere(function ($subQuery) use ($startDate, $endDate): void {
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * RenameParamToMatchTypeRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php:30

    ---------- begin diff ----------
@@ @@
     {
         $impediment = $this->findById($id);

-        return $impediment instanceof Impediment
-            ? $impediment->update($data)
-            : false;
+        return $impediment instanceof Impediment && $impediment->update($data);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * TernaryToBooleanOrFalseToBooleanAndRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php:30

    ---------- begin diff ----------
@@ @@
     {
         $schedule = $this->findById($id);

-        return $schedule instanceof Schedule
-            ? $schedule->update($data)
-            : false;
+        return $schedule instanceof Schedule && $schedule->update($data);
     }

     /**
@@ @@
      * @param int $schedulableId The schedulable ID
      * @param string $schedulableType The schedulable class type
      * @param array<string, mixed> $filters Filters to apply
-     * @return Builder
      */
     public function buildQueryWithFilters(
         int $schedulableId,
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector
 * TernaryToBooleanOrFalseToBooleanAndRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php:5

    ---------- begin diff ----------
@@ @@
 namespace Roster\Services;

 use Illuminate\Database\Eloquent\Builder;
-use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
@@ @@
     use FilterableTrait;

     private AvailabilityValidatorInterface $availabilityValidator;
+
     private ValidationServiceInterface $validationService;
+
     private AvailabilityRepositoryInterface $availabilityRepository;
+
     private AvailabilityMergerInterface $availabilityMerger;
+
     private SlotFinderInterface $slotFinder;
+
     private AvailabilityCheckerInterface $availabilityChecker;
+
     private ?Availability $currentAvailability = null;

     public function __construct(
@@ @@
             $this->throwNotFoundException();
         }

-        if (empty($this->data)) {
+        if ($this->data === []) {
             return;
         }
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * NewlineBetweenClassLikeStmtsRector


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php:41

    ---------- begin diff ----------
@@ @@
      * Scope the service to a specific parent model.
      *
      * @param Model $model The schedulable model to scope to
-     * @return static
      */
     final public function for(Model $model): static
     {
@@ @@

     /**
      * Get the current schedulable model.
-     *
-     * @return Model|null
      */
     final public function getSchedulable(): ?Model
     {
@@ @@

     /**
      * Clear all applied filters.
-     *
-     * @return static
      */
     final public function resetFilters(): static
     {
@@ @@
      * Filter results by type.
      *
      * @param string $type The type to filter by
-     * @return static
      */
     final public function whereType(string $type): static
     {
@@ @@

     /**
      * Return all matching results.
-     *
-     * @return Collection
      */
     final public function all(): Collection
     {
@@ @@
     /**
      * Execute the query with the current filters.
      *
-     * @return Collection
      * @throws MissingSchedulableException
      */
     final public function get(): Collection
@@ @@
     final protected function validateConfigurationRules(string $operation): void
     {
         $entityType = $this->getEntityType();
-        $entityConfig = Config::get(key: "roster.validate_future_dates.{$entityType}", default: []);
+        $entityConfig = Config::get(key: 'roster.validate_future_dates.' . $entityType, default: []);
         $globalEnabled = Config::get(key: 'roster.validate_future_dates.enabled', default: true);
         $entityEnabled = $entityConfig['enabled'] ?? $globalEnabled;

@@ @@
                 $validationService = $this->getValidationService();
                 if (! $validationService->validateTimezone(timezone: $timezone)) {
                     throw ValidationException::withMessage(
-                        message: "Invalid timezone: {$timezone}"
+                        message: 'Invalid timezone: ' . $timezone
                     );
                 }
+
                 break;
             }
         }
@@ @@

         $prefix = Config::get(key: 'roster.cache.prefix', default: 'roster_');
         $entityType = $this->getEntityType();
-        $cacheKey = "{$prefix}{$entityType}_{$entityId}";
+        $cacheKey = sprintf('%s%s_%d', $prefix, $entityType, $entityId);

         Cache::forget(key: $cacheKey);

         if (Config::get(key: 'roster.cache.use_tags', default: true)) {
-            Cache::tags(names: ["{$entityType}_{$entityId}"])->flush();
+            Cache::tags(names: [sprintf('%s_%d', $entityType, $entityId)])->flush();
         }
     }

@@ @@
     {
         $entityType = $this->getEntityType();
         $configFields = Config::get(
-            key: "roster.validation.required_fields.{$entityType}",
+            key: 'roster.validation.required_fields.' . $entityType,
             default: []
         );
         $allRequired = array_unique(array: array_merge($configFields, $requiredFields));
@@ @@
         foreach ($allRequired as $field) {
             if (empty($this->data[$field] ?? null)) {
                 throw ValidationException::withMessage(
-                    message: "Field '{$field}' is required"
+                    message: sprintf("Field '%s' is required", $field)
                 );
             }
         }
@@ @@

     /**
      * Get validation service instance.
-     *
-     * @return ValidationServiceInterface
      */
     abstract protected function getValidationService(): ValidationServiceInterface;

@@ @@

     /**
      * Apply filters to the query.
-     *
-     * @return Builder
      */
     abstract protected function buildQueryWithFilters(): Builder;
 }
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * NewlineAfterStatementRector
 * RemoveUselessReturnTagRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:105

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


10) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Facades/AvailabilityFacadeTest.php:4

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


11) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Facades/ImpedimentFacadeTest.php:4

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


 [OK] 11 files would have been changed (dry-run) by Rector                                                              

