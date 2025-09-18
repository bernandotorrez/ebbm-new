# Docker Structure Documentation

## 📁 Directory Structure

```
docker/
├── php/
│   ├── Dockerfile              # Multi-stage PHP build
│   ├── php.ini                 # PHP configuration
│   ├── opcache.ini            # OPcache optimization
│   └── php-fpm-pool.conf      # PHP-FPM pool settings
├── nginx/
│   ├── default.conf           # Nginx virtual host
│   └── ssl/                   # SSL certificates (production)
├── mysql/
│   └── my.cnf                 # MySQL optimization
└── redis/
    └── redis.conf             # Redis configuration

# Root level Docker files
├── docker-compose.yml         # Development environment
├── docker-compose.prod.yml    # Production environment
├── .dockerignore             # Docker build exclusions
├── .env.production           # Production environment variables
├── deploy.sh                 # Linux/macOS deployment script
├── deploy.bat                # Windows deployment script
└── DOCKER_DEPLOYMENT.md      # Deployment guide
```

## 🐳 Multi-Stage Dockerfile Architecture

### Stage 1: Node.js Builder
- **Purpose**: Build frontend assets with Vite
- **Base Image**: `node:18-alpine`
- **Output**: Compiled CSS/JS assets in `/app/public/build`

### Stage 2: PHP Base
- **Purpose**: Install PHP extensions and system dependencies
- **Base Image**: `php:8.3-fpm-alpine`
- **Extensions**: PDO MySQL, mbstring, exif, pcntl, bcmath, gd, zip, intl, opcache

### Stage 3: Development Image
- **Purpose**: Development environment with dev dependencies
- **Includes**: Composer dev packages, source code, debugging tools
- **Volume Mounts**: Live code reloading

### Stage 4: Production Image
- **Purpose**: Optimized production deployment
- **Excludes**: Dev dependencies, test files, build tools
- **Optimizations**: Composer autoload optimization, removed dev files

## 🔧 Service Configuration

### PHP-FPM Service
```yaml
- Container: ebbm-php
- Port: 9000 (internal)
- Process Manager: Dynamic (10 start, 5-15 spare, 50 max)
- Memory Limit: 512M per process
- Health Check: /ping endpoint
```

### Nginx Service
```yaml
- Container: ebbm-nginx
- Ports: 80:80, 443:443
- Features: FastCGI caching, gzip compression, rate limiting
- Cache: 100MB zone, 500MB storage
- Health Check: /nginx-health endpoint
```

### MySQL Service
```yaml
- Container: ebbm-mysql
- Image: percona:8.0
- Port: 3306
- Buffer Pool: 512MB (dev), 1GB (prod)
- Features: Slow query logging, binary logging
```

### Redis Service
```yaml
- Container: ebbm-redis
- Image: redis:7.2-alpine
- Port: 6379
- Memory: 256MB limit
- Persistence: AOF + RDB snapshots
```

## 🌐 Network Configuration

### Development Network
```yaml
Network: ebbm-network
Type: bridge
Subnet: 172.20.0.0/16
Services: All containers communicate internally
```

### Production Network
```yaml  
Network: ebbm-network
Type: bridge
Subnet: 172.21.0.0/16
Isolation: Production-grade network isolation
```

## 💾 Volume Management

### Development Volumes
```yaml
Volumes:
- mysql_data: Database persistence
- redis_data: Cache persistence  
- nginx_cache: FastCGI cache storage
- *_log: Centralized logging
- Source code: Bind mounted for live reload
```

### Production Volumes
```yaml
Volumes:
- mysql_data: Database persistence
- redis_data: Cache persistence
- app_storage: Application file storage
- app_cache: Framework cache
- *_log: Centralized logging
```

## 🛡️ Security Features

### Container Security
- Non-root user execution (www-data)
- Minimal base images (Alpine Linux)
- Security headers in Nginx
- Disabled dangerous PHP functions
- Resource limits and constraints

### Network Security
- Internal service communication
- Rate limiting on API/admin endpoints
- SSL/TLS support (production)
- Firewall-friendly port mapping

## 📊 Performance Optimizations

### PHP Optimizations
```ini
OPcache:
- JIT compilation enabled
- 256MB memory allocation
- Optimized for Laravel

PHP-FPM:
- Dynamic process management
- Optimized pool configuration
- Health monitoring
```

### Database Optimizations
```ini
MySQL:
- InnoDB buffer pool tuning
- Query cache optimization
- Slow query logging
- Binary logging for replication
```

### Web Server Optimizations
```ini
Nginx:
- FastCGI caching (60min TTL)
- Gzip compression
- Static file caching (1 year)
- Rate limiting protection
```

### Cache Strategy
```ini
Redis:
- Session storage
- Application cache
- Queue backend
- LRU eviction policy
```

## 🔄 Container Lifecycle

### Startup Sequence
1. MySQL container starts first
2. Redis starts in parallel
3. PHP-FPM waits for database
4. Nginx starts after PHP-FPM
5. Queue workers start after core services
6. Health checks validate all services

### Health Monitoring
- All services have health checks
- 30-second intervals
- Automatic restart on failure
- Graceful degradation

## 📈 Scaling Considerations

### Horizontal Scaling
```yaml
Production Scaling:
- PHP-FPM: 3 replicas
- Queue Workers: 4 replicas
- Database: Single master (can add read replicas)
- Redis: Single instance (can cluster)
```

### Resource Allocation
```yaml
Development:
- Total RAM: ~2GB
- Total CPU: 2-3 cores
- Disk: ~5GB

Production:
- Total RAM: ~6GB
- Total CPU: 4-6 cores  
- Disk: ~20GB
```

## 🔧 Customization Points

### Environment-Specific Overrides
- Different compose files for dev/prod
- Environment variable substitution
- Volume mount strategies
- Service replica counts

### Configuration Customization
- PHP.ini settings per environment
- Nginx virtual host configuration
- MySQL performance tuning
- Redis memory policies

## 🧪 Testing Infrastructure

### Container Testing
```bash
# Service health validation
docker-compose exec php php artisan migrate:status
docker-compose exec nginx curl -f localhost/nginx-health
docker-compose exec mysql mysql -e "SELECT 1"
docker-compose exec redis redis-cli ping
```

### Performance Testing
```bash
# Load testing endpoints
ab -n 1000 -c 10 http://localhost/
wrk -t12 -c400 -d30s http://localhost/

# Database performance
docker-compose exec mysql mysql -e "SHOW ENGINE INNODB STATUS\G"
```

## 📋 Maintenance Tasks

### Regular Maintenance
- Log rotation and cleanup
- Database optimization (weekly)
- Security updates (monthly)
- Performance monitoring
- Backup verification

### Troubleshooting Tools
- Container logs analysis
- Resource usage monitoring
- Network connectivity testing
- Database slow query analysis
- Cache hit rate monitoring

---

This structure provides a robust, scalable, and maintainable Docker environment for Laravel applications with clear separation of concerns and environment-specific optimizations.