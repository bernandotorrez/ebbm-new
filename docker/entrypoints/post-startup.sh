#!/bin/sh

# This script runs after the main application has started
# It handles migrations and cache optimizations

echo "Waiting for application to be ready..."
sleep 10

echo "Running post-startup tasks..."

# Check if migrations table exists
if php artisan migrate:status >/dev/null 2>&1; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migrations failed, continuing..."
else
    echo "Creating and running initial migrations..."
    php artisan migrate --force || echo "Initial migrations failed, continuing..."
fi

echo "Running cache optimizations..."
# Clear and rebuild caches
php artisan config:clear || echo "Config clear failed, continuing..."
php artisan route:clear || echo "Route clear failed, continuing..."
php artisan view:clear || echo "View clear failed, continuing..."
php artisan cache:clear || echo "Cache clear failed, continuing..."

echo "Post-startup tasks completed."