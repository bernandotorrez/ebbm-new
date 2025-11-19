# Deployment Guide - EBBM Application

## Quick Start

### Deployment Sederhana (Recommended)

```bash
# 1. Pull code terbaru
git pull origin main

# 2. Restart Docker
docker compose down
docker compose up -d

# 3. WAJIB: Clear browser cache
# Chrome/Edge: Ctrl+Shift+Delete
# Firefox: Ctrl+Shift+Delete
# Atau buka Incognito mode
```

**Selesai!** Semua fix dan optimization berjalan otomatis.

## Apa yang Terjadi Otomatis?

Saat `docker compose up -d`, script `post-startup.sh` akan otomatis menjalankan:

1. ✓ **Database Migrations** - Update database schema
2. ✓ **Storage Setup** - Setup directories dan permissions
3. ✓ **Clear Caches** - Clear app, config, route, view, event cache
4. ✓ **Clear Filament Cache** - Clear Filament component cache
5. ✓ **Livewire Fix** - Clear temp files, setup directories, fix permissions
6. ✓ **Production Optimization** - Cache config, routes, views (jika production)

## Monitoring Deployment

### Lihat Logs Post-Startup
```bash
docker compose logs -f app | grep -A 50 "Post-Startup"
```

Anda akan melihat output seperti:
```
=========================================
Post-Startup Tasks Starting...
=========================================

1. Running database migrations...
   ✓ Migrations completed

2. Setting up storage...
   ✓ Storage setup completed

3. Clearing all caches...
   ✓ Application caches cleared

4. Clearing Filament cache...
   ✓ Filament cache cleared

5. Applying Livewire fix...
   ✓ Livewire fix applied

6. Optimizing for production...
   ✓ Production optimization completed

=========================================
✓ Post-Startup Tasks Completed!
=========================================
```

### Cek Container Status
```bash
docker compose ps
```

Semua container harus status "Up".

### Cek Livewire Directory
```bash
docker exec -it ebbl_app ls -la storage/app/livewire-tmp
```

Output harus menunjukkan directory dengan permissions 775.

## Troubleshooting

### Error 404 Masih Muncul?

**Penyebab paling umum: Browser cache!**

```bash
# 1. WAJIB clear browser cache
Ctrl + Shift + Delete

# 2. Hard refresh
Ctrl + Shift + R

# 3. Atau test di Incognito mode
```

### Container Tidak Start?

```bash
# Cek logs
docker compose logs app

# Cek error
docker compose logs app | grep -i error

# Rebuild dari awal
docker compose down -v
docker compose up -d --build
```

### Livewire Error Masih Ada?

```bash
# Manual fix
docker exec -it ebbl_app sh -c "
  mkdir -p storage/app/livewire-tmp && 
  chmod -R 775 storage/app/livewire-tmp && 
  chown -R www-data:www-data storage/app/livewire-tmp &&
  php artisan optimize:clear &&
  php artisan filament:clear-cached-components &&
  php artisan config:cache
"

# Restart
docker compose restart
```

### Permissions Error?

```bash
# Fix all permissions
docker exec -it ebbl_app sh -c "
  chmod -R 775 storage bootstrap/cache public && 
  chown -R www-data:www-data storage bootstrap/cache public
"
```

## Deployment Lengkap (Full Rebuild)

Jika perlu rebuild image, install dependencies, dll:

```bash
bash deploy-after-pull.sh
```

Script ini akan:
- Rebuild Docker images
- Install Composer dependencies
- Run migrations
- Clear all caches
- Setup Livewire
- Fix permissions
- Restart containers

## Maintenance

### Clear Livewire Temp Files
```bash
# Otomatis setiap restart (>24 jam)
# Atau manual:
docker exec -it ebbl_app php artisan livewire:delete-uploaded-files --hours=24
```

### Clear All Caches
```bash
docker exec -it ebbl_app php artisan optimize:clear
docker exec -it ebbl_app php artisan filament:clear-cached-components
docker compose restart
```

### Monitoring Livewire
```bash
# Cek ukuran directory
docker exec -it ebbl_app du -sh storage/app/livewire-tmp

# Cek jumlah files
docker exec -it ebbl_app find storage/app/livewire-tmp -type f | wc -l
```

## Verifikasi Deployment Berhasil

### 1. Cek Logs
```bash
docker compose logs app | tail -50
```

Harus ada pesan: "✓ Post-Startup Tasks Completed!"

### 2. Test Upload File
1. Login ke aplikasi
2. Buka form Delivery Order
3. Upload file dan submit
4. Pastikan tidak ada popup error 404

### 3. Cek Browser Console
Buka DevTools (F12) → Console, harus muncul:
```
Livewire 404 fix initialized
```

Saat upload file:
```
File upload started
Redirect detected, will suppress 404 errors
```

## Best Practices

1. ✓ **Selalu pull code terbaru** sebelum restart
2. ✓ **Gunakan `docker compose down && docker compose up -d`** untuk deployment
3. ✓ **Clear browser cache** setelah deployment
4. ✓ **Monitor logs** untuk memastikan tidak ada error
5. ✓ **Test di staging** sebelum production
6. ✓ **Backup database** sebelum run migrations

## Scripts Available

- `post-startup.sh` - Berjalan otomatis saat container start (RECOMMENDED)
- `deploy-after-pull.sh` - Full deployment dengan rebuild
- `deploy-silent.sh` - Silent deployment
- `force-refresh-assets.sh` - Force refresh assets & cache
- `fix-file-upload-docker.sh` - Fix file upload error
- `fix-production-404.sh` - Fix 404 errors

## Documentation

- `README-DEPLOYMENT.md` - Panduan deployment (file ini)
- `QUICK-DEPLOY.md` - Quick reference
- `DEPLOYMENT-LIVEWIRE-FIX.md` - Detail Livewire fix
- `TROUBLESHOOTING-404-KEMBALI.md` - Troubleshooting error 404
- `TROUBLESHOOTING-FILE-UPLOAD.md` - Troubleshooting file upload
- `FIX-SUMMARY.md` - Ringkasan fix yang diterapkan

## Support

Jika masih ada masalah, cek dokumentasi troubleshooting atau hubungi tim development.
