# Docker Deployment Guide for EBBM Laravel Project

## Overview

This Docker setup provides a complete, production-ready environment for the EBBM Laravel Filament application with performance optimizations including:

- **PHP 8.3.17-FPM** with OPcache, JIT compilation, and Laravel-optimized extensions
- **Nginx** with FastCGI caching and static file optimization
- **Percona MySQL 8.0** with performance tuning for Laravel workloads
- **Redis** for caching and session storage
- **Node.js** for Vite development server (local environment)

## Quick Start

### Prerequisites

- Docker Desktop (Windows/macOS) or Docker Engine + Docker Compose (Linux)
- Git
- 4GB+ RAM available for containers

### Local Development

```bash
# Clone and navigate to project
git clone <repository-url>
cd ebbm-new

# Deploy with development tools
./deploy.sh --dev                    # Linux/macOS
deploy.bat --dev                     # Windows

# Access the application
# Application: http://localhost
# PHPMyAdmin: http://localhost:8080
# Redis Commander: http://localhost:8081
# Vite Dev Server: http://localhost:5173
```

### Production Deployment

```bash
# Configure environment
cp .env.production .env.production.local
# Edit .env.production.local with your settings

# Deploy production
./deploy.sh --env production         # Linux/macOS
deploy.bat --env production          # Windows
```

## Architecture

### Services Overview

| Service | Purpose | Port | Image |
|---------|---------|------|-------|
| `nginx` | Web server & reverse proxy | 80, 443 | nginx:alpine |
| `php` | Laravel application | 9000 | Custom PHP 8.3.17-FPM |
| `mysql` | Database | 3306 | percona:8.0 |
| `redis` | Cache & sessions | 6379 | redis:7-alpine |
| `node` | Vite dev server (local only) | 5173 | node:18-alpine |
| `queue` | Laravel queue worker (prod) | - | Custom PHP 8.3.17-FPM |
| `scheduler` | Laravel scheduler (prod) | - | Custom PHP 8.3.17-FPM |

### Performance Optimizations

#### PHP Optimizations
- **OPcache**: Enabled with JIT compilation for PHP 8.3+
- **Memory**: 512MB limit with optimized buffer sizes
- **Extensions**: bcmath, exif, gd, intl, mbstring, opcache, pcntl, pdo_mysql, redis, zip
- **FPM Pool**: Dynamic process management with optimized worker counts

#### Nginx Optimizations
- **FastCGI**: Caching and connection pooling
- **Gzip**: Compression for text assets
- **Static Files**: Long-term caching with proper headers
- **Security**: Rate limiting, security headers, and file access controls

#### MySQL Optimizations
- **InnoDB**: Buffer pool tuning and I/O optimization
- **Memory**: Optimized buffer sizes for Laravel workloads
- **Connections**: Configured for concurrent access
- **Logging**: Slow query logging enabled

#### Redis Optimizations
- **Memory**: LRU eviction policy with 256MB-512MB limits
- **Persistence**: Configurable based on environment
- **Performance**: Pipelining and connection pooling ready

## Directory Structure

```
docker/
├── php/
│   ├── Dockerfile              # PHP-FPM with Laravel optimizations
│   ├── php.ini                 # PHP configuration
│   ├── opcache.ini            # OPcache and JIT settings
│   └── php-fpm-pool.conf      # FPM pool configuration
├── nginx/
│   └── default.conf           # Nginx virtual host with FastCGI
├── mysql/
│   └── my.cnf                 # MySQL performance configuration
└── entrypoints/
    └── php-entrypoint.sh      # Container initialization script
```

## Environment Configuration

### Local Development (.env)
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=mysql
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Production (.env.production)
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=mysql
CACHE_DRIVER=redis
SESSION_DRIVER=redis
OPCACHE_ENABLE=1
```

## Deployment Scripts

### deploy.sh / deploy.bat Options

| Option | Description |
|--------|-------------|
| `--env local\|production` | Set deployment environment |
| `--fresh` | Remove all images and rebuild |
| `--dev` | Include development tools |
| `--help` | Show help message |

### Examples

```bash
# Local with fresh build and dev tools
./deploy.sh --fresh --dev

# Production deployment
./deploy.sh --env production

# Fresh production build
./deploy.sh --env production --fresh
```

## Container Management

### Common Commands

```bash
# View logs
docker-compose logs -f

# Access PHP container
docker-compose exec php bash

# Run Laravel commands
docker-compose exec php php artisan migrate
docker-compose exec php php artisan cache:clear

# Monitor performance
docker stats

# Stop all services
docker-compose down

# Stop and remove everything
docker-compose down --rmi all --volumes --remove-orphans
```

### Database Access

```bash
# MySQL CLI
docker-compose exec mysql mysql -u ebbm_user -p ebbm_local

# PHPMyAdmin (dev mode)
# http://localhost:8080
```

### Redis Access

```bash
# Redis CLI
docker-compose exec redis redis-cli

# Redis Commander (dev mode)
# http://localhost:8081
```

## Production Considerations

### Security

1. **Environment Variables**: Store sensitive data in `.env.production`
2. **Secrets**: Use Docker secrets for database passwords
3. **SSL**: Configure SSL certificates for HTTPS
4. **Firewall**: Restrict access to necessary ports only

### Monitoring

1. **Logs**: Centralized logging with Docker logging drivers
2. **Health Checks**: Container health monitoring
3. **Metrics**: Application and infrastructure monitoring
4. **Alerts**: Automated alerting for critical issues

### Backup

1. **Database**: Regular MySQL dumps
2. **Storage**: Laravel storage directory backups
3. **Configuration**: Environment and secret files backup

### Scaling

1. **Horizontal**: Multiple PHP-FPM workers
2. **Load Balancing**: Multiple nginx instances
3. **Database**: Read replicas for heavy read workloads
4. **Cache**: Redis clustering for high availability

## Troubleshooting

### Common Issues

1. **Port Conflicts**: Change ports in docker-compose.yml
2. **Memory Issues**: Increase Docker Desktop memory allocation
3. **Permission Issues**: Check file ownership and Docker user mapping
4. **Build Failures**: Run with `--fresh` flag to rebuild

### Performance Tuning

1. **Monitor**: Use `docker stats` to monitor resource usage
2. **Optimize**: Adjust memory limits and worker counts
3. **Cache**: Ensure Redis is properly configured
4. **Database**: Monitor slow query log

### Logs and Debugging

```bash
# Application logs
docker-compose logs php

# Nginx access/error logs
docker-compose logs nginx

# Database logs
docker-compose logs mysql

# All services
docker-compose logs -f
```

## Development Workflow

### Daily Development

1. Start containers: `./deploy.sh --dev`
2. Develop with hot reload via Vite
3. Use PHPMyAdmin for database management
4. Monitor with Redis Commander

### Code Changes

1. PHP code changes are reflected immediately (volume mount)
2. Frontend changes trigger Vite hot reload
3. Configuration changes require container restart

### Testing

```bash
# Run PHPUnit tests
docker-compose exec php ./vendor/bin/phpunit

# Run Laravel Pint (code style)
docker-compose exec php ./vendor/bin/pint

# Access Tinker
docker-compose exec php php artisan tinker
```

This Docker setup provides a robust, scalable foundation for the EBBM Laravel Filament application with optimal performance and development experience.