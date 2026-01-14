#!/bin/bash

echo "=== Deployment After Git Pull ==="
echo ""

# Detect docker command
DOCKER="docker"
DC="docker compose"

if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
        DC="sudo docker compose"
    else
        echo "❌ Error: Docker is not running"
        exit 1
    fi
fi

echo "📦 Starting deployment process..."
echo ""

# Step 1: Rebuild Docker images
echo "1️⃣  Rebuilding Docker images..."
$DC down
$DC up -d --build

echo ""
echo "⏳ Waiting for containers to start..."
sleep 15

# Step 2: Install/Update Composer dependencies
echo ""
echo "2️⃣  Installing Composer dependencies..."
$DOCKER exec ebmp_app composer install --no-dev --optimize-autoloader

# Step 3: Run migrations
echo ""
echo "3️⃣  Running database migrations..."
$DOCKER exec ebmp_app php artisan migrate --force

# Step 4: Clear all caches
echo ""
echo "4️⃣  Clearing all caches..."
$DOCKER exec ebmp_app php artisan cache:clear
$DOCKER exec ebmp_app php artisan config:clear
$DOCKER exec ebmp_app php artisan route:clear
$DOCKER exec ebmp_app php artisan view:clear
$DOCKER exec ebmp_app php artisan event:clear
$DOCKER exec ebmp_app php artisan filament:clear-cached-components
$DOCKER exec ebmp_app php artisan livewire:delete-uploaded-files --hours=24

# Step 5: Rebuild caches for production
echo ""
echo "5️⃣  Rebuilding caches for production..."
$DOCKER exec ebmp_app php artisan config:cache
$DOCKER exec ebmp_app php artisan route:cache
$DOCKER exec ebmp_app php artisan view:cache
$DOCKER exec ebmp_app php artisan event:cache

# Step 6: Publish assets
echo ""
echo "6️⃣  Publishing assets..."
$DOCKER exec ebmp_app php artisan vendor:publish --tag=public --force
$DOCKER exec ebmp_app php artisan filament:assets

# Step 7: Create storage link
echo ""
echo "7️⃣  Creating storage link..."
$DOCKER exec ebmp_app php artisan storage:link

# Step 8: Fix permissions
echo ""
echo "8️⃣  Fixing permissions..."
$DOCKER exec ebmp_app sh -c "chmod -R 775 storage bootstrap/cache public && chown -R www-data:www-data storage bootstrap/cache public"

# Step 8.1: Setup Livewire directories
echo ""
echo "8️⃣.1 Setting up Livewire directories..."
$DOCKER exec ebmp_app sh -c "mkdir -p storage/app/livewire-tmp && chmod -R 775 storage/app/livewire-tmp && chown -R www-data:www-data storage/app/livewire-tmp"

# Step 9: Restart containers
echo ""
echo "9️⃣  Restarting containers..."
$DC restart

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 10

# Step 10: Health check
echo ""
echo "🔟 Running health check..."
if $DC ps | grep -q "Up"; then
    echo "✅ All containers are running"
else
    echo "⚠️  Some containers may have issues"
fi

echo ""
echo "=== ✅ Deployment Completed! ==="
echo ""
echo "🌐 Application URLs:"
echo "   • http://localhost"
echo "   • http://$(hostname -I 2>/dev/null | awk '{print $1}' || echo 'your-ip')"
echo ""
echo "📊 Container Status:"
$DC ps
echo ""
echo "💡 Useful commands:"
echo "   • View logs:    docker compose logs -f"
echo "   • Check app:    docker exec -it ebmp_app php artisan --version"
echo "   • Shell:        docker exec -it ebmp_app sh"
