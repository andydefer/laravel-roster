# Rector Refactoring Report
*Generated: sam. 27 déc. 2025 13:10:51 WAT*


41 files with changes
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


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php:46

    ---------- begin diff ----------
@@ @@
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
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/ServiceInterface.php:87

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


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AbstractData.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\DTOs;

-use Illuminate\Database\Eloquent\Model;
+use InvalidArgumentException;
 use Illuminate\Support\Carbon;

 /**
@@ @@
      * @param string|Carbon|null $datetime Datetime input to parse
      * @return Carbon|null Parsed Carbon instance or null
      *
-     * @throws \InvalidArgumentException If the input is not null, string, or Carbon
+     * @throws InvalidArgumentException If the input is not null, string, or Carbon
      */
     final protected static function parseDateTime(string|Carbon|null $datetime): ?Carbon
     {
@@ @@
             $datetime === null => null,
             $datetime instanceof Carbon => $datetime,
             is_string($datetime) => Carbon::parse($datetime),
-            default => throw new \InvalidArgumentException(
+            default => throw new InvalidArgumentException(
                 'Datetime must be null, string or instance of Carbon'
             ),
         };
    ----------- end diff -----------

Applied rules:


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\DTOs;

+use Exception;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Roster\Domain\Helpers\TimeSlotHelper;
-use Roster\Enums\DaysOfWeek;
 use Roster\Models\Availability;
 use Roster\Support\RosterMutationContext;

@@ @@
  */
 class AvailabilityData extends AbstractData
 {
-    private ?Availability $existingEntity = null;
+    private ?Availability $availability = null;

     /**
      * @param int|null $id Unique identifier of the availability
@@ @@
     /**
      * Creates an AvailabilityData instance from an Availability Eloquent model.
      *
-     * @param Availability $availability Eloquent model instance
+     * @param Availability $model Eloquent model instance
      * @return self New immutable AvailabilityData instance
      */
-    public static function fromModel(Model $availability): self
+    public static function fromModel(Model $model): self
     {
         return new self(
-            id: $availability->id,
-            type: $availability->type,
-            days: $availability->days,
-            validityStart: $availability->validity_start,
-            validityEnd: $availability->validity_end,
-            dailyStart: $availability->daily_start,
-            dailyEnd: $availability->daily_end,
-            schedulableId: $availability->schedulable_id,
-            schedulableType: $availability->schedulable_type
+            id: $model->id,
+            type: $model->type,
+            days: $model->days,
+            validityStart: $model->validity_start,
+            validityEnd: $model->validity_end,
+            dailyStart: $model->daily_start,
+            dailyEnd: $model->daily_end,
+            schedulableId: $model->schedulable_id,
+            schedulableType: $model->schedulable_type
         );
     }

@@ @@
         $data = $this->toArray();
         $data['days'] = $days;

-        return static::fromArray($data);
+        return self::fromArray($data);
     }

     /**
@@ @@
     private function getAdjustedDays(): array
     {
         // Déterminer si c'est une mise à jour (entité existante chargée)
-        $isUpdate = $this->existingEntity !== null;
+        $isUpdate = $this->availability !== null;

         // Récupérer les données existantes si disponible
-        $existingDays = $this->existingEntity?->days;
-        $existingValidityStart = $this->existingEntity?->validity_start;
-        $existingValidityEnd = $this->existingEntity?->validity_end;
+        $existingDays = $this->availability?->days;
+        $existingValidityStart = $this->availability?->validity_start;
+        $existingValidityEnd = $this->availability?->validity_end;

         // Utiliser TimeSlotHelper pour le calcul des jours ajustés
         return TimeSlotHelper::getAdjustedDays(
@@ @@

         try {
             // Utiliser le contexte de mutation pour charger l'entité
-            $this->existingEntity = RosterMutationContext::allow(function () {
+            $this->availability = RosterMutationContext::allow(function () {
                 return Availability::find($this->id);
             });
-        } catch (\Exception) {
-            $this->existingEntity = null;
+        } catch (Exception) {
+            $this->availability = null;
         }
     }

@@ @@
      */
     public function isUpdateOperation(): bool
     {
-        return $this->existingEntity !== null;
+        return $this->availability !== null;
     }

     /**
@@ @@
      */
     public function getExistingEntity(): ?Availability
     {
-        return $this->existingEntity;
+        return $this->availability;
     }
 }
    ----------- end diff -----------

Applied rules:
 * ConvertStaticToSelfRector
 * RenameParamToMatchTypeRector
 * RenamePropertyToMatchTypeRector


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/DataInterface.php:5

    ---------- begin diff ----------
@@ @@
 namespace Roster\DTOs;

 use Illuminate\Database\Eloquent\Model;
-use Illuminate\Support\Carbon;

 /**
  * Interface for all Data Transfer Objects.
    ----------- end diff -----------

Applied rules:


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php:69

    ---------- begin diff ----------
@@ @@
     /**
      * Create an ImpedimentData instance from an Impediment Eloquent model.
      *
-     * @param Impediment $impediment Eloquent model instance
+     * @param Impediment $model Eloquent model instance
      * @return self New immutable ImpedimentData instance
      */
-    public static function fromModel(Model $impediment): self
+    public static function fromModel(Model $model): self
     {
         return new self(
-            id: $impediment->id,
-            availabilityId: $impediment->availability_id,
-            startDatetime: self::parseDateTime($impediment->start_datetime),
-            endDatetime: self::parseDateTime($impediment->end_datetime),
-            reason: $impediment->reason,
-            metadata: $impediment->metadata ?? [],
-            schedulableId: $impediment->schedulable_id,
-            schedulableType: $impediment->schedulable_type
+            id: $model->id,
+            availabilityId: $model->availability_id,
+            startDatetime: self::parseDateTime($model->start_datetime),
+            endDatetime: self::parseDateTime($model->end_datetime),
+            reason: $model->reason,
+            metadata: $model->metadata ?? [],
+            schedulableId: $model->schedulable_id,
+            schedulableType: $model->schedulable_type
         );
     }
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


