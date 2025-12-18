<?php

declare(strict_types=1);

namespace Roster\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface SchedulableServiceContract
{
    public function for(Model $model): self;

    public function resetFilters(): self;

    public function whereType(string $type): self;

    public function all(): Collection;

    public function get(): Collection;
}
