# 🔧 Docker Troubleshooting Guide for Laravel Filament 3

## 🚨 Common Issues and Solutions

### 1. **Build Issues**

#### Issue: "Build context too large"
```bash
# Solution: Optimize .dockerignore
echo "node_modules/" >> .dockerignore
echo "vendor/" >> .dockerignore
echo ".git/" >> .dockerignore
```

#### Issue: "Composer install fails"
```bash
# Solution: Clear composer cache and retry
docker system prune -f
docker-compose build --no-cache php
```

#### Issue: "PHP extensions missing"
```dockerfile
# Solution: Add missing extensions in Dockerfile
RUN docker-php-ext-install \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo \
    pdo_mysql \
    zip
```

### 2. **Runtime Issues**

#### Issue: "Permission denied" errors
```bash
# Solution: Fix permissions
docker-compose exec php chown -R app:app /var/www/html/storage
docker-compose exec php chown -R app:app /var/www/html/bootstrap/cache
```

#### Issue: "Database connection refused"
```bash
# Solution: Check service health
docker-compose ps
docker-compose logs mysql

# Wait for MySQL to be healthy
docker-compose up -d mysql
docker-compose exec mysql mysqladmin ping
```

#### Issue: "Redis connection failed"
```bash
# Solution: Check Redis service
docker-compose logs redis
docker-compose exec redis redis-cli ping
```

### 3. **Performance Issues**

#### Issue: "Slow page loads"
```bash
# Solutions:
# 1. Check OPcache status
docker-compose exec php php -m | grep -i opcache

# 2. Verify FastCGI cache
docker-compose exec nginx nginx -t
docker-compose logs nginx | grep cache

# 3. Monitor resource usage
docker stats --no-stream
```

#### Issue: "High memory usage"
```bash
# Solution: Adjust memory limits
# Edit docker-compose.yml:
deploy:
  resources:
    limits:
      memory: 256M  # Reduce if needed
```

### 4. **Laravel Specific Issues**

#### Issue: "Class not found" errors
```bash
# Solution: Regenerate autoloader
docker-compose exec php composer dump-autoload --optimize
docker-compose exec php php artisan clear-compiled
docker-compose exec php php artisan config:cache
```

#### Issue: "Queue jobs not processing"
```bash
# Solution: Check queue worker
docker-compose logs queue
docker-compose exec php php artisan queue:restart
```

#### Issue: "Filament admin panel 404"
```bash
# Solution: Ensure Filament is properly installed
docker-compose exec php php artisan filament:install --panels
docker-compose exec php php artisan migrate
```

### 5. **Security Issues**

#### Issue: "Container running as root"
```dockerfile
# Solution: Always use non-root user
USER app
```

#### Issue: "Exposed sensitive ports"
```yaml
# Solution: Remove port exposure for internal services
# mysql:
#   ports:
#     - "3306:3306"  # Remove this line
```

## 🎯 Performance Optimization Commands

### Development Environment
```bash
# Start optimized development environment
docker-compose up -d

# Monitor performance
docker stats

# Check service health
docker-compose ps
docker-compose exec php php artisan about
```

### Production Environment
```bash
# Build and deploy production
./build-image.sh --tag production
docker-compose -f docker-compose.prod.yml up -d

# Monitor production services
docker-compose -f docker-compose.prod.yml logs -f
docker-compose -f docker-compose.prod.yml exec php php artisan optimize
```

## 🔍 Debug Commands

### Container Inspection
```bash
# Enter PHP container
docker-compose exec php bash

# Check PHP configuration
docker-compose exec php php --ini
docker-compose exec php php -m

# Check Laravel configuration
docker-compose exec php php artisan about
docker-compose exec php php artisan config:show
```

### Service Debugging
```bash
# Check Nginx configuration
docker-compose exec nginx nginx -t
docker-compose exec nginx nginx -T

# MySQL connection test
docker-compose exec mysql mysql -u ebbm_user -p -e "SHOW DATABASES;"

# Redis functionality test
docker-compose exec redis redis-cli info
```

### Log Analysis
```bash
# View all service logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mysql

# View Laravel logs
docker-compose exec php tail -f storage/logs/laravel.log
```

## ⚡ Performance Tuning

### 1. **OPcache Optimization**
```ini
# docker/php/opcache.ini
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.jit = tracing
opcache.jit_buffer_size = 128M
```

### 2. **Nginx Caching**
```nginx
# docker/nginx/nginx.conf
fastcgi_cache_path /var/cache/nginx/fastcgi
                   levels=1:2
                   keys_zone=LARAVEL:100m
                   inactive=60m;
```

### 3. **MySQL Tuning**
```ini
# docker/mysql/my.cnf
innodb_buffer_pool_size = 512M
innodb_buffer_pool_instances = 2
max_connections = 200
```

### 4. **Redis Optimization**
```yaml
# docker-compose.yml
redis:
  command: >
    redis-server
    --maxmemory 512mb
    --maxmemory-policy allkeys-lru
```

## 🛡️ Security Checklist

- ✅ Non-root user execution
- ✅ Secrets management for production
- ✅ Security headers in Nginx
- ✅ Rate limiting configured
- ✅ File permission restrictions
- ✅ Network isolation
- ✅ No exposed sensitive ports
- ✅ Environment variable isolation

## 📊 Monitoring Commands

### Resource Usage
```bash
# Monitor container resources
docker stats --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"

# Check disk usage
docker system df
```

### Service Health
```bash
# Check all health statuses
docker-compose ps

# Individual service health
docker-compose exec php php artisan about
docker-compose exec mysql mysqladmin status
docker-compose exec redis redis-cli ping
```

## 🔄 Maintenance Tasks

### Daily
```bash
# Check service status
docker-compose ps
docker stats --no-stream
```

### Weekly
```bash
# Clean up unused resources
docker system prune -f
docker volume prune -f
```

### Monthly
```bash
# Update base images
docker-compose pull
docker-compose build --no-cache
```

## 🆘 Emergency Procedures

### Complete Reset
```bash
# Stop all services
docker-compose down

# Remove all containers and volumes
docker-compose down -v --remove-orphans

# Clean system
docker system prune -af

# Rebuild and restart
docker-compose build --no-cache
docker-compose up -d
```

### Backup Before Reset
```bash
# Backup database
docker-compose exec mysql mysqldump -u ebbm_user -p ebbm_local > backup.sql

# Backup storage
cp -r storage/ storage_backup/
```

## 📞 Support Contacts

For additional support:
1. Check Docker logs: `docker-compose logs`
2. Verify configurations: `./validate-docker.bat`
3. Review optimization guide: `DOCKER_OPTIMIZATION_SUMMARY.md`

**Status**: ✅ **READY FOR PRODUCTION USE**