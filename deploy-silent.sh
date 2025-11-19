#!/bin/bash

echo "=== Laravel Docker Deployment (Silent Mode) ==="
echo ""

# Detect docker command
DOCKER="docker"
if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
    else
        echo "❌ Error: Docker is not running"
        exit 1
    fi
fi

# Detect docker compose
if $DOCKER compose version >/dev/null 2>&1; then
    DC="$DOCKER compose"
elif docker-compose version >/dev/null 2>&1; then
    DC="docker-compose"
else
    echo "❌ Error: Docker Compose not found"
    exit 1
fi

echo "🐳 Docker: $DOCKER"
echo "📦 Docker Compose: $DC"
echo ""

# Stop existing containers
echo "⏹️  Stopping existing containers..."
$DC down >/dev/null 2>&1

# Build and start
echo "🔨 Building and starting containers..."
if $DC up -d --build >/dev/null 2>&1; then
    echo "✅ Build successful!"
else
    echo "❌ Build failed! Showing logs..."
    $DC logs
    exit 1
fi

# Wait for initialization
echo "⏳ Waiting for containers to initialize..."
sleep 15

# Quick health check
echo "🏥 Health check..."
if $DC ps | grep -q "Up"; then
    echo "✅ Containers are running"
else
    echo "⚠️  Some containers may not be running properly"
fi

# Fix permissions and setup Livewire silently
$DC exec app chown -R www-data:www-data /var/www/html/storage >/dev/null 2>&1
$DC exec app chmod -R 775 /var/www/html/storage >/dev/null 2>&1
$DC exec app mkdir -p /var/www/html/storage/app/livewire-tmp >/dev/null 2>&1
$DC exec app chmod -R 775 /var/www/html/storage/app/livewire-tmp >/dev/null 2>&1
$DC exec app chown -R www-data:www-data /var/www/html/storage/app/livewire-tmp >/dev/null 2>&1

# Clear caches silently
$DC exec app php artisan optimize:clear >/dev/null 2>&1
$DC exec app php artisan filament:clear-cached-components >/dev/null 2>&1
$DC exec app php artisan livewire:delete-uploaded-files --hours=24 >/dev/null 2>&1
$DC exec app php artisan config:cache >/dev/null 2>&1

echo ""
echo "=== ✅ Deployment Completed! ==="
echo ""
echo "🌐 Application URLs:"
echo "   • http://localhost"
echo "   • http://$(hostname -I 2>/dev/null | awk '{print $1}' || echo 'your-ip')"
echo ""
echo "📊 Container Status:"
$DC ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
echo ""
echo "💡 Useful commands:"
echo "   • View logs:    docker compose logs -f"
echo "   • Stop:         docker compose down"
echo "   • Restart:      docker compose restart"
echo "   • Shell:        docker exec -it ebbl_app sh"
