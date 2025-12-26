<?php

namespace Roster\Domain\Services;

use Illuminate\Console\Command;
use Roster\Domain\DTOs\CacheStats;
use Roster\Validation\Cache\RuleCacheGenerator;

/**
 * Service for managing validation rule cache operations.
 *
 * Handles generation, clearing, and display of cached validation rules
 * used by the roster system.
 */
class CacheRulesService
{
    /**
     * @param RuleCacheGenerator $generator Service for generating rule cache files
     */
    public function __construct(
        private RuleCacheGenerator $generator
    ) {}

    /**
     * Generate a new cache file with all validation rules.
     *
     * @return CacheStats Statistics about the generated cache
     * @throws \RuntimeException When cache generation fails
     */
    public function generate(): CacheStats
    {
        if (! $this->generator->generate()) {
            throw new \RuntimeException('Cache generation failed');
        }

        return CacheStats::fromPath($this->generator->getCachePath());
    }

    /**
     * Clear the existing cache file.
     *
     * @param bool $force When true, regenerate cache after clearing
     * @return CacheStats|null Cache statistics if regenerated, null otherwise
     * @throws \RuntimeException When cache clearing fails
     */
    public function clear(bool $force = false): ?CacheStats
    {
        if (! $this->generator->clear()) {
            throw new \RuntimeException('Cache clear failed');
        }

        if ($force) {
            return $this->generate();
        }

        return null;
    }

    /**
     * Show cache statistics, generating cache if it doesn't exist.
     *
     * @return CacheStats Statistics about the cache
     */
    public function show(): CacheStats
    {
        $path = $this->generator->getCachePath();

        if (! file_exists($path)) {
            return $this->generate();
        }

        return CacheStats::fromPath($path);
    }

    /**
     * Display cached rules as a formatted table in the console.
     *
     * @param Command $command Console command instance for output
     */
    public function displayRulesTable(Command $command): void
    {
        $cacheFile = config('roster.cache.cache_file');

        if (! file_exists($cacheFile)) {
            $command->error("Cache file not found: $cacheFile");
            return;
        }

        /** @var array<string, array{priority?: int, entities?: string[], operations?: string[]}> $rules */
        $rules = require $cacheFile;

        if (empty($rules)) {
            $command->info("No rules found in cache.");
            return;
        }

        $tableRows = $this->formatRulesForTable($rules);

        $command->table(
            ['No', 'Class', 'Priority', 'Entities', 'Operations'],
            $tableRows
        );
    }

    /**
     * Format rules array for console table display.
     *
     * @param array<string, array{priority?: int, entities?: string[], operations?: string[]}> $rules
     * @return array<int, array{No: int, Class: string, Priority: string, Entities: string, Operations: string}>
     */
    private function formatRulesForTable(array $rules): array
    {
        $tableRows = [];
        $index = 1;

        foreach ($rules as $className => $ruleDefinition) {
            $tableRows[] = [
                'No' => $index++,
                'Class' => $className,
                'Priority' => (string) ($ruleDefinition['priority'] ?? ''),
                'Entities' => implode(', ', $ruleDefinition['entities'] ?? []),
                'Operations' => implode(', ', $ruleDefinition['operations'] ?? []),
            ];
        }

        return $tableRows;
    }
}
