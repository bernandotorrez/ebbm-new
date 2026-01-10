# Linux Quick Start Guide

## 🚀 Deploy dalam 5 Menit

### 1. Make Scripts Executable
```bash
chmod +x make-executable.sh
./make-executable.sh
```

### 2. Setup Environment
```bash
cp .env.production .env
nano .env  # Edit APP_URL dan DB_PASSWORD
```

### 3. Setup Firewall
```bash
sudo ./setup-firewall.sh
```

### 4. Update Nginx Config
```bash
nano docker/nginx/default.conf
# Baris 3: ganti xxx.com dengan domain Anda
# server_name localhost yourdomain.com www.yourdomain.com _;
```

### 5. Deploy
```bash
./deploy-production.sh
```

### 6. Verify
```bash
curl http://localhost
curl http://yourdomain.com
```

## 📝 Script Commands

### Deployment
```bash
./deploy-production.sh    # Full deployment with prompts
./quick-deploy.sh         # Quick deploy (no prompts)
./deploy.sh               # Standard deploy
```

### Troubleshooting
```bash
./fix-domain.sh                    # Interactive troubleshooting
./check-domain.sh yourdomain.com   # Diagnostic report
```

### Firewall
```bash
sudo ./setup-firewall.sh   # Setup UFW (allow 22, 80, 443)
```

## 🔧 Common Commands

### View Logs
```bash
docker-compose logs -f app
docker-compose logs --tail=100 app
```

### Restart
```bash
docker-compose restart app
docker-compose restart
```

### Clear Cache
```bash
docker-compose exec app php artisan optimize:clear
```

### Database
```bash
# Migrate
docker-compose exec app php artisan migrate --force

# Backup
docker-compose exec mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup.sql

# Restore
docker-compose exec -T mysql mysql -u ebbm_user -pebbm_password ebbm < backup.sql
```

## 🔍 Troubleshooting

### Domain tidak bisa diakses?
```bash
# Quick diagnostic
./check-domain.sh yourdomain.com

# Or manual check
nslookup yourdomain.com           # Check DNS
sudo ufw status                   # Check firewall
docker-compose ps                 # Check containers
docker-compose logs app           # Check logs
curl -I http://localhost          # Test localhost
curl -I http://yourdomain.com     # Test domain
```

### Container tidak start?
```bash
docker-compose logs app           # Check errors
sudo lsof -i :80                  # Check port 80
docker-compose down               # Stop all
docker-compose up -d              # Start all
```

### 502 Bad Gateway?
```bash
docker-compose restart app        # Restart container
docker-compose logs app           # Check logs
```

## 🔐 Security

### Firewall
```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

### Production Settings (.env)
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

### Hide MySQL Port
Edit `docker-compose.yml`:
```yaml
mysql:
  # Comment out to disable external access
  # ports:
  #   - "3306:3306"
```

## 📚 Full Documentation

- **README-LINUX-DEPLOYMENT.md** - Complete Linux guide
- **QUICK-FIX-DOMAIN.md** - Quick domain fix
- **TROUBLESHOOTING-DOMAIN.md** - Detailed troubleshooting
- **DOMAIN-FLOW-DIAGRAM.md** - Visual diagrams

## 💡 Tips

### Auto-start on Boot
```bash
# Enable Docker auto-start
sudo systemctl enable docker

# Create systemd service for app
sudo nano /etc/systemd/system/ebbl-app.service
```

### Automated Backups
```bash
# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec -T mysql mysqldump -u ebbm_user -pebbm_password ebbm > backup_$DATE.sql
find . -name "backup_*.sql" -mtime +7 -delete
EOF

chmod +x backup.sh

# Add to crontab (daily at 2 AM)
crontab -e
# Add: 0 2 * * * /path/to/backup.sh
```

### Monitor Logs
```bash
# Real-time monitoring
docker-compose logs -f app

# Or use tail
docker-compose exec app tail -f /var/log/nginx/access.log
docker-compose exec app tail -f /var/log/nginx/error.log
```

### Performance Monitoring
```bash
# Container stats
docker stats

# System resources
htop
df -h
free -h
```

## 🌐 Setup HTTPS

### Option 1: Cloudflare (Easiest)
1. Add domain to Cloudflare
2. Update nameservers
3. Enable "Flexible SSL"
4. Done!

### Option 2: Certbot
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### Option 3: Caddy
```bash
# Install Caddy
sudo apt install caddy

# Edit docker-compose.yml port to 8080
# Create Caddyfile:
yourdomain.com {
    reverse_proxy localhost:8080
}

sudo systemctl restart caddy
```

## ❓ Need Help?

Run diagnostic:
```bash
./check-domain.sh yourdomain.com > diagnostic.txt
cat diagnostic.txt
```

Check logs:
```bash
docker-compose logs --tail=50 app
```

Test connectivity:
```bash
curl -v http://localhost
curl -v http://yourdomain.com
```
