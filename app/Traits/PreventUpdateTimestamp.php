<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait PreventUpdateTimestamp
{
    /**
     * Boot the trait and attach model event listeners
     */
    public static function bootPreventUpdateTimestamp()
    {
        static::creating(function ($model) {
            // Set created_by if the field exists and user is authenticated
            if (Auth::check() && $model->isFillable('created_by') && !isset($model->created_by)) {
                // Use the authenticated user's ID
                $user = Auth::user();
                if ($user && isset($user->user_id)) {
                    $model->created_by = $user->user_id;
                } elseif ($user && isset($user->id)) {
                    $model->created_by = $user->id;
                }
            }
            
            // Ensure updated_at is not set during creation
            if (!isset($model->updated_at)) {
                $model->updated_at = null;
            }
        });
        
        static::updating(function ($model) {
            // Set updated_by if the field exists and user is authenticated
            if (Auth::check() && $model->isFillable('updated_by') && !isset($model->updated_by)) {
                // Use the authenticated user's ID
                $user = Auth::user();
                if ($user && isset($user->user_id)) {
                    $model->updated_by = $user->user_id;
                } elseif ($user && isset($user->id)) {
                    $model->updated_by = $user->id;
                }
            }
        });
        
        static::deleting(function ($model) {
            // For soft deletes, set deleted_by
            if (Auth::check() && $model->isFillable('deleted_by')) {
                // Use the authenticated user's ID
                $user = Auth::user();
                if ($user && isset($user->user_id)) {
                    $model->deleted_by = $user->user_id;
                } elseif ($user && isset($user->id)) {
                    $model->deleted_by = $user->id;
                }
                
                // Save the model to persist the deleted_by field
                $model->save();
            }
        });
    }
}