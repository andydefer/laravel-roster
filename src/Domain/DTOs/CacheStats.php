<?php

declare(strict_types=1);

namespace Roster\Domain\DTOs;

final class CacheStats
{
    public function __construct(
        public readonly string $path,
        public readonly int $rulesCount,
        public readonly int $sizeBytes,
        public readonly float $generationTimeMs,
    ) {}

    /**
     * Create stats from an existing cache file.
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
     * Human readable cache size.
     */
    public function formattedSize(): string
    {
        $bytes = $this->sizeBytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
