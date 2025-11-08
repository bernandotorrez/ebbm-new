<?php

namespace App\Helpers;

use App\Enums\LevelUser;
use Illuminate\Support\Facades\Auth;

class RolePermissionHelper
{
    /**
     * Check if the current user can access a specific resource based on their role
     */
    public static function canAccessResource(string $resourceName): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Get user's level value - the cast in User model returns enum instance, so get the value
        $levelValue = $user->level instanceof LevelUser ? $user->level->value : $user->level;

        // Define access permissions based on role
        // Only explicitly mentioned resources are accessible by non-admin users
        $accessPermissions = [
            LevelUser::ADMIN->value => [
                'all' => true, // Admin can access all resources
            ],
            LevelUser::KANPUS->value => [
                'Pagu' => true,
                'GolonganBbm' => true,  // Harga BBM
                'HargaBekal' => true,  // Harga BBM
                'Sp3m' => true,
                'DeliveryOrder' => true,
                'Pemakaian' => true,
            ],
            LevelUser::KANSAR->value => [
                'Sp3m' => true,
                'DeliveryOrder' => true,
                'Pemakaian' => true,
            ],
            LevelUser::ABK->value => [
                'DeliveryOrder' => true,
                'Pemakaian' => true,
            ],
        ];

        // Check if user's level exists in permissions
        if (!isset($accessPermissions[$levelValue])) {
            return false;
        }

        // Admin has access to all resources
        if ($levelValue === LevelUser::ADMIN->value) {
            return true;
        }

        // For non-admin users, check if the specific resource is explicitly allowed
        return isset($accessPermissions[$levelValue][$resourceName]) && $accessPermissions[$levelValue][$resourceName];
    }

    /**
     * Get the list of accessible resources based on user role
     */
    public static function getAccessibleResources(): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        $levelValue = $user->level instanceof LevelUser ? $user->level->value : $user->level;

        $resourceMap = [
            LevelUser::ADMIN->value => [
                'Pagu', 'GolonganBbm', 'HargaBekal', 'Sp3m', 'DeliveryOrder', 'Pemakaian', 
                // Add other resources that Admin may access
                'Alpal', 'Bekal', 'KantorSar', 'Kemasan', 'Kota', 'Pack', 'Pelumas', 
                'PosSandar', 'Satuan', 'Tbbm', 'User', 'Wilayah'
            ],
            LevelUser::KANPUS->value => [
                'Pagu', 'GolonganBbm', 'HargaBekal', 'Sp3m', 'DeliveryOrder', 'Pemakaian'
            ],
            LevelUser::KANSAR->value => [
                'Sp3m', 'DeliveryOrder', 'Pemakaian'
            ],
            LevelUser::ABK->value => [
                'DeliveryOrder', 'Pemakaian'
            ],
        ];

        return $resourceMap[$levelValue] ?? [];
    }
}