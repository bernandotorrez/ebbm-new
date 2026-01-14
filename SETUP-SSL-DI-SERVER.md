# Setup SSL di Server

## Langkah 1: Deploy Tanpa SSL (HTTP Only)

Aplikasi sekarang pakai config `default-http-only.conf` yang hanya HTTP.

```bash
# Di server
docker-compose up -d

# Test akses
curl -I http://e-bmp.basarnas.go.id
```

## Langkah 2: Setup SSL di Server

Setelah aplikasi jalan, setup SSL:

### A. Pastikan SSL Files Ada

```bash
# Cek file SSL
ls -la docker/ssl/
# Harus ada:
# - basarnas.go.id.key
# - fullchain.pem
```

### B. Update docker-compose.yml di Server

Edit file `docker-compose.yml` di server, ubah bagian nginx volumes:

```yaml
nginx:
  volumes:
    # Ganti ke config dengan SSL
    - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    # Uncomment SSL mount
    - ./docker/ssl:/etc/ssl/BasarnasSSL:ro
    - ./public:/var/www/html/public:ro
    - ./storage/app/public:/var/www/html/storage/app/public:ro
    - nginx_logs:/var/log/nginx
```

### C. Restart Nginx

```bash
# Restart nginx container
docker-compose restart nginx

# Cek logs
docker-compose logs -f nginx
```

### D. Test HTTPS

```bash
# Test HTTP redirect
curl -I http://e-bmp.basarnas.go.id
# Harus dapat: 301 Moved Permanently

# Test HTTPS
curl -I https://e-bmp.basarnas.go.id
# Harus dapat: 200 OK
```

## Troubleshooting

### Jika masih error SSL not found:

```bash
# Masuk ke nginx container
docker exec -it ebmp_nginx sh

# Cek apakah SSL files ter-mount
ls -la /etc/ssl/BasarnasSSL/

# Jika tidak ada, cek permissions di host
exit
ls -la docker/ssl/
chmod 644 docker/ssl/*.pem
chmod 600 docker/ssl/*.key
```

### Jika permission denied:

```bash
# Fix permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# Restart nginx
docker-compose restart nginx
```

## Rollback ke HTTP Only

Jika ada masalah dengan SSL:

```bash
# Edit docker-compose.yml, ganti kembali ke:
# - ./docker/nginx/default-http-only.conf:/etc/nginx/conf.d/default.conf:ro

# Restart
docker-compose restart nginx
```

## Verifikasi Final

```bash
# Cek semua service running
docker-compose ps

# Cek logs tidak ada error
docker-compose logs nginx | grep -i error

# Test akses
curl -I http://e-bmp.basarnas.go.id   # → 301 redirect
curl -I https://e-bmp.basarnas.go.id  # → 200 OK

# Test dari browser
# https://e-bmp.basarnas.go.id
```
