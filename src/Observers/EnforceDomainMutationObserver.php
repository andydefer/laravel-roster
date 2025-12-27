<?php

declare(strict_types=1);

namespace Roster\Observers;

use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\ForbiddenModelMutationException;
use Roster\Support\RosterMutationContext;

/**
 * Observer that enforces domain-controlled mutation rules on models.
 *
 * Prevents any direct model mutations (create, update, delete) unless
 * explicitly permitted by the RosterMutationContext, ensuring all
 * data modifications flow through the domain service layer.
 */
final class EnforceDomainMutationObserver
{
    /**
     * Intercepts model creation attempts.
     *
     * @param Model $model Model being created
     * @throws ForbiddenModelMutationException If mutation not allowed by context
     */
    public function creating(Model $model): void
    {
        $this->guard($model);
    }

    /**
     * Intercepts model update attempts.
     *
     * @param Model $model Model being updated
     * @throws ForbiddenModelMutationException If mutation not allowed by context
     */
    public function updating(Model $model): void
    {
        $this->guard($model);
    }

    /**
     * Intercepts model deletion attempts.
     *
     * @param Model $model Model being deleted
     * @throws ForbiddenModelMutationException If mutation not allowed by context
     */
    public function deleting(Model $model): void
    {
        $this->guard($model);
    }

    /**
     * Validates that the current mutation context allows the operation.
     *
     * @param Model $model Model being mutated
     * @throws ForbiddenModelMutationException If mutation context does not permit operation
     */
    private function guard(Model $model): void
    {
        if (!RosterMutationContext::isAllowed()) {
            throw ForbiddenModelMutationException::create($model::class);
        }
    }
}