9) /home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php:78

    ---------- begin diff ----------
@@ @@
     /**
      * Create a ScheduleData instance from a Schedule Eloquent model.
      *
-     * @param Schedule $schedule Eloquent model instance
+     * @param Schedule $model Eloquent model instance
      * @return self New immutable ScheduleData instance
      */
-    public static function fromModel(Model $schedule): self
+    public static function fromModel(Model $model): self
     {
         return new self(
-            id: $schedule->id,
-            availabilityId: $schedule->availability_id,
-            title: $schedule->title,
-            description: $schedule->description,
-            startDatetime: self::parseDateTime($schedule->start_datetime),
-            endDatetime: self::parseDateTime($schedule->end_datetime),
-            metadata: $schedule->metadata ?? [],
-            status: $schedule->status,
-            schedulableId: $schedule->schedulable_id,
-            schedulableType: $schedule->schedulable_type
+            id: $model->id,
+            availabilityId: $model->availability_id,
+            title: $model->title,
+            description: $model->description,
+            startDatetime: self::parseDateTime($model->start_datetime),
+            endDatetime: self::parseDateTime($model->end_datetime),
+            metadata: $model->metadata ?? [],
+            status: $model->status,
+            schedulableId: $model->schedulable_id,
+            schedulableType: $model->schedulable_type
         );
     }
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


10) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/DTOs/CacheStats.php:4

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


11) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/DTOs/ConflictResult.php:95

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


12) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Helpers/TimeSlotHelper.php:256

    ---------- begin diff ----------
@@ @@
         // 3️⃣ Auto-adjust days from period
         return roster_days_in_period($validityStart, $validityEnd);
     }
-
-    /**
-     * Determine whether automatic adjustment of days should be performed.
-     *
-     * @param Carbon|null $start Validity start date
-     * @param Carbon|null $end Validity end date
-     * @return bool True if auto-adjustment should occur
-     */
-    private static function shouldAutoAdjustDays(?Carbon $start, ?Carbon $end): bool
-    {
-        return $start instanceof Carbon
-            && $end instanceof Carbon
-            && roster_should_auto_adjust_days($start, $end);
-    }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedPrivateMethodRector


13) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/CacheRulesService.php:1

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


14) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/RosterInstallerService.php:1

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


15) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/TemporalConflictService.php:60

    ---------- begin diff ----------
@@ @@
             return ConflictResult::noConflict();
         }

-        $firstConflict = $conflictingAvailabilities->first();
+        $availability = $conflictingAvailabilities->first();
         return new ConflictResult(
             hasConflicts: true,
             conflictingSchedules: [],
             conflictingImpediments: [],
-            message: $this->generateAvailabilityConflictMessage($firstConflict)
+            message: $this->generateAvailabilityConflictMessage($availability)
         );
     }

@@ @@
         ?int $excludeScheduleId = null,
         ?int $excludeImpedimentId = null
     ): ConflictResult {
-        $scheduleConflict = $this->checkScheduleConflicts(
+        $conflictResult = $this->checkScheduleConflicts(
             availabilityId: $availabilityId,
             start: $start,
             end: $end,
@@ @@
             excludeScheduleId: $excludeScheduleId
         );

-        if ($scheduleConflict->hasConflicts) {
-            return $scheduleConflict;
+        if ($conflictResult->hasConflicts) {
+            return $conflictResult;
         }

         $impedimentConflict = $this->checkImpedimentConflicts(
@@ @@
      *     validityEnd: Carbon|null,
      *     type: string|null
      * } $period
-     * @return bool
      */
     private function isValidAvailabilityPeriod(array $period): bool
     {
    ----------- end diff -----------

Applied rules:
 * RemoveUselessReturnTagRector
 * RenameVariableToMatchMethodCallReturnTypeRector


16) /home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/DirectServiceUsageException.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Exceptions;

+use RuntimeException;
+
 /**
  * Exception lancée lorsqu'un service est utilisé directement sans passer par un helper.
  */
-final class DirectServiceUsageException extends \RuntimeException
+final class DirectServiceUsageException extends RuntimeException
 {
     public static function create(string $serviceClass): self
     {
    ----------- end diff -----------

Applied rules:


17) /home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/InvalidServiceContextException.php:20

    ---------- begin diff ----------
@@ @@

         $message = match (true) {
             str_contains($serviceClass, 'AvailabilityService') =>
-            "{$serviceName} requires a valid schedulable context.\n\n" .
+            $serviceName . ' requires a valid schedulable context.
+
+' .
                 "This usually happens because you are calling the service without providing a schedulable model.\n\n" .
                 "How to fix:\n" .
                 "- Always use the availability_for() helper with a schedulable model.\n" .
@@ @@
                 "availability_for(\$schedulable)->create([...]);",

             str_contains($serviceClass, 'ScheduleService') || str_contains($serviceClass, 'ImpedimentService') =>
-            "{$serviceName} requires both schedulable and owner context.\n\n" .
+            $serviceName . ' requires both schedulable and owner context.
+
+' .
                 "This usually happens because:\n" .
                 "1. You are calling the service without providing an availability as owner.\n" .
                 "2. You are using the wrong helper function.\n\n" .
@@ @@
                 "impediment_for(\$availability)->create([...]);",

             default =>
-            "{$serviceName} requires a valid context.\n\n" .
+            $serviceName . ' requires a valid context.
+
+' .
                 "This usually happens because you are using the service incorrectly.\n\n" .
                 "How to fix:\n" .
                 "- Use the appropriate helper function instead of instantiating the service directly.\n" .
    ----------- end diff -----------

Applied rules:
 * EncapsedStringsToSprintfRector


18) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:60

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


19) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:105

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
 * DocblockGetterReturnArrayFromPropertyDocblockVarRector
 * ClassMethodArrayDocblockParamFromLocalCallsRector


20) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php:23

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


21) /home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php:36

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


22) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

-use Roster\DTOs\AvailabilityData;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Models\Availability;
    ----------- end diff -----------

