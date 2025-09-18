# Docker Performance Optimization Guide

## 🚀 Build Speed Optimization

### 1. Docker BuildKit
Enable BuildKit for faster, more efficient builds:

**Windows PowerShell:**
```powershell
$env:DOCKER_BUILDKIT=1
$env:COMPOSE_DOCKER_CLI_BUILD=1
```

**Linux/macOS:**
```bash
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1
```

### 2. Layer Caching Strategy
The Dockerfile is optimized with proper layer ordering:
- System dependencies first (rarely change)
- Composer dependencies second (change occasionally)  
- Application code last (change frequently)

### 3. Multi-Stage Build Benefits
- **Reduced Image Size**: Production image ~150MB vs ~500MB without multi-stage
- **Security**: No build tools in production image
- **Speed**: Parallel build stages where possible

### 4. .dockerignore Optimization
Excludes unnecessary files to speed up context transfer:
- Development files (tests, docs)
- Git history and metadata
- Node modules and build artifacts
- IDE and OS files

## 📊 Runtime Performance

### 1. PHP Optimizations

**OPcache Configuration:**
```ini
opcache.memory_consumption = 256M    # Increased for better caching
opcache.max_accelerated_files = 20000 # Handle large codebases
opcache.validate_timestamps = 0       # Skip file checks in production
opcache.jit = tracing                # PHP 8+ JIT compilation
opcache.jit_buffer_size = 128M       # JIT memory allocation
```

**PHP-FPM Pool Tuning:**
```ini
pm = dynamic                         # Dynamic process management
pm.max_children = 50                # Max concurrent processes
pm.start_servers = 10               # Initial processes
pm.min_spare_servers = 5            # Minimum idle processes
pm.max_spare_servers = 15           # Maximum idle processes
pm.max_requests = 1000              # Recycle after N requests
```

### 2. Database Optimizations

**MySQL Configuration:**
```ini
innodb_buffer_pool_size = 512M      # Main memory cache
innodb_log_file_size = 128M         # Transaction log size
max_connections = 200               # Connection limit
query_cache_size = 64M              # Query result cache
thread_cache_size = 50              # Thread reuse cache
```

**Connection Pooling:**
- Use persistent connections in Laravel
- Configure appropriate timeout values
- Monitor connection usage

### 3. Caching Strategy

**Redis Configuration:**
```ini
maxmemory = 256mb                   # Memory limit
maxmemory-policy = allkeys-lru      # Eviction policy
save 900 1                         # Persistence settings
appendonly = yes                   # AOF for durability
```

**Laravel Caching:**
```bash
# Production cache commands
php artisan config:cache
php artisan route:cache  
php artisan view:cache
php artisan event:cache
```

### 4. Nginx Optimizations

**FastCGI Caching:**
```nginx
fastcgi_cache_path /var/cache/nginx/fastcgi levels=1:2 keys_zone=laravel:100m;
fastcgi_cache laravel;
fastcgi_cache_valid 200 301 302 60m;
```

**Static File Caching:**
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 💾 Disk Usage Optimization

### 1. Image Size Reduction

**Multi-stage builds reduce final image size by:**
- Removing build dependencies (~200MB)
- Excluding source files (~50MB)
- Removing dev packages (~100MB)

**Alpine Linux benefits:**
- Smaller base images (~5MB vs ~200MB)
- Faster container startup
- Reduced attack surface

### 2. Volume Management

**Efficient volume usage:**
```yaml
# Separate volumes for different data types
mysql_data:      # Database files
redis_data:      # Cache persistence  
nginx_cache:     # Web cache
app_storage:     # Application files
```

**Volume cleanup:**
```bash
# Remove unused volumes
docker volume prune

# Remove specific volumes
docker volume rm $(docker volume ls -qf dangling=true)
```

### 3. Log Management

**Log rotation configuration:**
```yaml
logging:
  driver: "json-file"
  options:
    max-size: "10m"
    max-file: "3"
```

## 🔧 Advanced Optimizations

### 1. Resource Limits

