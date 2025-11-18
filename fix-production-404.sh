#!/bin/bash

echo "=== Comprehensive Fix for Livewire 404 in Production ==="
echo ""

# Detect docker command
DOCKER="docker"
DC="docker compose"

if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
        DC="sudo docker compose"
    else
        echo "Error: Docker is not running"
        exit 1
    fi
fi

echo "Step 1: Checking APP_URL..."
$DOCKER exec ebbl_app php -r "echo 'Current APP_URL: ' . env('APP_URL') . PHP_EOL;"
echo ""

echo "Step 2: Clearing ALL caches..."
$DOCKER exec ebbl_app php artisan cache:clear
$DOCKER exec ebbl_app php artisan config:clear
$DOCKER exec ebbl_app php artisan route:clear
$DOCKER exec ebbl_app php artisan view:clear
$DOCKER exec ebbl_app php artisan event:clear

echo ""
echo "Step 3: Creating required directories..."
$DOCKER exec ebbl_app sh -c "mkdir -p storage/app/livewire-tmp storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs"

echo ""
echo "Step 4: Setting proper permissions..."
$DOCKER exec ebbl_app sh -c "chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache"

echo ""
echo "Step 5: Creating storage link..."
$DOCKER exec ebbl_app php artisan storage:link

echo ""
echo "Step 6: Running migrations (if needed)..."
$DOCKER exec ebbl_app php artisan migrate --force

echo ""
echo "Step 7: Publishing assets..."
$DOCKER exec ebbl_app php artisan vendor:publish --tag=livewire:assets --force
$DOCKER exec ebbl_app php artisan filament:assets
$DOCKER exec ebbl_app sh -c "chmod -R 755 public && chown -R www-data:www-data public"

echo ""
echo "Step 8: Rebuilding caches for production..."
$DOCKER exec ebbl_app php artisan config:cache
$DOCKER exec ebbl_app php artisan route:cache
$DOCKER exec ebbl_app php artisan view:cache

echo ""
echo "Step 9: Verifying Livewire routes..."
$DOCKER exec ebbl_app php artisan route:list --path=livewire

echo ""
echo "Step 10: Restarting container..."
$DC restart

echo ""
echo "=== Fix completed! ==="
echo ""
echo "Please test your application now."
echo "If error persists, check:"
echo "1. APP_URL in .env matches the URL you're accessing"
echo "2. Browser console for detailed error messages"
echo "3. Docker logs: docker compose logs app"
