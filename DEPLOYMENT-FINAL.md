# Deployment Guide - Laravel + Nginx + SSL

## Prerequisites

✅ File SSL sudah ada di `docker/ssl/`:
- `fullchain.pem`
- `basarnas.go.id.key`

✅ File `.env` sudah dikonfigurasi dengan benar

✅ Build assets sudah selesai (`npm run build`)

## Deployment Steps

### 1. Di Server - Pastikan SSL Files Ada

```bash
# Cek file SSL
ls -la docker/ssl/
# Output harus:
# basarnas.go.id.key
# fullchain.pem

# Set permissions yang benar
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key
```

### 2. Build dan Start Container

```bash
# Stop container lama jika ada
docker-compose down

# Build image baru
docker-compose build --no-cache

# Start semua services
docker-compose up -d

# Cek status
docker-compose ps
```

### 3. Verifikasi Deployment

```bash
# Cek logs
docker-compose logs -f

# Cek nginx logs khusus
docker-compose logs nginx

# Cek app logs khusus
docker-compose logs app
```

### 4. Test Akses

```bash
# Test HTTP (harus redirect ke HTTPS)
curl -I http://e-bmp.basarnas.go.id
# Expected: HTTP/1.1 301 Moved Permanently
# Location: https://e-bmp.basarnas.go.id

# Test HTTPS
curl -I https://e-bmp.basarnas.go.id
# Expected: HTTP/2 200
```

### 5. Test dari Browser

Buka browser dan akses:
- `http://e-bmp.basarnas.go.id` → otomatis redirect ke HTTPS
- `https://e-bmp.basarnas.go.id` → aplikasi jalan dengan SSL

## Troubleshooting

### Error: Cannot load certificate

**Problem:**
```
nginx: [emerg] cannot load certificate "/etc/ssl/BasarnasSSL/fullchain.pem"
```

**Solution:**
```bash
# Cek file SSL ada
ls -la docker/ssl/

# Jika tidak ada, copy dari backup
cp /path/to/backup/fullchain.pem docker/ssl/
cp /path/to/backup/basarnas.go.id.key docker/ssl/

# Fix permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# Restart nginx
docker-compose restart nginx
```

### Error: 502 Bad Gateway

**Problem:** Nginx tidak bisa connect ke PHP-FPM

**Solution:**
```bash
# Cek app container running
docker-compose ps app

# Cek logs app
docker-compose logs app

# Test koneksi dari nginx ke app
docker exec -it ebbm_nginx nc -zv app 9000

# Restart app
docker-compose restart app
```

### Error: Permission Denied di Storage

**Problem:** Laravel tidak bisa write ke storage

**Solution:**
```bash
# Masuk ke app container
docker exec -it ebbm_app sh

# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Khusus livewire-tmp
chmod -R 775 storage/app/livewire-tmp
chown -R www-data:www-data storage/app/livewire-tmp

# Exit
exit
```

### Error: Database Connection Failed

**Problem:** App tidak bisa connect ke MySQL

**Solution:**
```bash
# Cek MySQL running
docker-compose ps mysql

# Cek logs MySQL
docker-compose logs mysql

# Test koneksi dari app
docker exec -it ebbm_app sh
nc -zv mysql 3306
exit

# Cek .env database config
cat .env | grep DB_
```

## Post-Deployment Checklist

```bash
# ✅ Semua container running
docker-compose ps

# ✅ Tidak ada error di logs
docker-compose logs | grep -i error

# ✅ HTTP redirect ke HTTPS
curl -I http://e-bmp.basarnas.go.id | grep 301

# ✅ HTTPS accessible
curl -I https://e-bmp.basarnas.go.id | grep 200

# ✅ Database migration status
docker exec -it ebbm_app php artisan migrate:status

# ✅ Storage link exists
docker exec -it ebbm_app ls -la public/storage

# ✅ Permissions correct
docker exec -it ebbm_app ls -la storage/

# ✅ Test login admin
# Buka browser: https://e-bmp.basarnas.go.id/admin

# ✅ Test upload file (max 50M)
# Login → Upload file → Cek berhasil
```

## Maintenance Commands

### Restart Services

```bash
# Restart semua
docker-compose restart

# Restart nginx saja
docker-compose restart nginx

# Restart app saja
docker-compose restart app

# Restart MySQL saja
docker-compose restart mysql
```

### View Logs

```bash
# All logs
docker-compose logs -f

# Nginx logs
docker-compose logs -f nginx

# App logs
docker-compose logs -f app

# MySQL logs
docker-compose logs -f mysql

# Nginx access log
docker exec -it ebbm_nginx tail -f /var/log/nginx/e-bmp.access.log

# Nginx error log
docker exec -it ebbm_nginx tail -f /var/log/nginx/e-bmp.error.log
```

### Update Application

```bash
# Pull latest code
git pull

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migrations
docker exec -it ebbm_app php artisan migrate --force

# Clear cache
docker exec -it ebbm_app php artisan cache:clear
docker exec -it ebbm_app php artisan config:clear
docker exec -it ebbm_app php artisan view:clear
```

### Update SSL Certificate

```bash
# Copy new certificate
cp /path/to/new-fullchain.pem docker/ssl/fullchain.pem
cp /path/to/new-key.key docker/ssl/basarnas.go.id.key

# Fix permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# Restart nginx only (no rebuild needed)
docker-compose restart nginx

# Verify
curl -I https://e-bmp.basarnas.go.id
```

### Backup Database

```bash
# Backup
docker exec ebbm_mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup-$(date +%Y%m%d).sql

# Restore
docker exec -i ebbm_mysql mysql -u ebbm_user -pebbm_password ebbm < backup-20260114.sql
```

## Monitoring

```bash
# Resource usage
docker stats

# Disk usage
docker system df

# Container health
docker-compose ps

# Network info
docker network inspect ebbm_network
```

## Architecture

```
Internet
    ↓
HAProxy (10.0.3.42) - Real IP Header
    ↓
Nginx Container (80/443) - SSL Termination + Reverse Proxy
    ↓
PHP-FPM Container (9000) - Laravel Application
    ↓
MySQL Container (3306) - Database
```

## Security Notes

- ✅ SSL/TLS 1.2 & 1.3 enabled
- ✅ Real IP dari HAProxy di-forward
- ✅ Security headers configured
- ✅ Sensitive files protected (.env, vendor, config)
- ✅ Server tokens hidden
- ✅ SSL files di .gitignore
- ✅ PHP-FPM hanya accessible dari nginx container
- ✅ MySQL hanya accessible dari app container

## Support

Jika ada masalah:
1. Cek logs: `docker-compose logs -f`
2. Cek status: `docker-compose ps`
3. Cek resource: `docker stats`
4. Review error di nginx error log
5. Review error di Laravel log: `docker/exec -it ebbm_app tail -f storage/logs/laravel.log`
