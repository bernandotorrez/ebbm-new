# 🚀 Deployment Guide - EBMP Basarnas

## Platform Anda

### 🐧 Linux Server
**Start here:** [LINUX-QUICK-START.md](LINUX-QUICK-START.md)

Quick commands:
```bash
chmod +x make-executable.sh && ./make-executable.sh
./deploy-production.sh
```

### 🪟 Windows Server
**Start here:** [README-DOMAIN-SETUP.md](README-DOMAIN-SETUP.md)

Quick commands:
```cmd
deploy-production.bat
```

---

## 📚 Dokumentasi Lengkap

### Quick Start Guides
- **[LINUX-QUICK-START.md](LINUX-QUICK-START.md)** - Linux deployment (5 menit) ⭐
- **[QUICK-FIX-DOMAIN.md](QUICK-FIX-DOMAIN.md)** - Quick domain fix (5 menit) ⭐
- **[README-DOMAIN-SETUP.md](README-DOMAIN-SETUP.md)** - Domain setup guide

### Detailed Guides
- **[README-LINUX-DEPLOYMENT.md](README-LINUX-DEPLOYMENT.md)** - Complete Linux guide
- **[DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md)** - Deployment checklist
- **[TROUBLESHOOTING-DOMAIN.md](TROUBLESHOOTING-DOMAIN.md)** - Troubleshooting detail
- **[DEPLOYMENT-FIXES.md](DEPLOYMENT-FIXES.md)** - Technical fixes explained
- **[DOMAIN-FLOW-DIAGRAM.md](DOMAIN-FLOW-DIAGRAM.md)** - Visual flow diagrams

---

## 🎯 Masalah Umum & Solusi Cepat

### ❌ Domain sudah pointing tapi tidak bisa diakses

**Penyebab:** Nginx hanya accept request untuk `localhost`

**Solusi:**
1. Edit `docker/nginx/default.conf` baris 3:
   ```nginx
   server_name localhost yourdomain.com www.yourdomain.com _;
   ```
2. Rebuild: `docker-compose down && docker-compose build --no-cache && docker-compose up -d`

**Baca:** [QUICK-FIX-DOMAIN.md](QUICK-FIX-DOMAIN.md)

---

### ❌ Connection timeout / tidak bisa diakses dari luar

**Penyebab:** Firewall block port 80

**Solusi Linux:**
```bash
sudo ./setup-firewall.sh
# atau
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

**Solusi Windows:**
- Windows Defender Firewall > Inbound Rules > Allow TCP 80, 443

**Baca:** [TROUBLESHOOTING-DOMAIN.md](TROUBLESHOOTING-DOMAIN.md)

---

### ❌ Container tidak start / crash loop

**Solusi:**
```bash
# Check logs
docker-compose logs app

# Rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

**Baca:** [DEPLOYMENT-FIXES.md](DEPLOYMENT-FIXES.md)

---

### ❌ 502 Bad Gateway

**Solusi:**
```bash
# Restart container
docker-compose restart app

# Check PHP-FPM
docker-compose exec app ps aux | grep php-fpm

# Check logs
docker-compose logs app
```

---

## 🛠️ Available Scripts

### Linux (.sh)
| Script | Deskripsi |
|--------|-----------|
| `./deploy-production.sh` | Full production deployment |
| `./quick-deploy.sh` | Quick deploy (no prompts) |
| `./fix-domain.sh` | Interactive domain troubleshooting |
| `./check-domain.sh yourdomain.com` | Domain diagnostic report |
| `./setup-firewall.sh` | Setup UFW firewall |
| `./make-executable.sh` | Make all scripts executable |

### Windows (.bat)
| Script | Deskripsi |
|--------|-----------|
| `deploy-production.bat` | Full production deployment |
| `deploy.bat` | Standard deployment |
| `fix-domain.bat` | Domain troubleshooting |

---

## ✅ Deployment Checklist

### Pre-deployment
- [ ] Copy `.env.production` to `.env`
- [ ] Edit `.env`: set `APP_URL` dengan domain Anda
- [ ] Edit `.env`: set `DB_PASSWORD` dengan password aman
- [ ] Edit `docker/nginx/default.conf`: update `server_name`
- [ ] Edit `docker-compose.yml`: ganti MySQL passwords

### Deployment
- [ ] Run deployment script
- [ ] Check container status: `docker-compose ps`
- [ ] Check logs: `docker-compose logs app`
- [ ] Test localhost: `curl http://localhost`
- [ ] Test domain: `curl http://yourdomain.com`

### Post-deployment
- [ ] Setup firewall (allow port 80, 443)
- [ ] Setup HTTPS (Cloudflare/Let's Encrypt)
- [ ] Setup database backup
- [ ] Setup monitoring
- [ ] Test admin panel: `http://yourdomain.com/admin`

**Baca:** [DEPLOYMENT-CHECKLIST.md](DEPLOYMENT-CHECKLIST.md)

---

## 🔍 Quick Diagnostic

### Linux
```bash
./check-domain.sh yourdomain.com
```

### Manual Check
```bash
# DNS
nslookup yourdomain.com

# Container
docker-compose ps

# Logs
docker-compose logs --tail=50 app

# Test
curl -I http://localhost
curl -I http://yourdomain.com
```

---

## 🔐 Security Checklist

- [ ] `APP_ENV=production` di .env
- [ ] `APP_DEBUG=false` di .env
- [ ] Ganti `MYSQL_ROOT_PASSWORD` di docker-compose.yml
- [ ] Ganti `MYSQL_PASSWORD` di docker-compose.yml dan .env
- [ ] Setup firewall (allow 22, 80, 443 only)
- [ ] Hide MySQL port (comment out port 3306)
- [ ] Setup HTTPS
- [ ] Regular database backups

---

## 🌐 Setup HTTPS

### Option 1: Cloudflare (Termudah) ⭐
1. Daftar di cloudflare.com
2. Add domain
3. Update nameservers di registrar
4. Enable "Flexible SSL"
5. Done! Auto HTTPS

### Option 2: Let's Encrypt + Certbot
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Option 3: Caddy Reverse Proxy
```bash
sudo apt install caddy
# Edit Caddyfile
yourdomain.com {
    reverse_proxy localhost:8080
}
```

---

## 📞 Butuh Bantuan?

### 1. Check Logs
```bash
docker-compose logs --tail=100 app
```

### 2. Run Diagnostic
```bash
# Linux
./check-domain.sh yourdomain.com

# Manual
nslookup yourdomain.com
docker-compose ps
netstat -tuln | grep :80
curl -I http://localhost
```

### 3. Common Issues
- DNS belum propagate → Tunggu 1-24 jam
- Firewall block → Allow port 80/443
- Container crash → Check logs
- Nginx config salah → Update server_name

---

## 📖 Dokumentasi Tambahan

- [README.md](README.md) - Main project README
- [DEPLOYMENT.md](DEPLOYMENT.md) - Original deployment guide
- [DEPLOYMENT-GUIDE.md](DEPLOYMENT-GUIDE.md) - Deployment guide

---

## 🎉 Quick Commands Reference

### Deployment
```bash
# Linux
./deploy-production.sh

# Windows
deploy-production.bat
```

### Logs
```bash
docker-compose logs -f app
```

### Restart
```bash
docker-compose restart app
```

### Clear Cache
```bash
docker-compose exec app php artisan optimize:clear
```

### Database Backup
```bash
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql
```

### Database Restore
```bash
docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm < backup.sql
```

---

**Catatan:** Ganti `yourdomain.com` dengan domain Anda yang sebenarnya di semua konfigurasi.
