# ✅ Checklist Deploy - Copy Paste Aja!

## Persiapan (Di Komputer Lokal)

### 1. Edit Domain
```bash
# Edit file: docker/nginx/default.conf
# Baris 3, ganti dengan domain Anda:
server_name localhost example.com www.example.com _;
```

### 2. Upload ke Server
Upload semua file ke server Linux.

---

## Di Server Linux

### 3. Jalankan Command Ini (Copy-Paste):

```bash
# Make script executable dan jalankan
chmod +x setup-all.sh && ./setup-all.sh
```

**Script akan:**
- Setup .env
- Tanya domain Anda (ketik domain lalu Enter)
- Setup firewall
- Build & start Docker
- Selesai!

---

## Verifikasi

### 4. Test Akses:

```bash
# Test dari server
curl http://localhost

# Buka di browser
http://yourdomain.com
http://yourdomain.com/admin
```

---

## Troubleshooting (Kalau Ada Masalah)

### Domain tidak bisa diakses?

```bash
# Allow firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Restart
docker-compose restart app
```

### Cek status:

```bash
# Lihat container
docker-compose ps

# Lihat logs
docker-compose logs app
```

### Masih error?

```bash
# Diagnostic lengkap
./check-domain.sh yourdomain.com
```

---

## Command Berguna

```bash
# Restart
docker-compose restart app

# Lihat logs
docker-compose logs -f app

# Clear cache
docker-compose exec app php artisan optimize:clear

# Backup database
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql
```

---

## Summary

**Cuma 4 langkah:**

1. ✅ Edit `docker/nginx/default.conf` (ganti domain)
2. ✅ Upload ke server
3. ✅ Run: `chmod +x setup-all.sh && ./setup-all.sh`
4. ✅ Buka browser: `http://yourdomain.com`

**DONE!** 🎉

---

## Kalau Butuh Detail

Baca file ini kalau butuh penjelasan lebih:
- **START-HERE.md** - Panduan lengkap
- **LINUX-QUICK-START.md** - Linux guide
- **TROUBLESHOOTING-DOMAIN.md** - Troubleshooting

Tapi seharusnya 4 langkah di atas sudah cukup! 👍
