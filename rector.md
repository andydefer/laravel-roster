# Rector Refactoring Report
*Generated: sam. 27 déc. 2025 03:42:59 WAT*


18 files with changes
=====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php:33

    ---------- begin diff ----------
@@ @@
     /**
      * Execute the console command.
      *
-     * @param CacheRulesService $service The cache rules service
+     * @param CacheRulesService $cacheRulesService The cache rules service
      * @return int Command exit code (SUCCESS or FAILURE)
      */
-    public function handle(CacheRulesService $service): int
+    public function handle(CacheRulesService $cacheRulesService): int
     {
         try {
             if ($this->option('clear')) {
-                $stats = $service->clear(
+                $stats = $cacheRulesService->clear(
                     force: (bool) $this->option('force')
                 );
             } elseif ($this->option('list')) {
-                $service->displayRulesTable($this);
+                $cacheRulesService->displayRulesTable($this);
                 return self::SUCCESS;
             } elseif ($this->option('show')) {
-                $stats = $service->show();
+                $stats = $cacheRulesService->show();
             } else {
-                $stats = $service->generate();
+                $stats = $cacheRulesService->generate();
             }

             if ($stats instanceof CacheStats) {
@@ @@
             }

             return self::SUCCESS;
-        } catch (Throwable $exception) {
-            $this->error($exception->getMessage());
+        } catch (Throwable $throwable) {
+            $this->error($throwable->getMessage());
             return self::FAILURE;
         }
     }
@@ @@
     /**
      * Display cache statistics in a formatted way.
      *
-     * @param CacheStats $stats Cache statistics to display
+     * @param CacheStats $cacheStats Cache statistics to display
      */
-    protected function displayCacheStats(CacheStats $stats): void
+    protected function displayCacheStats(CacheStats $cacheStats): void
     {
         $this->line('📊 Cache stats:');
-        $this->line('   Path: ' . $stats->path);
-        $this->line('   Rules: ' . $stats->rulesCount);
-        $this->line('   Size: ' . $stats->formattedSize());
+        $this->line('   Path: ' . $cacheStats->path);
+        $this->line('   Rules: ' . $cacheStats->rulesCount);
+        $this->line('   Size: ' . $cacheStats->formattedSize());

-        if ($stats->generationTimeMs > 0) {
-            $this->line('   Duration: ' . $stats->generationTimeMs . ' ms');
+        if ($cacheStats->generationTimeMs > 0) {
+            $this->line('   Duration: ' . $cacheStats->generationTimeMs . ' ms');
         }
     }
 }
    ----------- end diff -----------

Applied rules:
 * CatchExceptionNameMatchingTypeRector
 * RenameParamToMatchTypeRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php:10

    ---------- begin diff ----------
@@ @@
 final class InstallRosterCommand extends Command
 {
     protected $signature = 'roster:install {--force : Force publish without confirmation}';
+
     protected $description = 'Install the Roster package';

-    public function handle(RosterInstallerService $installer): int
+    public function handle(RosterInstallerService $rosterInstallerService): int
     {
-        $installer->install($this, (bool) $this->option('force'));
+        $rosterInstallerService->install($this, (bool) $this->option('force'));
         return self::SUCCESS;
     }
 }
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RenameParamToMatchTypeRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/ServiceInterface.php:87

    ---------- begin diff ----------
@@ @@
      * Set data for operations.
      *
      * @param array $data Operation data
-     * @return self
      */
     public function setData(array $data): self;

@@ @@
      * Replace all filters.
      *
      * @param array $filters New filters
-     * @return self
      */
     public function setFilters(array $filters): self;

     /**
      * Clear all filters.
-     *
-     * @return self
      */
     public function resetFilters(): self;

@@ @@
      *
      * @param string $key Filter key
      * @param mixed $value Filter value
-     * @return self
      */
     public function setFilter(string $key, mixed $value): self;

@@ @@
      * Set the schedulable entity context.
      *
      * @param Model $model Schedulable entity
-     * @return self
      */
     public function setSchedulable(Model $model): self;

@@ @@

     /**
      * Clear all contextual data (filters, data, schedulable).
-     *
-     * @return self
      */
     public function clear(): self;
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/DTOs/CacheStats.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Domain\DTOs;

+use RuntimeException;
+
 /**
  * Data Transfer Object representing cache statistics.
  *
@@ @@
      *
      * @param string $path Path to the cache file
      * @param float $generationTimeMs Optional generation time for new caches
-     * @return self
-     * @throws \RuntimeException When cache file is missing or has invalid format
+     * @throws RuntimeException When cache file is missing or has invalid format
      */
     public static function fromPath(
         string $path,
@@ @@
         float $generationTimeMs = 0.0
     ): self {
         if (! file_exists($path)) {
-            throw new \RuntimeException("Cache file not found: {$path}");
+            throw new RuntimeException('Cache file not found: ' . $path);
         }

         $rules = require $path;

         if (! is_array($rules)) {
-            throw new \RuntimeException('Invalid cache format');
+            throw new RuntimeException('Invalid cache format');
         }

         return new self(
@@ @@

         while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
             $bytes /= 1024;
-            $unitIndex++;
+            ++$unitIndex;
         }

         return round($bytes, 2) . ' ' . $units[$unitIndex];
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * PostIncDecToPreIncDecRector
 * RemoveUselessReturnTagRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/DTOs/ConflictResult.php:95

    ---------- begin diff ----------
@@ @@
      */
     public function hasScheduleConflicts(): bool
     {
-        return !empty($this->conflictingSchedules);
+        return $this->conflictingSchedules !== [];
     }

     /**
@@ @@
      */
     public function hasImpedimentConflicts(): bool
     {
-        return !empty($this->conflictingImpediments);
+        return $this->conflictingImpediments !== [];
     }

     /**
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/CacheRulesService.php:1

    ---------- begin diff ----------
@@ @@
 <?php

+declare(strict_types=1);
+
 namespace Roster\Domain\Services;

+use RuntimeException;
 use Illuminate\Console\Command;
 use Roster\Domain\DTOs\CacheStats;
 use Roster\Validation\Cache\RuleCacheGenerator;
@@ @@
 class CacheRulesService
 {
     /**
-     * @param RuleCacheGenerator $generator Service for generating rule cache files
+     * @param RuleCacheGenerator $ruleCacheGenerator Service for generating rule cache files
      */
     public function __construct(
-        private RuleCacheGenerator $generator
+        private RuleCacheGenerator $ruleCacheGenerator
     ) {}

     /**
@@ @@
      * Generate a new cache file with all validation rules.
      *
      * @return CacheStats Statistics about the generated cache
-     * @throws \RuntimeException When cache generation fails
+     * @throws RuntimeException When cache generation fails
      */
     public function generate(): CacheStats
     {
-        if (! $this->generator->generate()) {
-            throw new \RuntimeException('Cache generation failed');
+        if (! $this->ruleCacheGenerator->generate()) {
+            throw new RuntimeException('Cache generation failed');
         }

-        return CacheStats::fromPath($this->generator->getCachePath());
+        return CacheStats::fromPath($this->ruleCacheGenerator->getCachePath());
     }

     /**
@@ @@
      *
      * @param bool $force When true, regenerate cache after clearing
      * @return CacheStats|null Cache statistics if regenerated, null otherwise
-     * @throws \RuntimeException When cache clearing fails
+     * @throws RuntimeException When cache clearing fails
      */
     public function clear(bool $force = false): ?CacheStats
     {
-        if (! $this->generator->clear()) {
-            throw new \RuntimeException('Cache clear failed');
+        if (! $this->ruleCacheGenerator->clear()) {
+            throw new RuntimeException('Cache clear failed');
         }

         if ($force) {
@@ @@
      */
     public function show(): CacheStats
     {
-        $path = $this->generator->getCachePath();
+        $path = $this->ruleCacheGenerator->getCachePath();

         if (! file_exists($path)) {
             return $this->generate();
@@ @@
         $cacheFile = config('roster.cache.cache_file');

         if (! file_exists($cacheFile)) {
-            $command->error("Cache file not found: $cacheFile");
+            $command->error('Cache file not found: ' . $cacheFile);
             return;
         }
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector
 * RenamePropertyToMatchTypeRector
 * DeclareStrictTypesRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/RosterInstallerService.php:1

    ---------- begin diff ----------
@@ @@
 <?php

+declare(strict_types=1);
+
 namespace Roster\Domain\Services;

 use Illuminate\Console\Command;
    ----------- end diff -----------

Applied rules:
 * DeclareStrictTypesRector


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:60

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
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:105

    ---------- begin diff ----------
@@ @@
      *
      * @throws MissingSchedulableException When no schedulable is provided
      */
-    private function validateSchedulable(?Model $schedulable): void
+    private function validateSchedulable(?Model $model): void
     {
-        if (!$schedulable instanceof Model) {
+        if (!$model instanceof Model) {
             throw MissingSchedulableException::create();
         }
     }
@@ @@
      *
      * @throws InvalidOwnerException When owner is provided for Availability model
      */
-    private function validateOwnerForAvailability(?Model $owner): void
+    private function validateOwnerForAvailability(?Model $model): void
     {
-        if ($owner instanceof Model && $this->isAvailabilityModel()) {
+        if ($model instanceof Model && $this->isAvailabilityModel()) {
             throw InvalidOwnerException::forAvailability();
         }
     }
@@ @@
      *
      * @throws MissingOwnerException When owner is not provided for non-Availability model
      */
-    private function validateOwnerForNonAvailability(?Model $owner): void
+    private function validateOwnerForNonAvailability(?Model $model): void
     {
-        if (!$this->isAvailabilityModel() && !$owner instanceof Model) {
+        if (!$this->isAvailabilityModel() && !$model instanceof Model) {
             throw MissingOwnerException::create($this->getModelClass());
         }
     }
@@ @@
     /**
      * Apply owner constraint to a query builder for non-Availability models.
      */
-    private function applyOwnerConstraint(Builder $builder, ?Model $owner): Builder
+    private function applyOwnerConstraint(Builder $builder, ?Model $model): Builder
     {
-        if ($owner instanceof Model && !$this->isAvailabilityModel()) {
-            $builder->where('availability_id', $owner->id);
+        if ($model instanceof Model && !$this->isAvailabilityModel()) {
+            $builder->where('availability_id', $model->id);
         }

         return $builder;
@@ @@
      *
      * @throws MissingOwnerException When owner is required but not provided
      */
-    private function applyOwnerConstraintToData(array $data, ?Model $owner): array
+    private function applyOwnerConstraintToData(array $data, ?Model $model): array
     {
-        $this->validateOwnerForNonAvailability($owner);
+        $this->validateOwnerForNonAvailability($model);

-        if ($owner instanceof Model && !$this->isAvailabilityModel()) {
-            $data['availability_id'] = $owner->id;
+        if ($model instanceof Model && !$this->isAvailabilityModel()) {
+            $data['availability_id'] = $model->id;
         }

         return $data;
@@ @@
      *
      * @param array<string, mixed> $filters
      */
-    public function buildQueryWithFilters(Model $schedulable, array $filters): Builder
+    public function buildQueryWithFilters(Model $model, array $filters): Builder
     {
-        $this->validateSchedulable($schedulable);
+        $this->validateSchedulable($model);

-        $builder = $this->buildBaseQuery($schedulable);
+        $builder = $this->buildBaseQuery($model);
         return $this->applyFilters($builder, $filters);
     }

@@ @@
         /** @var Collection<int, Impediment> $sortedImpediments */
         $sortedImpediments = $impediments->sortBy('start_datetime');

-        foreach ($sortedImpediments as $impediment) {
-            $impedimentStart = $impediment->start_datetime;
-            $impedimentEnd = $impediment->end_datetime;
+        foreach ($sortedImpediments as $sortedImpediment) {
+            $impedimentStart = $sortedImpediment->start_datetime;
+            $impedimentEnd = $sortedImpediment->end_datetime;

             if ($impedimentStart->gt($currentTime)) {
                 $availableSlots->push([
@@ @@
      * - Fields containing 'end': WHERE <= value
      * - String values: WHERE LIKE pattern
      * - Other values: WHERE = value
+     * @param array<string, mixed> $filters
      */
     protected function applyFilters(Builder $builder, array $filters = []): Builder
     {
@@ @@

     /**
      * {@inheritDoc}
+     * @return array<string, mixed>
      */
     public function getFilters(): array
     {
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector
 * RenameForeachValueVariableToMatchExprVariableRector
 * DocblockGetterReturnArrayFromPropertyDocblockVarRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php:23

    ---------- begin diff ----------
@@ @@
     /**
      * Retrieves a query builder for availabilities of a specific schedulable entity.
      *
-     * @param Model $schedulable The schedulable entity (e.g., User, Team)
+     * @param Model $model The schedulable entity (e.g., User, Team)
      * @param string|null $type Optional availability type filter
      *
      * @return Builder Query builder for availabilities
      */
-    public function findForSchedulable(Model $schedulable, ?string $type = null): Builder
+    public function findForSchedulable(Model $model, ?string $type = null): Builder
     {
-        $builder = $this->buildBaseQuery($schedulable);
+        $builder = $this->buildBaseQuery($model);

         if ($type !== null) {
             $builder->where('type', $type);
@@ @@
     /**
      * Retrieves availabilities valid within a specific date range.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $start Start date of the range
      * @param Carbon $end End date of the range
      * @param string|null $type Optional availability type filter
@@ @@
      * @return Collection<int, Availability> Collection of matching availabilities
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
                 $query->whereNull('validity_start')
                     ->orWhere('validity_start', '<=', $end);
@@ @@
     /**
      * Retrieves availabilities applicable to a specific date.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $date Target date
      * @param string|null $type Optional availability type filter
      *
@@ @@
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

         if ($type !== null) {
@@ @@
     /**
      * Finds an availability for a time slot with conflict detection information.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      * @param Carbon $start Start time of the slot
      * @param Carbon $end End time of the slot
      * @param string|null $type Optional availability type filter
@@ @@
      * @return Availability|null Matching availability with conflict flags or null
      */
     public function findForTimeSlotWithConflictInfo(
-        Model $schedulable,
+        Model $model,
         Carbon $start,
         Carbon $end,
         ?string $type = null
     ): ?Availability {
-        return Availability::where('schedulable_id', $schedulable->id)
-            ->where('schedulable_type', get_class($schedulable))
+        return Availability::where('schedulable_id', $model->id)
+            ->where('schedulable_type', get_class($model))
             ->when($type !== null, function ($query) use ($type): void {
                 $query->where('type', $type);
             })
@@ @@
     /**
      * Builds a base query for availabilities of a schedulable entity.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      *
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
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php:36

    ---------- begin diff ----------
@@ @@
 {
     /**
      * Bootstrap package services.
-     *
-     * @return void
      */
     public function boot(): void
     {
@@ @@

     /**
      * Register package services and dependencies.
-     *
-     * @return void
      */
     public function register(): void
     {
@@ @@

     /**
      * Register observers for domain models.
-     *
-     * @return void
      */
     protected function registerModelObservers(): void
     {
@@ @@

     /**
      * Load package helper functions.
-     *
-     * @return void
      */
     protected function loadHelpers(): void
     {
@@ @@

     /**
      * Register repository interfaces with their implementations.
-     *
-     * @return void
      */
     protected function registerRepositories(): void
     {
@@ @@

     /**
      * Register validation system components.
-     *
-     * @return void
      */
     protected function registerValidationSystem(): void
     {
@@ @@

     /**
      * Register domain services with dependency injection container.
-     *
-     * @return void
      */
     protected function registerDomainServices(): void
     {
@@ @@

     /**
      * Publish package resources for user customization.
-     *
-     * @return void
      */
     private function publishResources(): void
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:6

    ---------- begin diff ----------
@@ @@

 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
-use Illuminate\Database\Eloquent\Model;
 use Roster\Domain\Helpers\TimeSlotHelper;
 use Roster\Domain\Helpers\TimeWindowHelper;
 use Roster\DTOs\ImpedimentData;
    ----------- end diff -----------

Applied rules:


13) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php:25

    ---------- begin diff ----------
@@ @@
 class ValidationContext implements ValidationContextInterface
 {
     private OperationType $operationType;
+
     private EntityType $entityType;

     /**
@@ @@
      */
     private array $data;

-    private ?Model $schedulable;
+    private ?Model $model;
+
     private mixed $currentEntity;

     /**
@@ @@
         OperationType $operationType,
         EntityType $entityType,
         array $data,
-        ?Model $schedulable = null,
+        ?Model $model = null,
         mixed $currentEntity = null
     ) {
         $this->operationType = $operationType;
         $this->entityType = $entityType;
         $this->data = $data;
-        $this->schedulable = $schedulable;
+        $this->model = $model;
         $this->currentEntity = $currentEntity;
     }

@@ @@
      */
     public function getSchedulable(): ?Model
     {
-        return $this->schedulable;
+        return $this->model;
     }

     /**
@@ @@
     /**
      * Build Schedule service with the appropriate context.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      *
      * @return ServiceInterface Configured Schedule service
      *
      * @throws RuntimeException When owner is required but not available
      */
-    private function buildScheduleService(Model $schedulable): ServiceInterface
+    private function buildScheduleService(Model $model): ServiceInterface
     {
         $owner = $this->resolveOwner();

@@ @@
             throw new RuntimeException('Cannot get Schedule service: owner is required but not available in validation context');
         }

-        return Schedule::for($schedulable)->owner($owner);
+        return Schedule::for($model)->owner($owner);
     }

     /**
      * Build Impediment service with the appropriate context.
      *
-     * @param Model $schedulable The schedulable entity
+     * @param Model $model The schedulable entity
      *
      * @return ServiceInterface Configured Impediment service
      *
      * @throws RuntimeException When owner is required but not available
      */
-    private function buildImpedimentService(Model $schedulable): ServiceInterface
+    private function buildImpedimentService(Model $model): ServiceInterface
     {
         $owner = $this->resolveOwner();

@@ @@
             throw new RuntimeException('Cannot get Impediment service: owner is required but not available in validation context');
         }

-        return Impediment::for($schedulable)->owner($owner);
+        return Impediment::for($model)->owner($owner);
     }

     /**
@@ @@
      */
     private function resolveOwner(): ?Model
     {
-        if (isset($this->data['availability_id']) && $this->schedulable instanceof Model) {
+        if (isset($this->data['availability_id']) && $this->model instanceof Model) {
             try {
                 return \Roster\Models\Availability::find($this->data['availability_id']);
             } catch (Exception $e) {
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RenameParamToMatchTypeRector
 * RenamePropertyToMatchTypeRector


14) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php:180

    ---------- begin diff ----------
@@ @@

         $rules = [];

-        foreach ($this->directories as $ruleDirectory) {
-            if (!is_dir($ruleDirectory)) {
+        foreach ($this->directories as $directory) {
+            if (!is_dir($directory)) {
                 continue;
             }

             $finder = new Finder();
-            $finder->files()->in($ruleDirectory)->name('*Rule.php');
+            $finder->files()->in($directory)->name('*Rule.php');

             foreach ($finder as $file) {
                 $className = $this->extractClassNameFromFile($file->getPathname());
@@ @@
                     if ($reflection->implementsInterface(RuleInterface::class)) {
                         $validationRule = $this->extractValidationRule($className, $reflection);

-                        if ($validationRule !== null) {
+                        if ($validationRule instanceof ValidationRule) {
                             $rules[$className] = $validationRule;
                         }
                     }
@@ @@
      * Extracts the ValidationRule attribute from a rule class.
      *
      * @param string $className The fully qualified class name
-     * @param ReflectionClass $reflection Reflection of the class
+     * @param ReflectionClass $reflectionClass Reflection of the class
      *
      * @return ValidationRule|null The validation rule attribute, or null if not found
      */
-    private function extractValidationRule(string $className, ReflectionClass $reflection): ?ValidationRule
+    private function extractValidationRule(string $className, ReflectionClass $reflectionClass): ?ValidationRule
     {
         try {
             $ruleInstance = app()->make($className);
@@ @@
             if ($attribute) {
                 return $attribute;
             }
-        } catch (Throwable $e) {
-            $attributes = $reflection->getAttributes(ValidationRule::class);
+        } catch (Throwable $throwable) {
+            $attributes = $reflectionClass->getAttributes(ValidationRule::class);

-            if (!empty($attributes)) {
+            if ($attributes !== []) {
                 return $attributes[0]->newInstance();
             }
         }
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * FlipTypeControlToUseExclusiveTypeRector
 * CatchExceptionNameMatchingTypeRector
 * RenameParamToMatchTypeRector
 * RenameForeachValueVariableToMatchExprVariableRector


15) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDateRangeRule.php:43

    ---------- begin diff ----------
@@ @@
      * Validates validity start and end dates.
      *
      * @param ValidationContextInterface $validationContext The validation context
-     * @param Model|null $entity The entity being validated
+     * @param Model|null $model The entity being validated
      * @param OperationType $operationType The operation type
      */
     private function validateValidityDates(
         ValidationContextInterface $validationContext,
-        ?Model $entity,
+        ?Model $model,
         OperationType $operationType
     ): void {
         if ($operationType === OperationType::CREATE) {
             $this->validateCreateValidityDates($validationContext);
         } else {
-            $this->validateUpdateValidityDates($validationContext, $entity);
+            $this->validateUpdateValidityDates($validationContext, $model);
         }
     }

@@ @@
      * Validates validity dates for UPDATE operations.
      *
      * @param ValidationContextInterface $validationContext The validation context
-     * @param Model|null $entity The entity being validated
+     * @param Model|null $model The entity being validated
      */
     private function validateUpdateValidityDates(
         ValidationContextInterface $validationContext,
-        ?Model $entity
+        ?Model $model
     ): void {
         $hasStart = $validationContext->has('validity_start');
         $hasEnd = $validationContext->has('validity_end');
@@ @@

         $startValue = $hasStart
             ? $validationContext->get('validity_start')
-            : ($entity?->validity_start ?? null);
+            : ($model?->validity_start ?? null);

         $endValue = $hasEnd
             ? $validationContext->get('validity_end')
-            : ($entity?->validity_end ?? null);
+            : ($model?->validity_end ?? null);

         $this->validateDateRange(
             validationContext: $validationContext,
@@ @@
      * Validates daily start and end times.
      *
      * @param ValidationContextInterface $validationContext The validation context
-     * @param Model|null $entity The entity being validated
+     * @param Model|null $model The entity being validated
      * @param OperationType $operationType The operation type
      */
     private function validateDailyTimes(
         ValidationContextInterface $validationContext,
-        ?Model $entity,
+        ?Model $model,
         OperationType $operationType
     ): void {
         if ($operationType === OperationType::CREATE) {
             $this->validateCreateDailyTimes($validationContext);
         } else {
-            $this->validateUpdateDailyTimes($validationContext, $entity);
+            $this->validateUpdateDailyTimes($validationContext, $model);
         }
     }

@@ @@
      * Validates daily times for UPDATE operations.
      *
      * @param ValidationContextInterface $validationContext The validation context
-     * @param Model|null $entity The entity being validated
+     * @param Model|null $model The entity being validated
      */
     private function validateUpdateDailyTimes(
         ValidationContextInterface $validationContext,
-        ?Model $entity
+        ?Model $model
     ): void {
         $hasStart = $validationContext->has('daily_start');
         $hasEnd = $validationContext->has('daily_end');
@@ @@

         $startValue = $hasStart
             ? $validationContext->get('daily_start')
-            : ($entity?->daily_start ?? null);
+            : ($model?->daily_start ?? null);

         $endValue = $hasEnd
             ? $validationContext->get('daily_end')
-            : ($entity?->daily_end ?? null);
+            : ($model?->daily_end ?? null);

         $this->validateTimeRange(
             validationContext: $validationContext,
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


16) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php:77

    ---------- begin diff ----------
@@ @@

         $this->sortRulesByPriority($applicableRules);

-        foreach ($applicableRules as $rule) {
+        foreach ($applicableRules as $applicableRule) {
             try {
-                $rule->validate($validationContext);
+                $applicableRule->validate($validationContext);
             } catch (Throwable $exception) {
-                $this->handleRuleException($validationContext, $rule, $exception);
+                $this->handleRuleException($validationContext, $applicableRule, $exception);
             }
         }

@@ @@
      */
     public function hasRule(string $ruleClass): bool
     {
-        foreach ($this->allRules as $rule) {
-            if (get_class($rule) === $ruleClass) {
+        foreach ($this->allRules as $allRule) {
+            if (get_class($allRule) === $ruleClass) {
                 return true;
             }
         }
@@ @@
      *
      * @param ValidationContextInterface $validationContext Current validation context
      * @param RuleInterface $rule The rule that failed
-     * @param Throwable $exception The thrown exception
+     * @param Throwable $throwable The thrown exception
      */
     private function handleRuleException(
         ValidationContextInterface $validationContext,
         RuleInterface $rule,
-        Throwable $exception
+        Throwable $throwable
     ): void {
         $validationContext->setViolation(
             '_system',
@@ @@
             sprintf(
                 'Validation rule %s failed: %s',
                 $rule->getName(),
-                $exception->getMessage()
+                $throwable->getMessage()
             )
         );
     }
@@ @@
      * Indexes a rule using its ValidationRule attribute metadata.
      *
      * @param RuleInterface $rule The rule to index
-     * @param ValidationRule $attribute The validation rule attribute
+     * @param ValidationRule $validationRule The validation rule attribute
      */
-    private function indexRuleByAttribute(RuleInterface $rule, ValidationRule $attribute): void
+    private function indexRuleByAttribute(RuleInterface $rule, ValidationRule $validationRule): void
     {
-        foreach ($attribute->entities as $entity) {
+        foreach ($validationRule->entities as $entity) {
             if (!$entity instanceof EntityType) {
                 continue;
             }

-            foreach ($attribute->operations as $operation) {
+            foreach ($validationRule->operations as $operation) {
                 if (!$operation instanceof OperationType) {
                     continue;
                 }
@@ @@
      * Adds a rule to the index for quick lookup.
      *
      * @param RuleInterface $rule The rule to add
-     * @param OperationType $operation The operation type
-     * @param EntityType $entity The entity type
+     * @param OperationType $operationType The operation type
+     * @param EntityType $entityType The entity type
      */
     private function registerRuleToIndex(
         RuleInterface $rule,
-        OperationType $operation,
-        EntityType $entity
+        OperationType $operationType,
+        EntityType $entityType
     ): void {
-        $key = $this->createCacheKey($operation, $entity);
+        $key = $this->createCacheKey($operationType, $entityType);

         if (!isset($this->rulesByEntityOperation[$key])) {
             $this->rulesByEntityOperation[$key] = [];
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector
 * RenameForeachValueVariableToMatchExprVariableRector


17) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/CacheRulesCommandTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Commands;

-use Illuminate\Foundation\Testing\RefreshDatabase;
+use RuntimeException;
 use Illuminate\Support\Facades\File;
 use Mockery;
 use Roster\Commands\CacheRulesCommand;
@@ @@

     public function test_command_can_be_instantiated(): void
     {
-        $command = new CacheRulesCommand;
+        $cacheRulesCommand = new CacheRulesCommand;

-        $this->assertSame('roster:cache-rules', $command->getName());
-        $this->assertSame('Manage Roster validation rules cache', $command->getDescription());
+        $this->assertSame('roster:cache-rules', $cacheRulesCommand->getName());
+        $this->assertSame('Manage Roster validation rules cache', $cacheRulesCommand->getDescription());

-        $definition = $command->getDefinition();
-        $this->assertTrue($definition->hasOption('clear'));
-        $this->assertTrue($definition->hasOption('force'));
-        $this->assertTrue($definition->hasOption('show'));
-        $this->assertTrue($definition->hasOption('list'));
+        $inputDefinition = $cacheRulesCommand->getDefinition();
+        $this->assertTrue($inputDefinition->hasOption('clear'));
+        $this->assertTrue($inputDefinition->hasOption('force'));
+        $this->assertTrue($inputDefinition->hasOption('show'));
+        $this->assertTrue($inputDefinition->hasOption('list'));
     }

     public function test_command_generates_cache_successfully(): void
     {
-        $mockStats = new CacheStats(
+        $cacheStats = new CacheStats(
             path: $this->cacheFilePath,
             rulesCount: 1,
             sizeBytes: 123,
@@ @@
         );

         $mockService = Mockery::mock(CacheRulesService::class);
-        $mockService->shouldReceive('generate')->once()->andReturn($mockStats);
+        $mockService->shouldReceive('generate')->once()->andReturn($cacheStats);

         $this->app->instance(CacheRulesService::class, $mockService);

@@ @@

     public function test_command_clears_and_regenerates_with_force(): void
     {
-        $mockStats = new CacheStats(
+        $cacheStats = new CacheStats(
             path: $this->cacheFilePath,
             rulesCount: 1,
             sizeBytes: 123,
@@ @@
         );

         $mockService = Mockery::mock(CacheRulesService::class);
-        $mockService->shouldReceive('clear')->once()->andReturn($mockStats);
+        $mockService->shouldReceive('clear')->once()->andReturn($cacheStats);

         $this->app->instance(CacheRulesService::class, $mockService);

@@ @@

     public function test_command_shows_cache_successfully(): void
     {
-        $mockStats = new CacheStats(
+        $cacheStats = new CacheStats(
             path: $this->cacheFilePath,
             rulesCount: 2,
             sizeBytes: 456,
@@ @@
         );

         $mockService = Mockery::mock(CacheRulesService::class);
-        $mockService->shouldReceive('show')->once()->andReturn($mockStats);
+        $mockService->shouldReceive('show')->once()->andReturn($cacheStats);

         $this->app->instance(CacheRulesService::class, $mockService);

@@ @@

         File::put($this->cacheFilePath, '<?php return ' . var_export($rules, true) . ';');

-        $mockService = Mockery::mock(CacheRulesService::class)->makePartial();
-        $mockService->shouldAllowMockingProtectedMethods();
+        $legacyMock = Mockery::mock(CacheRulesService::class)->makePartial();
+        $legacyMock->shouldAllowMockingProtectedMethods();

-        $this->app->instance(CacheRulesService::class, $mockService);
+        $this->app->instance(CacheRulesService::class, $legacyMock);

         $this->artisan('roster:cache-rules', ['--list' => true])
             ->expectsTable(
@@ @@
     public function test_command_fails_when_generation_fails(): void
     {
         $mockService = Mockery::mock(CacheRulesService::class);
-        $mockService->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Cache generation failed'));
+        $mockService->shouldReceive('generate')->once()->andThrow(new RuntimeException('Cache generation failed'));

         $this->app->instance(CacheRulesService::class, $mockService);

@@ @@
     public function test_command_fails_when_clear_fails(): void
     {
         $mockService = Mockery::mock(CacheRulesService::class);
-        $mockService->shouldReceive('clear')->once()->andThrow(new \RuntimeException('Cache clear failed'));
+        $mockService->shouldReceive('clear')->once()->andThrow(new RuntimeException('Cache clear failed'));

         $this->app->instance(CacheRulesService::class, $mockService);
    ----------- end diff -----------

Applied rules:
 * RenameVariableToMatchMethodCallReturnTypeRector
 * RenameVariableToMatchNewTypeRector


18) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/InstallRosterCommandTest.php:13

    ---------- begin diff ----------
@@ @@
 {
     public function test_command_can_be_instantiated(): void
     {
-        $command = new InstallRosterCommand;
-        $this->assertSame('roster:install', $command->getName());
-        $this->assertSame('Install the Roster package', $command->getDescription());
+        $installRosterCommand = new InstallRosterCommand;
+        $this->assertSame('roster:install', $installRosterCommand->getName());
+        $this->assertSame('Install the Roster package', $installRosterCommand->getDescription());
     }

     public function test_handle_calls_installer_service_with_force_option(): void
@@ @@
         $mockService = Mockery::mock(RosterInstallerService::class);
         $mockService->shouldReceive('install')
             ->once()
-            ->withArgs(function ($command, $force) {
+            ->withArgs(function ($command, $force): bool {
                 return $command instanceof InstallRosterCommand && $force === true;
             });

-        $command = new InstallRosterCommand;
+        new InstallRosterCommand;
         $this->app->instance(RosterInstallerService::class, $mockService);

         $this->artisan('roster:install', ['--force' => true])
@@ @@
         $mockService = Mockery::mock(RosterInstallerService::class);
         $mockService->shouldReceive('install')
             ->once()
-            ->withArgs(function ($command, $force) {
+            ->withArgs(function ($command, $force): bool {
                 return $command instanceof InstallRosterCommand && $force === false;
             });

-        $command = new InstallRosterCommand;
+        new InstallRosterCommand;
         $this->app->instance(RosterInstallerService::class, $mockService);

         $this->artisan('roster:install')
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedVariableAssignRector
 * RenameVariableToMatchNewTypeRector
 * ClosureReturnTypeRector


 [OK] 18 files would have been changed (dry-run) by Rector                                                              

