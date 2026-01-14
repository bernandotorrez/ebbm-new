# Pre-Deployment Checklist

## ✅ Konfigurasi yang Sudah Benar

### Docker Configuration
- [x] **docker-compose.yml** - Service app dan nginx terpisah
- [x] **Dockerfile** - PHP-FPM tanpa nginx
- [x] **PHP-FPM** - Listen di `0.0.0.0:9000` untuk koneksi dari nginx container
- [x] **Supervisor** - Hanya jalankan PHP-FPM (nginx sudah terpisah)
- [x] **Volumes** - Shared `./public` dan `./storage` antara app dan nginx

### Nginx Configuration
- [x] **HTTP → HTTPS redirect** - Port 80 redirect ke 443
- [x] **SSL Configuration** - TLS 1.2 & 1.3
- [x] **Real IP dari HAProxy** - `set_real_ip_from 10.0.3.42`
- [x] **Client max body size** - 50M untuk upload
- [x] **FastCGI pass** - Connect ke `app:9000`
- [x] **Livewire routes** - Prioritas tinggi untuk upload/update
- [x] **Static assets** - Cache control untuk CSS/JS/images
- [x] **Security headers** - X-Frame-Options, CSP, dll

### Laravel/Livewire Specific
- [x] **Livewire upload** - Max 50M
- [x] **Livewire routes** - `/livewire/upload-file`, `/livewire/update`
- [x] **Admin routes** - `/admin` untuk Filament
- [x] **Storage link** - Symbolic link di entrypoint
- [x] **Permissions** - 775 untuk storage dan bootstrap/cache

### Security
- [x] **SSL files** - Di `.gitignore`
- [x] **Sensitive files** - Protected (vendor, config, database, .env)
- [x] **Server tokens** - Hidden
- [x] **.env** - Di `.gitignore`

## 📋 Yang Perlu Dilakukan Sebelum Deploy

### 1. SSL Certificate
```bash
# Copy SSL files ke docker/ssl/
[ ] fullchain.pem
[ ] basarnas.go.id.key
```

### 2. Environment File
```bash
# Pastikan .env sudah dikonfigurasi
[ ] APP_ENV=production
[ ] APP_DEBUG=false
[ ] APP_URL=https://e-bmp.basarnas.go.id
[ ] Database credentials
[ ] APP_KEY (akan di-generate otomatis jika kosong)
```

### 3. Build Assets
```bash
# Build Vite assets
[ ] npm install
[ ] npm run build
```

### 4. Permissions
```bash
# Set permissions untuk storage dan cache
[ ] chmod -R 775 storage bootstrap/cache
[ ] chown -R www-data:www-data storage bootstrap/cache
```

### 5. Database
```bash
# Pastikan database sudah siap
[ ] Database created
[ ] User dan password sesuai .env
```

## 🚀 Deployment Commands

```bash
# 1. Build image
docker-compose build --no-cache

# 2. Start services
docker-compose up -d

# 3. Cek status
docker-compose ps

# 4. Cek logs
docker-compose logs -f

# 5. Test akses
curl -I http://e-bmp.basarnas.go.id
curl -I https://e-bmp.basarnas.go.id
```

## 🔍 Post-Deployment Verification

```bash
# Cek nginx config
[ ] docker exec -it ebmp_nginx nginx -t

# Cek PHP-FPM
[ ] docker exec -it ebmp_app php-fpm -t

# Cek koneksi database
[ ] docker exec -it ebmp_app php artisan migrate:status

# Cek storage link
[ ] docker exec -it ebmp_app ls -la public/storage

# Cek permissions
[ ] docker exec -it ebmp_app ls -la storage/

# Test upload Livewire
[ ] Login ke admin
[ ] Test upload file
[ ] Cek max 50M bisa upload
```

## ⚠️ Common Issues

### Issue: Nginx 502 Bad Gateway
**Solution:**
```bash
# Cek PHP-FPM running
docker-compose logs app

# Cek koneksi
docker exec -it ebmp_nginx nc -zv app 9000
```

### Issue: SSL Certificate Error
**Solution:**
```bash
# Cek SSL files ada
docker exec -it ebmp_nginx ls -la /etc/ssl/BasarnasSSL/

# Cek nginx config
docker exec -it ebmp_nginx nginx -t
```

### Issue: Permission Denied
**Solution:**
```bash
# Fix permissions
docker exec -it ebmp_app chmod -R 775 storage bootstrap/cache
docker exec -it ebmp_app chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Livewire Upload Failed
**Solution:**
```bash
# Cek livewire-tmp folder
docker exec -it ebmp_app ls -la storage/app/livewire-tmp/

# Fix permissions
docker exec -it ebmp_app chmod -R 775 storage/app/livewire-tmp
docker exec -it ebmp_app chown -R www-data:www-data storage/app/livewire-tmp
```

## 📊 Monitoring

```bash
# Resource usage
docker stats

# Logs real-time
docker-compose logs -f

# Nginx access logs
docker exec -it ebmp_nginx tail -f /var/log/nginx/e-bmp.access.log

# Nginx error logs
docker exec -it ebmp_nginx tail -f /var/log/nginx/e-bmp.error.log
```