Applied rules:


23) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services\Core;

+use Roster\DTOs\AvailabilityData;
+use Roster\DTOs\ScheduleData;
+use Roster\DTOs\ImpedimentData;
 use BadMethodCallException;
 use Illuminate\Contracts\Pagination\LengthAwarePaginator;
 use Illuminate\Database\Eloquent\Model;
@@ @@

     /**
      * Active filters for query operations.
+     * @var mixed[]
      */
     protected array $filters = [];

     /**
      * Current operation data.
+     * @var mixed[]
      */
     protected array $data = [];

@@ @@
     final protected function createDTOFromArray(array $data, OperationType $operationType): mixed
     {
         return match ($this->getEntityTypeEnum()) {
-            EntityType::AVAILABILITY => \Roster\DTOs\AvailabilityData::fromArray($data),
-            EntityType::SCHEDULE => \Roster\DTOs\ScheduleData::fromArray($data),
-            EntityType::IMPEDIMENT => \Roster\DTOs\ImpedimentData::fromArray($data),
+            EntityType::AVAILABILITY => AvailabilityData::fromArray($data),
+            EntityType::SCHEDULE => ScheduleData::fromArray($data),
+            EntityType::IMPEDIMENT => ImpedimentData::fromArray($data),
             default => throw new LogicException('Unsupported entity type for DTO creation')
         };
     }
@@ @@
     /**
      * Gets current operation data.
      *
-     * @return array Current data
+     * @return mixed[] Current data
      */
     public function getData(): array
     {
@@ @@
     /**
      * Gets active filters.
      *
-     * @return array Current filters
+     * @return mixed[] Current filters
      */
     public function getFilters(): array
     {
    ----------- end diff -----------

Applied rules:
 * DocblockGetterReturnArrayFromPropertyDocblockVarRector
 * DocblockVarArrayFromGetterReturnRector


24) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Services;

-use Illuminate\Database\Eloquent\Model;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Roster\Domain\Helpers\TimeSlotHelper;
 use Roster\Domain\Helpers\TimeWindowHelper;
-use Roster\DTOs\ImpedimentData;
 use Roster\Enums\EntityType;
 use Roster\Enums\OperationType;
 use Roster\Models\Availability;
    ----------- end diff -----------

Applied rules:


25) /home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php:7

    ---------- begin diff ----------
@@ @@
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
 use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
-use Roster\DTOs\ScheduleData;
 use Roster\Enums\EntityType;
-use Roster\Enums\OperationType;
 use Roster\Models\Availability;
 use Roster\Services\Core\AbstractService;
-use Roster\Validation\Exceptions\ValidationFailedException;

 /**
  * Service for managing Schedule entities and time slot availability.
@@ @@
             availabilityEnd: $availabilityEnd
         );

-        if ($slotStart === null) {
+        if (!$slotStart instanceof Carbon) {
             return null;
         }

@@ @@
         Carbon $availabilityStart,
         Carbon $availabilityEnd
     ): ?Carbon {
-        if ($searchStart === null || !$searchStart->isSameDay($day)) {
+        if (!$searchStart instanceof Carbon || !$searchStart->isSameDay($day)) {
             return $availabilityStart->copy();
         }
    ----------- end diff -----------

Applied rules:
 * FlipTypeControlToUseExclusiveTypeRector


26) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php:33

    ---------- begin diff ----------
@@ @@
      */
     private array $data;

-    private ?Model $schedulable;
+    private ?Model $model;

     private mixed $currentEntity;

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

         return match ($this->getEntityType()) {
             EntityType::AVAILABILITY => availability_for($schedulable),
-            EntityType::SCHEDULE => $this->buildScheduleService($schedulable),
-            EntityType::IMPEDIMENT => $this->buildImpedimentService($schedulable),
+            EntityType::SCHEDULE => $this->buildScheduleService(),
+            EntityType::IMPEDIMENT => $this->buildImpedimentService(),
         };
     }

@@ @@
     /**
      * Build Schedule service with the appropriate context.
      *
-     * @param Model $schedulable The schedulable entity
      *
      * @return ServiceInterface Configured Schedule service
-     *
      * @throws RuntimeException When owner is required but not available
      */
-    private function buildScheduleService(Model $schedulable): ServiceInterface
+    private function buildScheduleService(): ServiceInterface
     {
         $owner = $this->resolveOwner();
-
         if (!$owner instanceof Model) {
             throw new RuntimeException(
                 'Cannot get Schedule service: owner is required but not available in validation context'
             );
         }
-
         return schedule_for($owner);
     }

@@ @@
     /**
      * Build Impediment service with the appropriate context.
      *
-     * @param Model $schedulable The schedulable entity
      *
      * @return ServiceInterface Configured Impediment service
-     *
      * @throws RuntimeException When owner is required but not available
      */
-    private function buildImpedimentService(Model $schedulable): ServiceInterface
+    private function buildImpedimentService(): ServiceInterface
     {
         $owner = $this->resolveOwner();
-
         if (!$owner instanceof Model) {
             throw new RuntimeException(
                 'Cannot get Impediment service: owner is required but not available in validation context'
             );
         }
-
         return impediment_for($owner);
     }

@@ @@
      */
     private function resolveOwner(): ?Model
     {
-        if (isset($this->data['availability_id']) && $this->schedulable instanceof Model) {
+        if (isset($this->data['availability_id']) && $this->model instanceof Model) {
             try {
                 return AvailabilityModel::find($this->data['availability_id']);
             } catch (Exception $exception) {
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedPrivateMethodParameterRector
 * RenameParamToMatchTypeRector
 * RenamePropertyToMatchTypeRector


27) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php:180

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


28) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDateRangeRule.php:43

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


29) /home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php:77

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


30) /home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php:6

    ---------- begin diff ----------
@@ @@
  * Collection of helper functions for the Roster package.
  * Provides date and day-related utilities and service instantiation helpers.
  */
