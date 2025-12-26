<?php

namespace Roster\Domain\Services;

use Roster\Domain\DTOs\CacheStats;
use Roster\Validation\Cache\RuleCacheGenerator;

class CacheRulesService
{
    public function __construct(
        private RuleCacheGenerator $generator,
    ) {}

    public function generate(): CacheStats
    {
        if (! $this->generator->generate()) {
            throw new \RuntimeException('Cache generation failed');
        }

        return CacheStats::fromPath($this->generator->getCachePath());
    }

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

    public function show(): CacheStats
    {
        $path = $this->generator->getCachePath();

        if (! file_exists($path)) {
            return $this->generate();
        }

        return CacheStats::fromPath($path);
    }
}
