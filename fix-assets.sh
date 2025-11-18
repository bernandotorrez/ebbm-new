#!/bin/bash

echo "=== Fix Assets Not Loading ==="
echo ""

# Detect docker command
DOCKER="docker"
DC="docker compose"

if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
        DC="sudo docker compose"
    fi
fi

echo "Step 1: Checking public directory..."
$DOCKER exec ebbl_app ls -la public/

echo ""
echo "Step 2: Checking build directory..."
$DOCKER exec ebbl_app ls -la public/build/ 2>/dev/null || echo "Build directory not found"

echo ""
echo "Step 3: Setting proper permissions for public directory..."
$DOCKER exec ebbl_app sh -c "chmod -R 755 public && chown -R www-data:www-data public"

echo ""
echo "Step 4: Clearing all caches..."
$DOCKER exec ebbl_app php artisan cache:clear
$DOCKER exec ebbl_app php artisan config:clear
$DOCKER exec ebbl_app php artisan view:clear

echo ""
echo "Step 5: Publishing vendor assets..."
$DOCKER exec ebbl_app php artisan vendor:publish --tag=public --force
$DOCKER exec ebbl_app php artisan filament:assets

echo ""
echo "Step 6: Checking APP_URL and ASSET_URL..."
$DOCKER exec ebbl_app php -r "echo 'APP_URL: ' . env('APP_URL') . PHP_EOL; echo 'ASSET_URL: ' . (env('ASSET_URL') ?: 'Not set') . PHP_EOL;"

echo ""
echo "Step 7: Restarting container..."
$DC restart

echo ""
echo "=== Fix completed! ==="
echo ""
echo "If assets still not loading, check:"
echo "1. Browser console for 404 errors on specific files"
echo "2. Network tab to see which assets are failing"
echo "3. Make sure APP_URL in .env is correct: $(grep APP_URL .env)"