-
+use Roster\Services\AvailabilityService;
+use Roster\Services\ImpedimentService;
+use Roster\Services\ScheduleService;
 use Carbon\Carbon;
 use Carbon\WeekDay;
 use Carbon\Month;
@@ @@
      */
     function roster_format_period_days_for_display(array $days): string
     {
-        if (empty($days)) {
+        if ($days === []) {
             return '';
         }

@@ @@
         $dayIndices = array_map(fn($day): false|int => array_search($day, $dayOrder, true), $days);

         $isContinuousSequence = true;
-        for ($i = 0; $i < count($dayIndices) - 1; $i++) {
+        for ($i = 0; $i < count($dayIndices) - 1; ++$i) {
             $currentIndex = $dayIndices[$i];
             $nextIndex = $dayIndices[$i + 1];

@@ @@
      */
     function roster_format_days_for_display(array $days): string
     {
-        if (empty($days)) {
+        if ($days === []) {
             return '';
         }

@@ @@
     /**
      * Creates an Availability service instance for a given schedulable model.
      *
-     * @param Model $schedulable The schedulable model instance
-     * @return \Roster\Services\AvailabilityService
+     * @param Model $model The schedulable model instance
      * @throws BindingResolutionException If the service cannot be resolved from the container
      */
-    function availability_for(Model $schedulable): \Roster\Services\AvailabilityService
+    function availability_for(Model $model): AvailabilityService
     {
-        return RosterServiceContext::allowViaHelper(function () use ($schedulable) {
-            /** @var \Roster\Services\AvailabilityService $service */
+        return RosterServiceContext::allowViaHelper(function () use ($model) {
+            /** @var AvailabilityService $service */
             $service = app('roster.availability');
-            return $service->for($schedulable);
+            return $service->for($model);
         });
     }
 }
@@ @@
      * Creates an Impediment service instance for a given availability.
      * Automatically extracts the schedulable from the availability's polymorphic relationship.
      *
-     * @param ModelsAvailability $availability The availability model instance
-     * @return \Roster\Services\ImpedimentService
-     * @throws \InvalidArgumentException If the availability has no schedulable relationship
+     * @param ModelsAvailability $modelsAvailability The availability model instance
+     * @throws InvalidArgumentException If the availability has no schedulable relationship
      * @throws BindingResolutionException If the service cannot be resolved from the container
      */
-    function impediment_for(ModelsAvailability $availability): \Roster\Services\ImpedimentService
+    function impediment_for(ModelsAvailability $modelsAvailability): ImpedimentService
     {
-        return RosterServiceContext::allowViaHelper(function () use ($availability) {
-            $schedulable = $availability->schedulable;
+        return RosterServiceContext::allowViaHelper(function () use ($modelsAvailability) {
+            $schedulable = $modelsAvailability->schedulable;

             if (!$schedulable) {
-                throw new \InvalidArgumentException(
+                throw new InvalidArgumentException(
                     'The provided availability does not have a schedulable relationship.'
                 );
             }

-            /** @var \Roster\Services\ImpedimentService $service */
+            /** @var ImpedimentService $service */
             $service = app('roster.impediment');
-            return $service->for($schedulable)->owner($availability);
+            return $service->for($schedulable)->owner($modelsAvailability);
         });
     }
 }
@@ @@
      * Creates a Schedule service instance for a given availability.
      * Automatically extracts the schedulable from the availability's polymorphic relationship.
      *
-     * @param ModelsAvailability $availability The availability model instance
-     * @return \Roster\Services\ScheduleService
-     * @throws \InvalidArgumentException If the availability has no schedulable relationship
+     * @param ModelsAvailability $modelsAvailability The availability model instance
+     * @throws InvalidArgumentException If the availability has no schedulable relationship
      * @throws BindingResolutionException If the service cannot be resolved from the container
      */
-    function schedule_for(ModelsAvailability $availability): \Roster\Services\ScheduleService
+    function schedule_for(ModelsAvailability $modelsAvailability): ScheduleService
     {
-        return RosterServiceContext::allowViaHelper(function () use ($availability) {
-            $schedulable = $availability->schedulable;
+        return RosterServiceContext::allowViaHelper(function () use ($modelsAvailability) {
+            $schedulable = $modelsAvailability->schedulable;

             if (!$schedulable) {
-                throw new \InvalidArgumentException(
+                throw new InvalidArgumentException(
                     'The provided availability does not have a schedulable relationship.'
                 );
             }

-            /** @var \Roster\Services\ScheduleService $service */
+            /** @var ScheduleService $service */
             $service = app('roster.schedule');
-            return $service->for($schedulable)->owner($availability);
+            return $service->for($schedulable)->owner($modelsAvailability);
         });
     }
 }
    ----------- end diff -----------

Applied rules:
 * SimplifyEmptyCheckOnEmptyArrayRector
 * PostIncDecToPreIncDecRector
 * RemoveUselessReturnTagRector
 * RenameParamToMatchTypeRector


31) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php:6

    ---------- begin diff ----------
@@ @@

 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
-use Illuminate\Support\Carbon;
 use Roster\Enums\DaysOfWeek;
-use Roster\Models\Availability as AvailabilityModel;
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\Support\TestSchedulable;
 use Tests\TestCase;
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up test environment.
@@ @@
     {
         parent::setUp();

-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
     }

     /**
@@ @@
         $validityEnd = '2038-07-04';

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $providedDays = ['thursday', 'friday'];

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/Day 'monday' is not within the validity period/");

         // Act
-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $validityEnd = '2038-07-07';

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $validityEnd = '2038-07-15';

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $originalValidityEnd = '2038-07-18';
         $newValidityEnd = '2038-07-10';

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->update(
+        $result = availability_for($this->model)->update(
             id: $availability->id,
             data: ['validity_end' => $newValidityEnd]
         );
@@ @@
         $originalValidityEnd = '2038-07-11';
         $newValidityEnd = '2038-07-18';

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->update(
+        $result = availability_for($this->model)->update(
             id: $availability->id,
             data: ['validity_end' => $newValidityEnd]
         );
@@ @@
         $originalValidityEnd = '2038-07-18';
         $newValidityEnd = '2038-07-09';

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/Day 'saturday' is not within the validity period/");

         // Act
-        availability_for($this->testSchedulable)->update(
+        availability_for($this->model)->update(
             id: $availability->id,
             data: [
                 'days' => $invalidNewDays,
@@ @@
         $validityEnd = '2038-07-01';

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $explicitDays = ['thursday', 'friday'];

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $originalDays = ['monday', 'wednesday', 'friday'];
         $newDays = ['tuesday', 'thursday'];

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->update(
+        $result = availability_for($this->model)->update(
             id: $availability->id,
             data: ['days' => $newDays]
         );
@@ @@
         $validityEnd = '2038-07-10';

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


32) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/CacheRulesCommandTest.php:4

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


33) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/InstallRosterCommandTest.php:13

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


34) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Domain/RepositoryMutationTest.php:6

    ---------- begin diff ----------
@@ @@

 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Foundation\Testing\RefreshDatabase;
-use Illuminate\Support\Carbon;
 use Roster\Exceptions\ForbiddenModelMutationException;
 use Roster\Exceptions\InvalidOwnerException;
 use Roster\Exceptions\MissingOwnerException;
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up test environment.
@@ @@
     protected function setUp(): void
     {
         parent::setUp();
-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
     }

     /**
@@ @@
         ];

         // Act
-        $availability = availability_for($this->testSchedulable)->create($availabilityData);
+        $availability = availability_for($this->model)->create($availabilityData);

         // Assert
         $this->assertInstanceOf(AvailabilityModel::class, $availability);
-        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
+        $this->assertSame($this->model->id, $availability->schedulable_id);
         $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
     }

@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $updated = availability_for($this->testSchedulable)->update($availability->id, [
+        $updated = availability_for($this->model)->update($availability->id, [
             'daily_start' => '10:00:00',
         ]);

@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $deleted = availability_for($this->testSchedulable)->delete($availability->id);
+        $deleted = availability_for($this->model)->delete($availability->id);

         // Assert
         $this->assertTrue($deleted);
@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $endDate1 = now()->addDays(30)->startOfDay();
         $day1 = strtolower($startDate1->format('l'));

-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $endDate2 = now()->addDays(32)->startOfDay();
         $day2 = strtolower($startDate2->format('l'));

-        $availability2 = availability_for($this->testSchedulable)->create([
+        $availability2 = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '08:00:00',
             'daily_end' => '18:00:00',
@@ @@

         // Act
         $availabilityRepository->all(
-            schedulable: $this->testSchedulable,
+            schedulable: $this->model,
             owner: $availability2
         );
     }
@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
                 'start_datetime' => $startDate->copy()->setTime(10, 0),
                 'end_datetime' => $startDate->copy()->setTime(11, 0),
             ],
-            schedulable: $this->testSchedulable
+            schedulable: $this->model
         );
     }

@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         // Assert
         $this->assertInstanceOf(ScheduleModel::class, $schedule);
         $this->assertSame($availability->id, $schedule->availability_id);
-        $this->assertSame($this->testSchedulable->id, $schedule->schedulable_id);
+        $this->assertSame($this->model->id, $schedule->schedulable_id);
     }

     /**
@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         // Act
         $scheduleRepository->find(
             id: $schedule->id,
-            schedulable: $this->testSchedulable
+            schedulable: $this->model
         );
     }

@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         // Act
         $foundSchedule = $scheduleRepository->find(
             id: $schedule->id,
-            schedulable: $this->testSchedulable,
+            schedulable: $this->model,
             owner: $availability
         );

@@ @@
         $endDate = now()->addDays(30)->startOfDay();
         $day = strtolower($startDate->format('l'));

-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectException(MissingOwnerException::class);

         // Act
-        $scheduleRepository->all(schedulable: $this->testSchedulable);
+        $scheduleRepository->all(schedulable: $this->model);
     }
 }
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


35) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/AvailabilityTest.php:19

    ---------- begin diff ----------
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up the test environment.
@@ @@
     {
         parent::setUp();

-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
     }

     /**
@@ @@
             'validity_end' => '2038-07-31 23:59:59',
         ];

-        return availability_for($this->testSchedulable)
+        return availability_for($this->model)
             ->create(array_merge($defaultAttributes, $attributes));
     }

@@ @@

         // Assert
         $this->assertInstanceOf(AvailabilityModel::class, $availability);
-        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
+        $this->assertSame($this->model->id, $availability->schedulable_id);
         $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
         $this->assertSame('training', $availability->type);
         $this->assertSame(['monday', 'wednesday', 'friday'], $availability->days);
@@ @@
     {
         // Arrange
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->testSchedulable->id,
+            'schedulable_id' => $this->model->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
     {
         // Arrange
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->testSchedulable->id,
+            'schedulable_id' => $this->model->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
     {
         // Arrange
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->testSchedulable->id,
+            'schedulable_id' => $this->model->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@
     {
         // Arrange
         $availability = new AvailabilityModel([
-            'schedulable_id' => $this->testSchedulable->id,
+            'schedulable_id' => $this->model->id,
             'schedulable_type' => TestSchedulable::class,
             'type' => 'consultation',
             'daily_start' => '09:00:00',
@@ @@

         // Act & Assert
         $this->assertInstanceOf(TestSchedulable::class, $availability->schedulable);
-        $this->assertSame($this->testSchedulable->id, $availability->schedulable->id);
+        $this->assertSame($this->model->id, $availability->schedulable->id);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


36) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Models/ImpedimentTest.php:24

    ---------- begin diff ----------
@@ @@
     /**
      * Test schedulable instance.
      */
-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Test availability instance.
@@ @@
     {
         parent::setUp();

-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
         $this->availability = $this->createAvailability();
     }

@@ @@
      */
     private function createAvailability(): Availability
     {
-        return availability_for($this->testSchedulable)->create([
+        return availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@

         // Assert
         $this->assertInstanceOf(ImpedimentModel::class, $impediment);
-        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
+        $this->assertSame($this->model->id, $impediment->schedulable_id);
         $this->assertSame(TestSchedulable::class, $impediment->schedulable_type);
         $this->assertSame($this->availability->id, $impediment->availability_id);
         $this->assertSame('Vacation', $impediment->reason);
@@ @@

         // Assert
         $this->assertInstanceOf(TestSchedulable::class, $schedulable);
-        $this->assertSame($this->testSchedulable->id, $schedulable->id);
+        $this->assertSame($this->model->id, $schedulable->id);
     }

     /**
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


37) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/AvailabilityServiceTest.php:21

    ---------- begin diff ----------
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up test environment.
@@ @@
     {
         parent::setUp();

-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
     }

     /**
@@ @@
         ];

         // Act
-        $availability = availability_for($this->testSchedulable)->create($availabilityData);
+        $availability = availability_for($this->model)->create($availabilityData);

         // Assert
         $this->assertInstanceOf(AvailabilityModel::class, $availability);
@@ @@
         $this->assertDatabaseHas('roster_availabilities', [
             'id' => $availability->id,
             'type' => 'consultation',
-            'schedulable_id' => $this->testSchedulable->id,
+            'schedulable_id' => $this->model->id,
             'schedulable_type' => TestSchedulable::class,
         ]);

         $this->assertSame('consultation', $availability->type);
         $this->assertSame(['monday', 'wednesday', 'friday'], $availability->days);
-        $this->assertSame($this->testSchedulable->id, $availability->schedulable_id);
+        $this->assertSame($this->model->id, $availability->schedulable_id);
         $this->assertSame(TestSchedulable::class, $availability->schedulable_type);
     }

@@ @@
         ];

         // Act
-        $availability = availability_for($this->testSchedulable)->create($availabilityData);
+        $availability = availability_for($this->model)->create($availabilityData);

         // Assert
         $this->assertNotEmpty($availability->days);
@@ @@
     public function test_can_update_an_existing_availability(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ];

         // Act
-        $result = availability_for($this->testSchedulable)->update($availability->id, $updateData);
+        $result = availability_for($this->model)->update($availability->id, $updateData);

         // Assert
         $this->assertTrue($result);
@@ @@
         );

         // Act
-        availability_for($this->testSchedulable)->update($availabilityId, $updateData);
+        availability_for($this->model)->update($availabilityId, $updateData);
     }

     /**
@@ @@
     public function test_can_delete_an_availability(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->delete($availability->id);
+        $result = availability_for($this->model)->delete($availability->id);

         // Assert
         $this->assertTrue($result);
@@ @@
         );

         // Act
-        availability_for($this->testSchedulable)->delete($availabilityId);
+        availability_for($this->model)->delete($availabilityId);
     }

     /**
@@ @@
     public function test_can_find_an_availability_by_id(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->find($availability->id);
+        $result = availability_for($this->model)->find($availability->id);

         // Assert
         $this->assertInstanceOf(AvailabilityModel::class, $result);
@@ @@
         $availabilityId = 999;

         // Act
-        $result = availability_for($this->testSchedulable)->find($availabilityId);
+        $result = availability_for($this->model)->find($availabilityId);

         // Assert
         $this->assertNull($result);
@@ @@
     public function test_can_get_all_availabilities_with_filters(): void
     {
         // Arrange
-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
             'validity_end' => '2038-07-31',
         ]);

-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'training',
             'daily_start' => '14:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)
+        $result = availability_for($this->model)
             ->whereType('consultation')
             ->all();

@@ @@
         $this->expectException(ValidationFailedException::class);

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
     public function test_handles_validation_failure_during_update(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectException(ValidationFailedException::class);

         // Act
-        availability_for($this->testSchedulable)->update($availability->id, $updateData);
+        availability_for($this->model)->update($availability->id, $updateData);
     }

     /**
@@ @@
     public function test_validate_partial_date_update_fails_when_end_before_existing_start(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectException(ValidationFailedException::class);

         // Act
-        availability_for($this->testSchedulable)->update($availability->id, $updateData);
+        availability_for($this->model)->update($availability->id, $updateData);
     }

     /**
@@ @@
         $this->expectException(ValidationFailedException::class);

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
     public function test_sets_and_gets_filters_correctly(): void
     {
         // Arrange
-        $availabilityService = availability_for($this->testSchedulable);
+        $availabilityService = availability_for($this->model);
         $filters = [
             'type' => 'consultation',
             'day' => 'monday',
@@ @@
     public function test_does_not_merge_non_adjacent_availabilities(): void
     {
         // Arrange
-        $existingAvailability = availability_for($this->testSchedulable)->create([
+        $existingAvailability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
         ];

         // Act
-        $availability = availability_for($this->testSchedulable)->create($newData);
+        $availability = availability_for($this->model)->create($newData);

         // Assert
         $this->assertDatabaseHas('roster_availabilities', [
@@ @@
         $this->expectExceptionMessageMatches('/Minimum duration/');

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
         $this->expectException(ValidationFailedException::class);

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
     public function test_cannot_update_schedulable_fields(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/cannot be changed/");

         // Act
-        availability_for($this->testSchedulable)->update($availability->id, $updateData);
+        availability_for($this->model)->update($availability->id, $updateData);
     }

     /**
@@ @@
     public function test_can_get_availabilities_by_type_filter(): void
     {
         // Arrange
-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
             'validity_end' => '2038-07-31',
         ]);

-        availability_for($this->testSchedulable)->create([
+        availability_for($this->model)->create([
             'type' => 'training',
             'daily_start' => '14:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)
+        $result = availability_for($this->model)
             ->whereType('consultation')
             ->all();

@@ @@
     public function test_can_reset_filters(): void
     {
         // Arrange
-        $availabilityService = availability_for($this->testSchedulable);
+        $availabilityService = availability_for($this->model);
         $availabilityService->setFilters(['type' => 'consultation']);
         $availabilityService->setFilter('day', 'monday');

         // Act
         $availabilityService->resetFilters();
+
         $filters = $availabilityService->getFilters();

         // Assert
@@ @@
     public function test_can_filter_by_availability_id(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->find($availability->id);
+        $result = availability_for($this->model)->find($availability->id);

         // Assert
         $this->assertSame($availability->id, $result->id);
@@ @@
         $this->expectExceptionMessage("Day 'not-an-array' is not a valid day of week");

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
         $this->expectExceptionMessage('Days array cannot be empty');

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
     public function test_partial_update_allowed(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ];

         // Act
-        $result = availability_for($this->testSchedulable)->update($availability->id, $updateData);
+        $result = availability_for($this->model)->update($availability->id, $updateData);

         // Assert
         $this->assertTrue($result);
@@ @@
         $this->expectExceptionMessage("Day 'invalid-day' is not a valid day of week");

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
         $this->expectExceptionMessageMatches("/Invalid type 'invalid-type'/");

         // Act
-        availability_for($this->testSchedulable)->create($availabilityData);
+        availability_for($this->model)->create($availabilityData);
     }

     /**
@@ @@
         config()->set('roster.allowed_types', []);

         // Act
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'anything',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_update_does_not_trigger_merge(): void
     {
         // Arrange
-        $availability1 = availability_for($this->testSchedulable)->create([
+        $availability1 = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
             'validity_end' => '2038-07-31',
         ]);

-        $availability2 = availability_for($this->testSchedulable)->create([
+        $availability2 = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '14:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ];

         // Act
-        $result = availability_for($this->testSchedulable)->update($availability2->id, $updateData);
+        $result = availability_for($this->model)->update($availability2->id, $updateData);

         // Assert
         $this->assertTrue($result);
    ----------- end diff -----------

Applied rules:
 * NewlineBeforeNewAssignSetRector
 * RenamePropertyToMatchTypeRector


38) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ImpedimentServiceTest.php:25

    ---------- begin diff ----------
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;
+
     private AvailabilityModel $availabilityModel;

     /**
@@ @@
     {
         parent::setUp();

-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();

         Config::set('roster.durations.default_slot_interval_minutes', 15);
         Config::set('roster.durations.max_search_period_days', 30);

-        $this->availabilityModel = availability_for($this->testSchedulable)->create([
+        $this->availabilityModel = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->assertInstanceOf(ImpedimentModel::class, $impediment);
         $this->assertSame('System maintenance', $impediment->reason);
         $this->assertSame($this->availabilityModel->id, $impediment->availability_id);
-        $this->assertSame($this->testSchedulable->id, $impediment->schedulable_id);
+        $this->assertSame($this->model->id, $impediment->schedulable_id);
         $this->assertSame(['priority' => 'high'], $impediment->metadata);
     }

@@ @@
     public function test_is_time_slot_blocked_with_type_filter(): void
     {
         // Arrange
-        $otherAvailability = availability_for($this->testSchedulable)->create([
+        $otherAvailability = availability_for($this->model)->create([
             'type' => 'emergency',
             'daily_start' => '18:00:00',
             'daily_end' => '21:00:00',
@@ @@
         ]);

         // Assert
-        $this->assertSame(150.0, $impediment->duration_minutes);
+        $this->assertEqualsWithDelta(150.0, $impediment->duration_minutes, PHP_FLOAT_EPSILON);
     }

     /**
@@ @@
         $dailyEnd   = $now->copy()->addMinutes(20);
         $validityEnd = $now->copy()->addHour();

-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'instant-test',
             'daily_start' => $dailyStart->format('H:i:s'),
             'daily_end' => $dailyEnd->format('H:i:s'),
@@ @@
     public function test_impediment_on_non_availability_day(): void
     {
         // Arrange
-        $mondayOnlyAvailability = availability_for($this->testSchedulable)->create([
+        $mondayOnlyAvailability = availability_for($this->model)->create([
             'type' => 'monday-only',
             'daily_start' => '20:00:00',
             'daily_end' => '22:00:00',
@@ @@
     public function test_impediment_outside_availability_hours(): void
     {
         // Arrange
-        $limitedAvailability = availability_for($this->testSchedulable)->create([
+        $limitedAvailability = availability_for($this->model)->create([
             'type' => 'limited',
             'daily_start' => '20:00:00',
             'daily_end' => '23:00:00',
@@ @@
         }

         // Act
-        $paginator = impediment_for($this->availabilityModel)->paginate(10);
+        $lengthAwarePaginator = impediment_for($this->availabilityModel)->paginate(10);

         // Assert
-        $this->assertSame(25, $paginator->total());
-        $this->assertSame(10, $paginator->perPage());
-        $this->assertSame(3, $paginator->lastPage());
-        $this->assertCount(10, $paginator->items());
+        $this->assertSame(25, $lengthAwarePaginator->total());
+        $this->assertSame(10, $lengthAwarePaginator->perPage());
+        $this->assertSame(3, $lengthAwarePaginator->lastPage());
+        $this->assertCount(10, $lengthAwarePaginator->items());
     }

     /**
    ----------- end diff -----------

Applied rules:
 * NewlineBetweenClassLikeStmtsRector
 * RenameVariableToMatchMethodCallReturnTypeRector
 * RenamePropertyToMatchTypeRector
 * AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector


39) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Services/ScheduleServiceTest.php:11

    ---------- begin diff ----------
@@ @@
 use Illuminate\Support\Facades\Config;
 use Roster\Enums\ScheduleStatus;
 use Roster\Models\Schedule as ScheduleModel;
-use Roster\Models\Availability as AvailabilityModel;
 use Roster\Validation\Exceptions\ValidationFailedException;
 use Tests\TestCase;
 use Tests\Support\TestSchedulable;
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up test environment.
@@ @@
     {
         parent::setUp();

-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();

         Config::set('roster.durations.default_slot_interval_minutes', 15);
         Config::set('roster.durations.max_search_period_days', 30);
@@ @@
     public function test_create_schedule_successfully(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_create_schedule_with_default_status(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_create_schedule_fails_when_end_before_start(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_create_schedule_fails_when_too_short(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_update_schedule_successfully(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_update_schedule_with_datetime_changes(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_update_schedule_fails_when_overlap(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_create_schedule_fails_when_overlap(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_update_schedule_fails_when_not_found(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_delete_schedule_successfully(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_delete_schedule_fails_when_not_found(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_schedule_by_id(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_all_schedules(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_get_schedules_with_filters(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_get_schedules_with_datetime_range_filter(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_next_slot_without_conflicts(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_next_slot_return_start_only(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_next_slot_respects_availability_hours(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_next_slot_returns_null_when_no_availability(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'limited',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
     public function test_is_time_slot_available_returns_true(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_time_slot_available_returns_false_when_schedule_overlap(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_time_slot_available_returns_false_when_impediment_overlap(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_time_slot_available_returns_false_when_outside_availability(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_time_slot_available_with_type_filter(): void
     {
         // Arrange
-        $availabilityConsultation = availability_for($this->testSchedulable)->create([
+        $availabilityConsultation = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
             'validity_end' => '2038-01-31',
         ]);

-        $availabilityTraining = availability_for($this->testSchedulable)->create([
+        $availabilityTraining = availability_for($this->model)->create([
             'type' => 'training',
             'daily_start' => '13:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_available_slots_in_range(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_period_available_returns_true(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_period_available_returns_false_when_schedule_conflict(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_period_available_returns_false_when_impediment_conflict(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_is_period_available_returns_false_when_no_availability(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_concurrent_schedule_creation_prevents_double_booking(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_schedule_on_non_availability_day(): void
     {
         // Arrange
-        $mondayOnlyAvailability = availability_for($this->testSchedulable)->create([
+        $mondayOnlyAvailability = availability_for($this->model)->create([
             'type' => 'monday-only',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_schedule_outside_availability_hours(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_schedule_exact_boundary_not_overlap(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_find_next_slot_with_adjacent_impediments(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_schedule_metadata_serialization(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_schedule_duration_calculation(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Assert
-        $this->assertSame(90.0, $schedule->duration_minutes);
+        $this->assertEqualsWithDelta(90.0, $schedule->duration_minutes, PHP_FLOAT_EPSILON);
     }

     /**
@@ @@
     public function test_full_booking_scenario(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_reschedule_scenario(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_complex_availability_scenario(): void
     {
         // Arrange
-        $morningAvailability = availability_for($this->testSchedulable)->create([
+        $morningAvailability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '12:00:00',
@@ @@
             'validity_end' => '2038-01-31',
         ]);

-        $afternoonAvailability = availability_for($this->testSchedulable)->create([
+        $afternoonAvailability = availability_for($this->model)->create([
             'type' => 'training',
             'daily_start' => '13:00:00',
             'daily_end' => '17:00:00',
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector
 * AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector


40) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/AvailabilityTemporalCoherenceRuleTest.php:20

    ---------- begin diff ----------
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up test environment.
@@ @@
     protected function setUp(): void
     {
         parent::setUp();
-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
     }

     /**
@@ @@
     public function test_cannot_shorten_availability_before_existing_future_schedule(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/Cannot set validity_start/");

         // Act
-        availability_for($this->testSchedulable)->update($availability->id, [
+        availability_for($this->model)->update($availability->id, [
             'validity_start' => '2038-01-05',
         ]);
     }
@@ @@
     public function test_cannot_extend_availability_end_before_existing_future_schedule(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/Cannot set validity_end/");

         // Act
-        availability_for($this->testSchedulable)->update($availability->id, [
+        availability_for($this->model)->update($availability->id, [
             'validity_end' => '2038-01-05',
         ]);
     }
@@ @@
     public function test_cannot_remove_days_with_future_impediments(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         );

         // Act
-        availability_for($this->testSchedulable)->update($availability->id, [
+        availability_for($this->model)->update($availability->id, [
             'days' => ['monday'],
         ]);
     }
@@ @@
     public function test_can_update_availability_without_conflict(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->update($availability->id, [
+        $result = availability_for($this->model)->update($availability->id, [
             'daily_end' => '18:00:00',
         ]);

@@ @@
     public function test_cannot_delete_availability_with_future_schedules(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/Cannot delete availability with future schedules/");

         // Act
-        availability_for($this->testSchedulable)->delete($availability->id);
+        availability_for($this->model)->delete($availability->id);
     }

     /**
@@ @@
     public function test_cannot_delete_availability_with_future_impediments(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         $this->expectExceptionMessageMatches("/Cannot delete availability with future impediments/");

         // Act
-        availability_for($this->testSchedulable)->delete($availability->id);
+        availability_for($this->model)->delete($availability->id);
     }

     /**
@@ @@
     public function test_can_delete_availability_without_future_conflict(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
         ]);

         // Act
-        $result = availability_for($this->testSchedulable)->delete($availability->id);
+        $result = availability_for($this->model)->delete($availability->id);

         // Assert
         $this->assertTrue($result);
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


41) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Validation/Rules/ImpedimentScheduleDaysCoherenceRuleTest.php:20

    ---------- begin diff ----------
@@ @@
 {
     use RefreshDatabase;

-    private Model $testSchedulable;
+    private Model $model;

     /**
      * Set up test environment.
@@ @@
     protected function setUp(): void
     {
         parent::setUp();
-        $this->testSchedulable = TestSchedulable::create();
+        $this->model = TestSchedulable::create();
     }

     /**
@@ @@
     public function test_cannot_create_impediment_on_non_availability_day(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_cannot_create_schedule_on_non_availability_day(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
@@ @@
     public function test_can_create_impediment_on_allowed_day(): void
     {
         // Arrange
-        $availability = availability_for($this->testSchedulable)->create([
+        $availability = availability_for($this->model)->create([
             'type' => 'consultation',
             'daily_start' => '09:00:00',
             'daily_end' => '17:00:00',
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector


 [OK] 41 files would have been changed (dry-run) by Rector                                                              

