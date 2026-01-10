# Checklist Deployment Laravel + Docker

## Persiapan Sebelum Deploy

### 1. Setup File .env
```bash
# Copy file .env.production ke .env
cp .env.production .env

# PENTING: Generate APP_KEY baru untuk production
# Buka file .env dan pastikan APP_KEY kosong atau hapus valuenya
# Nanti akan di-generate otomatis saat container start
```

### 2. Update Konfigurasi Database di .env
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=ebbm
DB_USERNAME=ebbm_user
DB_PASSWORD=ebbm_password
```

### 3. Update APP_URL dan Domain di .env
```env
# Ganti dengan domain/IP server Anda
APP_URL=http://xxx.com
```

### 4. Update Nginx Server Name
Edit file `docker/nginx/default.conf`, ganti:
```nginx
server_name localhost;
```

Dengan:
```nginx
server_name localhost xxx.com www.xxx.com _;
```

Ganti `xxx.com` dengan domain Anda yang sebenarnya.

## Deploy dengan Docker Compose

### Build dan Start Container
```bash
# Build image (pertama kali atau setelah update code)
docker-compose build --no-cache

# Start container
docker-compose up -d

# Cek status container
docker-compose ps
```

### Cek Logs
```bash
# Lihat semua logs
docker-compose logs -f

# Lihat logs app saja
docker-compose logs -f app

# Lihat logs mysql saja
docker-compose logs -f mysql
```

## Troubleshooting

### Container Tidak Start
```bash
# Cek logs untuk error
docker-compose logs app

# Restart container
docker-compose restart app
```

### Database Connection Error
```bash
# Pastikan MySQL sudah ready
docker-compose logs mysql

# Test koneksi dari container app
docker-compose exec app php artisan db:show
```

### Permission Error
```bash
# Fix permission storage
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Clear Cache
```bash
# Clear semua cache
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

### Migrate Database
```bash
# Run migration
docker-compose exec app php artisan migrate --force

# Run migration dengan seed
docker-compose exec app php artisan migrate:fresh --seed --force
```

## Akses Aplikasi

- Web: http://localhost atau http://your-server-ip
- Admin Panel (Filament): http://localhost/admin

## Stop & Remove Container

```bash
# Stop container
docker-compose down

# Stop dan hapus volume (HATI-HATI: data MySQL akan hilang)
docker-compose down -v
```

## Update Aplikasi

```bash
# Pull code terbaru
git pull

# Rebuild dan restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migration jika ada perubahan database
docker-compose exec app php artisan migrate --force
```

## Masalah Umum

### 1. Port 80 sudah digunakan
Edit `docker-compose.yml`, ubah port mapping:
```yaml
ports:
  - "8080:80"  # Akses via http://localhost:8080
```

### 2. Port 3306 sudah digunakan
Edit `docker-compose.yml`, ubah port MySQL:
```yaml
ports:
  - "3307:3306"  # MySQL accessible di port 3307
```

### 3. Livewire/Filament tidak berfungsi
```bash
# Publish ulang assets
docker-compose exec app php artisan livewire:publish --assets --force
docker-compose exec app php artisan filament:assets
docker-compose exec app php artisan optimize:clear
```

### 4. 404 Not Found untuk semua route
- Cek nginx config di `docker/nginx/default.conf`
- Pastikan `root /var/www/html/public;` sudah benar
- Restart container: `docker-compose restart app`
