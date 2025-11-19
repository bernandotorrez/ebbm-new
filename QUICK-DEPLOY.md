# Quick Deployment Guide

## Deployment Cepat (Otomatis)

### 1. Pull Latest Code
```bash
git pull origin main
```

### 2. Restart Docker
```bash
docker compose down
docker compose up -d
```

**Itu saja!** Semua fix Livewire akan berjalan otomatis di `post-startup.sh`:
- ✓ Clear all caches
- ✓ Clear Filament cache
- ✓ Clear Livewire temporary files
- ✓ Setup Livewire directories
- ✓ Fix permissions
- ✓ Optimize untuk production

### 3. Clear Browser Cache
**PENTING!** Setelah deployment, user harus clear browser cache:
- Chrome/Edge: `Ctrl + Shift + Delete`
- Firefox: `Ctrl + Shift + Delete`
- Atau buka Incognito/Private mode

## Deployment dengan Script (Opsional)

Jika ingin deployment lebih lengkap (rebuild image, install dependencies, dll):
```bash
bash deploy-after-pull.sh
```

## Apa yang Terjadi Otomatis?

✓ Rebuild Docker images
✓ Install dependencies
✓ Run migrations
✓ Clear all caches (app, config, route, view, filament, livewire)
✓ Setup Livewire directories dengan permissions yang benar
✓ Rebuild caches untuk production
✓ Fix permissions
✓ Restart containers

## Livewire Fix Included

Semua script deployment sudah include:
- Clear Filament cache
- Clear Livewire temporary files
- Setup Livewire directory (storage/app/livewire-tmp)
- Fix permissions untuk Livewire

## Verifikasi

### Cek Container
```bash
docker compose ps
```

### Cek Logs
```bash
docker compose logs -f app | grep -i livewire
```

### Test Upload
1. Login ke aplikasi
2. Buka form Delivery Order
3. Upload file dan submit
4. Pastikan tidak ada popup error 404

## Troubleshooting

### Error 404 Masih Muncul?
```bash
# 1. Clear browser cache (WAJIB!)
Ctrl + Shift + Delete

# 2. Hard refresh
Ctrl + Shift + R

# 3. Atau test di Incognito
```

### Container Error?
```bash
# Cek logs
docker compose logs app

# Restart
docker compose restart
```

### Need Manual Fix?
```bash
bash force-refresh-assets.sh
```

## Scripts Available

- `deploy-after-pull.sh` - Full deployment (recommended)
- `deploy-silent.sh` - Silent deployment
- `force-refresh-assets.sh` - Force refresh assets & cache
- `fix-file-upload-docker.sh` - Fix file upload error
- `fix-production-404.sh` - Fix 404 errors

## Documentation

- `DEPLOYMENT-LIVEWIRE-FIX.md` - Detail deployment dengan Livewire fix
- `TROUBLESHOOTING-404-KEMBALI.md` - Troubleshooting error 404
- `TROUBLESHOOTING-FILE-UPLOAD.md` - Troubleshooting file upload
- `FIX-SUMMARY.md` - Ringkasan fix yang diterapkan
