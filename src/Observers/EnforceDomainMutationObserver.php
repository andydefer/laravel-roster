<?php

declare(strict_types=1);

namespace Roster\Observers;

use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Support\RosterMutationContext;

/**
 * Blocks any model mutation unless explicitly allowed
 * by the domain mutation context.
 */
final class EnforceDomainMutationObserver
{
    public function creating(Model $model): void
    {
        $this->guard($model);
    }

    public function updating(Model $model): void
    {
        $this->guard($model);
    }

    public function deleting(Model $model): void
    {
        $this->guard($model);
    }

    private function guard(Model $model): void
    {
        if (!RosterMutationContext::isAllowed()) {
            throw ForbiddenModelMutationException::create($model::class);
        }
    }
}
