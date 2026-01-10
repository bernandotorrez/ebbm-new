# Troubleshooting Domain Tidak Bisa Diakses

## Masalah: Domain sudah pointing ke IP server tapi tidak bisa diakses

### Checklist Diagnosa

#### 1. Verifikasi DNS Propagation
```bash
# Cek apakah domain sudah resolve ke IP server
nslookup xxx.com

# Atau pakai ping
ping xxx.com

# Harusnya menunjukkan IP server Anda
```

**Jika IP tidak sesuai:**
- DNS belum propagate (tunggu 1-24 jam)
- Setting DNS di registrar salah
- Gunakan https://dnschecker.org untuk cek propagation global

#### 2. Cek Firewall Server
```bash
# Cek apakah port 80 terbuka
netstat -tuln | grep :80

# Atau di Windows
netstat -an | findstr :80
```

**Jika port 80 tidak listening:**
```bash
# Cek container running
docker-compose ps

# Cek port mapping
docker-compose port app 80
```

**Jika firewall block:**
```bash
# Linux (UFW)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Linux (iptables)
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT

# Windows Firewall
# Buka Windows Defender Firewall > Inbound Rules > New Rule
# Pilih Port > TCP > 80, 443 > Allow
```

#### 3. Cek Nginx Server Name
**MASALAH UTAMA:** Nginx hanya accept request untuk `localhost`

**Solusi:** Update `docker/nginx/default.conf`
```nginx
server {
    listen 80;
    server_name localhost xxx.com www.xxx.com _;
    # ... rest of config
}
```

Ganti `xxx.com` dengan domain Anda yang sebenarnya.

**Penjelasan:**
- `xxx.com` - domain utama
- `www.xxx.com` - subdomain www
- `_` - catch-all untuk IP langsung atau domain lain

#### 4. Update APP_URL di .env
```env
# Ganti dengan domain production
APP_URL=http://xxx.com

# Atau kalau pakai HTTPS
APP_URL=https://xxx.com
```

#### 5. Rebuild & Restart Container
```bash
# Stop container
docker-compose down

# Rebuild (karena nginx config berubah)
docker-compose build --no-cache

# Start lagi
docker-compose up -d

# Cek logs
docker-compose logs -f app
```

#### 6. Test dari Server Langsung
```bash
# Test dari dalam server (harusnya berhasil)
curl -I http://localhost

# Test dengan domain dari dalam server
curl -I http://xxx.com

# Test dengan IP
curl -I http://YOUR_SERVER_IP
```

#### 7. Cek dari Luar (Client)
```bash
# Test dari komputer lain
curl -I http://xxx.com

# Atau buka di browser
http://xxx.com
```

## Skenario Masalah & Solusi

### Skenario 1: DNS Belum Propagate
**Gejala:** `nslookup xxx.com` tidak menunjukkan IP server

**Solusi:**
- Tunggu 1-24 jam untuk DNS propagation
- Cek setting DNS di registrar/cloudflare
- Sementara akses pakai IP: `http://YOUR_SERVER_IP`

### Skenario 2: Firewall Block Port 80
**Gejala:** Timeout saat akses domain, tapi `curl localhost` dari server berhasil

**Solusi:**
```bash
# Cek firewall
sudo ufw status

# Allow port 80 & 443
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### Skenario 3: Nginx Server Name Salah
**Gejala:** Akses IP berhasil, tapi domain tidak

**Solusi:** Update `server_name` di nginx config (sudah diperbaiki)

### Skenario 4: Container Tidak Running
**Gejala:** Connection refused

**Solusi:**
```bash
# Cek status
docker-compose ps

# Cek logs untuk error
docker-compose logs app

# Restart
docker-compose restart app
```

### Skenario 5: Port 80 Sudah Dipakai
**Gejala:** Container tidak bisa start, error "port already in use"

**Solusi 1:** Stop service yang pakai port 80
```bash
# Cek siapa yang pakai port 80
# Linux
sudo lsof -i :80

