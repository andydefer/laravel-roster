# Rector Refactoring Report
*Generated: sam. 24 janv. 2026 11:53:55 WAT*


41 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/DebugRulesCommand.php:39

    ---------- begin diff ----------
@@ @@
                             {--show-methods : Display validation method details}
                             {--show-source : Display rule source code location}
                             {--details : Show all details including rule priorities and dependencies}';
+
     /**
      * The command description.
      *
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
 * NewlineBetweenClassLikeStmtsRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/ListRulesCommand.php:11

    ---------- begin diff ----------
@@ @@
 use Roster\Contracts\Validation\RuleInterface;
 use Illuminate\Console\Command;
 use Symfony\Component\Console\Helper\Table;
-use Symfony\Component\Console\Helper\TableSeparator;
 use ValueError;
 use Throwable;

@@ @@

         $rules = $this->getRulesForEntity($entityType, $operationFilter, $validator);

-        if (empty($rules)) {
+        if ($rules === []) {
             $this->warn('No validation rules found for this entity/operation combination.');
             return;
         }
@@ @@

         $allRules = $this->getAllRules($validator);

-        if (empty($allRules)) {
+        if ($allRules === []) {
             $this->warn('No validation rules found in the system.');
             return;
         }
@@ @@
     /**
      * Display details details for a rule.
      *
-     * @param array $ruleData Rule data
+     * @param array<string, mixed> $ruleData Rule data
      */
     private function displayVerboseDetails(array $ruleData): void
     {
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * AddParamArrayDocblockFromDimFetchAccessRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php:62

    ---------- begin diff ----------
@@ @@
      * @param int $scheduleId The ID of the schedule
      * @param Model $model The model to attach
      * @param array|null $metadata Optional metadata for the relationship
-     * @return void
      */
     public function attach(int $scheduleId, Model $model, ?array $metadata = null): void;

@@ @@
      * @param int $scheduleId The ID of the schedule
      * @param array<Model> $models The models to attach
      * @param array|null $metadata Optional metadata for the relationships
-     * @return void
      */
     public function attachMany(int $scheduleId, array $models, ?array $metadata = null): void;

@@ @@
      *
      * @param int $scheduleId The ID of the schedule
      * @param Model $model The model to detach
-     * @return void
      */
     public function detach(int $scheduleId, Model $model): void;

@@ @@
      *
      * @param int $scheduleId The ID of the schedule
      * @param array<Model> $models The models to detach
-     * @return void
      */
     public function detachMany(int $scheduleId, array $models): void;

@@ @@
      * Detach all models from a schedule.
      *
      * @param int $scheduleId The ID of the schedule
-     * @return void
      */
     public function detachAll(int $scheduleId): void;

@@ @@
      * @param int $scheduleId The ID of the schedule
      * @param array<Model> $models The models to attach
      * @param array|null $metadata Optional metadata for the relationships
-     * @return void
      */
     public function sync(int $scheduleId, array $models, ?array $metadata = null): void;
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/ScheduleServiceInterface.php:13

    ---------- begin diff ----------
@@ @@
      * Set the current schedule for link operations.
      *
      * @param Model $schedule The schedule model
-     * @return static
      */
     public function schedule(Model $schedule): static;

@@ @@
      *
      * @param Model $model The model to attach
      * @param array|null $metadata Optional metadata for the relationship
-     * @return static
      */
     public function attach(Model $model, ?array $metadata = null): static;

@@ @@
      *
      * @param array<Model> $models The models to attach
      * @param array|null $metadata Optional metadata for the relationships
-     * @return static
      */
     public function attachMany(array $models, ?array $metadata = null): static;

@@ @@
      * Detach a model from the current schedule.
      *
      * @param Model $model The model to detach
-     * @return static
      */
     public function detach(Model $model): static;

@@ @@
      * Detach multiple models from the current schedule.
      *
      * @param array<Model> $models The models to detach
-     * @return static
      */
     public function detachMany(array $models): static;

     /**
      * Detach all models from the current schedule.
-     *
-     * @return static
      */
     public function detachAll(): static;

@@ @@
      *
      * @param array<Model> $models The models to attach
      * @param array|null $metadata Optional metadata for the relationships
-     * @return static
      */
     public function sync(array $models, ?array $metadata = null): static;
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/ServiceInterface.php:82

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


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityDto.php:235

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


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:269

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


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php:93

    ---------- begin diff ----------
@@ @@
      */
     public function attach(int $scheduleId, Model $model, ?array $metadata = null): void
     {
-        RosterMutationContext::allow(function () use ($scheduleId, $model, $metadata) {
+        RosterMutationContext::allow(function () use ($scheduleId, $model, $metadata): void {
             DB::table('roster_schedule_links')->insertOrIgnore([
                 'schedule_id' => $scheduleId,
                 'linkable_id' => $model->getKey(),
@@ @@

     /**
      * Attach multiple models to a schedule.
+     * @param Model[] $models
      */
     public function attachMany(int $scheduleId, array $models, ?array $metadata = null): void
     {
-        RosterMutationContext::allow(function () use ($scheduleId, $models, $metadata) {
+        RosterMutationContext::allow(function () use ($scheduleId, $models, $metadata): void {
             $links = [];
             $now = now();

@@ @@
                 ];
             }

-            if (!empty($links)) {
+            if ($links !== []) {
                 DB::table('roster_schedule_links')->insertOrIgnore($links);
             }
         });
@@ @@
      */
     public function detach(int $scheduleId, Model $model): void
     {
-        RosterMutationContext::allow(function () use ($scheduleId, $model) {
+        RosterMutationContext::allow(function () use ($scheduleId, $model): void {
             DB::table('roster_schedule_links')
                 ->where('schedule_id', $scheduleId)
                 ->where('linkable_id', $model->getKey())
@@ @@
      */
     public function detachMany(int $scheduleId, array $models): void
     {
-        RosterMutationContext::allow(function () use ($scheduleId, $models) {
+        RosterMutationContext::allow(function () use ($scheduleId, $models): void {
             foreach ($models as $model) {
                 $this->detach($scheduleId, $model);
             }
@@ @@
      */
     public function detachAll(int $scheduleId): void
     {
-        RosterMutationContext::allow(function () use ($scheduleId) {
+        RosterMutationContext::allow(function () use ($scheduleId): void {
             DB::table('roster_schedule_links')
                 ->where('schedule_id', $scheduleId)
                 ->delete();
@@ @@
      */
     public function sync(int $scheduleId, array $models, ?array $metadata = null): void
     {
-        RosterMutationContext::allow(function () use ($scheduleId, $models, $metadata) {
+        RosterMutationContext::allow(function () use ($scheduleId, $models, $metadata): void {
             $this->detachAll($scheduleId);
             $this->attachMany($scheduleId, $models, $metadata);
         });
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

+use InvalidArgumentException;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Roster\Domain\Helpers\TimeWindowHelper;
-use Roster\DTOs\AvailabilityDto;
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
      * @param string|null $type Optional availability type filter
      *
      * @return Availability|null Matching availability or null if none found
-     * @throws \InvalidArgumentException When time window is invalid
+     * @throws InvalidArgumentException When time window is invalid
      */
     public function getAvailabilityForTimeSlot(
         Model $schedulable,
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


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:125

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


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

+use RuntimeException;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
      * @param Model $model The model to attach
      * @param array|null $metadata Optional metadata for the attachment
      * @return static Service instance for method chaining
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function attach(Model $model, ?array $metadata = null): static
     {
@@ @@
      * @param array $models Array of models to attach
      * @param array|null $metadata Optional metadata for all attachments
      * @return static Service instance for method chaining
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function attachMany(array $models, ?array $metadata = null): static
     {
@@ @@
      *
      * @param Model $model The model to detach
      * @return static Service instance for method chaining
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function detach(Model $model): static
     {
@@ @@
      *
      * @param array $models Array of models to detach
      * @return static Service instance for method chaining
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function detachMany(array $models): static
     {
@@ @@
      * Detach all models from the current schedule.
      *
      * @return static Service instance for method chaining
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function detachAll(): static
     {
@@ @@
      *
      * @param Model $model The model to check
      * @return bool True if model is attached
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function hasAttached(Model $model): bool
     {
@@ @@
      * Get all models attached to the current schedule.
      *
      * @return Collection All attached models
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function getAttached(): Collection
     {
@@ @@
      *
      * @param string $modelClass The model class to filter by
      * @return Collection Attached models of specified type
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function getAttachedByType(string $modelClass): Collection
     {
@@ @@
      * @param array $models Array of models to synchronize
      * @param array|null $metadata Optional metadata for synchronized attachments
      * @return static Service instance for method chaining
-     * @throws \RuntimeException If no schedule is set
+     * @throws RuntimeException If no schedule is set
      */
     public function sync(array $models, ?array $metadata = null): static
     {
@@ @@
     /**
      * Requires that a current schedule is set.
      *
-     * @throws \RuntimeException If no current schedule is set
+     * @throws RuntimeException If no current schedule is set
      */
     private function requireCurrentSchedule(): void
     {
         if (!$this->currentSchedule instanceof Model) {
-            throw new \RuntimeException(
+            throw new RuntimeException(
                 'No schedule set for link operations. Use schedule() method first.'
             );
         }
    ----------- end diff -----------

Applied rules:


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/AttachableToSchedules.php:84

    ---------- begin diff ----------
@@ @@
      *
      * @param Schedule|int $schedule Schedule instance or ID
      * @param array|null $metadata Optional metadata for the relationship
-     * @return void
      */
     public function attachToSchedule(Schedule|int $schedule, ?array $metadata = null): void
     {
@@ @@
      * the Roster package, use the service methods instead.
      *
      * @param Schedule|int $schedule Schedule instance or ID
-     * @return void
      */
     public function detachFromSchedule(Schedule|int $schedule): void
     {
@@ @@
      *
      * @param array<Schedule|int> $schedules Schedules to attach
      * @param array|null $metadata Optional metadata for the relationships
-     * @return void
      */
     public function syncSchedules(array $schedules, ?array $metadata = null): void
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/HasRoster.php:75

    ---------- begin diff ----------
@@ @@
     public function getImpedimentsInPeriod(Carbon $startDate, Carbon $endDate): Collection
     {
         return $this->impediments()
-            ->where(function ($query) use ($startDate, $endDate) {
+            ->where(function ($query) use ($startDate, $endDate): void {
                 // Cas 1: L'impediment commence avant la période et se termine pendant
                 $query->where('start_datetime', '<', $endDate)
                     ->where('end_datetime', '>', $startDate);
@@ @@
     public function getSchedulesInPeriod(Carbon $startDate, Carbon $endDate): Collection
     {
         return $this->schedules()
-            ->where(function ($query) use ($startDate, $endDate) {
+            ->where(function ($query) use ($startDate, $endDate): void {
                 // Cas 1: Le schedule commence avant la période et se termine pendant
                 $query->where('start_datetime', '<', $endDate)
                     ->where('end_datetime', '>', $startDate);
@@ @@
     public function hasConflictsInPeriod(Carbon $startDate, Carbon $endDate): bool
     {
         $items = $this->getRosterItemsInPeriod($startDate, $endDate);
-
-        return !$items['impediments']->isEmpty() || !$items['schedules']->isEmpty();
+        if (!$items['impediments']->isEmpty()) {
+            return true;
+        }
+        return !$items['schedules']->isEmpty();
     }

     /**
@@ @@
     public function getAvailabilitiesInPeriod(Carbon $startDate, Carbon $endDate, ?string $type = null): Collection
     {
         $query = $this->availabilities()
-            ->where(function ($query) use ($endDate) {
+            ->where(function ($query) use ($endDate): void {
                 $query->whereNull('validity_start')
                     ->orWhere('validity_start', '<=', $endDate);
             })
-            ->where(function ($query) use ($startDate) {
+            ->where(function ($query) use ($startDate): void {
                 $query->whereNull('validity_end')
                     ->orWhere('validity_end', '>=', $startDate);
             });
    ----------- end diff -----------

Applied rules:
 * ReturnBinaryOrToEarlyReturnRector
 * AddClosureVoidReturnTypeWhereNoReturnRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysInPeriodRule.php:19

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


15) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTemporalCoherenceRule.php:144

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


16) /home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php:43

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


17) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/ScheduleLinksAdvancedTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Services\ScheduleService;

-use Illuminate\Database\Eloquent\Model;
+use RuntimeException;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\Config;
@@ @@
 {
     use RefreshDatabase;

-    /** @var Model The schedulable model used for testing */
-    private Model $schedulable;
-
     /**
      * Set up test environment.
      */
@@ @@
     {
         parent::setUp();

-        $this->schedulable = TestSchedulable::create();
+        TestSchedulable::create();

         Config::set('roster.durations.default_slot_interval_minutes', 15);
         Config::set('roster.durations.max_search_period_days', 30);
@@ @@

         // 10. Test that operations require schedule context
         $serviceWithoutSchedule = schedule_for($availability);
-        $this->expectException(\RuntimeException::class);
+        $this->expectException(RuntimeException::class);
         $this->expectExceptionMessage('No schedule set for link operations. Use schedule() method first.');
         $serviceWithoutSchedule->attach($doctor);
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * NarrowUnusedSetUpDefinedPropertyRector


18) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/ScheduleLinksEdgeCasesTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Services\ScheduleService;

-use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Facades\Config;
 use Roster\Enums\ScheduleStatus;
@@ @@
 {
     use RefreshDatabase;

-    /** @var Model The schedulable model used for testing */
-    private Model $schedulable;
-
     /**
      * Set up test environment.
      */
@@ @@
     {
         parent::setUp();

-        $this->schedulable = TestSchedulable::create();
+        TestSchedulable::create();

         Config::set('roster.durations.default_slot_interval_minutes', 15);
         Config::set('roster.durations.max_search_period_days', 30);
@@ @@

         // 6. Test attaching null metadata
         $doctor = TestDoctor::create(['name' => 'Dr. Test', 'specialty' => 'testing']);
-        $service->attach($doctor, null);
+        $service->attach($doctor);
         $this->assertTrue($service->hasAttached($doctor), 'Should attach with null metadata');
     }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * RemoveNullArgOnNullDefaultParamRector
 * NarrowUnusedSetUpDefinedPropertyRector


19) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/ScheduleLinksMixedTypesTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Services\ScheduleService;

-use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
-use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\Config;
 use Roster\Enums\ScheduleStatus;
 use Tests\Support\TestCar;
@@ @@
 {
     use RefreshDatabase;

-    /** @var Model The schedulable model used for testing */
-    private Model $schedulable;
-
     /**
      * Set up test environment.
      */
@@ @@
     {
         parent::setUp();

-        $this->schedulable = TestSchedulable::create();
+        TestSchedulable::create();

         Config::set('roster.durations.default_slot_interval_minutes', 15);
         Config::set('roster.durations.max_search_period_days', 30);
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * NarrowUnusedSetUpDefinedPropertyRector


20) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/ScheduleLinksTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Services\ScheduleService;

+use Roster\Services\ScheduleService;
+use RuntimeException;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\Config;
-use Roster\Enums\ScheduleStatus;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;

@@ @@
         $service = schedule_for($availability)->schedule($schedule);

         // Assert: Should return service instance with schedule set
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);

         // Note: The currentSchedule property is private, so we test through public API
         // Attempt to use a link operation which would fail without schedule
-        $this->expectException(\RuntimeException::class);
+        $this->expectException(RuntimeException::class);

         // Create new service without schedule set and try link operation
         $serviceWithoutSchedule = schedule_for($availability);
@@ @@
             ->attach($modelToAttach, ['role' => 'participant', 'priority' => 'high']);

         // Assert: Should return service instance for chaining
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);
     }

     /**
@@ @@
             ->attachMany($models, ['type' => 'required']);

         // Assert: Should return service instance for chaining
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);
     }

     /**
@@ @@
             ->detach($modelToDetach);

         // Assert: Should return service instance
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);
     }

     /**
@@ @@
             ->sync($models, ['batch' => 'initial']);

         // Assert: Should return service instance
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);
     }

     /**
@@ @@
         $model = TestSchedulable::create(['name' => 'Test Model']);

         // Assert: Should throw exception when no schedule is set
-        $this->expectException(\RuntimeException::class);
+        $this->expectException(RuntimeException::class);
         $this->expectExceptionMessage('No schedule set for link operations. Use schedule() method first.');

         // Act: Try to attach without setting schedule
@@ @@
             ->detach($model1);

         // Assert: Should return service instance for chaining
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);
     }

     /**
@@ @@
             ->detachAll();

         // Assert: Should return service instance
-        $this->assertInstanceOf(\Roster\Services\ScheduleService::class, $service);
+        $this->assertInstanceOf(ScheduleService::class, $service);
     }
 }
    ----------- end diff -----------

Applied rules:


21) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Traits/HasRosterTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Traits;

+use Exception;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
     public function test_get_availabilities_in_period_with_type_filter(): void
     {
         // Arrange: Create availabilities with different types
-        $consultationAvailability = availability_for($this->testModel)->create([
+        availability_for($this->testModel)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
             'validity_end' => '2038-07-31',
         ]);

-        $surgeryAvailability = availability_for($this->testModel)->create([
+        availability_for($this->testModel)->create([
             'type' => 'surgery',
             'daily_start' => '13:00:00',
             'daily_end' => '17:00:00',
@@ @@
             'validity_end' => '2038-07-31',
         ]);

-        $trainingAvailability = availability_for($this->testModel)->create([
+        availability_for($this->testModel)->create([
             'type' => 'training',
             'daily_start' => '08:00:00',
             'daily_end' => '10:00:00',
@@ @@
         $mondayDates = ['2038-07-05', '2038-07-12', '2038-07-19']; // Mondays in July 2038
         $tuesdayDates = ['2038-07-06', '2038-07-13', '2038-07-20']; // Tuesdays in July 2038

-        for ($i = 1; $i <= 3; $i++) {
+        for ($i = 1; $i <= 3; ++$i) {
             try {
                 $impediment = impediment_for($availability)->create([
-                    'reason' => "Impediment $i",
-                    'start_datetime' => "{$mondayDates[$i - 1]} 10:00:00",
-                    'end_datetime' => "{$mondayDates[$i - 1]} 12:00:00",
+                    'reason' => 'Impediment ' . $i,
+                    'start_datetime' => $mondayDates[$i - 1] . ' 10:00:00',
+                    'end_datetime' => $mondayDates[$i - 1] . ' 12:00:00',
                 ]);
                 $operations[] = ['type' => 'impediment', 'success' => true, 'id' => $impediment->id];
-            } catch (\Exception $e) {
+            } catch (Exception $e) {
                 $operations[] = ['type' => 'impediment', 'success' => false, 'error' => $e->getMessage()];
             }

             try {
                 $schedule = schedule_for($availability)->create([
-                    'title' => "Schedule $i",
-                    'start_datetime' => "{$tuesdayDates[$i - 1]} 14:00:00",
-                    'end_datetime' => "{$tuesdayDates[$i - 1]} 15:00:00",
+                    'title' => 'Schedule ' . $i,
+                    'start_datetime' => $tuesdayDates[$i - 1] . ' 14:00:00',
+                    'end_datetime' => $tuesdayDates[$i - 1] . ' 15:00:00',
                 ]);
                 $operations[] = ['type' => 'schedule', 'success' => true, 'id' => $schedule->id];
-            } catch (\Exception $e) {
+            } catch (Exception $e) {
                 $operations[] = ['type' => 'schedule', 'success' => false, 'error' => $e->getMessage()];
             }
         }
@@ @@
         $startTime = microtime(true);

         // Create 20 impediments (weekdays in July)
-        for ($i = 1; $i <= 20; $i++) {
+        for ($i = 1; $i <= 20; ++$i) {
             // Calculate valid weekday dates in July
             $date = Carbon::parse('2038-07-01')->addDays($i - 1);
             if ($date->isWeekday()) {
@@ @@
                 $day = str_pad((string)$date->day, 2, '0', STR_PAD_LEFT);
                 try {
                     impediment_for($availability)->create([
-                        'reason' => "Impediment $i",
-                        'start_datetime' => "2038-07-$day 10:00:00",
-                        'end_datetime' => "2038-07-$day 12:00:00",
+                        'reason' => 'Impediment ' . $i,
+                        'start_datetime' => sprintf('2038-07-%s 10:00:00', $day),
+                        'end_datetime' => sprintf('2038-07-%s 12:00:00', $day),
                     ]);
-                } catch (\Exception $e) {
+                } catch (Exception $e) {
                     // Skip if validation fails
                 }
             }
@@ @@
         }

         // Create 20 schedules (weekdays in July)
-        for ($i = 1; $i <= 20; $i++) {
+        for ($i = 1; $i <= 20; ++$i) {
             // Calculate valid weekday dates in July
             $date = Carbon::parse('2038-07-01')->addDays($i + 4); // Offset to avoid overlapping with impediments
             if ($date->isWeekday() && $date->month === 7) {
@@ @@
                 $day = str_pad((string)$date->day, 2, '0', STR_PAD_LEFT);
                 try {
                     schedule_for($availability)->create([
-                        'title' => "Schedule $i",
-                        'start_datetime' => "2038-07-$day 14:00:00",
-                        'end_datetime' => "2038-07-$day 15:00:00",
+                        'title' => 'Schedule ' . $i,
+                        'start_datetime' => sprintf('2038-07-%s 14:00:00', $day),
+                        'end_datetime' => sprintf('2038-07-%s 15:00:00', $day),
                     ]);
-                } catch (\Exception $e) {
+                } catch (Exception $e) {
                     // Skip if validation fails
                 }
             }
@@ @@

         $impediments = $this->testModel->getImpedimentsInPeriod($periodStart, $periodEnd);
         $schedules = $this->testModel->getSchedulesInPeriod($periodStart, $periodEnd);
-        $rosterItems = $this->testModel->getRosterItemsInPeriod($periodStart, $periodEnd);
+        $this->testModel->getRosterItemsInPeriod($periodStart, $periodEnd);
         $hasConflicts = $this->testModel->hasConflictsInPeriod($periodStart, $periodEnd);

         $executionTime = microtime(true) - $startTime;
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * PostIncDecToPreIncDecRector
 * RemoveUnusedVariableAssignRector


22) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDateRangeRuleTest.php:530

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


23) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php:557

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


24) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/FutureDateRuleTest.php:667

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


25) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/ValidationContextTest.php:966

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


26) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Integration/CompleteRosterIntegrationTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Feature\Integration;

+use Illuminate\Database\Eloquent\Model;
 use Carbon\Exceptions\InvalidFormatException;
 use Exception;
 use Illuminate\Foundation\Testing\RefreshDatabase;
@@ @@
         $nonExistentSchedule = schedule_for($availability)->find(999999);

         // Assert schedule not found
-        $this->assertNull($nonExistentSchedule);
+        $this->assertNotInstanceOf(Model::class, $nonExistentSchedule);

         // Act & Assert: Test update non-existent schedule
         $this->expectException(ValidationFailedException::class);
    ----------- end diff -----------

Applied rules:
 * AssertEmptyNullableObjectToAssertInstanceofRector


27) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php:237

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


28) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Integration/Traits/BelongsToSchedulableTest.php:421

    ---------- begin diff ----------
@@ @@
         // Assert: Schedule should only be found with correct context
         $this->assertInstanceOf(ScheduleModel::class, $foundSchedule);
         $this->assertSame($schedule->id, $foundSchedule->id);
-        $this->assertNull($notFoundSchedule);
+        $this->assertNotInstanceOf(Model::class, $notFoundSchedule);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * AssertEmptyNullableObjectToAssertInstanceofRector


29) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/Helpers/TimezoneHelperTest.php:14

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


30) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/RepositoryMutationTest.php:4

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


31) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Http/Resources/AvailabilityResource.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Http\Resources;

+use DateTimeInterface;
+use Illuminate\Database\Eloquent\Collection;
 use Illuminate\Http\Request;
 use Illuminate\Http\Resources\Json\JsonResource;
 use Roster\Models\Availability;
@@ @@
  * @property-read string $schedulable_type
  * @property-read string $type
  * @property-read array $days
- * @property-read \DateTimeInterface|null $daily_start
- * @property-read \DateTimeInterface|null $daily_end
- * @property-read \DateTimeInterface|null $validity_start
- * @property-read \DateTimeInterface|null $validity_end
- * @property-read \DateTimeInterface|null $created_at
- * @property-read \DateTimeInterface|null $updated_at
- * @property-read \DateTimeInterface|null $deleted_at
- * @property-read \Illuminate\Database\Eloquent\Collection|null $schedules
- * @property-read \Illuminate\Database\Eloquent\Collection|null $impediments
+ * @property-read DateTimeInterface|null $daily_start
+ * @property-read DateTimeInterface|null $daily_end
+ * @property-read DateTimeInterface|null $validity_start
+ * @property-read DateTimeInterface|null $validity_end
+ * @property-read DateTimeInterface|null $created_at
+ * @property-read DateTimeInterface|null $updated_at
+ * @property-read DateTimeInterface|null $deleted_at
+ * @property-read Collection|null $schedules
+ * @property-read Collection|null $impediments
  *
  * @mixin Availability
  */
@@ @@
     /**
      * Transform the resource into an array
      *
-     * @param Request $request
      * @return array<string, mixed>
      */
     public function toArray(Request $request): array
@@ @@

     /**
      * Format time to H:i:s format if not null
-     *
-     * @param \DateTimeInterface|null $time
-     * @return string|null
      */
-    private function formatTimeOrNull(?\DateTimeInterface $time): ?string
+    private function formatTimeOrNull(?DateTimeInterface $time): ?string
     {
         return $time?->format('H:i:s');
     }
@@ @@

     /**
      * Format datetime to ISO 8601 string if not null
-     *
-     * @param \DateTimeInterface|null $dateTime
-     * @return string|null
      */
-    private function formatDateTimeToIso8601(?\DateTimeInterface $dateTime): ?string
+    private function formatDateTimeToIso8601(?DateTimeInterface $dateTime): ?string
     {
         return $dateTime?->format('c'); // format('c') returns ISO 8601 date
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


32) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Http/Resources/ImpedimentResource.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Http\Resources;

+use DateTimeInterface;
 use Illuminate\Http\Request;
 use Illuminate\Http\Resources\Json\JsonResource;
 use Roster\Models\Impediment;
@@ @@
  * @property-read int $schedulable_id
  * @property-read string $schedulable_type
  * @property-read string $reason
- * @property-read \DateTimeInterface|null $start_datetime
- * @property-read \DateTimeInterface|null $end_datetime
+ * @property-read DateTimeInterface|null $start_datetime
+ * @property-read DateTimeInterface|null $end_datetime
  * @property-read array|null $metadata
  * @property-read int $duration_minutes
- * @property-read \DateTimeInterface|null $created_at
- * @property-read \DateTimeInterface|null $updated_at
- * @property-read \DateTimeInterface|null $deleted_at
+ * @property-read DateTimeInterface|null $created_at
+ * @property-read DateTimeInterface|null $updated_at
+ * @property-read DateTimeInterface|null $deleted_at
  *
  * @mixin Impediment
  */
@@ @@
     /**
      * Transform the resource into an array
      *
-     * @param Request $request
      * @return array<string, mixed>
      */
     public function toArray(Request $request): array
@@ @@

     /**
      * Format datetime to ISO 8601 string if not null
-     *
-     * @param \DateTimeInterface|null $dateTime
-     * @return string|null
      */
-    private function formatDateTimeToIso8601(?\DateTimeInterface $dateTime): ?string
+    private function formatDateTimeToIso8601(?DateTimeInterface $dateTime): ?string
     {
         return $dateTime?->format('c');
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


33) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Http/Resources/ScheduleResource.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Http\Resources;

+use DateTimeInterface;
+use Roster\Enums\ScheduleStatus;
 use Illuminate\Http\Request;
 use Illuminate\Http\Resources\Json\JsonResource;
 use Roster\Models\Schedule;
@@ @@
  * @property-read string $schedulable_type
  * @property-read string $title
  * @property-read string|null $description
- * @property-read \DateTimeInterface|null $start_datetime
- * @property-read \DateTimeInterface|null $end_datetime
- * @property-read \Roster\Enums\ScheduleStatus $status
+ * @property-read DateTimeInterface|null $start_datetime
+ * @property-read DateTimeInterface|null $end_datetime
+ * @property-read ScheduleStatus $status
  * @property-read array|null $metadata
  * @property-read string $type
  * @property-read int $duration_minutes
- * @property-read \DateTimeInterface|null $created_at
- * @property-read \DateTimeInterface|null $updated_at
- * @property-read \DateTimeInterface|null $deleted_at
+ * @property-read DateTimeInterface|null $created_at
+ * @property-read DateTimeInterface|null $updated_at
+ * @property-read DateTimeInterface|null $deleted_at
  *
  * @mixin Schedule
  */
@@ @@
     /**
      * Transform the resource into an array
      *
-     * @param Request $request
      * @return array<string, mixed>
      */
     public function toArray(Request $request): array
@@ @@

     /**
      * Format datetime to ISO 8601 string if not null
-     *
-     * @param \DateTimeInterface|null $dateTime
-     * @return string|null
      */
-    private function formatDateTimeToIso8601(?\DateTimeInterface $dateTime): ?string
+    private function formatDateTimeToIso8601(?DateTimeInterface $dateTime): ?string
     {
         return $dateTime?->format('c');
     }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessParamTagRector
 * RemoveUselessReturnTagRector


34) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/AttachableToSchedulesTest.php:5

    ---------- begin diff ----------
@@ @@
 namespace Tests\Unit\Models;

 use Illuminate\Foundation\Testing\RefreshDatabase;
-use Illuminate\Support\Carbon;
 use Roster\Enums\ScheduleStatus;
 use Tests\Support\TestCar;
 use Tests\Support\TestDoctor;
@@ @@
  * Validates that models can be attached to schedules and manage
  * their schedule relationships through the attachable trait.
  */
-class AttachableToSchedulesTest extends TestCase
+final class AttachableToSchedulesTest extends TestCase
 {
     use RefreshDatabase;
    ----------- end diff -----------

Applied rules:
 * FinalizeTestCaseClassRector


35) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/ScheduleTest.php:11

    ---------- begin diff ----------
@@ @@
 use Roster\Models\Availability;
 use Roster\Models\Schedule as ScheduleModel;
 use Roster\Support\RosterMutationContext;
-use Tests\Support\TestCar;
-use Tests\Support\TestDoctor;
-use Tests\Support\TestRoom;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;
    ----------- end diff -----------

Applied rules:


36) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/AvailabilityServiceFindTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Services;

+use InvalidArgumentException;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Carbon;
@@ @@
     {
         // Arrange: Create availability for Thursday 9:00-17:00 (1er juillet 2038 est un jeudi)
         $availability = $this->createTestAvailability(
-            days: ['thursday'], // Jeudi
+            // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
     {
         // Arrange: Create availability for Tuesday only
         $this->createTestAvailability(
-            days: ['tuesday'], // Mardi
+            // Mardi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['tuesday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create two availabilities for same time but different types
         $consultationAvailability = $this->createTestAvailability(
-            type: 'consultation',
-            days: ['thursday'], // Jeudi
+            type: 'consultation', // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );

         $this->createTestAvailability(
-            type: 'training',
-            days: ['thursday'], // Jeudi
+            type: 'training', // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
         // Arrange: Create availability with type 'consultation'
         $this->createTestAvailability(
             type: 'consultation',
-            days: ['thursday'],
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd, 'training');

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create availability 9:00-12:00
         $this->createTestAvailability(
-            days: ['thursday'], // Jeudi
+            // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '12:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create availability 9:00-12:00
         $this->createTestAvailability(
-            days: ['thursday'],
             dailyStart: '09:00:00',
             dailyEnd: '12:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null (slot must be fully contained)
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create availability valid only for 1er juillet
         $this->createTestAvailability(
-            days: ['thursday'], // Jeudi
+            // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-01' // Seulement le 1er juillet
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create multiple availabilities for same time but different days
         $firstAvailability = $this->createTestAvailability(
-            type: 'consultation',
-            days: ['thursday'], // Jeudi
+            type: 'consultation', // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '12:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );

         $this->createTestAvailability(
-            type: 'consultation',
-            days: ['friday'], // Vendredi (différent pour éviter conflit)
+            type: 'consultation', // Vendredi (différent pour éviter conflit)
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['friday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
         $slotEnd = Carbon::parse('2038-07-01 10:00:00');

         // Assert: Should throw InvalidArgumentException
-        $this->expectException(\InvalidArgumentException::class);
+        $this->expectException(InvalidArgumentException::class);
         $this->expectExceptionMessageMatches('/end.*before.*start|must be after|daily window/i');

         // Act: Try to find availability with invalid window
@@ @@
     {
         // Arrange: Create availability 9:00-17:00
         $availability = $this->createTestAvailability(
-            days: ['thursday'], // Jeudi
+            // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
     {
         // Arrange: Create availability starting exactly on 1er juillet
         $availability = $this->createTestAvailability(
-            days: ['thursday'], // Jeudi
+            // Jeudi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
     {
         // Arrange: Create availability ending exactly on 31 juillet
         $availability = $this->createTestAvailability(
-            days: ['saturday'], // Samedi (31 juillet 2038 est un samedi)
+            // Samedi (31 juillet 2038 est un samedi)
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['saturday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
     {
         // Arrange: Create availability starting on 8 juillet
         $this->createTestAvailability(
-            days: ['thursday'],
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-08', // Démarre le 8 juillet
             validityEnd: '2038-07-31'
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create availability ending on 1er juillet
         $this->createTestAvailability(
-            days: ['thursday'],
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-01' // Termine le 1er juillet
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     {
         // Arrange: Create availability for jeudi et vendredi
         $availability = $this->createTestAvailability(
-            days: ['thursday', 'friday'], // Jeudi et vendredi
+            // Jeudi et vendredi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday', 'friday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
     {
         // Arrange: Create availability only for jeudi et vendredi
         $this->createTestAvailability(
-            days: ['thursday', 'friday'], // Jeudi et vendredi
+            // Jeudi et vendredi
             dailyStart: '09:00:00',
             dailyEnd: '17:00:00',
+            days: ['thursday', 'friday'],
             validityStart: '2038-07-01',
             validityEnd: '2038-07-31'
         );
@@ @@
             ->getAvailabilityForTimeSlot($this->schedulable, $slotStart, $slotEnd);

         // Assert: Should return null
-        $this->assertNull($result);
+        $this->assertNotInstanceOf(AvailabilityModel::class, $result);
     }

     /**
@@ @@
     private function createTestAvailabilityForSchedulable(Model $schedulable): AvailabilityModel
     {
         // Utiliser le service avec le contexte mutation
-        return RosterMutationContext::allow(function () use ($schedulable) {
+        return RosterMutationContext::allow(function () use ($schedulable): Model {
             return availability_for($schedulable)->create([
                 'type' => 'consultation',
                 'daily_start' => '09:00:00',
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
 * SortCallLikeNamedArgsRector
 * AssertEmptyNullableObjectToAssertInstanceofRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector
 * ClosureReturnTypeRector


37) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/AvailabilityServiceTest.php:194

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


38) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ImpedimentServiceTest.php:293

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


39) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/AvailabilitySearchTest.php:9

    ---------- begin diff ----------
@@ @@
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Illuminate\Support\Facades\Config;
-use Roster\Enums\ScheduleStatus;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;
    ----------- end diff -----------

Applied rules:


40) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/BasicOperationsTest.php:422

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


41) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleService/ConflictDetectionTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Services\ScheduleService;

+use Roster\Models\Schedule;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Facades\Config;
-use Roster\Enums\ScheduleStatus;
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;
@@ @@
         ]);

         // Assert: Should allow adjacent schedules
-        $this->assertInstanceOf(\Roster\Models\Schedule::class, $schedule2);
+        $this->assertInstanceOf(Schedule::class, $schedule2);
         $this->assertSame('2038-01-04 11:00:00', $schedule2->start_datetime->format('Y-m-d H:i:s'));
     }

@@ @@
             'end_datetime' => $slot['end']->format('Y-m-d H:i:s'),
         ]);

-        $this->assertInstanceOf(\Roster\Models\Schedule::class, $schedule);
+        $this->assertInstanceOf(Schedule::class, $schedule);

         // 4. Verify cannot recreate same slot
         $this->expectException(ValidationFailedException::class);
    ----------- end diff -----------

Applied rules:


 [OK] 41 files would have been changed (dry-run) by Rector                                                              

