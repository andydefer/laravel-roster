<?php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;
use Roster\Domain\DTOs\CacheStats;
use Roster\Domain\Services\CacheRulesService;

/**
 * Command to manage validation rules cache for the Roster package.
 *
 * This command allows generating, clearing, and displaying cached validation rules
 * to improve performance of rule scanning operations.
 */
class CacheRulesCommand extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'roster:cache-rules
                            {--clear : Clear the cache}
                            {--force : Force regeneration}
                            {--show : Show cache contents}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Manage Roster validation rules cache';

    /**
     * Execute the console command.
     *
     * @param CacheRulesService $service The cache rules service instance
     * @return int Command exit code (SUCCESS or FAILURE)
     */
    public function handle(CacheRulesService $service): int
    {
        try {
            if ($this->option('clear')) {
                $stats = $service->clear(
                    force: (bool) $this->option('force')
                );
            } elseif ($this->option('show')) {
                $stats = $service->show();
            } else {
                $stats = $service->generate();
            }

            if ($stats instanceof CacheStats) {
                $this->displayCacheStats($stats);
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Display cache statistics.
     *
     * @param CacheStats $stats The cache statistics DTO
     */
    protected function displayCacheStats(CacheStats $stats): void
    {
        $this->line('📊 Cache stats:');
        $this->line('   Path: ' . $stats->path);
        $this->line('   Rules: ' . $stats->rulesCount);
        $this->line('   Size: ' . $stats->formattedSize());

        if ($stats->generationTimeMs > 0) {
            $this->line('   Duration: ' . $stats->generationTimeMs . ' ms');
        }
    }
}
