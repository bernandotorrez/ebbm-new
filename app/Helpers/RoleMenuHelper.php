<?php

namespace App\Helpers;

use App\Enums\LevelUser;

class RoleMenuHelper
{
    /**
     * Define menu access permissions based on user roles
     */
    public static function getMenuPermissions(): array
    {
        return [
            // Admin: can access all menus
            LevelUser::ADMIN->value => [
                'pagu',
                'harga-bbm', // Harga BBM
                'sp3m',
                'delivery-order',
                'pemakaian'
            ],
            
            // Kanpus: can access specific menus
            LevelUser::KANPUS->value => [
                'pagu',
                'harga-bbm', // Harga BBM
                'sp3m',
                'delivery-order',
                'pemakaian'
            ],
            
            // Kansar: can access specific menus
            LevelUser::KANSAR->value => [
                'sp3m',
                'delivery-order',
                'pemakaian',
            ],
            
            // Abk: can access limited menus
            LevelUser::ABK->value => [
                'delivery-order',
                'pemakaian',
            ],
        ];
    }
    
    /**
     * Check if a role has access to a specific menu
     */
    public static function hasMenuAccess(string $role, string $menu): bool
    {
        $permissions = self::getMenuPermissions();
        
        if (!isset($permissions[$role])) {
            return false;
        }
        
        return in_array($menu, $permissions[$role]);
    }
    
    /**
     * Get all accessible menu slugs for a specific role
     */
    public static function getAccessibleMenus(string $role): array
    {
        $permissions = self::getMenuPermissions();
        
        return $permissions[$role] ?? [];
    }
}