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
                // Master
                'golongan-bbm',  // Jenis Alut
                'satuan',
                'bekal',  // Jenis Bahan Bakar
                'wilayah',
                'kantor-sar',
                'kota',
                'pack',
                'kemasan',
                'pelumas',
                'pos-sandar',
                'tbbm',  // TBBM/DPPU
                // Transaksi
                'alut',
                // Admin
                'kelola-user',
            ],
            
            // Kanpus: can access specific menus
            LevelUser::KANPUS->value => [
                // Master
                'harga-bekal',  // Harga BBM
                // Transaksi
                'pagu',
                'sp3m',
                'delivery-order',
                'pemakaian',
                'sp3k',
                'bast',
            ],
            
            // Kansar: can access specific menus (TIDAK ADA PAGU & HARGA BEKAL)
            // SP3M, Pemakaian, SP3K adalah READ ONLY (tidak bisa add, edit, delete)
            LevelUser::KANSAR->value => [
                // Transaksi only
                'sp3m',          // Read Only
                'delivery-order',
                'pemakaian',     // Read Only
                'sp3k',          // Read Only
                'bast',
            ],
            
            // Abk: can access limited menus
            LevelUser::ABK->value => [
                // Transaksi only
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