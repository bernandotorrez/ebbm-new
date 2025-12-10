<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasIsActive
{
    /**
     * Boot the trait.
     */
    protected static function bootHasIsActive(): void
    {
        // Set is_active to '1' when creating new record
        static::creating(function ($model) {
            if (!isset($model->is_active)) {
                $model->is_active = '1';
            }
        });

        // Automatically filter by is_active = '1' on all queries
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable() . '.is_active', '1');
        });

        // Set is_active to '0' when soft deleting
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                $model->is_active = '0';
                $model->saveQuietly();
            }
        });

        // Set is_active to '1' when restoring
        static::restoring(function ($model) {
            $model->is_active = '1';
        });
    }

    /**
     * Scope to include inactive records
     */
    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active');
    }

    /**
     * Scope to get only inactive records
     */
    public function scopeOnlyInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active')->where($this->getTable() . '.is_active', '0');
    }
}
