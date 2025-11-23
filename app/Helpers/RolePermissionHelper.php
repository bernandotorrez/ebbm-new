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
                // Master
                'HargaBekal' => true,  // Harga BBM
                // Transaksi
                'Pagu' => true,
                'Sp3m' => true,
                'DeliveryOrder' => true,
                'Pemakaian' => true,
                'Sp3k' => true,
                // 'Bast' => true,  // TODO: Resource BAST belum ada, akan ditambahkan nanti
                // Laporan
                // 'DaftarSp3m' => true,  // TODO: Akan menggunakan Sp3m resource
                // 'RekapDo' => true,  // TODO: Belum ada, akan ditambahkan nanti
                // 'DetilTagihan' => true,  // TODO: Belum ada, akan ditambahkan nanti
            ],
            LevelUser::KANSAR->value => [
                // Transaksi
                // SP3M, Pemakaian, SP3K adalah READ ONLY (tidak bisa add, edit, delete)
                'Sp3m' => true,          // Read Only
                'DeliveryOrder' => true,
                'Pemakaian' => true,     // Read Only
                'Sp3k' => true,          // Read Only
                // 'Bast' => true,       // TODO: Resource BAST belum ada, akan ditambahkan nanti
            ],
            LevelUser::ABK->value => [
                // Transaksi
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
                // Master
                'GolonganBbm',  // Jenis Alut (Golongan BBM)
                'Satuan',
                'Bekal',  // Jenis Bahan Bakar (Bekal)
                'Wilayah',
                'KantorSar',  // Kantor SAR
                'Kota',
                'Pack',
                'Kemasan',
                'Pelumas',
                'PosSandar',  // Pos Sandar
                'Tbbm',  // TBBM/DPPU
                'HargaBekal',  // Harga Bekal
                // Transaksi
                'Alpal',  // Alut
                // Admin
                'User',  // Pengguna
            ],
            LevelUser::KANPUS->value => [
                // Master
                'HargaBekal',  // Harga BBM
                // Transaksi
                'Pagu',
                'Sp3m',
                'DeliveryOrder',
                'Pemakaian',
                'Sp3k',
                // 'Bast',  // TODO: Resource BAST belum ada, akan ditambahkan nanti
                // Laporan
                // 'DaftarSp3m',  // TODO: Menggunakan Sp3m resource
                // 'RekapDo',  // TODO: Belum ada
                // 'DetilTagihan',  // TODO: Belum ada
            ],
            LevelUser::KANSAR->value => [
                // Transaksi
                // SP3M, Pemakaian, SP3K adalah READ ONLY (tidak bisa add, edit, delete)
                'Sp3m',          // Read Only
                'DeliveryOrder',
                'Pemakaian',     // Read Only
                'Sp3k',          // Read Only
                // 'Bast',       // TODO: Resource BAST belum ada, akan ditambahkan nanti
            ],
            LevelUser::ABK->value => [
                // Transaksi
                'DeliveryOrder',
                'Pemakaian',
            ],
        ];

        return $resourceMap[$levelValue] ?? [];
    }
}