#!/bin/bash

# Quick Performance Test Script
echo "🚀 Laravel Performance Test"
echo "============================"

echo ""
echo "Testing application response time:"
echo "-----------------------------------"

# Test with timing
for i in {1..3}; do
    echo "Request $i:"
    docker-compose exec nginx curl -w "  ⏱️  Total: %{time_total}s | Connect: %{time_connect}s | TTFB: %{time_starttransfer}s\n" -s -o /dev/null http://localhost
done

echo ""
echo "📊 Current Performance Stats:"
echo "-----------------------------"

# OPcache stats
docker-compose exec php php -r "
\$status = opcache_get_status();
echo '🔥 OPcache: ' . \$status['opcache_statistics']['num_cached_scripts'] . ' cached scripts (' . round(\$status['opcache_statistics']['opcache_hit_rate'], 1) . '% hit rate)' . PHP_EOL;
"

# Check if FastCGI cache is working
echo -n "🌐 FastCGI Cache: "
CACHE_STATUS=$(docker-compose exec nginx curl -I -s http://localhost | grep -i "x-fastcgi-cache" || echo "Not cached")
echo $CACHE_STATUS

# PHP-FPM status
echo -n "⚡ PHP-FPM: "
docker-compose exec php php -r "echo 'Ready - ' . (extension_loaded('opcache') ? 'OPcache enabled' : 'OPcache disabled') . PHP_EOL;"

echo ""
echo "✅ Performance test completed!"