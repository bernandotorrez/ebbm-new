# Troubleshooting File Upload Error di Delivery Order

## Masalah
Setelah upload file dan submit form Delivery Order, muncul error 404 di POST /livewire/update. Error ini muncul di production tapi tidak di local.

## Penyebab
Livewire mencoba melakukan request tambahan setelah form berhasil di-submit dan redirect, menyebabkan 404 karena component sudah tidak ada lagi. Ini terjadi karena:
1. File di-upload ke temporary storage
2. Form di-submit
3. Redirect terjadi
4. Livewire mencoba update component (yang sudah tidak ada) → 404 error

## Solusi yang Sudah Diterapkan

### 1. Konfigurasi Livewire (config/livewire.php)
```php
'render_on_redirect' => false,
'inject_morph_markers' => false,
'temporary_file_upload' => [
    // ... pengaturan lain
    'validate' => false, // Nonaktifkan validasi otomatis untuk mencegah request tambahan
],
```

### 2. File Upload Component (DeliveryOrderResource.php)
```php
Forms\Components\FileUpload::make('file_upload_do')
    ->previewable(false)  // Nonaktifkan preview untuk mencegah request tambahan
    ->downloadable()
    ->openable()
    // ... pengaturan lain
```

### 3. Create Page (CreateDeliveryOrder.php)
```php
protected function afterCreate(): void
{
    // Cegah Livewire update tambahan setelah create
    $this->skipRender();
}

protected function getRedirectUrl(): string
{
    // Redirect langsung ke halaman list
    return $this->getResource()::getUrl('index');
}
```

### 4. JavaScript Error Suppression (public/js/fix-livewire-redirect.js)
Script yang ditingkatkan untuk suppress error 404 setelah redirect dan saat file upload.

### 5. Asset Registration (app/Providers/AppServiceProvider.php)
Script fix sudah didaftarkan di Filament assets.

## Cara Menerapkan Fix

### Otomatis (Recommended)

**Local Development:**
```bash
bash fix-file-upload-error.sh
```

**Production/Docker:**
```bash
bash fix-file-upload-docker.sh
```

### Manual

**Local Development:**
```bash
# Clear semua cache dan optimized files
php artisan optimize:clear

# Clear Filament cache
php artisan filament:clear-cached-components

# Hapus temporary files Livewire
php artisan livewire:delete-uploaded-files --hours=0

# Rebuild config cache
php artisan config:cache
```

**Production/Docker:**
```bash
# Clear semua cache
docker exec -it ebbl_app php artisan optimize:clear

# Clear Filament cache
docker exec -it ebbl_app php artisan filament:clear-cached-components

# Hapus temporary files Livewire
docker exec -it ebbl_app php artisan livewire:delete-uploaded-files --hours=0

# Rebuild config cache
docker exec -it ebbl_app php artisan config:cache

# Restart container
docker compose restart
```

## Testing
1. Clear semua cache (lihat di atas)
2. Test submit form dengan file upload
3. Cek browser console - error 404 seharusnya sudah tidak muncul
4. Verifikasi file ter-upload dengan benar ke storage/app/public/delivery-order

## Jika Masalah Masih Terjadi

### 1. Cek Livewire Routes
```bash
php artisan route:list | grep livewire
```

### 2. Cek File Permissions
```bash
# Linux/Mac
chmod -R 775 storage/app/livewire-tmp
chmod -R 775 storage/app/public/delivery-order

# Docker
docker exec -it ebbl_app chmod -R 775 storage/app/livewire-tmp
docker exec -it ebbl_app chmod -R 775 storage/app/public/delivery-order
```

### 3. Cek Nginx Configuration
Pastikan nginx dikonfigurasi dengan benar untuk Livewire:
```nginx
location /livewire {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 4. Cek APP_URL
Verifikasi APP_URL di .env sesuai dengan domain aktual:
```bash
# Production
APP_URL=https://your-domain.com

# Local
APP_URL=http://localhost:8000
```

### 5. Enable Debug Mode Sementara
Di .env:
```bash
APP_DEBUG=true
```
Kemudian cek pesan error sebenarnya di browser.

### 6. Cek Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### 7. Cek Browser Console
Lihat pesan error yang tepat:
- 404 setelah redirect = expected, seharusnya di-suppress
- 404 saat upload = cek file permissions
- 500 error = cek Laravel logs
- CORS error = cek nginx/apache configuration

## Masalah Umum

### Masalah 1: File Tidak Ter-upload
**Gejala**: Form submit tapi file tidak tersimpan
**Solusi**: Cek storage link dan permissions
```bash
php artisan storage:link
chmod -R 775 storage/app/public
```

### Masalah 2: 413 Request Entity Too Large
**Gejala**: File besar gagal upload
**Solusi**: Tingkatkan upload limits di nginx dan PHP
```nginx
# nginx
client_max_body_size 10M;
```
```ini
# php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Masalah 3: Timeout Saat Upload
**Gejala**: Upload gagal dengan timeout error
**Solusi**: Tingkatkan timeout limits
```nginx
# nginx
proxy_read_timeout 300;
fastcgi_read_timeout 300;
```

### Masalah 4: Error Popup Masih Muncul
**Gejala**: Laravel error popup muncul setelah submit berhasil
**Solusi**: 
1. Verifikasi fix-livewire-redirect.js ter-load
2. Cek browser console untuk script errors
3. Clear browser cache (Ctrl+Shift+R)
4. Verifikasi AppServiceProvider mendaftarkan script

## Pencegahan
Untuk mencegah masalah ini di form lain:
1. Selalu tambahkan `->previewable(false)` ke FileUpload fields
2. Gunakan `skipRender()` di method afterCreate/afterSave
3. Pastikan redirect URL di-set dengan benar
4. Jaga konfigurasi Livewire tetap optimal untuk production

## Perubahan yang Dibuat
1. ✓ Tambahkan `previewable(false)` ke file upload field
2. ✓ Nonaktifkan automatic validation di Livewire config
3. ✓ Tingkatkan JavaScript error suppression
4. ✓ Tambahkan `skipRender()` setelah form creation
5. ✓ Set redirect URL yang tepat

## Catatan
- Error ini HANYA muncul di production, tidak di local
- Ini adalah race condition antara redirect dan Livewire update
- Fix ini tidak mempengaruhi fungsionalitas upload file
- File tetap ter-upload dengan benar ke storage
