# Troubleshooting Livewire 404 Error di Production/Docker

## Masalah
Error 404 di `POST /livewire/update` atau `/livewire/upload-file` setelah sukses submit form Add/Edit (muncul sebentar lalu hilang)

**Catatan:** Error ini muncul di SEMUA module yang ada file upload-nya

## Penyebab Umum
1. **Race condition**: Livewire mencoba update setelah redirect
2. Route cache tidak sesuai dengan environment production
3. APP_URL tidak sesuai dengan URL production
4. Nginx configuration tidak handle Livewire routes dengan benar
5. CSRF token expired atau tidak valid
6. `render_on_redirect` setting di config/livewire.php

## Solusi

### 0. Quick Fix - Update Config & Permissions (RECOMMENDED)
**Step 1:** Pastikan setting ini di `config/livewire.php`:

```php
'render_on_redirect' => false,

'temporary_file_upload' => [
    'disk' => 'local',
    'directory' => 'livewire-tmp',
    'middleware' => 'throttle:60,1',
    'max_upload_time' => 10,
    'cleanup' => true,
],
```

**Step 2:** Buat directory dan set permissions:
```bash
docker exec -it ebbl_app sh -c "mkdir -p storage/app/livewire-tmp && chmod -R 775 storage/app/livewire-tmp && chown -R www-data:www-data storage/app/livewire-tmp"
```

**Step 3:** Clear config cache:
```bash
docker exec -it ebbl_app php artisan config:clear
docker exec -it ebbl_app php artisan config:cache
docker compose restart
```

### 1. Clear Cache di Docker Container
Jalankan perintah berikut di dalam container:

```bash
# Masuk ke container
docker exec -it ebbl_app sh

# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exit container
exit
```

### 2. Update APP_URL di .env Production
Pastikan APP_URL sesuai dengan domain production:

```env
# Development
APP_URL=http://127.0.0.1:8000

# Production (contoh)
APP_URL=https://yourdomain.com
# atau
APP_URL=http://your-server-ip
```

Setelah update .env, restart container:
```bash
docker compose restart
```

### 3. Verifikasi Nginx Configuration
File: `docker/nginx/default.conf`

Pastikan ada konfigurasi untuk Livewire:
```nginx
location ^~ /livewire {
    try_files $uri $uri/ /index.php?$query_string;
}

location = /livewire/update {
    try_files $uri /index.php?$query_string;
}
```

### 4. Rebuild Docker Image
Jika masih error, rebuild image:

```bash
docker compose down
docker compose up -d --build
```

### 5. Check Logs
Lihat error logs untuk detail:

```bash
# Nginx logs
docker compose logs app | grep error

# Laravel logs
docker exec -it ebbl_app cat storage/logs/laravel.log
```

### 6. Manual Script Clear Cache
Jalankan script yang sudah disediakan:

```bash
docker exec -it ebbl_app sh /clear-cache.sh
```

## Prevention
Untuk mencegah error ini di masa depan:

1. **Selalu clear cache setelah deployment**
2. **Pastikan APP_URL sesuai dengan environment**
3. **Jangan cache route di development** (hanya di production)
4. **Test di staging environment sebelum production**

## Quick Fix Command
Jalankan satu perintah ini untuk fix cepat:

```bash
docker exec -it ebbl_app sh -c "php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache"
```

## Catatan Penting
- Error ini HANYA terjadi di production/Docker, tidak di development
- Biasanya terjadi setelah deployment atau update code
- Livewire membutuhkan route yang tepat untuk POST requests
- CSRF token harus valid (pastikan session driver bekerja dengan baik)
