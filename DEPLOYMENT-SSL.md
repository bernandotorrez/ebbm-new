# Deployment Guide - Laravel + Nginx + SSL

## Persiapan SSL Certificate

1. **Copy file SSL ke folder `docker/ssl/`:**
   ```bash
   cp /path/to/fullchain.pem docker/ssl/
   cp /path/to/basarnas.go.id.key docker/ssl/
   ```

2. **Pastikan file SSL ada:**
   ```bash
   ls -la docker/ssl/
   # Harus ada:
   # - fullchain.pem
   # - basarnas.go.id.key
   ```

## Deployment Steps

### 1. Build dan Start Container

```bash
# Build image
docker-compose build --no-cache

# Start services
docker-compose up -d

# Cek status
docker-compose ps
```

### 2. Cek Logs

```bash
# Cek semua logs
docker-compose logs -f

# Cek logs nginx saja
docker-compose logs -f nginx

# Cek logs app (PHP-FPM) saja
docker-compose logs -f app
```

### 3. Test Akses

- **HTTP:** http://e-bmp.basarnas.go.id → otomatis redirect ke HTTPS
- **HTTPS:** https://e-bmp.basarnas.go.id

### 4. Troubleshooting

**Jika nginx error:**
```bash
# Masuk ke container nginx
docker exec -it ebbm_nginx sh

# Test config
nginx -t

# Cek SSL files
ls -la /etc/ssl/BasarnasSSL/
```

**Jika PHP-FPM error:**
```bash
# Masuk ke container app
docker exec -it ebbm_app sh

# Cek PHP-FPM status
php-fpm -t

# Cek permissions
ls -la storage/
```

**Jika koneksi ke database gagal:**
```bash
# Cek MySQL container
docker-compose logs mysql

# Test koneksi dari app container
docker exec -it ebbm_app sh
nc -zv mysql 3306
```

## Update SSL Certificate

Jika certificate expired atau perlu update:

```bash
# 1. Copy certificate baru
cp /path/to/new-fullchain.pem docker/ssl/fullchain.pem
cp /path/to/new-key.key docker/ssl/basarnas.go.id.key

# 2. Restart nginx saja (tidak perlu rebuild)
docker-compose restart nginx

# 3. Cek logs
docker-compose logs -f nginx
```

## Maintenance Commands

```bash
# Restart semua services
docker-compose restart

# Restart nginx saja
docker-compose restart nginx

# Restart app (PHP-FPM) saja
docker-compose restart app

# Stop semua
docker-compose down

# Stop dan hapus volumes (HATI-HATI!)
docker-compose down -v

# Rebuild tanpa cache
docker-compose build --no-cache

# Lihat resource usage
docker stats
```

## Architecture

```
Internet
    ↓
HAProxy (10.0.3.42)
    ↓
Nginx Container (Port 80/443)
    ↓
PHP-FPM Container (Port 9000)
    ↓
MySQL Container (Port 3306)
```

## File Structure

```
project/
├── docker/
│   ├── nginx/
│   │   └── default.conf          # Nginx config
│   ├── ssl/
│   │   ├── fullchain.pem         # SSL certificate
│   │   └── basarnas.go.id.key    # SSL private key
│   ├── php/
│   │   ├── php.ini
│   │   ├── opcache.ini
│   │   └── php-fpm-pool.conf     # PHP-FPM config
│   └── supervisor/
│       └── supervisord.conf       # Supervisor config
├── docker-compose.yml             # Docker compose
└── Dockerfile                     # App image
```

## Security Notes

- SSL files di `.gitignore` - tidak akan ter-commit
- PHP-FPM hanya listen di internal network
- Nginx sebagai reverse proxy
- Real IP dari HAProxy di-forward ke aplikasi