# Windows
netstat -ano | findstr :80

# Stop service (contoh: Apache)
sudo systemctl stop apache2
```

**Solusi 2:** Ganti port di docker-compose.yml
```yaml
ports:
  - "8080:80"  # Akses via http://xxx.com:8080
```

### Skenario 6: Cloud Provider Security Group
**Gejala:** Firewall server sudah allow, tapi tetap tidak bisa

**Solusi:** Cek Security Group di cloud provider
- **AWS EC2:** Security Groups > Inbound Rules > Allow TCP 80, 443
- **Google Cloud:** VPC Firewall > Create rule > Allow TCP 80, 443
- **Azure:** Network Security Group > Inbound rules > Allow TCP 80, 443
- **DigitalOcean:** Networking > Firewalls > Add rule TCP 80, 443

## Quick Fix Script

Buat file `fix-domain.sh`:
```bash
#!/bin/bash

echo "=== Domain Troubleshooting ==="
echo ""

# 1. Cek DNS
echo "1. Checking DNS resolution..."
nslookup xxx.com
echo ""

# 2. Cek container
echo "2. Checking containers..."
docker-compose ps
echo ""

# 3. Cek port
echo "3. Checking port 80..."
netstat -tuln | grep :80
echo ""

# 4. Test localhost
echo "4. Testing localhost..."
curl -I http://localhost
echo ""

# 5. Rebuild & restart
echo "5. Rebuilding and restarting..."
docker-compose down
docker-compose build --no-cache
docker-compose up -d
echo ""

# 6. Show logs
echo "6. Showing logs (Ctrl+C to exit)..."
docker-compose logs -f app
```

Windows version `fix-domain.bat`:
```batch
@echo off
echo === Domain Troubleshooting ===
echo.

echo 1. Checking DNS resolution...
nslookup xxx.com
echo.

echo 2. Checking containers...
docker-compose ps
echo.

echo 3. Checking port 80...
netstat -an | findstr :80
echo.

echo 4. Rebuilding and restarting...
docker-compose down
docker-compose build --no-cache
docker-compose up -d
echo.

echo 5. Showing logs...
docker-compose logs app
pause
```

## Verifikasi Setelah Fix

### 1. Dari Server
```bash
# Test localhost
curl -I http://localhost

# Test domain
curl -I http://xxx.com

# Harusnya dapat response 200 atau 302
```

### 2. Dari Browser
```
http://xxx.com
http://www.xxx.com
http://YOUR_SERVER_IP
```

Semua harusnya bisa diakses dan menampilkan aplikasi.

### 3. Cek Logs
```bash
# Harusnya tidak ada error
docker-compose logs app | grep -i error
```

## Setup HTTPS (Opsional tapi Recommended)

Setelah HTTP jalan, setup HTTPS dengan Let's Encrypt:

### Opsi 1: Pakai Reverse Proxy (Recommended)
Install Caddy atau Nginx di host, biarkan Docker di port 8080:

**docker-compose.yml:**
```yaml
ports:
  - "127.0.0.1:8080:80"  # Hanya accessible dari localhost
```

**Caddy (install di host):**
```
xxx.com {
    reverse_proxy localhost:8080
}
```

Caddy otomatis handle SSL certificate.

### Opsi 2: Certbot di Container
Lebih kompleks, perlu modify Dockerfile untuk include certbot.

## Kontak & Support

Jika masih bermasalah setelah semua langkah di atas:

1. Cek logs: `docker-compose logs -f app`
2. Cek nginx error: `docker-compose exec app cat /var/log/nginx/error.log`
3. Cek PHP-FPM: `docker-compose exec app cat /var/log/php-fpm.log`
4. Test manual: `docker-compose exec app curl -I http://localhost`

Sertakan output dari command di atas saat minta bantuan.
