# SSL Setup Guide

## Quick Start

### 1. Copy SSL Files

Copy your SSL certificate files to `docker/ssl/`:

```bash
# Linux/Mac
cp /path/to/fullchain.pem docker/ssl/
cp /path/to/basarnas.go.id.key docker/ssl/

# Windows
copy C:\path\to\fullchain.pem docker\ssl\
copy C:\path\to\basarnas.go.id.key docker\ssl\
```

### 2. Deploy

```bash
# Linux/Mac
chmod +x deploy-with-ssl.sh
./deploy-with-ssl.sh

# Windows
deploy-with-ssl.bat
```

### 3. Verify

Open browser and access:
- `http://e-bmp.basarnas.go.id` → redirects to HTTPS
- `https://e-bmp.basarnas.go.id` → application with SSL

## File Structure

```
project/
├── docker/
│   ├── ssl/
│   │   ├── fullchain.pem          ← Your SSL certificate
│   │   └── basarnas.go.id.key     ← Your SSL private key
│   ├── nginx/
│   │   ├── default.conf           ← Nginx config with SSL
│   │   └── default-http-only.conf ← Nginx config without SSL (backup)
│   ├── php/
│   └── supervisor/
├── docker-compose.yml              ← Docker services configuration
├── Dockerfile                      ← App image
├── deploy-with-ssl.sh              ← Deployment script (Linux/Mac)
├── deploy-with-ssl.bat             ← Deployment script (Windows)
└── DEPLOYMENT-FINAL.md             ← Full deployment guide
```

## Configuration Details

### Nginx Configuration

**HTTP (Port 80):**
- Redirects all traffic to HTTPS

**HTTPS (Port 443):**
- SSL/TLS 1.2 & 1.3
- HTTP/2 enabled
- Certificate: `/etc/ssl/BasarnasSSL/fullchain.pem`
- Private Key: `/etc/ssl/BasarnasSSL/basarnas.go.id.key`

### Real IP Configuration

HAProxy forwards real client IP:
```nginx
set_real_ip_from 10.0.3.42;
real_ip_header X-Forwarded-For;
real_ip_recursive on;
```

### Upload Limits

- Max upload size: 50MB
- Configured for Livewire file uploads

## Troubleshooting

### SSL Certificate Not Found

**Error:**
```
nginx: [emerg] cannot load certificate "/etc/ssl/BasarnasSSL/fullchain.pem"
```

**Solution:**
1. Check files exist:
   ```bash
   ls -la docker/ssl/
   ```

2. Fix permissions:
   ```bash
   chmod 644 docker/ssl/fullchain.pem
   chmod 600 docker/ssl/basarnas.go.id.key
   ```

3. Restart nginx:
   ```bash
   docker-compose restart nginx
   ```

### HTTP/2 Warning

**Warning:**
```
nginx: [warn] the "listen ... http2" directive is deprecated
```

**Status:** Already fixed in `default.conf` using new syntax:
```nginx
listen 443 ssl;
http2 on;
```

### 502 Bad Gateway

**Cause:** Nginx can't connect to PHP-FPM

**Solution:**
```bash
# Check app container
docker-compose ps app

# Check logs
docker-compose logs app

# Restart app
docker-compose restart app
```

## Security Features

✅ **SSL/TLS:**
- TLS 1.2 & 1.3 only
- Strong ciphers
- HTTPS redirect

✅ **Headers:**
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: enabled
- X-Content-Type-Options: nosniff
- Content-Security-Policy: configured for Livewire

✅ **File Protection:**
- SSL files in .gitignore
- Sensitive paths blocked (.env, vendor, config)
- Server tokens hidden

✅ **Network:**
- PHP-FPM only accessible from nginx container
- MySQL only accessible from app container
- Real IP forwarding from HAProxy

## Maintenance

### Update SSL Certificate

When certificate expires:

```bash
# 1. Copy new certificate
cp /path/to/new-fullchain.pem docker/ssl/fullchain.pem
cp /path/to/new-key.key docker/ssl/basarnas.go.id.key

# 2. Fix permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# 3. Restart nginx (no rebuild needed)
docker-compose restart nginx

# 4. Verify
curl -I https://e-bmp.basarnas.go.id
```

### View Logs

```bash
# All logs
docker-compose logs -f

# Nginx only
docker-compose logs -f nginx

# Nginx access log
docker exec -it ebmp_nginx tail -f /var/log/nginx/e-bmp.access.log

# Nginx error log
docker exec -it ebmp_nginx tail -f /var/log/nginx/e-bmp.error.log
```

### Restart Services

```bash
# All services
docker-compose restart

# Nginx only
docker-compose restart nginx

# App only
docker-compose restart app
```

## Testing

### Test HTTP Redirect

```bash
curl -I http://e-bmp.basarnas.go.id
# Expected: HTTP/1.1 301 Moved Permanently
# Location: https://e-bmp.basarnas.go.id
```

### Test HTTPS

```bash
curl -I https://e-bmp.basarnas.go.id
# Expected: HTTP/2 200
```

### Test SSL Certificate

```bash
openssl s_client -connect e-bmp.basarnas.go.id:443 -servername e-bmp.basarnas.go.id
```

### Test Upload

1. Login to admin: `https://e-bmp.basarnas.go.id/admin`
2. Try uploading a file
3. Verify max 50MB works

## Documentation

- **Full Deployment Guide:** `DEPLOYMENT-FINAL.md`
- **Pre-deployment Checklist:** `PRE-DEPLOYMENT-CHECKLIST.md`
- **SSL Setup (this file):** `README-SSL-SETUP.md`

## Support

For issues:
1. Check logs: `docker-compose logs -f`
2. Check status: `docker-compose ps`
3. Review: `DEPLOYMENT-FINAL.md#troubleshooting`
