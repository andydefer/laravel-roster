<?php

declare(strict_types=1);

namespace Roster\Domain\DTOs;

/**
 * Data Transfer Object representing cache statistics.
 *
 * Contains information about cache file size, rule count, and generation metrics.
 */
final class CacheStats
{
    /**
     * @param string $path Full path to the cache file
     * @param int $rulesCount Number of validation rules in cache
     * @param int $sizeBytes Size of cache file in bytes
     * @param float $generationTimeMs Time taken to generate cache in milliseconds
     */
    public function __construct(
        public readonly string $path,
        public readonly int $rulesCount,
        public readonly int $sizeBytes,
        public readonly float $generationTimeMs,
    ) {}

    /**
     * Create CacheStats instance from an existing cache file path.
     *
     * @param string $path Path to the cache file
     * @param float $generationTimeMs Optional generation time for new caches
     * @return self
     * @throws \RuntimeException When cache file is missing or has invalid format
     */
    public static function fromPath(
        string $path,
        float $generationTimeMs = 0.0
    ): self {
        if (! file_exists($path)) {
            throw new \RuntimeException("Cache file not found: {$path}");
        }

        $rules = require $path;

        if (! is_array($rules)) {
            throw new \RuntimeException('Invalid cache format');
        }

        return new self(
            path: $path,
            rulesCount: count($rules),
            sizeBytes: filesize($path) ?: 0,
            generationTimeMs: $generationTimeMs,
        );
    }

    /**
     * Get human readable formatted file size.
     *
     * @return string Formatted size with appropriate unit (B, KB, MB, GB)
     */
    public function formattedSize(): string
    {
        $bytes = $this->sizeBytes;

        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
