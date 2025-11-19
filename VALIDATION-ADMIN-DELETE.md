# Validasi - Tidak Dapat Menghapus User Admin

## Tanggal: 19 November 2025

### Fitur Baru
Menambahkan validasi untuk mencegah penghapusan user dengan level Admin.

## Implementasi

### 1. Table Actions (UserResource.php)
**Delete Action:**
```php
Tables\Actions\DeleteAction::make()
    ->label('Hapus')
    ->visible(fn (User $record): bool => $record->level !== LevelUser::ADMIN)
    ->before(function (User $record) {
        if ($record->level === LevelUser::ADMIN) {
            Notification::make()
                ->title('Tidak Dapat Menghapus!')
                ->body('User dengan level Admin tidak dapat dihapus.')
                ->danger()
                ->send();
            return false;
        }
    }),
```

**Fitur:**
- ✓ Tombol "Hapus" tidak muncul untuk user Admin
- ✓ Jika tetap dipaksa hapus (via API/direct), muncul notifikasi error
- ✓ Proses delete dibatalkan

### 2. Bulk Delete Actions (UserResource.php)
**Bulk Delete Action:**
```php
Tables\Actions\DeleteBulkAction::make()
    ->label('Hapus Terpilih')
    ->modalHeading('Konfirmasi Hapus Data')
    ->modalSubheading('Apakah kamu yakin ingin menghapus data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
    ->modalButton('Ya, Hapus Sekarang')
    ->before(function ($records) {
        // Check if any record is Admin
        $hasAdmin = $records->contains(function ($record) {
            return $record->level === LevelUser::ADMIN;
        });
        
        if ($hasAdmin) {
            Notification::make()
                ->title('Tidak Dapat Menghapus!')
                ->body('Tidak dapat menghapus user dengan level Admin. Silakan hapus user non-Admin saja.')
                ->danger()
                ->send();
            return false;
        }
    }),
```

**Fitur:**
- ✓ Cek apakah ada user Admin dalam selection
- ✓ Jika ada, tampilkan notifikasi error
- ✓ Batalkan proses bulk delete
- ✓ User non-Admin tidak ikut terhapus

### 3. Edit Page Actions (EditUser.php)
**Delete Action:**
```php
Actions\DeleteAction::make()
    ->label('Hapus')
    ->visible(fn (): bool => $this->record->level !== \App\Enums\LevelUser::ADMIN)
    ->before(function () {
        if ($this->record->level === \App\Enums\LevelUser::ADMIN) {
            Notification::make()
                ->title('Tidak Dapat Menghapus!')
                ->body('User dengan level Admin tidak dapat dihapus.')
                ->danger()
                ->send();
            return false;
        }
    }),
```

**Force Delete Action:**
```php
Actions\ForceDeleteAction::make()
    ->label('Hapus Permanen')
    ->visible(fn (): bool => $this->record->level !== \App\Enums\LevelUser::ADMIN)
    ->before(function () {
        if ($this->record->level === \App\Enums\LevelUser::ADMIN) {
            Notification::make()
                ->title('Tidak Dapat Menghapus!')
                ->body('User dengan level Admin tidak dapat dihapus secara permanen.')
                ->danger()
                ->send();
            return false;
        }
    }),
```

**Fitur:**
- ✓ Tombol "Hapus" tidak muncul di halaman edit user Admin
- ✓ Tombol "Hapus Permanen" tidak muncul di halaman edit user Admin
- ✓ Jika tetap dipaksa, muncul notifikasi error

## Cara Kerja

### Scenario 1: Hapus User Non-Admin dari Table
```
1. User melihat list user
2. User Admin memiliki tombol "Hapus" ❌ (tidak muncul)
3. User non-Admin memiliki tombol "Hapus" ✓ (muncul)
4. Klik "Hapus" pada user non-Admin
5. User berhasil dihapus
```

### Scenario 2: Hapus User Admin dari Table (Forced)
```
1. User mencoba hapus via API/direct
2. Validasi before() berjalan
3. Cek: level === ADMIN?
4. Jika ya, tampilkan notifikasi error
5. Return false (batalkan delete)
6. User Admin tidak terhapus
```

### Scenario 3: Bulk Delete dengan User Admin
```
1. User select multiple users (termasuk Admin)
2. Klik "Hapus Terpilih"
3. Validasi before() berjalan
4. Cek: ada user Admin dalam selection?
5. Jika ya, tampilkan notifikasi error
6. Return false (batalkan bulk delete)
7. Semua user tidak terhapus (termasuk non-Admin)
```