**Development limits:**
```yaml
deploy:
  resources:
    limits:
      cpus: '1.0'
      memory: 512M
    reservations:
      cpus: '0.5'
      memory: 256M
```

**Production scaling:**
```yaml
deploy:
  replicas: 3
  resources:
    limits:
      cpus: '2.0'
      memory: 1G
```

### 2. Health Check Optimization

**Efficient health checks:**
```yaml
healthcheck:
  test: ["CMD-SHELL", "curl -f http://localhost:9000/ping"]
  interval: 30s        # Check every 30 seconds
  timeout: 10s         # 10 second timeout
  retries: 3           # 3 failed attempts before unhealthy
  start_period: 40s    # Grace period for startup
```

### 3. Network Optimization

**Custom network benefits:**
- Improved security isolation
- Better DNS resolution
- Predictable IP addressing
- Traffic control capabilities

### 4. Container Startup Order

**Dependency management:**
```yaml
depends_on:
  - mysql
  - redis
```

**Wait strategies:**
```bash
# Wait for database
until docker-compose exec mysql mysqladmin ping; do
  echo "Waiting for database..."
  sleep 1
done
```

## ⚡ Quick Performance Tips

### 1. Docker Host Optimization

**Linux:**
```bash
# Increase file descriptor limits
echo 'fs.file-max = 65536' >> /etc/sysctl.conf

# Optimize memory usage
echo 'vm.max_map_count = 262144' >> /etc/sysctl.conf
sysctl -p
```

**Windows:**
```powershell
# Increase WSL2 memory (in .wslconfig)
[wsl2]
memory=4GB
processors=4
```

### 2. Development Workflow

**Faster rebuilds:**
```bash
# Build only changed services
docker-compose build --parallel

# Use build cache
docker-compose build --no-rm

# Skip dependency check
docker-compose up --no-deps service_name
```

### 3. Production Deployment

**Zero-downtime deployment:**
```bash
# Rolling update strategy
docker-compose -f docker-compose.prod.yml up -d --no-deps --scale php=3 --scale php_new=3
# Gradually shift traffic
docker-compose -f docker-compose.prod.yml stop php
docker-compose -f docker-compose.prod.yml rm php
```

## 📈 Monitoring and Profiling

### 1. Performance Monitoring

**Container metrics:**
```bash
# Resource usage
docker stats

# Container inspect
docker-compose exec php php artisan about

# Database performance
docker-compose exec mysql mysql -e "SHOW ENGINE INNODB STATUS\G"
```

### 2. Application Profiling

**Laravel Telescope (development):**
```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

**APM Integration:**
- New Relic
- Blackfire.io
- XHProf

### 3. Log Analysis

**Centralized logging:**
```bash
# View all logs
docker-compose logs -f

# Filter by service
docker-compose logs -f php nginx

# Follow specific errors
docker-compose logs -f | grep ERROR
```

## 🛠️ Troubleshooting Performance

### Common Issues and Solutions

**1. Slow Database Queries**
```bash
# Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

# Analyze queries
docker-compose exec mysql tail -f /var/log/mysql/slow.log
```

**2. High Memory Usage**
```bash
# Check PHP-FPM status
curl http://localhost/status

# Monitor Redis memory
docker-compose exec redis redis-cli info memory
```

**3. Container Startup Issues**
```bash
# Debug container startup
docker-compose up --no-deps --abort-on-container-exit php

# Check health status
docker-compose ps
```

## 📋 Performance Checklist

### Pre-deployment
- [ ] Enable BuildKit for faster builds
- [ ] Optimize .dockerignore file
- [ ] Configure proper resource limits
- [ ] Set up health checks
- [ ] Enable log rotation

### Post-deployment  
- [ ] Monitor resource usage
- [ ] Check application response times
- [ ] Verify cache hit rates
- [ ] Monitor database performance
- [ ] Set up alerting

### Regular Maintenance
- [ ] Update base images monthly
- [ ] Clean up unused volumes
- [ ] Rotate and archive logs
- [ ] Review slow query logs
- [ ] Optimize database indexes

---

This optimization guide provides comprehensive strategies for maximizing Docker performance in both development and production environments.