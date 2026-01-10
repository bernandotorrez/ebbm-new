# 📋 Langkah Deploy - Step by Step

## 🎯 Yang Perlu Anda Lakukan

### LANGKAH 1: Edit Domain (Di Komputer Lokal)
**File:** `docker/nginx/default.conf`  
**Baris:** 3

**Dari:**
```nginx
server_name localhost xxx.com www.xxx.com _;
```

**Jadi:**
```nginx
server_name localhost example.com www.example.com _;
```

⚠️ **Ganti `example.com` dengan domain Anda!**

---

### LANGKAH 2: Upload ke Server

Upload semua file project ke server Linux Anda.

Contoh pakai SCP:
```bash
scp -r /path/to/project user@server-ip:/home/user/project
```

Atau pakai FTP/SFTP client seperti FileZilla.

---

### LANGKAH 3: SSH ke Server

```bash
ssh user@server-ip
```

Masuk ke folder project:
```bash
cd /home/user/project
```

---

### LANGKAH 4: Jalankan Script Deploy

**Copy-paste command ini:**

```bash
chmod +x setup-all.sh && ./setup-all.sh
```

**Script akan:**
1. Check Docker ✅
2. Setup .env ✅
3. Tanya domain Anda → **Ketik domain lalu Enter**
4. Setup firewall ✅
5. Build Docker (tunggu 5-10 menit) ✅
6. Start aplikasi ✅

---

### LANGKAH 5: Verifikasi

**Buka browser:**
- http://yourdomain.com
- http://yourdomain.com/admin

**Harusnya sudah jalan!** 🎉

---

## 🔍 Kalau Ada Masalah

### Problem 1: Domain tidak bisa diakses

**Solusi:**
```bash
# Allow firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Restart
docker-compose restart app
```

### Problem 2: Container error

**Solusi:**
```bash
# Lihat error
docker-compose logs app

# Rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Problem 3: DNS belum propagate

**Solusi:**
- Tunggu 1-24 jam untuk DNS propagation
- Sementara akses pakai IP: `http://server-ip`

---

## 📞 Diagnostic

Kalau masih bermasalah, jalankan:

```bash
./check-domain.sh yourdomain.com
```

Atau manual:
```bash
# Cek DNS
nslookup yourdomain.com

# Cek container
docker-compose ps

# Cek logs
docker-compose logs app

# Test
curl http://localhost
```

---

## ✅ Checklist Sukses

Setelah deploy, pastikan:
- [ ] Container running: `docker-compose ps` → Status "Up"
- [ ] Localhost OK: `curl http://localhost` → HTTP 200/302
- [ ] Domain OK: Buka `http://yourdomain.com` di browser
- [ ] Admin OK: Buka `http://yourdomain.com/admin`

---

## 🎉 Selesai!

**Total waktu:** 10-15 menit (termasuk build Docker)

**Yang Anda lakukan:**
1. ✅ Edit domain di nginx config
2. ✅ Upload ke server
3. ✅ Run 1 command: `chmod +x setup-all.sh && ./setup-all.sh`
4. ✅ Buka browser

**Simple kan?** 😊

---

## 📚 Dokumentasi Lengkap

Kalau butuh detail lebih:
- **CHECKLIST-SIMPLE.md** - Checklist singkat
- **START-HERE.md** - Panduan lengkap
- **TROUBLESHOOTING-DOMAIN.md** - Troubleshooting

Tapi 5 langkah di atas harusnya sudah cukup! 👍
