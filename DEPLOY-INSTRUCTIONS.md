# Deployment Instructions - SSL di Server

## ⚠️ PENTING: SSL Files Harus Ada Sebelum Build

SSL certificate akan di-COPY ke dalam Docker image saat build, bukan di-mount dari host.

## Langkah Deployment

### 1. Di Server - Copy SSL Files

```bash
# Pastikan Anda sudah di direktori project
cd /path/to/ebmp-new

# Copy SSL files ke docker/ssl/
# File harus ada SEBELUM build image
ls -la docker/ssl/
# Harus ada:
# - fullchain.pem
# - basarnas.go.id.key
```

### 2. Set Permissions SSL Files

```bash
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key
```

### 3. Build dan Deploy

```bash
# Stop container lama
docker-compose down

# Build image (SSL akan di-copy ke dalam image)
docker-compose build --no-cache

# Start services
docker-compose up -d
```

### 4. Verify

```bash
# Cek status
docker-compose ps

# Cek logs nginx
docker-compose logs nginx

# Cek SSL files di dalam container
docker exec -it ebmp_nginx ls -la /etc/ssl/BasarnasSSL/

# Test HTTP redirect
curl -I http://localhost

# Test HTTPS
curl -I -k https://localhost
```

## Troubleshooting

### Error: Cannot load certificate

**Jika masih error SSL not found:**

```bash
# 1. Cek file SSL ada di host
ls -la docker/ssl/
# Harus ada fullchain.pem dan basarnas.go.id.key

# 2. Rebuild nginx image
docker-compose build --no-cache nginx

# 3. Restart
docker-compose up -d nginx

# 4. Cek di dalam container
docker exec -it ebmp_nginx ls -la /etc/ssl/BasarnasSSL/
```

### Jika File SSL Tidak Ada

```bash
# Copy dari backup
cp /backup/path/fullchain.pem docker/ssl/
cp /backup/path/basarnas.go.id.key docker/ssl/

# Set permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# Rebuild
docker-compose build --no-cache nginx
docker-compose up -d nginx
```

## Update SSL Certificate

Jika certificate expired atau perlu update:

```bash
# 1. Copy certificate baru
cp /path/to/new-fullchain.pem docker/ssl/fullchain.pem
cp /path/to/new-key.key docker/ssl/basarnas.go.id.key

# 2. Set permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# 3. Rebuild nginx image (karena SSL di-copy saat build)
docker-compose build --no-cache nginx

# 4. Restart nginx
docker-compose up -d nginx

# 5. Verify
curl -I https://e-bmp.basarnas.go.id
```

## Perbedaan dengan Mount Volume

**Sebelumnya (Mount - TIDAK BERFUNGSI di setup ini):**
```yaml
volumes:
  - ./docker/ssl:/etc/ssl/BasarnasSSL:ro  # ❌ Tidak berfungsi
```

**Sekarang (Copy saat Build - BERFUNGSI):**
```dockerfile
COPY ../ssl/fullchain.pem /etc/ssl/BasarnasSSL/fullchain.pem  # ✅ Berfungsi
```

**Keuntungan:**
- ✅ SSL files di dalam image, tidak tergantung mount
- ✅ Lebih portable
- ✅ Tidak ada masalah permission

**Kekurangan:**
- ⚠️ Harus rebuild image setiap update certificate
- ⚠️ Image size sedikit lebih besar

## Quick Commands

```bash
# Deploy lengkap
docker-compose down && docker-compose build --no-cache && docker-compose up -d

# Rebuild nginx saja
docker-compose build --no-cache nginx && docker-compose up -d nginx

# Cek SSL di container
docker exec -it ebmp_nginx ls -la /etc/ssl/BasarnasSSL/

# Test akses
curl -I http://e-bmp.basarnas.go.id
curl -I https://e-bmp.basarnas.go.id
```

## Checklist Deployment

- [ ] SSL files ada di `docker/ssl/`
- [ ] Permissions SSL sudah benar (644 untuk .pem, 600 untuk .key)
- [ ] File `.env` sudah dikonfigurasi
- [ ] Build assets sudah selesai (`npm run build`)
- [ ] Stop container lama (`docker-compose down`)
- [ ] Build image baru (`docker-compose build --no-cache`)
- [ ] Start services (`docker-compose up -d`)
- [ ] Cek logs tidak ada error (`docker-compose logs`)
- [ ] Test HTTP redirect (`curl -I http://...`)
- [ ] Test HTTPS (`curl -I https://...`)
- [ ] Test login admin
- [ ] Test upload file

## Architecture

```
Build Time:
docker/ssl/*.pem → COPY → /etc/ssl/BasarnasSSL/ (inside nginx image)

Runtime:
Internet → HAProxy → Nginx Container → PHP-FPM Container → MySQL Container
```
