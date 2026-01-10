# 🚀 Cara Deploy Aplikasi Ini

## 📖 Langkah Deploy (Simple!)

### 👉 Baca File Ini: **[LANGKAH-DEPLOY.md](LANGKAH-DEPLOY.md)** ⭐

File itu berisi 5 langkah mudah untuk deploy. Ikuti itu aja, selesai!

---

## ⚡ TL;DR (Too Long Didn't Read)

### 1. Edit Domain
Edit `docker/nginx/default.conf` baris 3:
```nginx
server_name localhost yourdomain.com www.yourdomain.com _;
```

### 2. Upload ke Server
Upload semua file ke server Linux.

### 3. Run Script
```bash
chmod +x setup-all.sh && ./setup-all.sh
```

### 4. Buka Browser
http://yourdomain.com

**SELESAI!** 🎉

---

## 📚 Dokumentasi

### File Penting (Baca Kalau Butuh)

| File | Untuk Apa |
|------|-----------|
| **[LANGKAH-DEPLOY.md](LANGKAH-DEPLOY.md)** | ⭐ Panduan deploy step-by-step |
| **[CHECKLIST-SIMPLE.md](CHECKLIST-SIMPLE.md)** | Checklist singkat |
| **[DEPLOY-SEKARANG.md](DEPLOY-SEKARANG.md)** | Deploy cepat (3 langkah) |
| **[TROUBLESHOOTING-DOMAIN.md](TROUBLESHOOTING-DOMAIN.md)** | Kalau ada masalah |
| **[COMMANDS-REFERENCE.md](COMMANDS-REFERENCE.md)** | Semua command |

### File Detail (Skip Kalau Gak Butuh)

| File | Untuk Apa |
|------|-----------|
| LINUX-QUICK-START.md | Linux guide lengkap |
| README-LINUX-DEPLOYMENT.md | Complete Linux guide |
| DEPLOYMENT-README.md | Master guide |
| DOCS-INDEX.md | Index semua dokumentasi |
| Dan 5 file lainnya... | Detail teknis |

**Intinya:** Cukup baca **LANGKAH-DEPLOY.md** aja! 👍

---

## 🔧 Command Berguna

### Lihat Logs
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

### Backup Database
```bash
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql
```

---

## 🔍 Troubleshooting

### Domain tidak bisa diakses?
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
docker-compose restart app
```

### Container error?
```bash
docker-compose logs app
docker-compose restart app
```

### Diagnostic lengkap?
```bash
./check-domain.sh yourdomain.com
```

---

## 📞 Butuh Bantuan?

1. Baca **[TROUBLESHOOTING-DOMAIN.md](TROUBLESHOOTING-DOMAIN.md)**
2. Jalankan: `./check-domain.sh yourdomain.com`
3. Lihat logs: `docker-compose logs app`

---

## ✅ Checklist Sukses

- [ ] Container running: `docker-compose ps`
- [ ] Localhost OK: `curl http://localhost`
- [ ] Domain OK: Buka `http://yourdomain.com`
- [ ] Admin OK: Buka `http://yourdomain.com/admin`

---

**Catatan:** Ganti `yourdomain.com` dengan domain Anda yang sebenarnya!

**Waktu deploy:** 10-15 menit (termasuk build Docker)

**Kesulitan?** Baca [LANGKAH-DEPLOY.md](LANGKAH-DEPLOY.md) untuk panduan detail.
