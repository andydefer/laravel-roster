<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Roster\Exceptions\MissingSchedulableException;

/**
 * Handles schedulable ownership and query scoping.
 */
trait BelongsToSchedulable
{
    protected static function bootBelongsToSchedulable(): void
    {
        static::creating(static::validateSchedulable(...));
        static::updating(static::validateSchedulable(...));

        static::addGlobalScope('schedulable', function (Builder $builder): void {
            $model = $builder->getModel();

            if ($model->schedulable_id && $model->schedulable_type) {
                $builder
                    ->where('schedulable_id', $model->schedulable_id)
                    ->where('schedulable_type', $model->schedulable_type);
            }
        });
    }

    protected static function validateSchedulable(Model $model): void
    {
        if (
            empty($model->schedulable_id) ||
            empty($model->schedulable_type)
        ) {
            throw MissingSchedulableException::create();
        }
    }

    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForSchedulable(
        Builder $builder,
        Model $model
    ): Builder {
        return $builder
            ->where('schedulable_id', $model->getKey())
            ->where('schedulable_type', $model::class);
    }
}
