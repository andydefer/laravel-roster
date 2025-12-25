<?php
// src/Commands/CacheRulesCommand.php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;
use Roster\Validation\Cache\RuleCacheGenerator;
use Roster\Validation\RuleScanner;

class CacheRulesCommand extends Command
{
    protected $signature = 'roster:cache-rules
                            {--clear : Clear the cache}
                            {--force : Force regeneration}
                            {--show : Show cache contents}';

    protected $description = 'Manage Roster validation rules cache';

    public function handle(RuleScanner $ruleScanner): int
    {
        $ruleCacheGenerator = new RuleCacheGenerator($ruleScanner);

        if ($this->option('clear')) {
            return $this->clearCache($ruleCacheGenerator);
        }

        if ($this->option('show')) {
            return $this->showCache($ruleCacheGenerator);
        }

        return $this->generateCache($ruleCacheGenerator);
    }

    private function generateCache(RuleCacheGenerator $generator): int
    {
        $this->info('Generating Roster validation rules cache...');

        $start = microtime(true);

        if ($generator->generate()) {
            $duration = round((microtime(true) - $start) * 1000, 2);
            $this->info("✅ Cache generated successfully at: " . $generator->getCachePath());
            $this->info(sprintf('⏱️  Duration: %sms', $duration));

            // Afficher des stats
            $this->showCacheStats($generator);

            return self::SUCCESS;
        }

        $this->error('Failed to generate cache');
        return self::FAILURE;
    }

    private function clearCache(RuleCacheGenerator $generator): int
    {
        if ($generator->clear()) {
            $this->info('✅ Cache cleared');

            // Régénérer si demandé
            if ($this->option('force')) {
                return $this->generateCache($generator);
            }

            return self::SUCCESS;
        }

        $this->error('Failed to clear cache');
        return self::FAILURE;
    }

    private function showCache(RuleCacheGenerator $generator): int
    {
        $cacheFile = $generator->getCachePath();

        if (!file_exists($cacheFile)) {
            $this->warn('Cache file does not exist: ' . $cacheFile);
            $this->info('Generating cache automatically...');

            // Générer le cache automatiquement
            $generator->generate();

            $this->info("✅ Cache generated successfully at: " . $cacheFile);
        }

        // Maintenant on est sûr que le fichier existe, on peut l'afficher
        $rules = require $cacheFile;
        $this->info('Rules count: ' . count($rules));

        // Préparer les lignes du tableau avec index
        $rows = [];
        $i = 1;
        foreach ($rules as $rule) {
            $rows[] = [
                $i++,                             // numéro de ligne
                $rule['class'],                   // nom de la classe
                $rule['priority'],
                implode(', ', $rule['entities']),
                implode(', ', $rule['operations']),
            ];
        }

        $this->table(
            ['#', 'Class', 'Priority', 'Entities', 'Operations'],
            $rows
        );

        return self::SUCCESS;
    }

    private function showCacheStats(RuleCacheGenerator $generator): void
    {
        $cacheFile = $generator->getCachePath();

        if (file_exists($cacheFile)) {
            $size = filesize($cacheFile);
            $rules = require $cacheFile;

            $this->line("📊 Cache stats:");
            $this->line("   Size: " . $this->formatBytes($size));
            $this->line("   Rules: " . count($rules));
            $this->line("   Path: " . $cacheFile);
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            ++$i;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
