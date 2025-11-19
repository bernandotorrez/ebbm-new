# Deployment dengan Livewire Fix

## Perubahan yang Diterapkan

Semua script deployment sudah diupdate untuk include fix Livewire secara otomatis.

### 1. Post-Startup Script (docker/entrypoints/post-startup.sh)
Script ini berjalan otomatis setiap kali container start.

**Perubahan:**
- Clear Filament cache
- Clear Livewire temporary files (24 jam)
- Setup Livewire directory dengan permissions yang benar
- Cache optimization untuk production

### 2. Deploy After Pull (deploy-after-pull.sh)
Script untuk deployment setelah git pull.

**Perubahan:**
- Clear Filament cache
- Clear Livewire temporary files
- Setup Livewire directory
- Fix permissions untuk livewire-tmp

### 3. Deploy Silent (deploy-silent.sh)
Script untuk deployment silent mode.

**Perubahan:**
- Setup Livewire directory
- Clear caches (optimize, filament, livewire)
- Cache optimization

## Cara Deployment

### Deployment Otomatis (Recommended)
```bash
# Pull latest code
git pull origin main

# Restart Docker (fix Livewire berjalan otomatis)
docker compose down
docker compose up -d
```

**Post-startup.sh akan otomatis menjalankan:**
1. ✓ Database migrations
2. ✓ Storage setup
3. ✓ Clear all caches (app, config, route, view, event)
4. ✓ Clear Filament cache
5. ✓ Clear Livewire temporary files (>24 jam)
6. ✓ Setup Livewire directories dengan permissions
7. ✓ Optimize untuk production (jika APP_ENV=production)

### Deployment dengan Script (Full Rebuild)
```bash
# Pull latest code
git pull origin main

# Deploy dengan script (rebuild image, install dependencies, dll)
bash deploy-after-pull.sh
```

Script ini akan melakukan full rebuild:
1. ✓ Rebuild Docker images
2. ✓ Install dependencies
3. ✓ Run migrations
4. ✓ Clear all caches (termasuk Filament & Livewire)
5. ✓ Setup Livewire directories
6. ✓ Rebuild caches untuk production
7. ✓ Fix permissions
8. ✓ Restart containers

### Deployment Silent
```bash
bash deploy-silent.sh
```

Untuk deployment cepat tanpa output verbose.

### Manual Deployment
```bash
# Stop containers
docker compose down

# Rebuild and start
docker compose up -d --build

# Wait for containers
sleep 15

# Clear caches
docker exec -it ebbl_app php artisan optimize:clear
docker exec -it ebbl_app php artisan filament:clear-cached-components
docker exec -it ebbl_app php artisan livewire:delete-uploaded-files --hours=24

# Setup Livewire
docker exec -it ebbl_app mkdir -p storage/app/livewire-tmp
docker exec -it ebbl_app chmod -R 775 storage/app/livewire-tmp
docker exec -it ebbl_app chown -R www-data:www-data storage/app/livewire-tmp

# Rebuild caches
docker exec -it ebbl_app php artisan config:cache
docker exec -it ebbl_app php artisan route:cache
docker exec -it ebbl_app php artisan view:cache

# Restart
docker compose restart
```

## Verifikasi Deployment

### 1. Cek Container Status
```bash
docker compose ps
```

Semua container harus status "Up".

### 2. Cek Logs
```bash
docker compose logs -f app
```

Cari pesan:
- "Post-startup tasks completed."
- "Clearing Filament cache..."
- "Clearing Livewire temporary files..."
- "Setting up Livewire directories..."

### 3. Cek Livewire Directory
```bash
docker exec -it ebbl_app ls -la storage/app/livewire-tmp
```

Directory harus ada dengan permissions 775.

### 4. Test Upload File
1. Login ke aplikasi
2. Buka form Delivery Order
3. Upload file dan submit
4. Pastikan tidak ada popup error 404

### 5. Cek Browser Console
Buka DevTools (F12) → Console, harus muncul:
```
Livewire 404 fix initialized
```

## Troubleshooting

### Container Tidak Start
```bash
# Cek logs
docker compose logs app

# Rebuild dari awal
docker compose down -v
docker compose up -d --build
```

### Error 404 Masih Muncul
```bash
# Clear browser cache (PENTING!)
Ctrl + Shift + Delete

# Hard refresh
Ctrl + Shift + R

# Atau test di Incognito mode
```

### Livewire Directory Tidak Ada
```bash
# Manual setup
docker exec -it ebbl_app sh -c "mkdir -p storage/app/livewire-tmp && chmod -R 775 storage/app/livewire-tmp && chown -R www-data:www-data storage/app/livewire-tmp"
```

### Permissions Error
```bash
# Fix all permissions
docker exec -it ebbl_app sh -c "chmod -R 775 storage bootstrap/cache public && chown -R www-data:www-data storage bootstrap/cache public"
```

## Maintenance

### Clear Livewire Temporary Files
```bash
# Manual cleanup (hapus file lebih dari 24 jam)
docker exec -it ebbl_app php artisan livewire:delete-uploaded-files --hours=24

# Cleanup semua
docker exec -it ebbl_app php artisan livewire:delete-uploaded-files --hours=0
```

### Clear All Caches
```bash
docker exec -it ebbl_app php artisan optimize:clear
docker exec -it ebbl_app php artisan filament:clear-cached-components
docker exec -it ebbl_app php artisan config:cache
docker compose restart
```

### Monitoring Livewire Temp Files
```bash
# Cek ukuran directory
docker exec -it ebbl_app du -sh storage/app/livewire-tmp

# Cek jumlah files
docker exec -it ebbl_app find storage/app/livewire-tmp -type f | wc -l
```

## Best Practices

1. **Selalu gunakan deployment script** - Jangan manual deploy
2. **Clear browser cache setelah deploy** - Untuk load asset terbaru
3. **Monitor logs** - Cek error setelah deployment
4. **Test di staging dulu** - Sebelum deploy ke production
5. **Backup database** - Sebelum run migrations
6. **Cleanup Livewire files** - Secara berkala (sudah otomatis di post-startup)

## Rollback

Jika deployment bermasalah:

```bash
# Stop containers
docker compose down

# Checkout ke commit sebelumnya
git checkout <previous-commit>

# Deploy ulang
bash deploy-after-pull.sh
```

## Notes

- Post-startup script berjalan otomatis setiap container restart
- Livewire temporary files di-cleanup otomatis (>24 jam)
- Cache di-rebuild otomatis untuk production environment
- Permissions di-set otomatis untuk Livewire directory
- Browser cache tetap harus di-clear manual oleh user
