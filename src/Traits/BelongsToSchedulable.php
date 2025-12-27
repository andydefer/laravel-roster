<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Roster\Exceptions\MissingSchedulableException;

/**
 * Trait for handling schedulable entity ownership and query scoping.
 *
 * Provides automatic validation, global scoping, and relationship methods
 * for models that belong to a schedulable polymorphic entity.
 */
trait BelongsToSchedulable
{
    /**
     * Boots the trait with event listeners and global scopes.
     *
     * @return void
     */
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

    /**
     * Validates that a schedulable reference is present before persistence.
     *
     * @param Model $model The model being created or updated
     * @return void
     * @throws MissingSchedulableException If schedulable reference is incomplete
     */
    protected static function validateSchedulable(Model $model): void
    {
        if (
            empty($model->schedulable_id) ||
            empty($model->schedulable_type)
        ) {
            throw MissingSchedulableException::create();
        }
    }

    /**
     * Defines the polymorphic relationship to the schedulable entity.
     *
     * @return MorphTo The schedulable polymorphic relationship
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes queries to a specific schedulable entity.
     *
     * @param Builder $builder The query builder instance
     * @param Model $model The schedulable entity model
     * @return Builder The scoped query builder
     */
    public function scopeForSchedulable(Builder $builder, Model $model): Builder
    {
        return $builder
            ->where('schedulable_id', $model->getKey())
            ->where('schedulable_type', $model::class);
    }
}
