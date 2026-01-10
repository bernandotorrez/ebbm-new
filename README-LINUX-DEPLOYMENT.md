# Deployment Guide untuk Linux Server

## 🚀 Quick Start (5 Menit)

### 1. Setup Permissions
```bash
# Make scripts executable
chmod +x make-executable.sh
./make-executable.sh
```

### 2. Setup Environment
```bash
# Copy .env
cp .env.production .env

# Edit .env (ganti domain dan password)
nano .env
# atau
vim .env
```

**Yang perlu diganti di .env:**
```env
APP_URL=http://yourdomain.com
DB_PASSWORD=your_secure_password
```

### 3. Setup Firewall
```bash
# Allow port 80 & 443
sudo ./setup-firewall.sh
```

### 4. Deploy
```bash
# Full deployment dengan prompts
./deploy-production.sh

# Atau quick deploy (no prompts)
./quick-deploy.sh
```

### 5. Verifikasi
```bash
# Check status
docker-compose ps

# Check logs
docker-compose logs -f app

# Test akses
curl http://localhost
```

## 📋 Available Scripts

### Deployment Scripts

#### `./deploy-production.sh`
Full production deployment dengan checks dan prompts.
```bash
./deploy-production.sh
```

#### `./quick-deploy.sh`
Quick deployment tanpa prompts (untuk automation).
```bash
./quick-deploy.sh
```

#### `./deploy.sh`
Existing deployment script.
```bash
./deploy.sh
```

### Domain & Troubleshooting Scripts

#### `./fix-domain.sh`
Interactive troubleshooting untuk masalah domain.
```bash
./fix-domain.sh
```

#### `./check-domain.sh`
Diagnostic lengkap untuk domain (non-interactive).
```bash
./check-domain.sh yourdomain.com
```

Output:
- DNS resolution
- Container status
- Port listening
- Firewall status
- Nginx config
- Test connectivity
- Recent logs

### Firewall Script

#### `./setup-firewall.sh`
Setup UFW firewall untuk web server.
```bash
sudo ./setup-firewall.sh
```

Allow ports:
- 22 (SSH)
- 80 (HTTP)
- 443 (HTTPS)

## 🔧 Common Tasks

### Update Nginx Config untuk Domain
```bash
# Edit nginx config
nano docker/nginx/default.conf

# Ganti baris 3:
server_name localhost yourdomain.com www.yourdomain.com _;

# Rebuild
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### View Logs
```bash
# All logs
docker-compose logs -f

# App only
docker-compose logs -f app

# Last 100 lines
docker-compose logs --tail=100 app

# MySQL logs
docker-compose logs -f mysql
```

### Clear Cache
```bash
docker-compose exec app php artisan optimize:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

### Run Migrations
```bash
# Run migrations
docker-compose exec app php artisan migrate --force

# Fresh migration with seed
docker-compose exec app php artisan migrate:fresh --seed --force
```

### Restart Services
```bash
# Restart app container
docker-compose restart app

# Restart all containers
docker-compose restart

# Stop all
docker-compose down

# Start all
docker-compose up -d
```

### Database Backup & Restore
```bash
# Backup
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm < backup_20250110.sql
```

## 🔍 Troubleshooting

### Domain Tidak Bisa Diakses

#### Step 1: Check DNS
```bash
nslookup yourdomain.com
# Harusnya menunjukkan IP server Anda
```

#### Step 2: Check Firewall
```bash
# Check UFW status
sudo ufw status

# Allow port 80 & 443 if not allowed
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

#### Step 3: Check Container
```bash
# Check status
docker-compose ps

# Check logs
docker-compose logs app

# Restart if needed
docker-compose restart app
```

#### Step 4: Check Nginx Config
```bash
# View nginx config
docker-compose exec app cat /etc/nginx/http.d/default.conf | grep server_name

# Should show:
# server_name localhost yourdomain.com www.yourdomain.com _;
```

#### Step 5: Test Connectivity
```bash
# From server
curl -I http://localhost
curl -I http://yourdomain.com

# From outside (another computer)
curl -I http://yourdomain.com
```

### Container Tidak Start

```bash
# Check logs for errors
docker-compose logs app

# Common issues:
# 1. Port 80 already in use
sudo lsof -i :80
# Kill process or change port in docker-compose.yml

# 2. Permission errors
sudo chown -R $USER:$USER .
docker-compose down
docker-compose up -d

# 3. Build errors
docker-compose build --no-cache
```

### 502 Bad Gateway

```bash
# Check PHP-FPM
docker-compose exec app ps aux | grep php-fpm

# Check nginx
docker-compose exec app ps aux | grep nginx

# Restart container
docker-compose restart app

