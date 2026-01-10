# 🚀 Start Here - Quick Deployment

## 📖 Baca Ini Dulu!

**Terlalu banyak file?** Tenang, cuma perlu baca 1 file:

### 👉 **[LANGKAH-DEPLOY.md](LANGKAH-DEPLOY.md)** ⭐ BACA INI!

File itu berisi 5 langkah simple untuk deploy. Cukup ikuti itu aja!

---

## ⚡ Super Quick (1 Command)

Kalau mau cepat, jalankan ini di server:

```bash
chmod +x setup-all.sh && ./setup-all.sh
```

**Tapi sebelumnya:** Edit `docker/nginx/default.conf` baris 3, ganti domain!

---

## 📚 File Penting (Sisanya Skip Aja)

| File | Kapan Baca |
|------|------------|
| **[LANGKAH-DEPLOY.md](LANGKAH-DEPLOY.md)** | ⭐ BACA INI DULU! |
| **[CHECKLIST-SIMPLE.md](CHECKLIST-SIMPLE.md)** | Checklist singkat |
| **[TROUBLESHOOTING-DOMAIN.md](TROUBLESHOOTING-DOMAIN.md)** | Kalau ada masalah |
| **[COMMANDS-REFERENCE.md](COMMANDS-REFERENCE.md)** | Butuh command tertentu |

**File lainnya?** Skip aja, nanti baca kalau butuh detail.

---

## Linux (Recommended)

### One-Command Setup
```bash
chmod +x setup-all.sh && ./setup-all.sh
```

Script ini akan:
- ✅ Check Docker & docker-compose
- ✅ Setup .env file
- ✅ Configure domain di nginx
- ✅ Setup firewall (UFW)
- ✅ Build & start containers
- ✅ Verify installation

### Manual Setup (3 Commands)
```bash
# 1. Make scripts executable
chmod +x make-executable.sh && ./make-executable.sh

# 2. Setup environment
cp .env.production .env
nano .env  # Edit APP_URL dan DB_PASSWORD

# 3. Deploy
./deploy-production.sh
```

---

## Windows

### One-Command Deploy
```cmd
deploy-production.bat
```

### Manual Setup
1. Copy `.env.production` to `.env`
2. Edit `.env`: set `APP_URL` dan `DB_PASSWORD`
3. Run `deploy-production.bat`

---

## ⚠️ PENTING: Update Nginx Config

Edit `docker/nginx/default.conf` baris 3:
```nginx
server_name localhost yourdomain.com www.yourdomain.com _;
```

Ganti `yourdomain.com` dengan domain Anda!

---

## 🔍 Verify Installation

### Check Status
```bash
docker-compose ps
```

### Check Logs
```bash
docker-compose logs -f app
```

### Test Access
```bash
curl http://localhost
curl http://yourdomain.com
```

### Open in Browser
- http://localhost
- http://yourdomain.com
- http://yourdomain.com/admin

---

## ❌ Troubleshooting

### Domain tidak bisa diakses?

**Linux:**
```bash
./check-domain.sh yourdomain.com
```

**Windows:**
```cmd
fix-domain.bat
```

### Container tidak start?
```bash
docker-compose logs app
docker-compose restart app
```

### Firewall block?

**Linux:**
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

**Windows:**
- Windows Defender Firewall > Allow port 80, 443

---

## 📚 Full Documentation

### Quick Guides (5 min)
- **[LINUX-QUICK-START.md](LINUX-QUICK-START.md)** - Linux quick start
- **[QUICK-FIX-DOMAIN.md](QUICK-FIX-DOMAIN.md)** - Domain quick fix

### Complete Guides
- **[DEPLOYMENT-README.md](DEPLOYMENT-README.md)** - Master guide
- **[README-LINUX-DEPLOYMENT.md](README-LINUX-DEPLOYMENT.md)** - Complete Linux guide
- **[TROUBLESHOOTING-DOMAIN.md](TROUBLESHOOTING-DOMAIN.md)** - Troubleshooting

---

## 🎯 Common Issues

| Issue | Solution |
|-------|----------|
| Domain tidak bisa diakses | Update nginx `server_name`, allow firewall port 80 |
| Connection timeout | Check firewall & cloud security group |
| 502 Bad Gateway | Restart container: `docker-compose restart app` |
| Container crash | Check logs: `docker-compose logs app` |
| DNS not found | Wait 1-24h for propagation |

---

## ✅ Post-Deployment Checklist

- [ ] Test http://localhost
- [ ] Test http://yourdomain.com
- [ ] Test admin panel: http://yourdomain.com/admin
- [ ] Setup firewall (port 80, 443)
- [ ] Setup HTTPS (Cloudflare/Let's Encrypt)
- [ ] Setup database backup
- [ ] Change MySQL passwords
- [ ] Set APP_DEBUG=false

---

## 🔐 Security

### Must Do
```bash
# 1. Edit .env
APP_ENV=production
APP_DEBUG=false

# 2. Change MySQL passwords in docker-compose.yml
MYSQL_ROOT_PASSWORD=your_secure_password
MYSQL_PASSWORD=your_secure_password

# 3. Setup firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## 🌐 Setup HTTPS

### Cloudflare (Easiest)
1. Add domain to Cloudflare
2. Update nameservers
3. Enable "Flexible SSL"
4. Done!

### Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

---

## 📞 Need Help?

Run diagnostic:
```bash
# Linux
./check-domain.sh yourdomain.com

# Manual
docker-compose logs --tail=50 app
docker-compose ps
curl -I http://localhost
```

---

**Next:** Read [DEPLOYMENT-README.md](DEPLOYMENT-README.md) for complete guide.