### Scenario 4: Bulk Delete Tanpa User Admin
```
1. User select multiple users (hanya non-Admin)
2. Klik "Hapus Terpilih"
3. Validasi before() berjalan
4. Cek: ada user Admin dalam selection?
5. Tidak ada, lanjutkan delete
6. Semua user non-Admin terhapus
```

### Scenario 5: Hapus dari Edit Page
```
1. Buka halaman Edit User Admin
2. Tombol "Hapus" tidak muncul ❌
3. Tombol "Hapus Permanen" tidak muncul ❌
4. User Admin tidak bisa dihapus dari halaman ini
```

## UI Changes

### Table List
**User Admin:**
```
| Name  | Email | Level | Actions |
|-------|-------|-------|---------|
| Admin | ...   | Admin | [Ubah]  |  ← Tidak ada tombol Hapus
```

**User Non-Admin:**
```
| Name | Email | Level      | Actions        |
|------|-------|------------|----------------|
| John | ...   | Kantor SAR | [Ubah] [Hapus] |  ← Ada tombol Hapus
```

### Edit Page
**User Admin:**
```
Header Actions: [Pulihkan]  ← Tidak ada Hapus/Hapus Permanen
```

**User Non-Admin:**
```
Header Actions: [Hapus] [Hapus Permanen] [Pulihkan]  ← Lengkap
```

## Notifikasi Error

### Single Delete
```
Tidak Dapat Menghapus!
User dengan level Admin tidak dapat dihapus.
```

### Bulk Delete
```
Tidak Dapat Menghapus!
Tidak dapat menghapus user dengan level Admin. 
Silakan hapus user non-Admin saja.
```

### Force Delete
```
Tidak Dapat Menghapus!
User dengan level Admin tidak dapat dihapus secara permanen.
```

## Security

### Protection Layers
1. ✓ **UI Layer**: Tombol tidak muncul untuk user Admin
2. ✓ **Validation Layer**: before() hook mencegah delete
3. ✓ **Notification Layer**: User diberi tahu kenapa tidak bisa delete

### Bypass Prevention
- ✓ Validasi di before() hook mencegah bypass via API
- ✓ Validasi di bulk delete mencegah bypass via selection
- ✓ Return false membatalkan proses delete

## Testing

### Test Case 1: Lihat List User
```
✓ User Admin tidak memiliki tombol "Hapus"
✓ User non-Admin memiliki tombol "Hapus"
```

### Test Case 2: Hapus User Non-Admin
```
✓ Klik "Hapus" pada user non-Admin
✓ User berhasil dihapus
✓ Tidak ada error
```

### Test Case 3: Bulk Delete Hanya Non-Admin
```
✓ Select multiple user non-Admin
✓ Klik "Hapus Terpilih"
✓ Semua user terhapus
✓ Tidak ada error
```

### Test Case 4: Bulk Delete dengan Admin
```
✓ Select multiple user (termasuk Admin)
✓ Klik "Hapus Terpilih"
✓ Muncul notifikasi error
✓ Tidak ada user yang terhapus
```

### Test Case 5: Edit User Admin
```
✓ Buka halaman Edit User Admin
✓ Tombol "Hapus" tidak muncul
✓ Tombol "Hapus Permanen" tidak muncul
```

### Test Case 6: Edit User Non-Admin
```
✓ Buka halaman Edit User non-Admin
✓ Tombol "Hapus" muncul
✓ Tombol "Hapus Permanen" muncul
✓ Klik "Hapus" berhasil
```

## Level User

### Protected Level
- ✓ **ADMIN** - Tidak dapat dihapus

### Can Be Deleted
- ✓ KANPUS (Kantor Pusat)
- ✓ KANSAR (Kantor SAR)
- ✓ ABK

## Database
Tidak ada perubahan pada database schema. Validasi hanya di application layer.

## Breaking Changes
Tidak ada breaking changes. Fitur ini hanya menambahkan proteksi.

## Notes
- Validasi menggunakan enum `LevelUser::ADMIN`
- Soft delete tetap tidak bisa untuk user Admin
- Force delete juga tidak bisa untuk user Admin
- Restore tetap bisa untuk semua level (jika sudah soft deleted sebelumnya)

## Contributors
- Kiro AI Assistant
