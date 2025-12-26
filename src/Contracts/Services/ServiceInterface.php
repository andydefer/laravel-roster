<?php

declare(strict_types=1);

namespace Roster\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ServiceInterface
{
    /* ========= CRUD ========= */

    public function create(array $data): mixed;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function find(int $id): mixed;

    public function all(): Collection;

    public function paginate(
        int $perPage = 15,
        array $columns = ['*'],
        string $pageName = 'page',
        ?int $page = null
    ): LengthAwarePaginator;

    /* ========= Data & Filters ========= */

    public function getData(): array;

    public function setData(array $data): self;

    public function getFilters(): array;

    public function setFilters(array $filters): self;

    public function resetFilters(): self;

    public function setFilter(string $key, mixed $value): self;

    /* ========= Context ========= */

    public function getSchedulable(): ?Model;

    public function setSchedulable(Model $model): self;

    public function for(Model $model): static;

    public function owner(Model $model): static;

    public function clear(): self;
}
