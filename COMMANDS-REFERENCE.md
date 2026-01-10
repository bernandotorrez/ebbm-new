# Commands Reference - Quick Copy-Paste

## 🚀 Deployment

### Linux - One Command Setup
```bash
chmod +x setup-all.sh && ./setup-all.sh
```

### Linux - Manual Deploy
```bash
chmod +x make-executable.sh && ./make-executable.sh
cp .env.production .env
nano .env
./deploy-production.sh
```

### Windows - Deploy
```cmd
copy .env.production .env
notepad .env
deploy-production.bat
```

---

## 🔧 Container Management

### Start/Stop/Restart
```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Restart all
docker-compose restart

# Restart app only
docker-compose restart app

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Status & Logs
```bash
# Check status
docker-compose ps

# View logs (follow)
docker-compose logs -f app

# View logs (last 100 lines)
docker-compose logs --tail=100 app

# View all logs
docker-compose logs -f

# MySQL logs
docker-compose logs -f mysql
```

---

## 🔍 Diagnostics

### Domain Check
```bash
# Linux - Full diagnostic
./check-domain.sh yourdomain.com

# Manual DNS check
nslookup yourdomain.com

# Test from server
curl -I http://localhost
curl -I http://yourdomain.com

# Test with verbose
curl -v http://yourdomain.com
```

### Container Health
```bash
# Container status
docker-compose ps

# Container stats (CPU, memory)
docker stats

# Check processes in container
docker-compose exec app ps aux

# Check PHP-FPM
docker-compose exec app ps aux | grep php-fpm

# Check Nginx
docker-compose exec app ps aux | grep nginx
```

### Port & Network
```bash
# Check port 80 listening
netstat -tuln | grep :80

# Linux - Check what's using port 80
sudo lsof -i :80

# Check Docker networks
docker network ls

# Inspect network
docker network inspect ebbl_network
```

---

## 🔐 Firewall

### Linux (UFW)
```bash
# Check status
sudo ufw status

# Allow ports
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Disable firewall (for testing)
sudo ufw disable

# Delete rule
sudo ufw delete allow 80/tcp
```

### Check Firewall Status
```bash
# UFW
sudo ufw status verbose

# iptables
sudo iptables -L -n

# Check if port is accessible from outside
# (run from another computer)
telnet YOUR_SERVER_IP 80
```

---

## 🗄️ Database

### Access MySQL
```bash
# MySQL shell
docker-compose exec mysql mysql -u root -prootpassword

# MySQL shell as app user
docker-compose exec mysql mysql -u ebbm_user -pebbm_password ebbm

# Run SQL query
docker-compose exec mysql mysql -u ebbm_user -pebbm_password ebbm -e "SELECT * FROM users;"
```

### Backup & Restore
```bash
# Backup
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup_$(date +%Y%m%d).sql

# Backup with gzip
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm | gzip > backup_$(date +%Y%m%d).sql.gz

# Restore
docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm < backup_20250110.sql

# Restore from gzip
gunzip < backup_20250110.sql.gz | docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm
```

### Database Info
```bash
# Show databases
docker-compose exec mysql mysql -u root -prootpassword -e "SHOW DATABASES;"

# Show tables
docker-compose exec mysql mysql -u ebbm_user -pebbm_password ebbm -e "SHOW TABLES;"

# Database size
docker-compose exec mysql mysql -u ebbm_user -pebbm_password ebbm -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES GROUP BY table_schema;"
```

---

## 🎨 Laravel Artisan

### Cache Management
```bash
# Clear all cache
docker-compose exec app php artisan optimize:clear

# Clear specific cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear

# Optimize for production
docker-compose exec app php artisan optimize
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Database Migrations
```bash
# Run migrations
docker-compose exec app php artisan migrate --force

# Rollback last migration
docker-compose exec app php artisan migrate:rollback --force

# Fresh migration (drop all tables)
docker-compose exec app php artisan migrate:fresh --force

# Fresh migration with seed
docker-compose exec app php artisan migrate:fresh --seed --force

# Check migration status
docker-compose exec app php artisan migrate:status
```

### Database Info
```bash
# Show database connection
docker-compose exec app php artisan db:show

# Show tables
docker-compose exec app php artisan db:table users
```

### Filament & Livewire
```bash
# Publish Filament assets
docker-compose exec app php artisan filament:assets

# Publish Livewire assets
docker-compose exec app php artisan livewire:publish --assets --force

# Create Filament user
docker-compose exec app php artisan make:filament-user
```

### Storage
```bash
# Create storage link
docker-compose exec app php artisan storage:link

# Clear storage
docker-compose exec app rm -rf storage/framework/cache/*
docker-compose exec app rm -rf storage/framework/sessions/*
docker-compose exec app rm -rf storage/framework/views/*
```

---

## 📝 File Management

### View Files in Container
```bash
# View nginx config
docker-compose exec app cat /etc/nginx/http.d/default.conf

# View PHP config
docker-compose exec app cat /usr/local/etc/php/php.ini

# View .env
docker-compose exec app cat .env

# View logs
docker-compose exec app cat /var/log/nginx/error.log
docker-compose exec app cat /var/log/nginx/access.log
```

