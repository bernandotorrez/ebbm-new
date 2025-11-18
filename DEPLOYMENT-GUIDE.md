# Deployment Guide - Production

## Setelah Git Pull di Production

### Quick Deploy (Recommended)
Jalankan script otomatis:
```bash
chmod +x deploy-after-pull.sh
./deploy-after-pull.sh
```

### Manual Steps

#### 1. Rebuild Docker Images
```bash
docker compose down
docker compose up -d --build
```

#### 2. Install/Update Dependencies
```bash
docker exec -it ebbl_app composer install --no-dev --optimize-autoloader
```

#### 3. Run Database Migrations
```bash
docker exec -it ebbl_app php artisan migrate --force
```

#### 4. Clear All Caches
```bash
docker exec -it ebbl_app php artisan cache:clear
docker exec -it ebbl_app php artisan config:clear
docker exec -it ebbl_app php artisan route:clear
docker exec -it ebbl_app php artisan view:clear
docker exec -it ebbl_app php artisan event:clear
```

#### 5. Rebuild Caches for Production
```bash
docker exec -it ebbl_app php artisan config:cache
docker exec -it ebbl_app php artisan route:cache
docker exec -it ebbl_app php artisan view:cache
docker exec -it ebbl_app php artisan event:cache
```

#### 6. Publish Assets
```bash
docker exec -it ebbl_app php artisan vendor:publish --tag=public --force
docker exec -it ebbl_app php artisan filament:assets
```

#### 7. Create Storage Link
```bash
docker exec -it ebbl_app php artisan storage:link
```

#### 8. Fix Permissions
```bash
docker exec -it ebbl_app sh -c "chmod -R 775 storage bootstrap/cache public && chown -R www-data:www-data storage bootstrap/cache public"
```

#### 9. Restart Containers
```bash
docker compose restart
```

---

## Deployment Checklist

### Before Deployment
- [ ] Backup database
- [ ] Check .env configuration
- [ ] Test in staging environment
- [ ] Review git changes: `git log --oneline -10`

### During Deployment
- [ ] Pull latest code: `git pull origin main`
- [ ] Run deployment script: `./deploy-after-pull.sh`
- [ ] Monitor logs: `docker compose logs -f`

### After Deployment
- [ ] Test critical features
- [ ] Check error logs: `docker exec -it ebbl_app cat storage/logs/laravel.log`
- [ ] Verify database migrations: `docker exec -it ebbl_app php artisan migrate:status`
- [ ] Test file uploads
- [ ] Test form submissions

---

## Common Issues & Solutions

### Issue: Assets not loading (CSS/JS 404)
```bash
./fix-assets.sh
```

### Issue: Livewire 404 errors
```bash
./fix-livewire-404.sh
```

### Issue: Permission denied
```bash
docker exec -it ebbl_app sh -c "chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"
```

### Issue: Config cached with wrong values
```bash
docker exec -it ebbl_app php artisan config:clear
docker exec -it ebbl_app php artisan config:cache
docker compose restart
```

### Issue: Routes not found
```bash
docker exec -it ebbl_app php artisan route:clear
docker exec -it ebbl_app php artisan route:cache
```

---

## Rollback Procedure

If deployment fails:

### 1. Rollback Code
```bash
git log --oneline -5  # Find previous commit
git reset --hard <commit-hash>
```

### 2. Rollback Database (if needed)
```bash
# Restore from backup
docker exec -i ebbl_mysql mysql -u root -prootpassword ebbm < backup.sql
```

### 3. Redeploy
```bash
./deploy-after-pull.sh
```

---

## Monitoring

### View Logs
```bash
# All logs
docker compose logs -f

# App logs only
docker compose logs -f app

# Laravel logs
docker exec -it ebbl_app tail -f storage/logs/laravel.log

# Nginx logs
docker compose logs -f app | grep nginx
```

### Check Container Status
```bash
docker compose ps
```

### Check Application Health
```bash
# Check Laravel version
docker exec -it ebbl_app php artisan --version

# Check database connection
docker exec -it ebbl_app php artisan db:show

# Check routes
docker exec -it ebbl_app php artisan route:list
```

---

## Performance Optimization

### After Deployment
```bash
# Optimize autoloader
docker exec -it ebbl_app composer dump-autoload --optimize

# Cache everything
docker exec -it ebbl_app php artisan optimize

# Clear old logs (optional)
docker exec -it ebbl_app sh -c "truncate -s 0 storage/logs/laravel.log"
```

---

## Backup Before Deployment

### Database Backup
```bash
# Create backup
docker exec ebbl_mysql mysqldump -u root -prootpassword ebbm > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
docker exec -i ebbl_mysql mysql -u root -prootpassword ebbm < backup_YYYYMMDD_HHMMSS.sql
```

### Files Backup
```bash
# Backup storage
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/

# Restore storage
tar -xzf storage_backup_YYYYMMDD_HHMMSS.tar.gz
```

---

## Environment-Specific Notes

### Production (.env)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-production-ip

# Use appropriate values
DB_HOST=mysql
DB_DATABASE=ebbm
DB_USERNAME=ebbm_user
DB_PASSWORD=strong_password
```

### Important
- Always set `APP_DEBUG=false` in production
- Use strong passwords
- Keep `.env` file secure
- Never commit `.env` to git

---

## Quick Reference

| Task | Command |
|------|---------|
| Deploy after pull | `./deploy-after-pull.sh` |
| View logs | `docker compose logs -f` |
| Clear cache | `docker exec -it ebbl_app php artisan cache:clear` |
| Restart | `docker compose restart` |
| Shell access | `docker exec -it ebbl_app sh` |
| Fix permissions | `./fix-assets.sh` |
| Fix Livewire | `./fix-livewire-404.sh` |

---

## Support

If you encounter issues:
1. Check logs: `docker compose logs -f`
2. Check Laravel logs: `docker exec -it ebbl_app cat storage/logs/laravel.log`
3. Run fix scripts: `./fix-livewire-404.sh` or `./fix-assets.sh`
4. Consult `TROUBLESHOOTING-LIVEWIRE.md`
