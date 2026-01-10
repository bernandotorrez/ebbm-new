# Flow Diagram: Request Domain ke Aplikasi

## 🌐 Normal Flow (Ketika Berhasil)

```
User Browser
    |
    | http://yourdomain.com
    |
    v
DNS Server
    |
    | Resolve ke IP Server (contoh: 123.45.67.89)
    |
    v
Internet
    |
    | Request ke 123.45.67.89:80
    |
    v
Server Firewall
    |
    | ✅ Allow port 80
    |
    v
Docker Container (Port 80)
    |
    | Request masuk ke Nginx
    |
    v
Nginx (docker/nginx/default.conf)
    |
    | ✅ server_name: yourdomain.com
    | ✅ root: /var/www/html/public
    |
    v
PHP-FPM (127.0.0.1:9000)
    |
    | Process Laravel Application
    |
    v
Laravel (public/index.php)
    |
    | Route, Controller, View
    |
    v
Response HTML
    |
    v
User Browser (Tampil Aplikasi) ✅
```

## ❌ Problem Flow (Ketika Gagal)

### Skenario 1: DNS Belum Resolve
```
User Browser
    |
    | http://yourdomain.com
    |
    v
DNS Server
    |
    | ❌ Domain not found / IP salah
    |
    v
Browser Error: "DNS_PROBE_FINISHED_NXDOMAIN"
```

**Fix:** Cek setting DNS di registrar, tunggu propagation

---

### Skenario 2: Firewall Block
```
User Browser
    |
    | http://yourdomain.com
    |
    v
DNS Server → IP: 123.45.67.89 ✅
    |
    v
Internet → Request ke 123.45.67.89:80
    |
    v
Server Firewall
    |
    | ❌ Port 80 blocked
    |
    v
Browser Error: "Connection Timeout"
```

**Fix:** Allow port 80 di firewall & security group

---

### Skenario 3: Container Tidak Running
```
User Browser
    |
    | http://yourdomain.com
    |
    v
DNS → IP ✅
    |
    v
Server Firewall → Allow ✅
    |
    v
Docker Container
    |
    | ❌ Container stopped/crashed
    |
    v
Browser Error: "Connection Refused"
```

**Fix:** `docker-compose up -d`, cek logs

---

### Skenario 4: Nginx Server Name Salah (MASALAH UTAMA)
```
User Browser
    |
    | http://yourdomain.com
    |
    v
DNS → IP ✅
    |
    v
Server Firewall → Allow ✅
    |
    v
Docker Container → Running ✅
    |
    v
Nginx
    |
    | ❌ server_name: localhost (tidak match yourdomain.com)
    | Nginx tidak process request
    |
    v
Browser Error: "502 Bad Gateway" atau "Connection Reset"
```

**Fix:** Update nginx config:
```nginx
server_name localhost yourdomain.com www.yourdomain.com _;
```

---

### Skenario 5: PHP-FPM Tidak Running
```
User Browser
    |
    | http://yourdomain.com
    |
    v
DNS → IP ✅
    |
    v
Server Firewall → Allow ✅
    |
    v
Docker Container → Running ✅
    |
    v
Nginx → server_name match ✅
    |
    v
PHP-FPM
    |
    | ❌ PHP-FPM crashed/not running
    |
    v
Browser Error: "502 Bad Gateway"
```

**Fix:** Restart container, cek logs PHP-FPM

---

## 🔍 Diagnostic Flow

```
START: Domain tidak bisa diakses
    |
    v
[1] Cek DNS: nslookup yourdomain.com
    |
    ├─ IP salah/tidak ada → Fix DNS di registrar
    |
    └─ IP benar ✅
        |
        v
[2] Cek dari server: curl http://localhost
    |
    ├─ Gagal → Container bermasalah
    |   |
    |   └─ docker-compose ps
    |       |
    |       ├─ Not running → docker-compose up -d
    |       └─ Running → Cek logs: docker-compose logs app
    |
    └─ Berhasil ✅
        |
        v
[3] Cek dari server: curl http://yourdomain.com
    |
    ├─ Gagal → Nginx server_name salah
    |   |
    |   └─ Edit docker/nginx/default.conf
    |       server_name localhost yourdomain.com _;
    |       Rebuild: docker-compose build && docker-compose up -d
    |
    └─ Berhasil ✅
        |
        v
[4] Cek dari luar: curl http://yourdomain.com (dari komputer lain)
    |
    ├─ Timeout → Firewall block
    |   |
    |   ├─ Server firewall → Allow port 80
    |   └─ Cloud security group → Allow port 80
    |
    └─ Berhasil ✅
        |
        v
DONE: Domain bisa diakses! 🎉
```

## 🛠️ Quick Diagnostic Commands

```bash
# 1. DNS Check
nslookup yourdomain.com
# Expected: IP server Anda

# 2. Port Check
netstat -an | findstr :80
# Expected: 0.0.0.0:80 atau :::80

# 3. Container Check
docker-compose ps
# Expected: Status "Up"

# 4. Localhost Test
curl -I http://localhost
# Expected: HTTP/1.1 200 OK atau 302

# 5. Domain Test (dari server)
curl -I http://yourdomain.com
# Expected: HTTP/1.1 200 OK atau 302

# 6. Nginx Config Check
docker-compose exec app cat /etc/nginx/http.d/default.conf | grep server_name
# Expected: server_name localhost yourdomain.com www.yourdomain.com _;

# 7. PHP-FPM Check
docker-compose exec app ps aux | grep php-fpm
# Expected: Multiple php-fpm processes running

# 8. Logs Check
docker-compose logs --tail=50 app
# Expected: No critical errors
```

## 📊 Port Mapping Diagram

```
Host Server (Your Server)
    |
    | Port 80 (HTTP)
    | Port 443 (HTTPS)
    |
    v
Docker Network Bridge
    |
    v
Container: ebbl_app
    |
    | Internal Port 80
    |
    ├─> Nginx (Port 80)
    |       |
    |       └─> PHP-FPM (Port 9000)
    |               |
    |               └─> Laravel App
    |
    └─> Supervisor (Manage Nginx + PHP-FPM)
```

## 🔐 Security Flow (Future: HTTPS)

```
User Browser
    |
    | https://yourdomain.com (Port 443)
    |
    v
Cloudflare / Reverse Proxy
    |
    | SSL Termination
    | Certificate: Let's Encrypt
    |
    v
Server (Port 80)
    |
    | HTTP (internal, aman karena di belakang proxy)
    |
    v
Docker Container
    |
    v
Nginx → PHP-FPM → Laravel
```

## 📝 Configuration Files Flow

```
docker-compose.yml
    |
    | Define services, ports, networks
    |
    v
Dockerfile
    |
    | Build image: PHP + Nginx + Supervisor
    | Copy configs
    |
    v
docker/nginx/default.conf ⭐ PENTING
    |
    | server_name: yourdomain.com
    | root: /var/www/html/public
    | PHP-FPM: 127.0.0.1:9000
    |
    v
docker/supervisor/supervisord.conf
    |
    | Start Nginx + PHP-FPM
    |
    v
docker/entrypoints/entrypoint.sh
    |
    | Setup permissions
    | Wait for MySQL
    | Run migrations
    |
    v
Application Running ✅
```

---

**Catatan:** File yang paling penting untuk masalah domain adalah `docker/nginx/default.conf` (baris `server_name`).