### Edit Files in Container
```bash
# Access container shell
docker-compose exec app sh

# Then inside container:
vi /etc/nginx/http.d/default.conf
```

### Copy Files
```bash
# Copy from container to host
docker cp ebbl_app:/var/www/html/.env ./env-backup

# Copy from host to container
docker cp ./config.php ebbl_app:/var/www/html/config/config.php
```

---

## 🔧 Permissions

### Fix Permissions
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache

# Fix all permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

---

## 🌐 Nginx

### Test Nginx Config
```bash
# Test config syntax
docker-compose exec app nginx -t

# Reload nginx
docker-compose exec app nginx -s reload

# View nginx version
docker-compose exec app nginx -v
```

### Nginx Logs
```bash
# Access log (real-time)
docker-compose exec app tail -f /var/log/nginx/access.log

# Error log (real-time)
docker-compose exec app tail -f /var/log/nginx/error.log

# Last 100 lines
docker-compose exec app tail -n 100 /var/log/nginx/error.log
```

---

## 🐘 PHP

### PHP Info
```bash
# PHP version
docker-compose exec app php -v

# PHP modules
docker-compose exec app php -m

# PHP config
docker-compose exec app php -i

# PHP-FPM status
docker-compose exec app php-fpm -t
```

### PHP Logs
```bash
# View PHP-FPM logs
docker-compose logs app | grep php-fpm

# View Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log
```

---

## 🧹 Cleanup

### Docker Cleanup
```bash
# Remove stopped containers
docker-compose down

# Remove containers and volumes
docker-compose down -v

# Remove unused images
docker image prune -a

# Remove all unused data
docker system prune -a

# Check disk usage
docker system df
```

### Application Cleanup
```bash
# Clear Laravel cache
docker-compose exec app php artisan optimize:clear

# Clear logs
docker-compose exec app rm -f storage/logs/*.log

# Clear sessions
docker-compose exec app rm -rf storage/framework/sessions/*
```

---

## 📊 Monitoring

### System Resources
```bash
# Container stats
docker stats

# Disk usage
df -h

# Memory usage
free -h

# CPU info
top
htop
```

### Application Monitoring
```bash
# Watch logs
watch -n 1 'docker-compose logs --tail=20 app'

# Monitor requests
docker-compose exec app tail -f /var/log/nginx/access.log

# Monitor errors
docker-compose exec app tail -f /var/log/nginx/error.log
```

---

## 🔄 Update & Maintenance

### Update Application
```bash
# Pull latest code
git pull

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear cache
docker-compose exec app php artisan optimize:clear
```

### Update Docker Images
```bash
# Pull latest base images
docker-compose pull

# Rebuild
docker-compose build --no-cache

# Restart
docker-compose up -d
```

---

## 🧪 Testing

### Test Connectivity
```bash
# Test localhost
curl -I http://localhost

# Test domain
curl -I http://yourdomain.com

# Test with verbose
curl -v http://yourdomain.com

# Test specific route
curl http://localhost/admin

# Test from outside (another computer)
curl -I http://YOUR_SERVER_IP
```

### Health Check
```bash
# Check if app is responding
curl -f http://localhost || echo "App is down"

# Check response time
time curl -I http://localhost

# Check with timeout
curl --max-time 5 http://localhost
```

---

## 📦 Backup & Restore

### Full Backup
```bash
# Backup database
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup_db.sql

# Backup storage
tar -czf backup_storage.tar.gz storage/

# Backup .env
cp .env backup_env

# All in one
mkdir backup_$(date +%Y%m%d)
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup_$(date +%Y%m%d)/database.sql
tar -czf backup_$(date +%Y%m%d)/storage.tar.gz storage/
cp .env backup_$(date +%Y%m%d)/env
```

### Restore
```bash
# Restore database
docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm < backup_db.sql

# Restore storage
tar -xzf backup_storage.tar.gz

# Restore .env
cp backup_env .env
```

---

## 🔐 Security

### Change Passwords
```bash
# Edit docker-compose.yml
nano docker-compose.yml
# Change MYSQL_ROOT_PASSWORD and MYSQL_PASSWORD

# Edit .env
nano .env
# Change DB_PASSWORD

# Restart
docker-compose down
docker-compose up -d
```

### Generate APP_KEY
```bash
# Generate new key
docker-compose exec app php artisan key:generate

# View current key
docker-compose exec app php artisan tinker --execute="echo config('app.key');"
```

---

## 📞 Emergency Commands

### Quick Restart
```bash
docker-compose restart app
```

### Force Rebuild
```bash
docker-compose down
docker-compose build --no-cache --pull
docker-compose up -d --force-recreate
```

### Emergency Stop
```bash
docker-compose down
docker stop $(docker ps -aq)
```

### View All Logs
```bash
docker-compose logs --tail=200 > debug.log
cat debug.log
```

---

**Tip:** Bookmark this file for quick reference!
