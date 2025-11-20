# Role-Based Menu Access Configuration

## Tanggal: 19 November 2025

## Overview
Konfigurasi akses menu berdasarkan role/level user di aplikasi EBBM.

## Role Hierarchy

### 1. Admin
**Full Access** - Dapat mengakses semua menu

#### Master
- Jenis Alut (Golongan BBM) - `GolonganBbmResource`
- Satuan - `SatuanResource`
- Jenis Bahan Bakar (Bekal) - `BekalResource`
- Wilayah - `WilayahResource`
- Kantor SAR - `KantorSarResource`
- Kota - `KotaResource`
- Pack - `PackResource`
- Kemasan - `KemasanResource`
- Pelumas - `PelumasResource`
- Pos Sandar - `PosSandarResource`
- TBBM/DPPU - `TbbmResource`
- Harga Bekal - `HargaBekalResource`

#### Transaksi
- Alut - `AlpalResource`

#### Admin
- Pengguna - `UserResource`

### 2. Kantor Pusat (KANPUS)

#### Master
- Harga BBM - `HargaBekalResource`

#### Transaksi
- Pagu - `PaguResource`
- SP3M - `Sp3mResource`
- DO - `DeliveryOrderResource`
- Pemakaian - `PemakaianResource`
- SP3K - `Sp3kResource`
- BAST - `BastResource` *(TODO: Belum ada, akan ditambahkan nanti)*

#### Laporan
- Daftar SP3M - Menggunakan `Sp3mResource`
- Rekap DO - `RekapDoResource` *(TODO: Belum ada, akan ditambahkan nanti)*
- Detil Tagihan - `DetilTagihanResource` *(TODO: Belum ada, akan ditambahkan nanti)*

### 3. Kantor SAR (KANSAR)

#### Transaksi
- SP3M - `Sp3mResource`
- DO - `DeliveryOrderResource`
- Pemakaian - `PemakaianResource`
- SP3K - `Sp3kResource`
- BAST - `BastResource` *(TODO: Belum ada, akan ditambahkan nanti)*

### 4. ABK

#### Transaksi
- DO - `DeliveryOrderResource`
- Pemakaian - `PemakaianResource`

## Implementation

### File: `app/Helpers/RolePermissionHelper.php`

```php
$accessPermissions = [
    LevelUser::ADMIN->value => [
        'all' => true, // Admin can access all resources
    ],
    LevelUser::KANPUS->value => [
        // Master
        'HargaBekal' => true,
        // Transaksi
        'Pagu' => true,
        'Sp3m' => true,
        'DeliveryOrder' => true,
        'Pemakaian' => true,
        'Sp3k' => true,
        // 'Bast' => true,  // TODO: Belum ada
    ],
    LevelUser::KANSAR->value => [
        // Transaksi
        'Sp3m' => true,
        'DeliveryOrder' => true,
        'Pemakaian' => true,
        'Sp3k' => true,
        // 'Bast' => true,  // TODO: Belum ada
    ],
    LevelUser::ABK->value => [
        // Transaksi
        'DeliveryOrder' => true,
        'Pemakaian' => true,
    ],
];
```

## Navigation Groups

### Admin
- **Master** - Sort 10-90
- **Transaksi** - Sort 100-190
- **Admin** - Sort 200

### Kantor Pusat
- **Master** - Sort 10 (Harga BBM)
- **Transaksi** - Sort 100-150
- **Laporan** - Sort 200 *(TODO)*

### Kantor SAR
- **Transaksi** - Sort 100-140

### ABK
- **Transaksi** - Sort 100-110

## Resource Navigation Configuration

### Master Group (Admin Only)
```php
protected static ?string $navigationGroup = 'Master';
protected static ?int $navigationSort = [number];
```

| Resource | Label | Sort |
|----------|-------|------|
| GolonganBbmResource | Jenis Alut | 10 |
| SatuanResource | Satuan | 20 |
| BekalResource | Jenis Bahan Bakar | 30 |
| WilayahResource | Wilayah | 40 |
| KantorSarResource | Kantor SAR | 50 |
| KotaResource | Kota | 60 |
| PackResource | Pack | 70 |
| KemasanResource | Kemasan | 80 |
| PelumasResource | Pelumas | 90 |
| PosSandarResource | Pos Sandar | 100 |
| TbbmResource | TBBM/DPPU | 110 |
| HargaBekalResource | Harga Bekal | 120 |

### Transaksi Group (All Roles)
```php
protected static ?string $navigationGroup = 'Transaksi';
protected static ?int $navigationSort = [number];
```

