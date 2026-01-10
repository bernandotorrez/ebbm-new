# 🚀 Deploy Sekarang - 3 Langkah Saja!

## Step 1: Upload ke Server

Upload semua file project ke server Linux Anda.

---

## Step 2: Edit Domain

Edit file `docker/nginx/default.conf` baris 3:

```nginx
server_name localhost yourdomain.com www.yourdomain.com _;
```

**Ganti `yourdomain.com` dengan domain Anda yang sebenarnya!**

---

## Step 3: Jalankan Script

```bash
chmod +x setup-all.sh && ./setup-all.sh
```

Script akan tanya domain Anda, ketik domain lalu Enter.

**SELESAI!** 🎉

---

## Verifikasi

Buka browser:
- http://yourdomain.com
- http://yourdomain.com/admin

---

## Kalau Ada Masalah

### Domain tidak bisa diakses?

```bash
# Cek firewall
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Restart container
docker-compose restart app
```

### Cek logs kalau error:

```bash
docker-compose logs app
```

---

## That's It!

Cuma 3 langkah:
1. ✅ Upload file
2. ✅ Edit domain di nginx config
3. ✅ Run `./setup-all.sh`

**Dokumentasi lengkap:** Baca `START-HERE.md` kalau butuh detail.
