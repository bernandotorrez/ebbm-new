#!/bin/bash

echo "=== Fix Livewire 404 Error ==="
echo ""

# Detect docker command
DOCKER="docker"
if ! $DOCKER info >/dev/null 2>&1; then
    if sudo docker info >/dev/null 2>&1; then
        DOCKER="sudo docker"
    else
        echo "Error: Docker is not running"
        exit 1
    fi
fi

echo "Checking APP_URL configuration..."
$DOCKER exec ebbl_app php artisan config:show app.url
echo ""
echo "IMPORTANT: Make sure APP_URL in .env matches the URL you're accessing!"
echo "Example: If accessing via http://10.0.3.42, set APP_URL=http://10.0.3.42"
echo ""

echo "Step 1: Clearing all caches..."
$DOCKER exec ebbl_app php artisan cache:clear
$DOCKER exec ebbl_app php artisan config:clear
$DOCKER exec ebbl_app php artisan route:clear
$DOCKER exec ebbl_app php artisan view:clear
$DOCKER exec ebbl_app php artisan event:clear

echo ""
echo "Step 2: Rebuilding caches for production..."
$DOCKER exec ebbl_app php artisan config:cache
$DOCKER exec ebbl_app php artisan route:cache
$DOCKER exec ebbl_app php artisan view:cache

echo ""
echo "Step 3: Creating livewire-tmp directory with proper permissions..."
$DOCKER exec ebbl_app sh -c "mkdir -p storage/app/livewire-tmp && chmod -R 775 storage/app/livewire-tmp && chown -R www-data:www-data storage/app/livewire-tmp"

echo ""
echo "Step 4: Recreating storage link..."
$DOCKER exec ebbl_app php artisan storage:link

echo ""
echo "Step 5: Verifying routes..."
$DOCKER exec ebbl_app php artisan route:list --path=livewire

echo ""
echo "=== Fix completed! ==="
echo "If error persists, try:"
echo "1. Check APP_URL in .env matches your domain"
echo "2. Restart container: docker compose restart"
echo "3. Rebuild image: docker compose up -d --build"
