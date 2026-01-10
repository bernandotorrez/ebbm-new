# Setup Domain untuk Aplikasi Laravel

## 🎯 Masalah: Domain Sudah Pointing tapi Tidak Bisa Diakses

### ✅ Solusi Sudah Diterapkan

File `docker/nginx/default.conf` sudah diupdate untuk accept request dari domain manapun:
```nginx
server_name localhost xxx.com www.xxx.com _;
```

## 📋 Checklist Setup Domain

### ☑️ Step 1: Update Nginx Config
**File:** `docker/nginx/default.conf`

Ganti `xxx.com` dengan domain Anda:
```nginx
server {
    listen 80;
    server_name localhost yourdomain.com www.yourdomain.com _;
    # ...
}
```

### ☑️ Step 2: Update .env
**File:** `.env`

```env
APP_URL=http://yourdomain.com
```

### ☑️ Step 3: Rebuild Container
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### ☑️ Step 4: Verifikasi DNS
```bash
nslookup yourdomain.com
```

Harusnya menunjukkan IP server Anda.

### ☑️ Step 5: Cek Firewall
Pastikan port 80 & 443 terbuka:

**Windows Firewall:**
- Control Panel > Windows Defender Firewall
- Advanced Settings > Inbound Rules
- New Rule > Port > TCP > 80, 443 > Allow

**Cloud Provider (AWS/GCP/Azure/DO):**
- Security Group / Firewall Rules
- Allow Inbound TCP 80, 443 from 0.0.0.0/0

### ☑️ Step 6: Test Akses
```bash
# Dari server
curl -I http://localhost
curl -I http://yourdomain.com

# Dari browser
http://yourdomain.com
```

## 🚀 Quick Start

### Automated Fix
```bash
fix-domain.bat
```

### Manual Fix (3 Menit)
```bash
# 1. Edit nginx config
notepad docker/nginx/default.conf
# Ganti xxx.com dengan domain Anda

# 2. Edit .env
notepad .env
# Set APP_URL=http://yourdomain.com

# 3. Rebuild
docker-compose down && docker-compose build --no-cache && docker-compose up -d

# 4. Test
curl http://yourdomain.com
```

## 🔍 Troubleshooting

### Problem 1: DNS Belum Resolve
**Gejala:** `nslookup yourdomain.com` tidak menunjukkan IP server

**Solusi:**
- Cek setting DNS di registrar (Namecheap, GoDaddy, dll)
- Tunggu propagation 1-24 jam
- Sementara akses pakai IP: `http://YOUR_SERVER_IP`

### Problem 2: Connection Timeout
**Gejala:** Browser loading terus, tidak ada response

**Solusi:**
- Cek firewall server (allow port 80)
- Cek cloud security group (allow port 80)
- Cek container running: `docker-compose ps`

### Problem 3: 502 Bad Gateway
**Gejala:** Nginx error 502

**Solusi:**
```bash
# Cek PHP-FPM
docker-compose exec app ps aux | grep php-fpm

# Restart container
docker-compose restart app

# Cek logs
docker-compose logs app
```

### Problem 4: 404 Not Found
**Gejala:** Nginx jalan tapi semua route 404

**Solusi:**
```bash
# Cek nginx config
docker-compose exec app cat /etc/nginx/http.d/default.conf

# Harusnya ada: root /var/www/html/public;
```

## 📋 Available Scripts

### Windows (.bat)
- `deploy-production.bat` - Full production deployment
- `deploy.bat` - Standard deployment
- `fix-domain.bat` - Domain troubleshooting

### Linux (.sh)
- `deploy-production.sh` - Full production deployment
- `quick-deploy.sh` - Quick deploy (no prompts)
- `fix-domain.sh` - Interactive domain troubleshooting
- `check-domain.sh` - Domain diagnostic (non-interactive)
- `setup-firewall.sh` - Setup UFW firewall

**Linux users:** Run `./make-executable.sh` first to make scripts executable.

## Quick Start

### Windows
```bash
deploy-production.bat
```

### Linux
```bash
# Make scripts executable
chmod +x make-executable.sh
./make-executable.sh

# Deploy
./deploy-production.sh
```

## 🔐 Setup HTTPS (Recommended)

Setelah HTTP jalan, setup SSL:

### Opsi 1: Cloudflare (Termudah) ⭐
1. Daftar di cloudflare.com
2. Tambahkan domain
3. Update nameserver di registrar
4. Enable "Flexible SSL"
5. Done! Otomatis HTTPS

### Opsi 2: Caddy Reverse Proxy
```bash
# Install Caddy di server
# Buat Caddyfile:
yourdomain.com {
    reverse_proxy localhost:8080
}

# Caddy otomatis handle SSL certificate
```

### Opsi 3: Nginx + Certbot
Manual setup, lebih kompleks.

## 📞 Butuh Bantuan?

Jika masih bermasalah, jalankan command ini:

```bash
# Diagnostic info
echo "=== DNS Check ===" && nslookup yourdomain.com
echo "=== Container Status ===" && docker-compose ps
echo "=== Port Check ===" && netstat -an | findstr :80
echo "=== Logs ===" && docker-compose logs --tail=30 app
echo "=== Test Localhost ===" && curl -I http://localhost
```

Kirim output dari command di atas untuk bantuan lebih lanjut.

## ✅ Verifikasi Sukses

Jika setup berhasil, Anda bisa akses:
- ✅ http://yourdomain.com → Aplikasi Laravel
- ✅ http://www.yourdomain.com → Aplikasi Laravel
- ✅ http://yourdomain.com/admin → Filament Admin Panel
- ✅ http://YOUR_SERVER_IP → Aplikasi Laravel

## 🎉 Next Steps

1. ✅ Setup HTTPS dengan Cloudflare atau Let's Encrypt
2. ✅ Setup database backup otomatis
3. ✅ Setup monitoring (Uptime Robot, etc)
4. ✅ Setup CDN untuk static assets
5. ✅ Optimize performance (Redis cache, etc)

---

**Catatan:** Ganti `yourdomain.com` dengan domain Anda yang sebenarnya di semua file konfigurasi.
