<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Support\Collection;

/**
 * Interface for services that support data filtering.
 * Provides a fluent interface for applying and managing filters.
 */
interface FilterableInterface
{
    /**
     * Apply multiple filters simultaneously.
     *
     * @param array<string, mixed> $filters Array of filter key-value pairs
     */
    public function setFilters(array $filters): static;

    /**
     * Apply a single filter.
     *
     * @param string $key The filter identifier
     * @param mixed $value The filter value
     */
    public function setFilter(string $key, mixed $value): static;

    /**
     * Filter results by entity type.
     *
     * @param string $type The type to filter by
     */
    public function whereType(string $type): static;

    /**
     * Remove all currently applied filters.
     */
    public function resetFilters(): static;

    /**
     * Execute the query with current filters and return results.
     *
     * @return Collection<int, mixed> The filtered results
     */
    public function get(): Collection;

    /**
     * Get all results without applying filters.
     *
     * @return Collection<int, mixed> All available results
     */
    public function all(): Collection;
}