# Check logs
docker-compose logs app
```

### Database Connection Error

```bash
# Check MySQL running
docker-compose exec mysql mysql -u root -prootpassword -e "SELECT 1"

# Check from app
docker-compose exec app php artisan db:show

# Check .env credentials
cat .env | grep DB_

# Restart MySQL
docker-compose restart mysql
```

## 🔐 Security Checklist

### Before Production

- [ ] Change `MYSQL_ROOT_PASSWORD` in docker-compose.yml
- [ ] Change `MYSQL_PASSWORD` in docker-compose.yml and .env
- [ ] Set `APP_DEBUG=false` in .env
- [ ] Set `APP_ENV=production` in .env
- [ ] Generate new `APP_KEY` (auto-generated on first run)
- [ ] Update `APP_URL` with production domain
- [ ] Setup firewall: `sudo ./setup-firewall.sh`
- [ ] Don't expose MySQL port (comment out port 3306 in docker-compose.yml)
- [ ] Setup HTTPS with Let's Encrypt or Cloudflare
- [ ] Setup regular database backups
- [ ] Setup monitoring (Uptime Robot, etc)

### Firewall Configuration

```bash
# Check current rules
sudo ufw status verbose

# Allow SSH (IMPORTANT!)
sudo ufw allow 22/tcp

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status
```

### Disable MySQL External Access

Edit `docker-compose.yml`:
```yaml
mysql:
  # Comment out ports to disable external access
  # ports:
  #   - "3306:3306"
```

## 📊 Monitoring

### Check System Resources
```bash
# CPU & Memory usage
docker stats

# Disk usage
df -h

# Docker disk usage
docker system df
```

### Check Application Health
```bash
# Container status
docker-compose ps

# Application logs
docker-compose logs --tail=50 app

# Nginx access log
docker-compose exec app tail -f /var/log/nginx/access.log

# Nginx error log
docker-compose exec app tail -f /var/log/nginx/error.log
```

### Automated Health Check
```bash
# Create health check script
cat > health-check.sh << 'EOF'
#!/bin/bash
STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ $STATUS -eq 200 ] || [ $STATUS -eq 302 ]; then
    echo "✅ Application is healthy (HTTP $STATUS)"
    exit 0
else
    echo "❌ Application is down (HTTP $STATUS)"
    exit 1
fi
EOF

chmod +x health-check.sh
./health-check.sh
```

## 🔄 Update Application

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

## 🌐 Setup HTTPS

### Option 1: Cloudflare (Easiest)
1. Add domain to Cloudflare
2. Update nameservers at registrar
3. Enable "Flexible SSL" in Cloudflare
4. Done! Auto HTTPS

### Option 2: Let's Encrypt + Certbot
```bash
# Install certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Option 3: Caddy Reverse Proxy
```bash
# Install Caddy
sudo apt install -y debian-keyring debian-archive-keyring apt-transport-https
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | sudo gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | sudo tee /etc/apt/sources.list.d/caddy-stable.list
sudo apt update
sudo apt install caddy

# Change docker port to 8080
# Edit docker-compose.yml:
# ports:
#   - "127.0.0.1:8080:80"

# Create Caddyfile
sudo nano /etc/caddy/Caddyfile

# Add:
yourdomain.com {
    reverse_proxy localhost:8080
}

# Restart Caddy
sudo systemctl restart caddy
```

## 📞 Need Help?

Run diagnostic and share output:
```bash
./check-domain.sh yourdomain.com > diagnostic.txt
cat diagnostic.txt
```

Or manual diagnostic:
```bash
echo "=== DNS ===" && nslookup yourdomain.com
echo "=== Container ===" && docker-compose ps
echo "=== Port ===" && netstat -tuln | grep :80
echo "=== Firewall ===" && sudo ufw status
echo "=== Logs ===" && docker-compose logs --tail=30 app
echo "=== Test ===" && curl -I http://localhost
```

## 📚 Documentation Files

- **README-LINUX-DEPLOYMENT.md** - This file (Linux guide)
- **QUICK-FIX-DOMAIN.md** - Quick domain fix (5 min)
- **TROUBLESHOOTING-DOMAIN.md** - Detailed troubleshooting
- **DOMAIN-FLOW-DIAGRAM.md** - Visual flow diagrams
- **DEPLOYMENT-CHECKLIST.md** - Deployment checklist
- **DEPLOYMENT-FIXES.md** - All fixes explained

## 🎯 Quick Commands Reference

```bash
# Deploy
./deploy-production.sh

# Check domain
./check-domain.sh yourdomain.com

# Fix domain issues
./fix-domain.sh

# Setup firewall
sudo ./setup-firewall.sh

# View logs
docker-compose logs -f app

# Restart
docker-compose restart app

# Clear cache
docker-compose exec app php artisan optimize:clear

# Database backup
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql
```