| Resource | Label | Sort | Access |
|----------|-------|------|--------|
| AlpalResource | Alut | 1 | Admin |
| PaguResource | Pagu | 2 | Admin, KANPUS |
| Sp3mResource | SP3M | 3 | Admin, KANPUS, KANSAR |
| DeliveryOrderResource | Delivery Order | 4 | Admin, KANPUS, KANSAR, ABK |
| PemakaianResource | Pemakaian | 5 | Admin, KANPUS, KANSAR, ABK |
| Sp3kResource | SP3K | 6 | Admin, KANPUS, KANSAR |

### Admin Group (Admin Only)
```php
protected static ?string $navigationGroup = 'Admin';
protected static ?int $navigationSort = 90;
```

| Resource | Label | Sort |
|----------|-------|------|
| UserResource | Pengguna | 90 |

## Access Control Flow

### 1. User Login
```
User login → Get user level → Load accessible resources
```

### 2. Navigation Menu
```
Filament loads resources → Check canAccess() → Show/hide menu items
```

### 3. Direct URL Access
```
User access URL → Check canViewAny() → Allow/deny access
```

### 4. CRUD Operations
```
Create: Check canCreate()
Read: Check canView()
Update: Check canEdit()
Delete: Check canDelete()
```

## Testing

### Test Case 1: Admin Login
```
✓ Can see all Master menu items
✓ Can see all Transaksi menu items
✓ Can see Admin menu items
✓ Can access all resources
```

### Test Case 2: Kantor Pusat Login
```
✓ Can see Harga BBM in Master
✓ Can see Pagu, SP3M, DO, Pemakaian, SP3K in Transaksi
✓ Cannot see other Master items
✓ Cannot see Admin menu
✓ Cannot access restricted resources
```

### Test Case 3: Kantor SAR Login
```
✓ Can see SP3M, DO, Pemakaian, SP3K in Transaksi
✓ Cannot see Master menu
✓ Cannot see Admin menu
✓ Cannot see Pagu
✓ Cannot access restricted resources
```

### Test Case 4: ABK Login
```
✓ Can see DO, Pemakaian in Transaksi
✓ Cannot see Master menu
✓ Cannot see Admin menu
✓ Cannot see SP3M, Pagu, SP3K
✓ Cannot access restricted resources
```

### Test Case 5: Direct URL Access
```
Admin: /admin/golongan-bbm → ✓ Allowed
KANPUS: /admin/golongan-bbm → ❌ Denied (403)
KANSAR: /admin/pagu → ❌ Denied (403)
ABK: /admin/sp3m → ❌ Denied (403)
```

## Security

### Protection Layers
1. ✓ **Navigation Menu** - Hidden for unauthorized roles
2. ✓ **URL Access** - Blocked via canAccess()
3. ✓ **CRUD Operations** - Checked via canCreate/Edit/Delete/View()
4. ✓ **Query Filtering** - Data filtered by user's kantor_sar_id (for non-admin)

### Bypass Prevention
- ✓ All resources use `RoleBasedResourceAccess` trait
- ✓ Direct URL access is blocked
- ✓ API endpoints are protected
- ✓ Database queries are filtered

## Future Enhancements

### TODO: Resources to be Added
1. **BastResource** - BAST (Berita Acara Serah Terima)
   - Access: KANPUS, KANSAR
   - Group: Transaksi

2. **RekapDoResource** - Rekap DO
   - Access: KANPUS
   - Group: Laporan

3. **DetilTagihanResource** - Detil Tagihan
   - Access: KANPUS
   - Group: Laporan

### TODO: Laporan Group
Create new navigation group for reports:
```php
protected static ?string $navigationGroup = 'Laporan';
```

## Maintenance

### Adding New Resource
1. Create resource with `RoleBasedResourceAccess` trait
2. Add to `$accessPermissions` in RolePermissionHelper
3. Add to `$resourceMap` in RolePermissionHelper
4. Set navigation group and sort
5. Test access for all roles

### Modifying Access
1. Update `$accessPermissions` in RolePermissionHelper
2. Update `$resourceMap` in RolePermissionHelper
3. Clear cache: `php artisan optimize:clear`
4. Test access for affected roles

## Notes
- Admin has full access to all resources (no restrictions)
- Non-admin users only see explicitly allowed resources
- Navigation menu automatically hides inaccessible items
- Direct URL access is blocked for unauthorized resources
- Data is filtered by kantor_sar_id for non-admin users (where applicable)

## Contributors
- Kiro AI Assistant
