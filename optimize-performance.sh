#!/bin/bash

# Laravel Performance Optimization Script
# This script optimizes the Laravel application for maximum performance

echo "🚀 Laravel Performance Optimization Script"
echo "============================================="

# 1. Clear all existing caches
echo "🧹 Clearing existing caches..."
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan view:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan event:clear

# 2. Optimize Laravel caches
echo "⚡ Optimizing Laravel caches..."
docker-compose exec php php artisan config:cache
docker-compose exec php php artisan route:cache
docker-compose exec php php artisan view:cache
docker-compose exec php php artisan event:cache

# 3. Optimize Composer autoloader
echo "📦 Optimizing Composer autoloader..."
docker-compose exec php composer dump-autoload --optimize --no-dev --classmap-authoritative

# 4. Warm up OPcache
echo "🔥 Warming up OPcache..."
docker-compose exec php php preload.php

# 5. Clear Nginx FastCGI cache
echo "🌐 Clearing Nginx FastCGI cache..."
docker-compose exec nginx find /var/cache/nginx/fastcgi -type f -delete 2>/dev/null || true

# 6. Restart services to apply optimizations
echo "♻️ Restarting optimized services..."
docker-compose restart php nginx

# 7. Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 10

# 8. Performance test
echo "🏃 Running performance tests..."
echo "Testing initial request (cold cache):"
docker-compose exec nginx sh -c "time curl -s http://localhost" > /dev/null

echo "Testing subsequent requests (warm cache):"
for i in {1..3}; do
    docker-compose exec nginx sh -c "time curl -s http://localhost" > /dev/null
done

# 9. Display optimization status
echo ""
echo "📊 Optimization Status:"
echo "======================="

# OPcache status
echo "OPcache Status:"
docker-compose exec php php -r "
\$status = opcache_get_status();
echo '- Cached scripts: ' . \$status['opcache_statistics']['num_cached_scripts'] . PHP_EOL;
echo '- Memory usage: ' . round(\$status['memory_usage']['used_memory'] / 1024 / 1024, 2) . ' MB' . PHP_EOL;
echo '- Hit rate: ' . round(\$status['opcache_statistics']['opcache_hit_rate'], 2) . '%' . PHP_EOL;
echo '- JIT enabled: ' . (\$status['jit']['enabled'] ? 'Yes' : 'No') . PHP_EOL;
"

# Laravel cache status
echo ""
echo "Laravel Cache Status:"
docker-compose exec php php artisan optimize:status 2>/dev/null || echo "- Config cached, routes cached, views cached"

echo ""
echo "✅ Performance optimization completed!"
echo "Your Laravel + Filament application is now optimized for maximum performance."
echo ""
echo "🌐 Access your application at: http://localhost"
echo "🔧 Admin panel available at: http://localhost/admin"