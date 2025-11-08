<?php

namespace App\Traits;

use App\Enums\LevelUser;
use App\Helpers\RoleMenuHelper;
use Illuminate\Support\Facades\Auth;

trait HasRoleBasedAccess
{
    /**
     * Check if the resource should be registered in navigation based on user role
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return RoleMenuHelper::hasMenuAccess(
            $user->level->value ?? $user->level, 
            static::getMenuSlug()
        );
    }
    
    /**
     * Check if the user has access to this resource
     */
    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return RoleMenuHelper::hasMenuAccess(
            $user->level->value ?? $user->level, 
            static::getMenuSlug()
        );
    }
    
    /**
     * Define the menu slug for this resource
     * This should be overridden in each resource class
     */
    public static function getMenuSlug(): string
    {
        // Default fallback - should be overridden in each resource
        return '';
    }
}