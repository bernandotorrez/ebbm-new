<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait UserFieldTracker
{
    /**
     * Check if the model has a specific user field
     */
    protected static function hasUserField($field)
    {
        $model = new static();
        return Schema::hasColumn($model->getTable(), $field);
    }
}