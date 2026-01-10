# Quick Fix: Domain Tidak Bisa Diakses

## Masalah Utama yang Sudah Diperbaiki ✅

**Nginx hanya accept request untuk `localhost`**

File `docker/nginx/default.conf` sudah diupdate:
```nginx
server_name localhost xxx.com www.xxx.com _;
```

## Langkah Cepat (5 Menit)

### 1. Ganti Domain di Nginx Config
```bash
# Edit file: docker/nginx/default.conf
# Baris 3, ganti xxx.com dengan domain Anda
server_name localhost yourdomain.com www.yourdomain.com _;
```

### 2. Update .env
```bash
# Edit file: .env
APP_URL=http://yourdomain.com
```

### 3. Rebuild & Restart
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### 4. Test
```bash
# Dari server
curl -I http://localhost
curl -I http://yourdomain.com

# Dari browser
http://yourdomain.com
```

## Jika Masih Tidak Bisa

### Cek 1: DNS Sudah Resolve?
```bash
nslookup yourdomain.com
# Harusnya menunjukkan IP server Anda
```

**Jika IP salah/tidak ada:**
- Cek setting DNS di registrar
- Tunggu propagation (1-24 jam)
- Sementara akses pakai IP: `http://YOUR_SERVER_IP`

### Cek 2: Firewall Allow Port 80?
```bash
# Windows
netstat -an | findstr :80

# Harusnya ada: 0.0.0.0:80 atau :::80
```

**Jika tidak ada:**
```bash
# Cek container
docker-compose ps

# Cek logs
docker-compose logs app
```

**Jika ada tapi tidak bisa diakses dari luar:**
- Buka Windows Firewall
- Allow Inbound TCP port 80 & 443
- Atau disable firewall sementara untuk test

### Cek 3: Cloud Security Group (AWS/GCP/Azure)
Jika pakai cloud provider, pastikan Security Group allow:
- Inbound TCP port 80 (HTTP)
- Inbound TCP port 443 (HTTPS)
- Source: 0.0.0.0/0 (anywhere)

### Cek 4: Container Running?
```bash
docker-compose ps

# Harusnya status: Up
# Jika Exit atau Restarting, cek logs:
docker-compose logs app
```

## Automated Fix

Jalankan script otomatis:
```bash
fix-domain.bat
```

Script akan:
1. Cek DNS resolution
2. Cek container status
3. Cek port 80
4. Rebuild & restart container
5. Test akses

## Verifikasi Berhasil

Jika sudah berhasil, Anda bisa akses:
- ✅ http://yourdomain.com
- ✅ http://www.yourdomain.com
- ✅ http://YOUR_SERVER_IP

Semua harusnya menampilkan aplikasi Laravel.

## Next Steps: Setup HTTPS

Setelah HTTP jalan, setup SSL certificate:

### Opsi 1: Cloudflare (Paling Mudah)
1. Tambahkan domain ke Cloudflare
2. Set DNS A record ke IP server
3. Enable "Flexible SSL" di Cloudflare
4. Done! Otomatis dapat HTTPS

### Opsi 2: Let's Encrypt + Caddy
1. Install Caddy di server
2. Config Caddy sebagai reverse proxy
3. Caddy otomatis handle SSL

### Opsi 3: Let's Encrypt + Certbot
Manual setup, lebih kompleks.

## Troubleshooting Lanjutan

Lihat file: `TROUBLESHOOTING-DOMAIN.md`

## Butuh Bantuan?

Jalankan command ini dan kirim outputnya:
```bash
# 1. DNS check
nslookup yourdomain.com

# 2. Container status
docker-compose ps

# 3. Logs
docker-compose logs --tail=50 app

# 4. Port check
netstat -an | findstr :80

# 5. Test dari server
curl -I http://localhost
curl -I http://yourdomain.com
```
