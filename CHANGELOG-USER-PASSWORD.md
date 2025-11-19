# Changelog - User Password Update

## Tanggal: 19 November 2025

### Perubahan pada Kelola User

#### Masalah Sebelumnya
- Password wajib diisi saat edit user
- Tidak bisa update user tanpa mengubah password
- User harus selalu input password baru meskipun tidak ingin mengubahnya

#### Solusi yang Diterapkan

### 1. UserResource.php
**Password Field Update:**
```php
Forms\Components\TextInput::make('password')
    ->password()
    ->autocomplete(false)
    ->required(fn (string $operation): bool => $operation === 'create')
    ->maxLength(255)
    ->helperText(fn (string $operation): ?string => 
        $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : null
    )
    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
    ->dehydrated(fn ($state) => filled($state)),
```

**Perubahan:**
- ✓ Password **required** hanya saat **create** user baru
- ✓ Password **optional** saat **edit** user
- ✓ Helper text muncul saat edit: "Kosongkan jika tidak ingin mengubah password"
- ✓ Password di-hash otomatis jika diisi
- ✓ Password tidak di-update jika kosong

### 2. EditUser.php
**mutateFormDataBeforeSave Update:**
```php
protected function mutateFormDataBeforeSave(array $data): array
{
    // Apply ucwords() to the 'name' field before saving
    $data['name'] = ucwords($data['name']);
    
    // Only update password if it's filled
    if (empty($data['password'])) {
        unset($data['password']);
    }

    return $data;
}
```

**Perubahan:**
- ✓ Cek apakah password diisi
- ✓ Jika kosong, hapus dari data (tidak update password)
- ✓ Jika diisi, password akan di-hash dan di-update

### 3. CreateUser.php
**Tidak ada perubahan logic**, hanya update comment untuk clarity.

## Cara Kerja

### Saat Create User Baru
1. Password **wajib** diisi
2. Password akan di-hash otomatis
3. User baru dibuat dengan password yang di-hash

### Saat Edit User
1. Password **optional** (tidak wajib diisi)
2. Jika **kosong**: Password lama tetap digunakan (tidak berubah)
3. Jika **diisi**: Password baru akan di-hash dan menggantikan password lama
4. Helper text muncul: "Kosongkan jika tidak ingin mengubah password"

## Testing

### Test Case 1: Create User Baru
```
1. Buka form Create User
2. Isi semua field termasuk password
3. Submit
✓ User baru dibuat dengan password yang di-hash
```

### Test Case 2: Edit User Tanpa Ubah Password
```
1. Buka form Edit User
2. Ubah field lain (name, email, dll)
3. Kosongkan field password
4. Submit
✓ User di-update tanpa mengubah password
✓ Password lama masih bisa digunakan untuk login
```

### Test Case 3: Edit User dengan Ubah Password
```
1. Buka form Edit User
2. Isi field password dengan password baru
3. Submit
✓ User di-update dengan password baru
✓ Password lama tidak bisa digunakan lagi
✓ Password baru bisa digunakan untuk login
```

## Validasi

### Create User
- ✓ Kantor SAR: Required
- ✓ Name: Required
- ✓ Username: Required, Unique
- ✓ Email: Required, Email format
- ✓ Password: **Required**
- ✓ Level: Required

### Edit User
- ✓ Kantor SAR: Required
- ✓ Name: Required
- ✓ Username: Required, Unique (ignore current record)
- ✓ Email: Required, Email format, Unique (ignore current record)
- ✓ Password: **Optional** (kosongkan jika tidak ingin ubah)
- ✓ Level: Required

## UI Changes

### Create User Form
```
Password *
[__________]
(Required field)
```

### Edit User Form
```
Password
[__________]
Kosongkan jika tidak ingin mengubah password
(Optional field)
```

## Security

- ✓ Password selalu di-hash menggunakan bcrypt
- ✓ Password tidak pernah disimpan dalam plain text
- ✓ Password lama tetap aman jika tidak diubah
- ✓ Autocomplete disabled untuk keamanan

## Breaking Changes
Tidak ada breaking changes. Perubahan ini backward compatible.

## Notes
- Password field menggunakan `filled()` helper untuk cek apakah diisi
- `dehydrated()` memastikan password hanya di-process jika diisi
- `dehydrateStateUsing()` meng-hash password sebelum disimpan
- Helper text hanya muncul saat edit, tidak saat create

## Contributors
- Kiro AI Assistant
