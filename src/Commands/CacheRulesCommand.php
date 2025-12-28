<?php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;
use Roster\Domain\DTOs\CacheStats;
use Roster\Domain\Services\CacheRulesService;
use Throwable;

/**
 * Command for managing validation rules cache in the Roster package.
 *
 * Provides operations to generate, clear, display cache statistics, and list all validation rules.
 */
class CacheRulesCommand extends Command
{
    /**
     * The command signature with available options.
     */
    protected $signature = 'roster:cache-rules
                            {--clear : Clear the cache}
                            {--force : Force regeneration}
                            {--show : Show cache stats}
                            {--list : Display all rules as a table}';

    /**
     * The command description.
     */
    protected $description = 'Manage Roster validation rules cache';

    /**
     * Execute the console command.
     *
     * @param CacheRulesService $cacheRulesService The cache rules service
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    public function handle(CacheRulesService $cacheRulesService): int
    {
        try {
            if ($this->option('clear')) {
                $stats = $cacheRulesService->clear(
                    force: (bool) $this->option('force')
                );
            } elseif ($this->option('list')) {
                $cacheRulesService->displayRulesTable($this);
                return self::SUCCESS;
            } elseif ($this->option('show')) {
                $stats = $cacheRulesService->show();
            } else {
                $stats = $cacheRulesService->generate();
            }

            if ($stats instanceof CacheStats) {
                $this->displayCacheStats($stats);
            }

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Display cache statistics in a formatted way.
     *
     * @param CacheStats $cacheStats Cache statistics to display
     */
    protected function displayCacheStats(CacheStats $cacheStats): void
    {
        $this->line('📊 Cache stats:');
        $this->line('   Path: ' . $cacheStats->path);
        $this->line('   Rules: ' . $cacheStats->rulesCount);
        $this->line('   Size: ' . $cacheStats->formattedSize());

        if ($cacheStats->generationTimeMs > 0) {
            $this->line('   Duration: ' . $cacheStats->generationTimeMs . ' ms');
        }
    }
}
