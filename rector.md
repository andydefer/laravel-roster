# Rector Refactoring Report
*Generated: ven. 26 déc. 2025 23:50:26 WAT*


6 files with changes
====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Commands;

+use Throwable;
 use Illuminate\Console\Command;
 use Roster\Domain\DTOs\CacheStats;
 use Roster\Services\CacheRulesService;
@@ @@
     /**
      * Execute the console command.
      *
-     * @param CacheRulesService $service The cache rules service instance
+     * @param CacheRulesService $cacheRulesService The cache rules service instance
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
-        } catch (\Throwable $exception) {
-            $this->error($exception->getMessage());
+        } catch (Throwable $throwable) {
+            $this->error($throwable->getMessage());

             return self::FAILURE;
         }
@@ @@
     /**
      * Display cache statistics.
      *
-     * @param CacheStats $stats The cache statistics DTO
+     * @param CacheStats $cacheStats The cache statistics DTO
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


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/CacheRulesService.php:1

    ---------- begin diff ----------
@@ @@
 <?php

+declare(strict_types=1);
+
 namespace Roster\Services;

+use RuntimeException;
 use Roster\Domain\DTOs\CacheStats;
 use Roster\Validation\Cache\RuleCacheGenerator;

@@ @@
 final class CacheRulesService
 {
     public function __construct(
-        private RuleCacheGenerator $generator,
+        private RuleCacheGenerator $ruleCacheGenerator,
     ) {}

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

     public function clear(bool $force = false): ?CacheStats
     {
-        if (! $this->generator->clear()) {
-            throw new \RuntimeException('Cache clear failed');
+        if (! $this->ruleCacheGenerator->clear()) {
+            throw new RuntimeException('Cache clear failed');
         }

         if ($force) {
@@ @@

     public function show(): CacheStats
     {
-        $path = $this->generator->getCachePath();
+        $path = $this->ruleCacheGenerator->getCachePath();

         if (! file_exists($path)) {
             return $this->generate();
    ----------- end diff -----------

Applied rules:
 * RenamePropertyToMatchTypeRector
 * DeclareStrictTypesRector


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/DTOs/CacheStats.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Domain\DTOs;

+use RuntimeException;
+
 final class CacheStats
 {
     public function __construct(
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


4) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:140

    ---------- begin diff ----------
@@ @@
         if ($this->validity_start && $start->lt($this->validity_start)) {
             return false;
         }
+
         return !($this->validity_end && $end->gt($this->validity_end));
     }

@@ @@
         if ($this->validity_start && $date->lt($this->validity_start)) {
             return false;
         }
+
         return !($this->validity_end && $date->gt($this->validity_end));
     }
    ----------- end diff -----------

Applied rules:
 * NewlineAfterStatementRector


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:405

    ---------- begin diff ----------
@@ @@

     /**
      * Apply filters to query builder.
+     * @param array<string, mixed> $filters
      */
     protected function applyFilters(Builder $builder, array $filters = []): Builder
     {
    ----------- end diff -----------

Applied rules:
 * ClassMethodArrayDocblockParamFromLocalCallsRector


6) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/CacheRulesCommandTest.php:4

    ---------- begin diff ----------
@@ @@

 namespace Tests\Unit\Commands;

-use Illuminate\Foundation\Testing\RefreshDatabase;
+use RuntimeException;
 use Illuminate\Support\Facades\File;
 use Mockery;
 use Roster\Commands\CacheRulesCommand;
@@ @@
      */
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
+        $inputDefinition = $cacheRulesCommand->getDefinition();
+        $this->assertTrue($inputDefinition->hasOption('clear'));
+        $this->assertTrue($inputDefinition->hasOption('force'));
+        $this->assertTrue($inputDefinition->hasOption('show'));
     }

     /**
@@ @@
      */
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
      */
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
      */
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


 [OK] 6 files would have been changed (dry-run) by Rector                                                               

