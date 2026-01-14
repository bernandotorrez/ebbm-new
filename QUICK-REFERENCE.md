# Quick Reference - Docker Commands

## 🚀 Deployment

```bash
# Deploy dengan SSL
./deploy-with-ssl.sh          # Linux/Mac
deploy-with-ssl.bat           # Windows

# Manual deployment
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

## 📊 Status & Monitoring

```bash
# Container status
docker-compose ps

# Resource usage
docker stats

# All logs (follow)
docker-compose logs -f

# Specific service logs
docker-compose logs -f nginx
docker-compose logs -f app
docker-compose logs -f mysql
```

## 🔄 Restart Services

```bash
# Restart all
docker-compose restart

# Restart specific service
docker-compose restart nginx
docker-compose restart app
docker-compose restart mysql
```

## 🛑 Stop/Start

```bash
# Stop all
docker-compose stop

# Start all
docker-compose start

# Stop and remove
docker-compose down

# Stop and remove with volumes (⚠️ DANGER)
docker-compose down -v
```

## 🔍 Debugging

```bash
# Enter container
docker exec -it ebbm_nginx sh
docker exec -it ebbm_app sh
docker exec -it ebbm_mysql sh

# Test nginx config
docker exec -it ebbm_nginx nginx -t

# Test PHP-FPM config
docker exec -it ebbm_app php-fpm -t

# Check SSL files in nginx
docker exec -it ebbm_nginx ls -la /etc/ssl/BasarnasSSL/

# Check permissions in app
docker exec -it ebbm_app ls -la storage/

# Test database connection
docker exec -it ebbm_app nc -zv mysql 3306

# Test nginx to app connection
docker exec -it ebbm_nginx nc -zv app 9000
```

## 📝 Laravel Commands

```bash
# Run artisan commands
docker exec -it ebbm_app php artisan migrate
docker exec -it ebbm_app php artisan migrate:status
docker exec -it ebbm_app php artisan cache:clear
docker exec -it ebbm_app php artisan config:clear
docker exec -it ebbm_app php artisan view:clear
docker exec -it ebbm_app php artisan storage:link

# Composer
docker exec -it ebbm_app composer install
docker exec -it ebbm_app composer update

# Fix permissions
docker exec -it ebbm_app chmod -R 775 storage bootstrap/cache
docker exec -it ebbm_app chown -R www-data:www-data storage bootstrap/cache
```

## 📋 Logs

```bash
# Nginx access log
docker exec -it ebbm_nginx tail -f /var/log/nginx/e-bmp.access.log

# Nginx error log
docker exec -it ebbm_nginx tail -f /var/log/nginx/e-bmp.error.log

# Laravel log
docker exec -it ebbm_app tail -f storage/logs/laravel.log

# PHP-FPM log
docker-compose logs -f app | grep php-fpm
```

## 🔐 SSL Management

```bash
# Check SSL files
ls -la docker/ssl/

# Fix SSL permissions
chmod 644 docker/ssl/fullchain.pem
chmod 600 docker/ssl/basarnas.go.id.key

# Update SSL certificate
cp /path/to/new-cert.pem docker/ssl/fullchain.pem
cp /path/to/new-key.key docker/ssl/basarnas.go.id.key
docker-compose restart nginx

# Test SSL
openssl s_client -connect e-bmp.basarnas.go.id:443
```

## 🧪 Testing

```bash
# Test HTTP redirect
curl -I http://e-bmp.basarnas.go.id
curl -I http://localhost

# Test HTTPS
curl -I https://e-bmp.basarnas.go.id
curl -I -k https://localhost

# Test from inside container
docker exec -it ebbm_nginx curl -I http://localhost
docker exec -it ebbm_app curl -I http://app:9000
```

## 💾 Database

```bash
# Backup database
docker exec ebbm_mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql

# Restore database
docker exec -i ebbm_mysql mysql -u ebbm_user -pebbm_password ebbm < backup.sql

# MySQL console
docker exec -it ebbm_mysql mysql -u ebbm_user -pebbm_password ebbm

# Show databases
docker exec -it ebbm_mysql mysql -u root -prootpassword -e "SHOW DATABASES;"
```

## 🧹 Cleanup

```bash
# Remove unused images
docker image prune -a

# Remove unused volumes
docker volume prune

# Remove unused networks
docker network prune

# Remove everything unused
docker system prune -a

# See disk usage
docker system df
```

## 🔧 Rebuild

```bash
# Rebuild specific service
docker-compose build app
docker-compose build nginx

# Rebuild without cache
docker-compose build --no-cache

# Rebuild and restart
docker-compose up -d --build
```

## 📦 Update Application

```bash
# Pull latest code
git pull

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migrations
docker exec -it ebbm_app php artisan migrate --force

# Clear cache
docker exec -it ebbm_app php artisan cache:clear
docker exec -it ebbm_app php artisan config:clear
docker exec -it ebbm_app php artisan view:clear
```

## ⚠️ Emergency

```bash
# Force stop all containers
docker-compose kill

# Remove all containers
docker-compose rm -f

# Start fresh
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d

# Check what's using port
netstat -tulpn | grep :80
netstat -tulpn | grep :443
netstat -tulpn | grep :3306

# Kill process using port (Linux)
sudo kill -9 $(sudo lsof -t -i:80)
```

## 📚 Documentation Files

- `DEPLOYMENT-FINAL.md` - Complete deployment guide
- `README-SSL-SETUP.md` - SSL setup guide
- `PRE-DEPLOYMENT-CHECKLIST.md` - Pre-deployment checklist
- `QUICK-REFERENCE.md` - This file
- `SETUP-SSL-DI-SERVER.md` - Server SSL setup

## 🌐 URLs

- **HTTP:** http://e-bmp.basarnas.go.id (redirects to HTTPS)
- **HTTPS:** https://e-bmp.basarnas.go.id
- **Admin:** https://e-bmp.basarnas.go.id/admin

## 🏗️ Architecture

```
Internet → HAProxy (10.0.3.42) → Nginx (80/443) → PHP-FPM (9000) → MySQL (3306)
```

## 📞 Quick Troubleshooting

| Problem | Command |
|---------|---------|
| 502 Bad Gateway | `docker-compose restart app` |
| SSL Error | `docker-compose restart nginx` |
| Permission Denied | `docker exec -it ebbm_app chmod -R 775 storage` |
| Database Error | `docker-compose restart mysql` |
| Can't upload file | Check `client_max_body_size` in nginx config |
| Livewire not working | Check CSP headers in nginx config |
