<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Roster\Exceptions\MissingSchedulableException;

/**
 * Provides relationship and scoping functionality for models that belong to a schedulable entity.
 *
 * This trait ensures that models have a polymorphic relationship to a schedulable owner
 * and automatically applies query scopes to filter by the schedulable entity.
 */
trait BelongsToSchedulable
{
    /**
     * Boot the trait and register model events and global scopes.
     */
    protected static function bootBelongsToSchedulable(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->schedulable_id) || empty($model->schedulable_type)) {
                throw new MissingSchedulableException(
                    sprintf(
                        '%s must have a schedulable owner. Set schedulable_id and schedulable_type.',
                        class_basename($model)
                    )
                );
            }
        });

        static::addGlobalScope('schedulable', function (Builder $builder): void {
            $model = $builder->getModel();

            if ($model->schedulable_id && $model->schedulable_type) {
                $builder->where('schedulable_id', $model->schedulable_id)
                    ->where('schedulable_type', $model->schedulable_type);
            }
        });
    }

    /**
     * Get the polymorphic schedulable relationship.
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to a specific schedulable entity.
     */
    public function scopeForSchedulable(Builder $builder, Model $model): Builder
    {
        return $builder->where('schedulable_id', $model->getKey())
            ->where('schedulable_type', get_class($model));
    }
}
