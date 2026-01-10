# Perbaikan Konfigurasi Docker & Nginx

## Masalah yang Ditemukan & Diperbaiki

### 1. ❌ Netcat (nc) Tidak Tersedia di Alpine
**Masalah:** Script `entrypoint.sh` menggunakan `nc -z mysql 3306` untuk cek koneksi database, tapi `nc` tidak terinstall di Alpine Linux.

**Solusi:** Ganti dengan `php artisan db:show` yang sudah built-in Laravel.

```bash
# SEBELUM (error):
while ! nc -z mysql 3306; do
  sleep 1
done

# SESUDAH (fixed):
max_attempts=30
attempt=0
until php artisan db:show 2>/dev/null || [ $attempt -eq $max_attempts ]; do
  attempt=$((attempt + 1))
  echo "Waiting for database connection... (attempt $attempt/$max_attempts)"
  sleep 2
done
```

### 2. ❌ Storage Volume Tidak Persistent
**Masalah:** Storage di-comment di `docker-compose.yml`, sehingga setiap restart container, uploaded files dan logs hilang.

**Solusi:** Aktifkan volume untuk storage dengan named volumes untuk logs & framework, bind mount untuk public files.

```yaml
# SEBELUM:
volumes:
  - .env:/var/www/html/.env:rw
  # - ./storage:/var/www/html/storage  # commented

# SESUDAH:
volumes:
  - .env:/var/www/html/.env:rw
  - ./storage/app/public:/var/www/html/storage/app/public
  - storage_logs:/var/www/html/storage/logs
  - storage_framework:/var/www/html/storage/framework
```

### 3. ⚠️ PHP-FPM Socket vs TCP (Minor)
**Masalah:** Dockerfile membuat socket file tapi tidak digunakan. Nginx menggunakan TCP `127.0.0.1:9000` yang sudah benar.

**Solusi:** Hapus pembuatan socket yang tidak perlu di Dockerfile.

```dockerfile
# SEBELUM:
RUN mkdir -p /var/run/php-fpm \
    && touch /var/run/php-fpm.sock \
    && chown www-data:www-data /var/run/php-fpm.sock

# SESUDAH:
RUN mkdir -p /var/run/php-fpm
```

### 4. ⚠️ .env.production Masih Mode Development
**Masalah:** File `.env.production` masih menggunakan:
- `APP_ENV=local` (harusnya `production`)
- `APP_DEBUG=true` (harusnya `false`)
- `LOG_LEVEL=debug` (harusnya `error`)

**Solusi:** Update ke production settings.

```env
# SEBELUM:
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug

# SESUDAH:
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

## Konfigurasi yang Sudah Benar ✅

### 1. Nginx Configuration
- ✅ PHP-FPM menggunakan TCP `127.0.0.1:9000` (benar untuk same-container)
- ✅ Livewire routes sudah diprioritaskan
- ✅ Security headers sudah lengkap
- ✅ Gzip compression enabled
- ✅ Static assets caching configured
- ✅ File upload size 20MB

### 2. Docker Compose
- ✅ Network configuration benar
- ✅ MySQL credentials konsisten
- ✅ DNS fallback (8.8.8.8, 8.8.4.4)
- ✅ Restart policy `unless-stopped`

### 3. Dockerfile
- ✅ Multi-stage build untuk Vite assets
- ✅ PHP 8.3 dengan extensions lengkap
- ✅ Supervisor untuk manage PHP-FPM + Nginx
- ✅ Health check configured
- ✅ Proper permissions untuk www-data

## Cara Deploy Setelah Perbaikan

### 1. Setup .env
```bash
# Copy dan edit
copy .env.production .env

# Edit file .env:
# - Ganti APP_URL dengan domain Anda
# - Ganti DB_PASSWORD dengan password yang kuat
# - Kosongkan APP_KEY (akan di-generate otomatis)
```

### 2. Deploy
```bash
# Windows
deploy-production.bat

# Linux/Mac
./deploy.sh
```

### 3. Verifikasi
```bash
# Cek container running
docker-compose ps

# Cek logs
docker-compose logs -f app

# Test database connection
docker-compose exec app php artisan db:show

# Akses aplikasi
# http://localhost atau http://your-domain.com
```

## Monitoring & Maintenance

### Cek Logs
```bash
# All logs
docker-compose logs -f

# App only
docker-compose logs -f app

# Last 100 lines
docker-compose logs --tail=100 app
```

### Clear Cache
```bash
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

### Restart Services
```bash
# Restart app container
docker-compose restart app

# Restart all
docker-compose restart
```

### Database Backup
```bash
# Backup
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql

# Restore
docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm < backup.sql
```

## Security Checklist untuk Production

- [ ] Ganti `MYSQL_ROOT_PASSWORD` di docker-compose.yml
- [ ] Ganti `MYSQL_PASSWORD` di docker-compose.yml dan .env
- [ ] Set `APP_DEBUG=false` di .env
- [ ] Set `APP_ENV=production` di .env
- [ ] Generate APP_KEY baru (otomatis saat first run)
- [ ] Update `APP_URL` dengan domain production
- [ ] Jangan expose MySQL port jika tidak perlu (comment port 3306)
- [ ] Setup HTTPS dengan reverse proxy (nginx/caddy) di depan container
- [ ] Setup firewall untuk restrict akses
- [ ] Regular backup database

## Troubleshooting

### Container Crash Loop
```bash
# Cek logs untuk error
docker-compose logs app

# Common issues:
# - Permission error: fix dengan chmod/chown
# - Database connection: cek credentials di .env
# - Missing APP_KEY: akan di-generate otomatis
```

### 502 Bad Gateway
```bash
# Cek PHP-FPM running
docker-compose exec app ps aux | grep php-fpm

# Restart container
docker-compose restart app
```

### Database Connection Refused
```bash
# Cek MySQL running
docker-compose exec mysql mysql -u root -prootpassword -e "SELECT 1"

# Cek dari app container
docker-compose exec app php artisan db:show
```
