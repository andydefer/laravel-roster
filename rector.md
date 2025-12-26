# Rector Refactoring Report
*Generated: sam. 27 déc. 2025 00:38:08 WAT*


10 files with changes
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


5) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/CacheRulesService.php:1

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


6) /home/andy-kani/pro/sites/packages/laravel-roster/src/Domain/Services/RosterInstallerService.php:1

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


7) /home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php:140

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


8) /home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php:405

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


9) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/CacheRulesCommandTest.php:4

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


10) /home/andy-kani/pro/sites/packages/laravel-roster/tests/Unit/Commands/InstallRosterCommandTest.php:13

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


 [OK] 10 files would have been changed (dry-run) by Rector                                                              

