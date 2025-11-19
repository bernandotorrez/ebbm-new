# Troubleshooting: Error 404 Muncul Lagi Setelah Sempat Berhasil

## Masalah
Error 404 di POST /livewire/update sempat hilang, tapi sekarang muncul lagi di production.

## Penyebab Umum

### 1. Browser Cache
Browser masih menggunakan versi lama dari JavaScript fix.

**Solusi:**
```bash
# Hard refresh browser
Ctrl + Shift + R (Chrome/Edge/Firefox)
Ctrl + F5 (Firefox)
Cmd + Shift + R (Mac)

# Atau buka Incognito/Private mode untuk test
```

### 2. Server Cache Belum Di-clear
Cache di server masih menyimpan konfigurasi lama.

**Solusi:**
```bash
# Local
bash force-refresh-assets.sh

# Docker
docker exec -it ebbl_app php artisan optimize:clear
docker exec -it ebbl_app php artisan config:cache
docker compose restart
```

### 3. JavaScript File Tidak Ter-load
File `fix-livewire-redirect.js` tidak ter-load di browser.

**Cara Cek:**
1. Buka browser DevTools (F12)
2. Tab Network
3. Refresh halaman
4. Cari file `fix-livewire-redirect.js`
5. Pastikan status 200 (bukan 404 atau 304)

**Solusi jika tidak ter-load:**
```bash
# Pastikan file ada
ls -la public/js/fix-livewire-redirect.js

# Clear cache dan rebuild
php artisan optimize:clear
php artisan config:cache
```

### 4. Livewire Hook Tidak Berjalan
JavaScript hook tidak kompatibel dengan versi Livewire.

**Cara Cek:**
1. Buka browser Console (F12 → Console)
2. Refresh halaman
3. Cari pesan: "Livewire 404 fix initialized"
4. Jika tidak ada, berarti hook tidak berjalan

**Solusi:**
File JavaScript sudah diupdate dengan versi yang lebih kompatibel. Pastikan:
- Clear browser cache (Ctrl+Shift+R)
- Clear server cache
- Restart server/container

### 5. Asset URL Tidak Benar
Asset URL tidak sesuai dengan domain production.

**Cara Cek:**
```bash
# Cek di browser DevTools → Network
# URL JavaScript harus: https://your-domain.com/js/fix-livewire-redirect.js
# Bukan: http://localhost/js/...
```

**Solusi:**
```bash
# Pastikan APP_URL benar di .env
APP_URL=https://your-production-domain.com

# Clear config
php artisan config:clear
php artisan config:cache
```

## Langkah-langkah Fix

### Step 1: Clear Server Cache
```bash
# Docker
docker exec -it ebbl_app php artisan optimize:clear
docker exec -it ebbl_app php artisan filament:clear-cached-components
docker exec -it ebbl_app php artisan config:cache

# Local
php artisan optimize:clear
php artisan filament:clear-cached-components
php artisan config:cache
```

### Step 2: Restart Server
```bash
# Docker
docker compose restart

# Local (jika pakai php artisan serve)
# Stop (Ctrl+C) dan start lagi
php artisan serve
```

### Step 3: Clear Browser Cache
**Penting!** Ini sering dilupakan.

```
Chrome/Edge: Ctrl + Shift + Delete → Clear browsing data
Firefox: Ctrl + Shift + Delete → Clear recent history
Safari: Cmd + Option + E → Empty caches

Atau gunakan Incognito/Private mode untuk test
```

### Step 4: Hard Refresh Browser
```
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

### Step 5: Verify JavaScript Loaded
1. Buka DevTools (F12)
2. Tab Console
3. Refresh halaman
4. Cari pesan: "Livewire 404 fix initialized"
5. Test upload file
6. Cek apakah ada pesan: "Redirect detected, will suppress 404 errors"

## Script Otomatis

### Force Refresh Everything
```bash
bash force-refresh-assets.sh
```

Script ini akan:
1. Clear semua cache
2. Touch JavaScript file (update timestamp)
3. Rebuild cache
4. Restart container (jika Docker)

## Verifikasi Fix Berhasil

### 1. Cek Console Browser
Setelah refresh, di Console harus muncul:
```
Livewire 404 fix initialized
```

### 2. Test Upload File
Saat submit form dengan file upload:
```
File upload started
Redirect detected, will suppress 404 errors
Suppressing 404 error (redirect or upload in progress)
```

### 3. Cek Network Tab
Di Network tab:
- Request ke `/livewire/update` mungkin masih ada
- Status 404 mungkin masih muncul
- **TAPI popup error tidak muncul** ← ini yang penting!

## Catatan Penting

1. **Browser cache adalah penyebab paling umum** - selalu clear browser cache dulu
2. **Hard refresh tidak cukup** - kadang perlu clear browsing data
3. **Test di Incognito mode** - untuk memastikan bukan masalah cache
4. **Cek Console log** - untuk memastikan JavaScript ter-load
5. **Error 404 di console itu normal** - yang penting popup tidak muncul

## Jika Masih Error

### Debug Mode
Aktifkan debug untuk lihat error detail:
```bash
# .env
APP_DEBUG=true

# Jangan lupa matikan lagi setelah debug
APP_DEBUG=false
```

### Cek Laravel Log
```bash
# Docker
docker exec -it ebbl_app tail -f storage/logs/laravel.log

# Local
tail -f storage/logs/laravel.log
```

### Cek Nginx Error Log
```bash
docker compose logs app | grep error
```

### Test dengan cURL
```bash
# Test Livewire endpoint
curl -X POST https://your-domain.com/livewire/update \
  -H "Content-Type: application/json" \
  -d '{"components":[]}'
```

## Prevention

Untuk mencegah masalah ini terulang:

1. **Selalu clear cache setelah update code**
2. **Gunakan cache busting** (sudah diterapkan di AppServiceProvider)
3. **Test di Incognito mode** sebelum deploy
4. **Dokumentasikan versi yang working**
5. **Backup sebelum update**

## Rollback (Jika Diperlukan)

Jika fix ini menyebabkan masalah lain:

```bash
# Hapus JavaScript fix
rm public/js/fix-livewire-redirect.js

# Revert AppServiceProvider
git checkout app/Providers/AppServiceProvider.php

# Clear cache
php artisan optimize:clear
php artisan config:cache
```
