<?php

namespace App\Traits;

use App\Helpers\RolePermissionHelper;
use App\Enums\LevelUser;
use Illuminate\Support\Facades\Auth;

trait RoleBasedResourceAccess
{
    /**
     * Check if the current user can access this resource based on their role
     */
    public static function canAccess(): bool
    {
        // Get the resource name without 'Resource' suffix for comparison
        $resourceName = class_basename(static::class);
        $resourceName = str_replace('Resource', '', $resourceName);
        
        return RolePermissionHelper::canAccessResource($resourceName);
    }
    
    /**
     * Check if the current user can view the index page
     */
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }
    
    /**
     * Check if the current user can create records
     */
    public static function canCreate(): bool
    {
        return static::canAccess();
    }
    
    /**
     * Check if the current user can edit records
     */
    public static function canEdit($record = null): bool
    {
        return static::canAccess();
    }
    
    /**
     * Check if the current user can delete records
     */
    public static function canDelete($record = null): bool
    {
        return static::canAccess();
    }
    
    /**
     * Check if the current user can view a specific record
     */
    public static function canView($record = null): bool
    {
        return static::canAccess();
    }
    
    /**
     * Check if this resource should be registered in navigation
     * Hide Transaksi and Laporan menu from Admin users
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        
        // Jika user adalah Admin, sembunyikan menu Transaksi dan Laporan
        if ($user && $user->level->value === LevelUser::ADMIN->value) {
            if (isset(static::$navigationGroup) && 
                in_array(static::$navigationGroup, ['Transaksi', 'Laporan'])) {
                return false;
            }
        }
        
        return static::canAccess();
    }
}