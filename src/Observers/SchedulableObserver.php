<?php

declare(strict_types=1);

namespace Roster\Observers;

use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\MissingSchedulableException;

class SchedulableObserver
{
    /**
     * Ensure schedulable entity is set before creating the model.
     *
     * @param Model $model The model being created
     *
     * @throws MissingSchedulableException If schedulable_id or schedulable_type are empty
     */
    public function creating(Model $model): void
    {
        if (empty($model->schedulable_id) || empty($model->schedulable_type)) {
            throw new MissingSchedulableException(
                sprintf(
                    '%s must belong to a schedulable entity.',
                    class_basename($model)
                )
            );
        }
    }
}
